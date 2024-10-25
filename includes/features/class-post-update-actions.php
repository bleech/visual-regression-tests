<?php

namespace Vrts\Features;

use Vrts\Models\Test;
use Vrts\Models\Alert;
use Vrts\Services\Test_Service;

class Post_Update_Actions {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_after_insert_post', [ $this, 'resume_test' ] );
		add_action( 'trashed_post', [ $this, 'on_trash_post_action' ], 10, 2 );
		add_action( 'transition_post_status', [ $this, 'on_transition_post_status_action' ], 10, 3 );
		add_action( 'update_option_vrts_remaining_tests', [ $this, 'on_update_option_vrts_remaining_tests_action' ], 10, 2 );
		add_action( 'post_updated', [ $this, 'on_post_updated_action' ], 10, 3 );
	}

	/**
	 * Add meta boxes.
	 *
	 * @param int $post_id Post ID.
	 */
	public function resume_test( $post_id ) {
		// If post has test, update the screenshot to the latest version.
		if ( Test::get_item_id( $post_id ) ) {
			$service = new Test_Service();
			$service->resume_test( $post_id );
		}
	}

	/**
	 * Delete tests when post is trashed.
	 *
	 * @param int $post_id Post ID.
	 */
	public function on_trash_post_action( $post_id ) {
		// If trashed post has test, delete the test too.
		$test_id = Test::get_item_id( $post_id );
		if ( $test_id ) {
			Test::delete( $test_id );
			// If an alert exists already, archive it too.
			Alert::set_alert_state_for_post_id( $post_id, 1 );
		}
	}

	/**
	 * Create or delete test remotely when post is published or unpublished.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post Post object.
	 */
	public function on_transition_post_status_action( $new_status, $old_status, $post ) {
		// If post has test and no active alerts, update the screenshot to the latest version.
		$test = Test::get_item_by_post_id( $post->ID );
		if ( $test ) {
			if ( 'publish' === $new_status && 'publish' !== $old_status ) {
				$service = new Test_Service();
				$service->create_remote_test( $post, (array) $test );
			}
			if ( 'publish' === $old_status && 'publish' !== $new_status ) {
				$service = new Test_Service();
				$service->delete_remote_test( $test );
			}
		}
	}

	/**
	 * Resume tests when remaining tests option is updated.
	 *
	 * @param string $old_value Old option value.
	 * @param string $value New option value.
	 */
	public function on_update_option_vrts_remaining_tests_action( $old_value, $value ) {
		if ( intval( $old_value ) === 0 && intval( $value ) > 0 ) {
			$service = new Test_Service();
			$service->resume_stale_tests();
		}
	}

	/**
	 * Update test URL when post slug is updated.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post_after Post object after update.
	 * @param WP_Post $post_before Post object before update.
	 */
	public function on_post_updated_action( $post_id, $post_after, $post_before ) {
		$test = Test::get_item_by_post_id( $post_id );
		if ( $test ) {
			if ( $post_after->post_name !== $post_before->post_name ) {
				$service = new Service();
				$service->update_test( $test->service_test_id, [
					'url' => get_permalink( $post_id ),
				] );
			}
		}
	}
}
