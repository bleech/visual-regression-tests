<?php

namespace Vrts\Services;

use Vrts\Core\Utilities\Url_Helpers;
use Vrts\Features\Service;
use Vrts\Features\Subscription;
use Vrts\Models\Alert;
use Vrts\Models\Test;
use Vrts\Models\Test_Run;
use Vrts\Services\Email_Service;

class Test_Run_Service {

	/**
	 * Create test from API data.
	 *
	 * @param array $data Data.
	 *
	 * @return boolean
	 */
	public function update_run_from_api_data( $data ) {
		$run_id = $data['run_id'];
		$test_run = Test_Run::get_by_service_test_run_id( $run_id );

		$test_run_just_finished = false;

		if ( $test_run && empty( $test_run->finished_at ) && ! empty( $data['finished_at'] ) ) {
			$test_run_just_finished = true;
			$alert_ids = $this->update_tests_and_create_alerts( $data['comparisons'], $test_run );
		}

		$test_ids = empty( $data['comparison_schedule_ids'] ) ? [] : array_map(function( $test ) {
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
		], true);

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
	 *
	 * @return boolean
	 */
	public function create_test_run( $service_test_run_id, $data, $update = false ) {
		$test_run = Test_Run::get_by_service_test_run_id( $service_test_run_id );

		if ( $test_run && ! $update ) {
			return false;
		}
		$test_run_id = Test_Run::save(array_merge( $data, [
			'service_test_run_id' => $service_test_run_id,
		]), $test_run->id ?? null);
		Test_Run::delete_duplicates();
		return $test_run_id;
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
					$this->update_run_from_api_data( $update );
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

	/**
	 * Update latest alert for all tests.
	 *
	 * @param object $test_run Test run.
	 *
	 * @return void
	 */
	public function update_latest_alert_for_all_tests( $test_run ) {
		$tests = maybe_unserialize( $test_run->tests );

		if ( ! empty( $tests ) ) {
			$service = new Test_Service();
			$service->update_latest_alerts( $tests );
		}
	}
}
