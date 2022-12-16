<?php

namespace Vrts\Features;

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
		add_action( 'add_option_vrts_click_selectors', [ $this, 'do_after_update_click_selectors' ], 10, 2 );
		add_action( 'update_option_vrts_click_selectors', [ $this, 'do_after_update_click_selectors' ], 10, 2 );
		add_action( 'pre_update_option_vrts_license_key', [ $this, 'do_before_add_license_key' ], 10, 2 );

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
		vrts()->settings()->add_section([
			'id' => 'vrts-settings-section-notifications',
			'page' => $this->page_slug,
			'title' => '',
		]);

		// Notice:
		// value_type can be one of these 'string', 'boolean', 'integer', 'number', 'array', and 'object'.
		// sanitize_callback can be a default wp sanitize function or a custom function from the Sanitization class.
		// 'sanitize_callback' => '[ Sanitization::class, 'sanitize_checkbox' ]'.

		$has_subscription = Subscription::get_subscription_status();
		if ( '1' !== $has_subscription ) {
			$email_notification_address_description = sprintf(
				'%1$s<br>%2$s <a href="%3$s" title="%4$s">%4$s</a>',
				esc_html__( 'Add a single email address.', 'visual-regression-tests' ),
				esc_html__( 'Want to add more email addresses?', 'visual-regression-tests' ),
				esc_url( admin_url( 'admin.php?page=vrts-upgrade' ) ),
				esc_html__( 'Upgrade here.', 'visual-regression-tests' )
			);
		} else {
			$email_notification_address_description = esc_html__( 'Add a single email address.', 'visual-regression-tests' );
		}

		vrts()->settings()->add_setting([
			'type' => 'text',
			'id' => 'vrts_email_notification_address',
			'title' => esc_html__( 'Notification Email Address', 'visual-regression-tests' ),
			'description' => $email_notification_address_description,
			'section' => 'vrts-settings-section-notifications',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest' => true,
			'value_type' => 'string',
			'default' => get_bloginfo( 'admin_email' ),
			'placeholder' => esc_html__( 'Email address', 'visual-regression-tests' ),
		]);

		if ( '1' === $has_subscription ) {
			vrts()->settings()->add_section([
				'id' => 'vrts-settings-section-notifications-pro',
				'page' => $this->page_slug,
				'title' => '',
			]);

			vrts()->settings()->add_setting([
				'type' => 'text',
				'id' => 'vrts_email_notification_cc_address',
				'title' => esc_html__( 'Notification Email CC Address(es)', 'visual-regression-tests' ),
				'description' => esc_html__( 'Add a single email address, or separate multiple email addresses with commas, i.e. info@example.com, admin@example.com.', 'visual-regression-tests' ),
				'section' => 'vrts-settings-section-notifications-pro',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest' => true,
				'value_type' => 'string',
				'default' => '',
				'placeholder' => esc_html__( 'Email address(es)', 'visual-regression-tests' ),
			]);
		}

		vrts()->settings()->add_section([
			'id' => 'vrts-settings-section-click-selectors',
			'page' => $this->page_slug,
			'title' => '',
		]);

		vrts()->settings()->add_setting([
			'type' => 'text',
			'id' => 'vrts_click_selectors',
			'title' => esc_html__( 'Click an element before creating a snapshot', 'visual-regression-tests' ),
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
			'section' => 'vrts-settings-section-click-selectors',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest' => true,
			'value_type' => 'string',
			'default' => '',
			'placeholder' => esc_html__( 'e.g.: [data-cookie-accept]', 'visual-regression-tests' ),
		]);

		vrts()->settings()->add_section([
			'id' => 'vrts-settings-section-click-license',
			'page' => $this->page_slug,
			'title' => '',
		]);

		vrts()->settings()->add_setting([
			'type' => 'text',
			'id' => 'vrts_license_key',
			'title' => esc_html__( 'License Key', 'visual-regression-tests' ),
			'description' => sprintf(
				'%1$s <a href="%2$s" title="%3$s">%3$s</a>',
				esc_html__( 'No license key yet?', 'visual-regression-tests' ),
				esc_url( admin_url( 'admin.php?page=vrts-upgrade' ) ),
				esc_html__( 'Upgrade here.', 'visual-regression-tests' )
			),
			'section' => 'vrts-settings-section-click-license',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest' => true,
			'value_type' => 'string',
			'default' => '',
			'placeholder' => esc_html_x( 'XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX', 'license key placeholder', 'visual-regression-tests' ),
		]);
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
}
