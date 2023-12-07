<?php

namespace Vrts\Features;

use Vrts\Services\Test_Service;

class Bulk_Actions {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', function() {
			$post_types = vrts()->get_public_post_types();

			foreach ( $post_types as $post_type ) {
				add_filter( 'bulk_actions-edit-' . $post_type, [ $this, 'register_bulk_test_option' ] );
				add_filter( 'handle_bulk_actions-edit-' . $post_type, [ $this, 'handle_bulk_optimize_action' ], 10, 3 );
			}
		} );
	}

	/**
	 * Register bulk optimize option.
	 *
	 * @param array $bulk_actions Bulk actions.
	 * @return array
	 */
	public function register_bulk_test_option( $bulk_actions ) {
		$bulk_actions['add-to-vrts'] = __( 'Add to VRTs', 'visual-regression-tests-service' );

		return $bulk_actions;
	}

	/**
	 * Handle bulk optimize action.
	 *
	 * @param string $redirect_to Redirect to.
	 * @param string $doaction Action.
	 * @param array  $post_ids Post ids.
	 * @return string
	 */
	public function handle_bulk_optimize_action( $redirect_to, $doaction, $post_ids ) {
		if ( 'add-to-vrts' !== $doaction ) {
			return $redirect_to;
		}

		$service = new Test_Service();
		$created_tests = $service->create_tests( $post_ids );
		$vrts_url = admin_url( 'admin.php?page=vrts' );

		if ( is_wp_error( $created_tests ) ) {
			$redirect_to = add_query_arg([
				'new-test-failed' => true,
			], $vrts_url);
		} else {
			$redirect_to = add_query_arg([
				'message' => 'success',
				'new-tests-added' => true,
				'post_ids' => wp_list_pluck( $created_tests, 'post_id' ),
			], $vrts_url);
		}

		return $redirect_to;
	}
}
