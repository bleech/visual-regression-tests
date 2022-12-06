<?php

namespace Vrts\Features;

use Vrts\Models\Test;
use Vrts\Models\Alert;
use WP_Error;

class Post_Update_Actions {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'save_post', [ $this, 'on_save_post_action' ], 10, 2 );
		add_action( 'trashed_post', [ $this, 'on_trash_post_action' ], 10, 2 );
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
		if ( Test::get_item_id( $post_id ) ) {
			Test::delete( $post_id );
			// If an alert exists already, resolve it too.
			$alert_id = Alert::get_alert_id_by_post_id( $post_id, 0 );
			if ( $alert_id ) {
				Alert::set_alert_state( $alert_id, 1 );
			}
		}
	}
}
