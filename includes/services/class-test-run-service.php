<?php

namespace Vrts\Services;

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
	 *
	 * @return boolean
	 */
	public function update_run_from_api_data( $data, $with_cleanup = true ) {
		$run_id = $data['run_id'];

		if ( empty( $run_id ) ) {
			return false;
		}

		$test_run = Test_Run::get_by_service_test_run_id( $run_id );

		$test_run_just_finished = false;

		if ( $test_run && empty( $test_run->finished_at ) && ! empty( $data['finished_at'] ) ) {
			$test_run_just_finished = true;
			$alert_ids = $this->update_tests_and_create_alerts( $data['comparisons'], $test_run );
		}

		$test_ids = empty( $data['comparison_schedule_ids'] ) ? [] : array_map(function ( $test ) {
			return [
				'id' => $test->id,
				'post_id' => $test->post_id,
				'post_title' => get_the_title( $test->post_id ),
				'permalink' => get_permalink( $test->post_id ),
			];
		}, Test::get_by_service_test_ids( $data['comparison_schedule_ids'] ));

		$test_run_id = $this->create_test_run( $data['run_id'], [
			'tests' => maybe_serialize( $test_ids ),
			'started_at' => $data['started_at'],
			'finished_at' => $data['finished_at'],
			'scheduled_at' => $data['scheduled_at'],
			'trigger' => $data['trigger'],
			'trigger_notes' => $data['trigger_notes'],
			'trigger_meta' => maybe_serialize( $data['trigger_meta'] ),
		], true, $with_cleanup );

		if ( $test_run_just_finished && ! empty( $alert_ids ) ) {
			$email_service = new Email_Service();
			$email_service->send_test_run_email( $test_run_id );
		}

		return true;
	}

	/**
	 * Update tests and create alerts.
	 *
	 * @param array  $comparisons Comparisons.
	 * @param object $test_run Test run.
	 *
	 * @return array
	 */
	protected function update_tests_and_create_alerts( $comparisons, $test_run ) {
		$alert_ids = [];

		foreach ( $comparisons as $comparison ) {
			$test_id = $comparison['comparison_schedule_id'];
			$alert_id = null;

			if ( $comparison['pixels_diff'] > 1 && ! $comparison['matches_false_positive'] ) {
				$post_id = Test::get_post_id_by_service_test_id( $test_id );
				$alert_service = new Alert_Service();
				$alert_id = $alert_service->create_alert_from_comparison( $post_id, $test_id, $comparison, $test_run );
				$alert_ids[] = $alert_id;
			}//end if

			$test_service = new Test_Service();
			$test_service->update_test_from_comparison( $alert_id, $test_id, [
				'comparison' => $comparison,
			] );
		}
		return $alert_ids;
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
				Test_Run::delete_by_service_test_run_id( $missing_test_run_id );
			}
		}
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
