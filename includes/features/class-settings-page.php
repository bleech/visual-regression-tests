<?php

namespace Vrts\Features;

use Vrts\Core\Utilities\Sanitization;
use Vrts\Features\Subscription;

class Settings_Page {
	/**
	 * Page slug.
	 *
	 * @var string
	 */
	protected $page_slug = 'vrts-settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
		add_action( 'admin_init', [ $this, 'settings_migration' ] );
		add_action( 'add_option_vrts_click_selectors', [ $this, 'do_after_update_click_selectors' ], 10, 2 );
		add_action( 'update_option_vrts_click_selectors', [ $this, 'do_after_update_click_selectors' ], 10, 2 );
		add_action( 'pre_update_option_vrts_license_key', [ $this, 'do_before_add_license_key' ], 10, 2 );
		add_action( 'pre_update_option_vrts_email_update_notification_address', [ $this, 'do_before_updating_email_address' ], 10 );
		add_action( 'pre_update_option_vrts_email_api_notification_address', [ $this, 'do_before_updating_email_address' ], 10 );
		add_action( 'update_option_vrts_automatic_comparison', [ $this, 'do_after_update_vrts_automatic_comparison' ], 10, 2 );

		$this->add_settings();
	}

	/**
	 * Add submenu.
	 */
	public function add_submenu_page() {
		$submenu_page = add_submenu_page(
			'vrts',
			esc_html__( 'Settings', 'visual-regression-tests' ),
			esc_html__( 'Settings', 'visual-regression-tests' ),
			'manage_options',
			$this->page_slug,
			[ $this, 'render_page' ]
		);

		add_action( 'load-' . $submenu_page, [ $this, 'init_notifications' ] );
	}

	/**
	 * Render settings page.
	 */
	public function render_page() {
		vrts()->component( 'settings-page', [
			'title' => esc_html__( 'Settings', 'visual-regression-tests' ),
			'settings_fields' => $this->page_slug,
			'settings_sections' => $this->page_slug,
		] );
	}

	/**
	 * Register settings.
	 */
	public function add_settings() {
		$has_subscription = (bool) Subscription::get_subscription_status();

		vrts()->settings()->add_section([
			'id' => 'vrts-settings-section-general',
			'page' => $this->page_slug,
			'title' => '',
		]);

		// Notice:
		// value_type can be one of these 'string', 'boolean', 'integer', 'number', 'array', and 'object'.
		// sanitize_callback can be a default wp sanitize function or a custom function from the Sanitization class.
		// 'sanitize_callback' => '[ Sanitization::class, 'sanitize_checkbox' ]'.

		vrts()->settings()->add_setting([
			'type' => 'text',
			'id' => 'vrts_click_selectors',
			'section' => 'vrts-settings-section-general',
			'title' => esc_html__( 'Click Element', 'visual-regression-tests' ),
			'description' => sprintf(
				'%s<br>%s',
				sprintf(
					/* translators: %s: link wrapper. */
					esc_html__( 'Add a %1$sCSS selector%2$s to click on the first element found before creating a new snapshot.', 'visual-regression-tests' ),
					'<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Selectors" target="_blank">',
					'</a>'
				),
				esc_html__( 'Useful to accept cookie banners or anything else that should be clicked after page load.', 'visual-regression-tests' )
			),
			'placeholder' => esc_html__( 'e.g.: #accept-cookies', 'visual-regression-tests' ),
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest' => true,
			'value_type' => 'string',
			'default' => '',
		]);

		vrts()->settings()->add_setting([
			'type' => 'text',
			'id' => 'vrts_license_key',
			'section' => 'vrts-settings-section-general',
			'title' => esc_html__( 'License Key', 'visual-regression-tests' ),
			'description' => sprintf(
				'%1$s <a href="%2$s" title="%3$s">%3$s</a>',
				esc_html__( 'No license key yet?', 'visual-regression-tests' ),
				esc_url( admin_url( 'admin.php?page=vrts-upgrade' ) ),
				esc_html__( 'Upgrade here.', 'visual-regression-tests' )
			),
			'placeholder' => esc_html_x( 'XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX', 'license key placeholder', 'visual-regression-tests' ),
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest' => true,
			'value_type' => 'string',
			'default' => '',
		]);

		vrts()->settings()->add_section([
			'id' => 'vrts-settings-section-triggers',
			'page' => $this->page_slug,
			'title' => 'Triggers',
			'title' => esc_html__( 'Triggers', 'visual-regression-tests' ) . '<span>' . esc_html__( 'Define when tests should run.', 'visual-regression-tests' ) . '</span>',
		]);

		vrts()->settings()->add_setting([
			'type' => 'checkbox',
			'id' => 'vrts_automatic_comparison',
			'section' => 'vrts-settings-section-triggers',
			'title' => esc_html__( 'Schedule', 'visual-regression-tests' ),
			'label' => esc_html__( 'Run Tests every 24 hours.', 'visual-regression-tests' ),
			'sanitize_callback' => [ Sanitization::class, 'sanitize_checkbox' ],
			'show_in_rest' => true,
			'value_type' => 'boolean',
			'value' => 1,
			'default' => 1,
			'disabled' => 1,
		]);

		vrts()->settings()->add_setting([
			'type' => 'checkbox',
			'id' => 'vrts_updates_comparison',
			'section' => 'vrts-settings-section-triggers',
			'title' => esc_html__( 'Update', 'visual-regression-tests' ),
			'label' => esc_html__( 'Run Tests after WordPress and plugin updates.', 'visual-regression-tests' ),
			'sanitize_callback' => [ Sanitization::class, 'sanitize_checkbox' ],
			'show_in_rest' => true,
			'value_type' => 'boolean',
			'value' => $has_subscription,
			'default' => $has_subscription,
			'disabled' => $has_subscription,
			'readonly' => ! $has_subscription,
			'is_pro' => $has_subscription,
		]);

		vrts()->settings()->add_setting([
			'type' => 'checkbox',
			'id' => 'vrts_api_comparison',
			'section' => 'vrts-settings-section-triggers',
			'title' => esc_html__( 'API', 'visual-regression-tests' ),
			'label' => sprintf(
				/* translators: %1$s, %2$s: link wrapper. */
				wp_kses_post( __( 'Run Tests with your favorite apps. %1$sRead the docs%2$s.', 'visual-regression-tests' ) ),
				'<a href="' . esc_url( 'https://vrts.app/' ) . '" target="_blank">',
				'</a>'
			),
			'sanitize_callback' => [ Sanitization::class, 'sanitize_checkbox' ],
			'show_in_rest' => true,
			'value_type' => 'boolean',
			'value' => $has_subscription,
			'default' => $has_subscription,
			'disabled' => $has_subscription,
			'readonly' => ! $has_subscription,
			'is_pro' => $has_subscription,
		]);

		vrts()->settings()->add_setting([
			'type' => 'checkbox',
			'id' => 'vrts_manual_comparison',
			'section' => 'vrts-settings-section-triggers',
			'title' => esc_html__( 'Manual', 'visual-regression-tests' ),
			'label' => esc_html__( 'Run Tests on demand.', 'visual-regression-tests' ),
			'sanitize_callback' => [ Sanitization::class, 'sanitize_checkbox' ],
			'show_in_rest' => true,
			'value_type' => 'boolean',
			'value' => $has_subscription,
			'default' => $has_subscription,
			'disabled' => $has_subscription,
			'readonly' => ! $has_subscription,
			'is_pro' => $has_subscription,
		]);

		vrts()->settings()->add_section([
			'id' => 'vrts-settings-section-notifications',
			'page' => $this->page_slug,
			'title' => esc_html__( 'Notifications', 'visual-regression-tests' ) . '<span>' . esc_html__( 'Notify team members based on specific trigger events.', 'visual-regression-tests' ) . '</span>',
		]);

		vrts()->settings()->add_setting([
			'type' => 'text',
			'id' => 'vrts_email_notification_address',
			'section' => 'vrts-settings-section-notifications',
			'title' => esc_html__( 'Schedule', 'visual-regression-tests' ),
			'description' => esc_html__( 'Separate multiple email addresses with commas. Or leave blank to disable notifications.', 'visual-regression-tests' ),
			'placeholder' => esc_html__( 'Email address(es)', 'visual-regression-tests' ),
			'sanitize_callback' => [ Sanitization::class, 'sanitize_multiple_emails' ],
			'show_in_rest' => true,
			'value_type' => 'string',
			'default' => get_bloginfo( 'admin_email' ),
			'return_value_callback' => [ $this, 'get_sanitized_emails' ],
		]);

		vrts()->settings()->add_setting([
			'type' => 'text',
			'id' => 'vrts_email_update_notification_address',
			'section' => 'vrts-settings-section-notifications',
			'title' => esc_html__( 'Update', 'visual-regression-tests' ),
			'placeholder' => esc_html__( 'Email address(es)', 'visual-regression-tests' ),
			'sanitize_callback' => [ Sanitization::class, 'sanitize_multiple_emails' ],
			'show_in_rest' => true,
			'value_type' => 'string',
			'default' => '',
			'readonly' => ! $has_subscription,
			'is_pro' => $has_subscription,
			'return_value_callback' => [ $this, 'get_sanitized_emails' ],
		]);

		vrts()->settings()->add_setting([
			'type' => 'text',
			'id' => 'vrts_email_api_notification_address',
			'section' => 'vrts-settings-section-notifications',
			'title' => esc_html__( 'API', 'visual-regression-tests' ),
			'placeholder' => esc_html__( 'Email address(es)', 'visual-regression-tests' ),
			'sanitize_callback' => [ Sanitization::class, 'sanitize_multiple_emails' ],
			'show_in_rest' => true,
			'value_type' => 'string',
			'default' => '',
			'readonly' => ! $has_subscription,
			'is_pro' => $has_subscription,
			'return_value_callback' => [ $this, 'get_sanitized_emails' ],
		]);

		vrts()->settings()->add_setting([
			'type' => 'info',
			'id' => 'vrts_email_manual_notification_address',
			'section' => 'vrts-settings-section-notifications',
			'title' => esc_html__( 'Manul', 'visual-regression-tests' ),
			'description' => esc_html__( 'Alerts are automatically sent to the user who triggers the manual test.', 'visual-regression-tests' ),
			'is_pro' => $has_subscription,
		]);
	}

	/**
	 * Settings migration.
	 */
	public function settings_migration() {
		$old_cc_addresses = vrts()->settings()->get_option( 'vrts_email_notification_cc_address' );

		if ( $old_cc_addresses ) {
			$old_cc_addresses = $this->get_sanitized_emails( $old_cc_addresses );
			$schedule_email = vrts()->settings()->get_option( 'vrts_email_notification_address' );
			$schedule_email = array_unique( array_merge( $schedule_email, $old_cc_addresses ) );
			$schedule_email = implode( ', ', $schedule_email );
			update_option( 'vrts_email_notification_address', $schedule_email );
			delete_option( 'vrts_email_notification_cc_address' );
		}

		if ( get_option( 'vrts_license_success' ) ) {
			$schedule_email = vrts()->settings()->get_option( 'vrts_email_notification_address', false );
			update_option( 'vrts_email_update_notification_address', $schedule_email );
			update_option( 'vrts_email_api_notification_address', $schedule_email );
		}
	}

	/**
	 * Update global click selector settings for project in service
	 *
	 * @param string $old Old value.
	 * @param string $new New value.
	 */
	public function do_after_update_click_selectors( $old, $new ) {
		if ( $old !== $new ) {
			$service_project_id = get_option( 'vrts_project_id' );
			$service_api_route = 'sites/' . $service_project_id;

			$parameters = [
				'screenshot_options' => [
					'clickSelectors'   => $new,
				],
			];

			$response = Service::rest_service_request( $service_api_route, $parameters, 'put' );
		}
	}

	/**
	 * Register the Gumroad API key with the service.
	 *
	 *  @param mixed $new new value.
	 *  @param mixed $old old value.
	 */
	public function do_before_add_license_key( $new, $old ) {
		// If license key is empty but was previously added.
		if ( ! $new && $old ) {
			self::remove_license_key();
			update_option( 'vrts_license_failed', true );
			return $new;
		}

		if ( $old !== $new ) {

			$service_project_id = get_option( 'vrts_project_id' );
			$service_api_route = 'sites/' . $service_project_id . '/register';

			$parameters = [
				'license_key'   => $new,
			];

			$response = Service::rest_service_request( $service_api_route, $parameters, 'post' );
			$status_code = $response['status_code'];
			Subscription::get_latest_status();

			if ( 200 !== $status_code ) {
				// If new key is not valid, remove the old one.
				self::remove_license_key();
				update_option( 'vrts_license_failed', true );
				return $new;
			}

			update_option( 'vrts_license_success', true );
			return $new;
		}//end if

		return $old;
	}

	/**
	 * Prevent updating email address if there is no subscription
	 *
	 * @param string $value New value.
	 */
	public function do_before_updating_email_address( $value ) {
		$has_subscription = (bool) Subscription::get_subscription_status();
		return $has_subscription ? $value : '';
	}

	/**
	 * Update automatic comparison settings for project in service
	 *
	 * @param string $old Old value.
	 * @param string $new New value.
	 */
	public function do_after_update_vrts_automatic_comparison( $old, $new ) {
		if ( $old !== $new ) {
			$service_project_id = get_option( 'vrts_project_id' );
			$service_api_route = 'sites/' . $service_project_id;

			$parameters = [
				'automatic_comparison' => empty( $new ) ? false : true,
			];

			$response = Service::rest_service_request( $service_api_route, $parameters, 'put' );
		}
	}

	/**
	 * Remove license key from the service
	 */
	public static function remove_license_key() {
		$service_project_id = get_option( 'vrts_project_id' );
		$service_api_route = 'sites/' . $service_project_id . '/unregister';

		$response = Service::rest_service_request( $service_api_route, [], 'post' );

		Subscription::get_latest_status();
	}

	/**
	 * Init notifications.
	 */
	public function init_notifications() {
		if ( true === (bool) get_option( 'vrts_license_success' ) ) {
			add_action( 'admin_notices', [ $this, 'render_notification_license_added' ] );
			delete_option( 'vrts_license_success' );
		} elseif ( true === (bool) get_option( 'vrts_license_failed' ) ) {
			add_action( 'admin_notices', [ $this, 'render_notification_license_not_added' ] );
			delete_option( 'vrts_license_failed' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's OK.
		} elseif ( isset( $_GET['settings-updated'] ) && true === (bool) $_GET['settings-updated'] ) {
			add_action( 'admin_notices', [ $this, 'render_notification_settings_saved' ] );
		}
	}

	/**
	 * Render Settings saved notification.
	 */
	public function render_notification_settings_saved() {
		Admin_Notices::render_notification( 'settings_saved', false );
	}

	/**
	 * Render License added notification.
	 */
	public function render_notification_license_added() {
		Admin_Notices::render_notification( 'license_added', false );
	}

	/**
	 * Render License adding failed notification.
	 */
	public function render_notification_license_not_added() {
		Admin_Notices::render_notification( 'license_not_added', false );
	}

	/**
	 * Render License adding removed notification.
	 */
	public function render_notification_license_removed() {
		Admin_Notices::render_notification( 'license_removed', false );
	}

	/**
	 * Sanitize multiple emails.
	 *
	 * @param string $emails The emails.
	 *
	 * @return array
	 */
	public function get_sanitized_emails( $emails ) {
		return array_filter( array_map( 'sanitize_email', explode( ',', $emails ) ) );
	}
}
