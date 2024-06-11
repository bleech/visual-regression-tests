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
					'is_running' => !empty($data['started_at'] && empty($data['finished_at']))
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
				'is_running' => !empty($data['started_at'] && empty($data['finished_at'])),
				'trigger' => 'scheduled',
			]);
			return true;
		}//end if
	}
}
