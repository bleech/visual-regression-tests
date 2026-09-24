<?php

namespace Vrts\Services;

use Vrts\Features\Cron_Jobs;
use Vrts\Features\Service;
use Vrts\Features\Subscription;
use Vrts\Models\Test;
use Vrts\Models\Test_Run;
use Vrts\Services\Email_Service;

class Test_Run_Service {

	/**
	 * Create test from API data.
	 *
	 * @param array $data Data.
	 * @param bool  $with_cleanup With cleanup.
	 * @param bool  $schedule_retry Schedule an exact retry when processing is incomplete.
	 *
	 * @return boolean
	 */
	public function update_run_from_api_data( $data, $with_cleanup = true, $schedule_retry = true ) {
		$run_id = $data['run_id'];

		if ( empty( $run_id ) ) {
			return false;
		}

		$test_run = Test_Run::get_by_service_test_run_id( $run_id );

		// Notifications can be delivered out of order by concurrent service
		// workers. Never let an older queued/running payload reopen a run that
		// has already been fully processed.
		if ( $test_run && ! empty( $test_run->finished_at ) && empty( $data['finished_at'] ) ) {
			return true;
		}

		// A run can be first seen already finished, e.g. when the hourly poll catches up
		// on a missed webhook — save the run first so its alerts can link to it.
		$test_run_just_finished = ! empty( $data['finished_at'] ) && ( ! $test_run || empty( $test_run->finished_at ) );

		$test_ids = empty( $data['comparison_schedule_ids'] ) ? [] : array_map(function ( $test ) {
			return [
				'id' => $test->id,
				'post_id' => $test->post_id,
				'post_title' => get_the_title( $test->post_id ),
				'permalink' => get_permalink( $test->post_id ),
			];
		}, Test::get_by_service_test_ids( $data['comparison_schedule_ids'] ));

		// The payload's schedule ids exclude failed comparisons, which never
		// get the per-comparison release — release them now, before they are
		// rewritten out of the run's local test list.
		if ( isset( $data['comparison_schedule_ids'] ) ) {
			$dropped_test_ids = array_diff(
				$this->get_local_test_ids( $test_run->tests ?? null ),
				array_column( $test_ids, 'id' )
			);
			Test::set_tests_running_by_ids( $dropped_test_ids, false );
		}

		// A manual trigger sets started_at locally before the service picks the
		// run up; a sync of the still-queued run must not null it out again.
		$started_at = ! empty( $data['started_at'] ) ? $data['started_at'] : ( $test_run->started_at ?? null );

		// finished_at is deliberately not saved here: it marks the run as fully
		// processed and is only set after its alerts exist, so a failure below
		// is retried by the next webhook or poll instead of being lost.
		$test_run_id = $this->create_test_run( $data['run_id'], [
			'tests' => maybe_serialize( $test_ids ),
			'started_at' => $started_at,
			'scheduled_at' => $data['scheduled_at'],
			'trigger' => $data['trigger'],
			'trigger_notes' => $data['trigger_notes'],
			'trigger_meta' => maybe_serialize( $data['trigger_meta'] ),
		], true, $with_cleanup );

		if ( ! $test_run_id ) {
			return false;
		}

		if ( $test_run_just_finished ) {
			$result = $this->update_tests_and_create_alerts( $data['comparisons'] ?? [], Test_Run::get_item( $test_run_id ) );

			// Keep the run unfinished until every comparison was processed, and
			// retry this exact service run rather than relying on the moving
			// project-updates cursor.
			if ( ! $result['complete'] ) {
				if ( $schedule_retry ) {
					Cron_Jobs::schedule_initial_fetch_test_run_updates( $test_run_id );
				}
				return false;
			}

			$marked_finished = Test_Run::mark_finished( $test_run_id, $data['finished_at'] );

			// A concurrent callback may have won the finish transition. That is
			// successful; only the winner sends the notification email.
			if ( ! $marked_finished ) {
				$current_test_run = Test_Run::get_item( $test_run_id );
				// A deleted run (e.g. cleaned up as empty) needs no retry.
				if ( $current_test_run && empty( $current_test_run->finished_at ) ) {
					if ( $schedule_retry ) {
						Cron_Jobs::schedule_initial_fetch_test_run_updates( $test_run_id );
					}
					return false;
				}
			}

			if ( $marked_finished && ! empty( $result['alert_ids'] ) ) {
				$email_service = new Email_Service();
				$email_service->send_test_run_email( $test_run_id );
			}
		}//end if

		return true;
	}

	/**
	 * Get the local test ids stored on a test run.
	 *
	 * @param mixed $tests The serialized tests value.
	 *
	 * @return array
	 */
	protected function get_local_test_ids( $tests ) {
		$tests = maybe_unserialize( $tests );

		return array_filter( array_map( function ( $test ) {
			return is_array( $test ) ? ( $test['id'] ?? null ) : $test;
		}, is_array( $tests ) ? $tests : [] ) );
	}

