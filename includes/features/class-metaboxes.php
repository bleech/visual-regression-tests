<?php

namespace Vrts\Features;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Models\Test;
use WP_Error;

class Metaboxes {

	/**
	 * Field key for the "Run Tests" checkbox post meta.
	 * _ (underscore prefix) represents a protected meta key.
	 *
	 * @var string
	 */
	public static $field_test_status_key = '_vrts_testing_status';

	/**
	 * Field key for the "is new test" post meta.
	 * _ (underscore prefix) represents a protected meta key.
	 *
	 * @var string
	 */
	public static $field_is_new_test_key = '_vrts_is_new_test';

	/**
	 * Nonce.
	 *
	 * @var string
	 */
	protected $nonce = 'vrts_metabox_nonce';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'init', [ $this, 'register_post_meta_fields' ] );
		add_action( 'save_post', [ $this, 'save_meta_boxes_data' ], 10, 2 );
		add_action( 'wp_loaded', [ $this, 'register_publish_hooks' ], 1 );
	}

	/**
	 * Get the value of the static key.
	 */
	public static function get_post_meta_key_status() {
		return self::$field_test_status_key;
	}

	/**
	 * Get the value of the static key.
	 */
	public static function get_post_meta_key_is_new_test() {
		return self::$field_is_new_test_key;
	}

	/**
	 * Is new test.
	 *
	 * @param int $post_id WP Post id.
	 */
	public static function is_new_test( $post_id ) {
		$test_added_show_notice = (bool) get_post_meta( $post_id, self::$field_is_new_test_key, true );

		if ( true === $test_added_show_notice ) {
			delete_post_meta(
				$post_id,
				self::$field_is_new_test_key
			);
		}

		return $test_added_show_notice;
	}

	/**
	 * Add meta boxes.
	 */
	public function add_meta_boxes() {
		if ( current_user_can( 'manage_options' ) ) {
			$custom_post_types = get_post_types([
				'public' => true,
				'_builtin' => false,
			]);

			add_meta_box(
				'vrts_post_options_metabox',
				vrts()->get_plugin_info( 'name' ),
				[ $this, 'render_metabox' ],
				array_merge( [ 'post', 'page' ], $custom_post_types ),
				'side',
				'default',
				[ '__back_compat_meta_box' => true ]
			);
		}
	}

	/**
	 * Render meta box.
	 */
	public function render_metabox() {
		global $post;
		$post_id = $post->ID ? $post->ID : 0;
		$post_custom_keys = get_post_custom_keys( $post_id );
		$run_tests_checked = is_array( $post_custom_keys ) && in_array( self::$field_test_status_key, $post_custom_keys, true ) ? intval( get_post_meta( $post_id, self::$field_test_status_key, true ) ) : 0;
		$snapshot_date = Test::get_snapshot_date( $post_id );
		if ( $snapshot_date ) {
			$snapshot_date = Date_Time_Helpers::get_formatted_date_time( $snapshot_date );
		}

		$alert_id = Test::get_alert_id( $post_id );
		$testing_status_instructions = '';
		if ( $alert_id ) {
			$base_link = admin_url( 'admin.php?page=vrts-alerts&action=edit&alert_id=' );
			$testing_status_instructions .= sprintf(
				/* translators: %1$s and %2$s: link wrapper. */
				esc_html__( 'Resolve %1$salert%2$s to resume testing', 'visual-regression-tests' ),
				'<a href="' . esc_url( $base_link . $alert_id ) . '">',
				'</a>'
			);
		}

		vrts()->component('metabox-options', [
			'post_id' => $post_id,
			'nonce' => $this->nonce,
			'run_tests_checked' => $run_tests_checked,
			'field_test_status_key' => self::$field_test_status_key,
			'has_post_alert' => Test::has_post_alert( $post_id ),
			'target_screenshot_url' => Test::get_target_screenshot_url( $post_id ),
			'snapshot_date' => $snapshot_date,
			'testing_status_instructions' => $testing_status_instructions,
			'placeholder_image_data_url' => vrts()->get_snapshot_placeholder_image(),
			'is_new_test' => self::is_new_test( $post_id ),
			'remaining_tests' => Subscription::get_remaining_tests(),
			'total_tests' => Subscription::get_total_tests(),
		]);
	}

	/**
	 * Register post meta for REST API to use its data on gutenberg and classic editor.
	 */
	public function register_post_meta_fields() {
		register_meta(
			'post',
			$this->get_post_meta_key_status(),
			[
				'show_in_rest' => true,
				'type' => 'boolean',
				'single' => true,
				'default' => 0,
				'sanitize_callback' => 'sanitize_boolean',
				'auth_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_meta(
			'post',
			$this->get_post_meta_key_is_new_test(),
			[
				'show_in_rest' => true,
				'type' => 'boolean',
				'single' => true,
				'default' => 0,
				'sanitize_callback' => 'sanitize_boolean',
				'auth_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

	}

	/**
	 * Add meta boxes.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	public function save_meta_boxes_data( $post_id, $post ) {
		$edit_cap = get_post_type_object( $post->post_type )->cap->edit_post;

		if ( ! current_user_can( $edit_cap, $post_id ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ $this->nonce ] ?? '' ) );

		// Verify nonce. Only valid when using classic editor.
		if ( ! wp_verify_nonce( $nonce, $this->nonce ) ) {
			return;
		}

		// Save "Run Tests" checkbox value to post meta.
		if ( array_key_exists( self::$field_test_status_key, $_POST ) && '1' === $_POST[ self::$field_test_status_key ] ) {
			update_post_meta(
				$post_id,
				self::$field_test_status_key,
				intval( $_POST[ self::$field_test_status_key ] )
			);

			update_post_meta(
				$post_id,
				self::$field_is_new_test_key,
				1
			);
		} else {
			// Delete data from tests database table if "Run Tests" checkbox is not checked.
			Test::delete( $post_id );
		}
	}


	/**
	 * Registers publish hooks against all public Post Types.
	 */
	public function register_publish_hooks() {
		add_action( 'transition_post_status', [ $this, 'transition_post_status' ], 10, 3 );
	}

	/**
	 * Fired when a Post's status transitions.
	 *
	 * Called by WordPress when wp_insert_post() is called.
	 *
	 * As wp_insert_post() is called by WordPress and the REST API whenever creating or updating a Post,
	 * we can safely rely on this hook.
	 *
	 * @param string  $new_status New Status.
	 * @param string  $old_status Old Status.
	 * @param WP_Post $post Post.
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {

		// Bail if the Post Type isn't public or post is revision.
		// This prevents the rest of this routine running on e.g. ACF Free, when saving Fields (which results in Field loss).
		$custom_post_types = get_post_types([
			'public' => true,
			'_builtin' => false,
		]);
		$post_types = array_merge( [ 'post', 'page' ], $custom_post_types );
		if ( ! in_array( $post->post_type, $post_types, true ) ) {
			return;
		}

		// Bail if we're working on a draft or trashed item.
		// TODO: Update Status of trashed posts to paused.
		if ( 'auto-draft' === $new_status || 'draft' === $new_status || 'inherit' === $new_status || 'trash' === $new_status ) {
			return;
		}

		/**
		* = REST API =
		* If this is a REST API Request, we can't use the wp_insert_post action, because any metadata
		* included in the REST API request is *not* included in the call to wp_insert_post().
		*
		* Instead, we must use a late REST API action that gives the REST API time to save metadata.
		*
		* Thankfully, the REST API supplies an action to do this: rest_after_insert_posttype, where posttype
		* is the Post Type in question.
		*
		* Note that any meta being supplied in the REST API Request MUST be registered with WordPress using
		* register_meta(). If you're using a third party plugin to register custom fields, you'll need to
		* confirm it uses register_meta() as part of its process.
		*
		* = Gutenberg =
		* If Gutenberg is being used on the given Post Type, two requests are sent:
		* - a REST API request, comprising of Post Data and Metadata registered *in* Gutenberg,
		* - a standard request, comprising of Post Metadata registered *outside* of Gutenberg (i.e. add_meta_box() data)
		*
		* If we're publishing a Post, the second request will be seen by transition_post_status() as an update, which
		* isn't strictly true.
		*
		* Therefore, we set a meta flag on the first Gutenberg REST API request to defer acting on the Post until
		* the second, standard request - at which point, all Post metadata will be available to the Plugin.
		*
		* = Classic Editor =
		* Metadata is included in the call to wp_insert_post(), meaning that it's saved to the Post before we use it.
		*/

		// Flag to determine if the current Post is a Gutenberg Post.
		$is_gutenberg_post = $this->is_gutenberg_post( $post );

		// If a previous request flagged that an 'update' request should be treated as a publish request (i.e.
		// we're using Gutenberg and request to post.php was made after the REST API), do this now.
		$needs_publishing = get_post_meta( $post->ID, '_needs_publishing', true );
		if ( $needs_publishing ) {
			// Run Publish Status Action now.
			delete_post_meta( $post->ID, '_needs_publishing' );
			add_action( 'wp_insert_post', [ $this, 'wp_insert_post_publish' ], 999 );

			// Don't need to do anything else, so exit.
			return;
		}

		// If a previous request flagged that an update request be deferred (i.e.
		// we're using Gutenberg and request to post.php was made after the REST API), do this now.
		$needs_updating = get_post_meta( $post->ID, '_needs_updating', true );
		if ( $needs_updating ) {
			// Run Publish Status Action now.
			delete_post_meta( $post->ID, '_needs_updating' );
			add_action( 'wp_insert_post', [ $this, 'wp_insert_post_update' ], 999 );

			// Don't need to do anything else, so exit.
			return;
		}

		// Publish.
		if ( 'publish' === $new_status && $new_status !== $old_status ) {
			/**
			* Classic Editor
			*/
			if ( ! defined( 'REST_REQUEST' ) || ( defined( 'REST_REQUEST' ) && ! REST_REQUEST ) ) {
				add_action( 'wp_insert_post', [ $this, 'wp_insert_post_publish' ], 999 );

				// Don't need to do anything else, so exit.
				return;
			}

			/**
			* Gutenberg Editor
			* - Non-Gutenberg metaboxes are POSTed via a second, separate request to post.php, which appears
			* as an 'update'. Define a meta key that we'll check on the separate request later.
			*/
			if ( $is_gutenberg_post ) {
				update_post_meta( $post->ID, '_needs_publishing', 1 );
				// Don't need to do anything else, so exit.
				return;
			}

			/**
			* REST API
			*/
			add_action( 'rest_after_insert_' . $post->post_type, [ $this, 'rest_api_post_publish' ], 10, 2 );

			// Don't need to do anything else, so exit.
			return;
		}//end if

		// Update.
		if ( 'publish' === $new_status && 'publish' === $old_status ) {
			/**
			* Classic Editor
			*/
			if ( ! defined( 'REST_REQUEST' ) || ( defined( 'REST_REQUEST' ) && ! REST_REQUEST ) ) {
				add_action( 'wp_insert_post', [ $this, 'wp_insert_post_update' ], 999 );

				// Don't need to do anything else, so exit.
				return;
			}

			/**
			* Gutenberg Editor
			* - Non-Gutenberg metaboxes are POSTed via a second, separate request to post.php, which appears
			* as an 'update'. Define a meta key that we'll check on the separate request later.
			*/
			if ( $is_gutenberg_post ) {
				update_post_meta( $post->ID, '_needs_updating', 1 );
				// Don't need to do anything else, so exit.
				return;
			}

			/**
			* REST API
			*/
			add_action( 'rest_after_insert_' . $post->post_type, [ $this, 'rest_api_post_update' ], 10, 2 );

			// Don't need to do anything else, so exit.
			return;
		}//end if
	}

	/**
	 * Helper function to determine if the Post is using the Gutenberg Editor.
	 *
	 * @param WP_Post $post Post.
	 * @return bool Post uses Gutenberg Editor.
	 */
	private function is_gutenberg_post( $post ) {

		// This will fail if a Post is created or updated with no content and only a title.
		if ( strpos( $post->post_content, '<!-- wp:' ) === false ) {
			return false;
		}

		return true;

	}

	/**
	 * Called when a Post has been Published via the REST API
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post         $post Post.
	 * @param WP_REST_Request $request Request Object.
	 */
	public function rest_api_post_publish( $post, $request ) {
		$this->wp_insert_post_publish( $post->ID );
	}

	/**
	 * Called when a Post has been Published via the REST API
	 *
	 * @param WP_Post         $post Post.
	 * @param WP_REST_Request $request Request Object.
	 */
	public function rest_api_post_update( $post, $request ) {
		$this->wp_insert_post_update( $post->ID );
	}

	/**
	 * Called when a Post has been Published
	 *
	 * @param int $post_id Post ID.
	 */
	public function wp_insert_post_publish( $post_id ) {
		// Call main function.
		$this->send( $post_id, 'publish' );
	}

	/**
	 * Called when a Post has been Updated
	 *
	 * @param int $post_id Post ID.
	 */
	public function wp_insert_post_update( $post_id ) {
		// Call main function.
		$this->send( $post_id, 'update' );
	}

	/**
	 * Main function. Called when any Page or Post is published or updated.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $action Action (publish|update).
	 * @return mixed WP_Error | API Results array.
	 */
	public function send( $post_id, $action ) {
		if ( ! $action ) {
			return;
		}

		// Get Post.
		$post = get_post( $post_id );

		if ( ! $post ) {
			/* translators: %s: The id of the Post */
			return new WP_Error( 'no_post', sprintf( esc_html__( 'No WordPress Post could be found for Post ID %s' ), $post_id ) );
		}

		// Bail if we're working on a auto-draft or inherit item.
		// TODO: Update Status of trashed posts to paused.
		$post_status = $post->post_status;
		if ( 'auto-draft' === $post_status || 'draft' === $post_status || 'inherit' === $post_status || 'trash' === $post_status ) {
			return;
		}

		// Preparing status.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's ok.
		$classic_editor_request = sanitize_text_field( wp_unslash( $_REQUEST['classic-editor'] ?? false ) );

		if ( $classic_editor_request ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- It's ok.
			$status = intval( $_POST[ self::$field_test_status_key ] ?? 0 );
		} else {
			// Gutenberg already saved the meta and don't send $_POST.
			if ( get_post_meta( $post_id, self::$field_test_status_key, true ) ) {
				$field_status_value = get_post_meta( $post_id, self::$field_test_status_key, true );
				$status = intval( $field_status_value ) === 1 ? 1 : 0;
			} else {
				$status = 0;
			}
		}

		// $status = "Run Tests" checkbox value.
		if ( 1 === $status ) {
			$current_test_id = Test::get_item_id( $post_id );

			// Save data to custom database table if test doesn't exist yet.
			if ( ! $current_test_id ) {
				// Set args.
				$args = [
					'id' => Test::get_item_id( $post_id ),
					'post_id' => $post_id,
					'status' => intval( $status ),
				];
				// Save data to custom database table.
				Test::save( $args );
			}

			// Required for metabox "new test added" notification.
			update_post_meta(
				$post_id,
				self::$field_is_new_test_key,
				1
			);
		} elseif ( 0 === $status ) {
			// Delete data from tests database table if "Run Tests" checkbox is not checked.
			Test::delete( $post_id );
		}//end if

	}

	/**
	 * Delete post meta keys.
	 */
	public static function delete_meta_keys() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		$wpdb->delete(
			$wpdb->postmeta,
			[
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- TODO: Check later
				'meta_key' => self::get_post_meta_key_status(),
			]
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- It's ok.
		$wpdb->delete(
			$wpdb->postmeta,
			[
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- TODO: Check later
				'meta_key' => self::get_post_meta_key_is_new_test(),
			]
		);
	}
}
