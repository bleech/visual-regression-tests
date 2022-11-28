<?php

namespace Vrts\Features;

use Vrts\Models\Test;
use WP_Error;

class Post_Update_Actions {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'save_post', [ $this, 'on_save_post_action' ], 10, 2 );
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

		/* translators: %s: The id of the Post */
		return new WP_Error( 'test', sprintf( esc_html__( 'Testing errors with Post ID %s' ), $post_id ) );
	}
}
