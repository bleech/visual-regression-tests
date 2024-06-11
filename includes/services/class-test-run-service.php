<?php

namespace Vrts\Services;

use Vrts\Models\Test;
use Vrts\Models\Test_Run;

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

		if ( $test_run ) {
			if ( ! empty( $data['finished_at'] ?? null ) ) {
				$this->update_finished_run( $data, $test_run );
			} else {
				$update_data = [
					'started_at' => $data['started_at'],
					'finished_at' => $data['finished_at'],
					'scheduled_at' => $data['scheduled_at'],
				];
				if ($data['trigger'] === 'scheduled' && !empty($data['started_at'])) {
					$test_ids = array_map(function( $test ) {
						return $test->id;
					}, Test::get_by_service_test_ids($data['comparison_schedule_ids']) );
					$update_data['tests'] = maybe_serialize( $test_ids );
				}
				Test_Run::save( $update_data, $test_run->id );
			}

			return true;
		} else {
			$test_ids = array_map(function( $test ) {
				return $test->id;
			}, Test::get_by_service_test_ids($data['comparison_schedule_ids']) );

			Test_Run::save([
				'service_test_run_id' => $data['run_id'],
				'tests' => maybe_serialize( $test_ids ),
				'started_at' => $data['started_at'],
				'finished_at' => $data['finished_at'],
				'scheduled_at' => $data['scheduled_at'],
				'trigger' => 'scheduled',
			]);
			return true;
		}//end if
	}

	protected function update_finished_run( $data, $test_run ) {
		$alert_ids = [];

		foreach ($data['comparisons'] as $comparison) {
			$test_id = $comparison['comparison_schedule_id'];
			$alert_id = null;

			if ( $comparison['pixels_diff'] > 1 && ! $comparison['matches_false_positive'] ) {
				$post_id = Test::get_post_id_by_service_test_id( $test_id );
				$alert_service = new Alert_Service();
				$alert_id = $alert_service->create_alert_from_comparison( $post_id, $test_id, $comparison );
				$alert_ids[] = $alert_id;
			}//end if

			$test_service = new Test_Service();
			$test_service->update_test_from_comparison( $alert_id, $test_id, [
				'comparison' => $comparison
			] );
		}
		$update_data = [
			'alerts' => $alert_ids ? maybe_serialize( $alert_ids ) : null,
			'started_at' => $data['started_at'],
			'finished_at' => $data['finished_at'],
		];
		$tests = maybe_unserialize( $test_run->tests );
		if (empty($tests)) {
			$test_ids = array_map(function( $test ) {
				return $test->id;
			}, Test::get_by_service_test_ids($data['comparison_schedule_ids']) );
			$update_data['tests'] = maybe_serialize( $test_ids );
		}

		Test_Run::save( $update_data, $test_run->id );

		// TODO Send e-mail notification.
		// $email_notifications = new Email_Notifications();
		// $email_notifications->send_email( $comparison['pixels_diff'], $post_id, $alert_id );
		return true;
	}
}