	/**
	 * Update tests and create alerts.
	 *
	 * @param array  $comparisons Comparisons.
	 * @param object $test_run Test run.
	 *
	 * @return array The created alert ids and whether every expected alert was created.
	 */
	protected function update_tests_and_create_alerts( $comparisons, $test_run ) {
		$alert_ids = [];
		$complete = true;

		foreach ( $comparisons as $comparison ) {
			$test_id = $comparison['comparison_schedule_id'];
			$alert_id = null;

			if ( Alert_Service::should_alert( $test_id, $comparison ) ) {
				$post_id = Test::get_post_id_by_service_test_id( $test_id );
				$alert_service = new Alert_Service();
				$alert_id = $alert_service->create_alert_from_comparison( $post_id, $test_id, $comparison, $test_run );

				if ( $alert_id ) {
					$alert_ids[] = $alert_id;
				} else {
					$complete = false;
				}
			}//end if

			$test_service = new Test_Service();
			$updated = $test_service->update_test_from_comparison( $alert_id, $test_id, [
				'comparison' => $comparison,
			] );

			if ( false === $updated ) {
				$complete = false;
			}
		}//end foreach
		return [
			'alert_ids' => $alert_ids,
			'complete' => $complete,
		];
	}

	/**
	 * Create test run.
	 *
	 * @param string $service_test_run_id Service test run id.
	 * @param array  $data Data.
	 * @param bool   $update Update.
	 * @param bool   $with_cleanup With cleanup.
	 *
	 * @return boolean
	 */
	public function create_test_run( $service_test_run_id, $data, $update = false, $with_cleanup = true ) {
		$test_run = Test_Run::get_by_service_test_run_id( $service_test_run_id );

		if ( $test_run && ! $update ) {
			return false;
		}
		$test_run_id = Test_Run::save(array_merge( $data, [
			'service_test_run_id' => $service_test_run_id,
		]), $test_run->id ?? null);
		if ( $with_cleanup ) {
			Test_Run::delete_duplicates();
			Test_Run::delete_empty();
			$this->check_stalled_test_runs();
		}
		return $test_run_id;
	}

	/**
	 * Delete a test run and release its tests from the running state.
	 *
	 * @param string $service_test_run_id Service test run id.
	 *
	 * @return boolean
	 */
	public function delete_test_run( $service_test_run_id ) {
		$test_run = Test_Run::get_by_service_test_run_id( $service_test_run_id );
		$deleted = Test_Run::delete_by_service_test_run_id( $service_test_run_id );

		if ( $deleted && $test_run ) {
			Test::set_tests_running_by_ids( $this->get_local_test_ids( $test_run->tests ), false );
		}

		return $deleted;
	}

	/**
	 * Check stalled test runs.
	 *
	 * @return void
	 */
	public function check_stalled_test_runs() {
		$test_run_ids = array_column( Test_Run::get_stalled_test_run_ids(), 'service_test_run_id' );
		if ( empty( $test_run_ids ) ) {
			return;
		}
		$response = Service::fetch_test_runs( $test_run_ids );
		if ( 200 === $response['status_code'] ) {
			$test_runs = $response['response']['data'] ?? [];
			foreach ( $test_runs as $test_run ) {
				$this->update_run_from_api_data( $test_run, false );
			}
			$missing_test_run_ids = array_diff( $test_run_ids, array_column( $test_runs, 'run_id' ) );
			foreach ( $missing_test_run_ids as $missing_test_run_id ) {
				$this->delete_test_run( $missing_test_run_id );
			}
		}
	}

	/**
	 * Fetch and update one exact test run.
	 *
	 * @param string $service_test_run_id Service test run id.
	 *
	 * @return bool Whether the run was successfully synchronized.
	 */
	public function fetch_and_update_test_run( $service_test_run_id ) {
		$response = Service::fetch_test_runs( [ $service_test_run_id ] );

		if ( 200 !== $response['status_code'] ) {
			return false;
		}

		if ( ! isset( $response['response']['data'] ) || ! is_array( $response['response']['data'] ) ) {
			return false;
		}

		$test_runs = $response['response']['data'];
		foreach ( $test_runs as $test_run ) {
			if ( ( $test_run['run_id'] ?? null ) === $service_test_run_id ) {
				return $this->update_run_from_api_data( $test_run, false, false );
			}
		}

		return $this->delete_test_run( $service_test_run_id );
	}

	/**
	 * Fetch and update tests.
	 *
	 * @return void
	 */
	public function fetch_and_update_test_runs() {
		$service_request = Service::fetch_updates();
		if ( 200 === $service_request['status_code'] ) {
			$response = $service_request['response'];
			if ( array_key_exists( 'run_updates', $response ) ) {
				$updates = $response['run_updates'];
				foreach ( $updates as $update ) {
					$this->update_run_from_api_data( $update, false );
				}
			}
			if (
				array_key_exists( 'remaining_credits', $response )
				&& array_key_exists( 'total_credits', $response )
				&& array_key_exists( 'has_subscription', $response )
				&& array_key_exists( 'tier_id', $response )
			) {
				Subscription::update_available_tests( $response['remaining_credits'], $response['total_credits'], $response['has_subscription'], $response['tier_id'] );
			}
		}
	}
}
