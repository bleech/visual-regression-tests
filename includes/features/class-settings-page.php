<?php

namespace Vrts\Features;

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

		$this->add_settings();
	}

	/**
	 * Add submenu.
	 */
	public function add_submenu_page() {
		add_submenu_page(
			'vrts',
			esc_html__( 'Settings', 'visual-regression-tests' ),
			esc_html__( 'Settings', 'visual-regression-tests' ),
			'manage_options',
			$this->page_slug,
			[ $this, 'render_page' ]
		);
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
			'id' => 'vrts-settings-section',
			'page' => $this->page_slug,
			'title' => '',
		]);

		// Notice:
		// value_type can be one of these 'string', 'boolean', 'integer', 'number', 'array', and 'object'.
		// sanitize_callback can be a default wp sanitize function or a custom function from the Sanitization class.
		// 'sanitize_callback' => '[ Sanitization::class, 'sanitize_checkbox' ]'.

		vrts()->settings()->add_setting([
			'type' => 'text',
			'id' => 'vrts_email_notification_address',
			'title' => esc_html__( 'Notification Email Address', 'visual-regression-tests' ),
			'description' => esc_html__( 'Add a single email address, or separate multiple email addresses with commas, i.e. info@example.com, admin@example.com.', 'visual-regression-tests' ),
			'section' => 'vrts-settings-section',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest' => true,
			'value_type' => 'string',
			'default' => get_bloginfo( 'admin_email' ),
			'placeholder' => esc_html__( 'Email address', 'visual-regression-tests' ),
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
			'section' => 'vrts-settings-section',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest' => true,
			'value_type' => 'string',
			'default' => '',
			'placeholder' => esc_html__( 'e.g.: [data-cookie-accept]', 'visual-regression-tests' ),
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
			'section' => 'vrts-settings-section',
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
}
