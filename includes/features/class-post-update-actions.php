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
		add_action( 'save_post', [ $this, 'on_save_post_action' ], 10, 2 );
		add_action( 'trashed_post', [ $this, 'on_trash_post_action' ], 10, 2 );
		add_action( 'transition_post_status', [ $this, 'on_transition_post_status_action' ], 10, 3 );
	}

	/**
	 * Add meta boxes.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function on_save_post_action( $post_id, $post ) {
		// If post has test and no active alerts, update the screenshot to the latest version.
		if ( Test::get_item_id( $post_id ) && ! Test::has_post_alert( $post_id ) ) {
			Service::resume_test( $post_id );
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
			// If an alert exists already, resolve it too.
			$alert_id = Alert::get_alert_id_by_post_id( $post_id, 0 );
			if ( $alert_id ) {
				Alert::set_alert_state( $alert_id, 1 );
			}
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
}
