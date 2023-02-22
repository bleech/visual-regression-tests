<?php

namespace Vrts\Features;

use Vrts\Models\Alert;

class Admin {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_main_menu' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( vrts()->get_plugin_file() ), [ $this, 'plugin_action_links' ] );
		add_action( 'admin_init', 'Vrts\Features\Service::connect_service' );
	}

	/**
	 * Add main menu where other sub menus can be added to.
	 */
	public function add_main_menu() {
		$count = Alert::get_total_items();

		add_menu_page(
			'VRTs',
			$count ? 'VRTs <span class="update-plugins count-' . esc_attr( $count ) . '">' . esc_html( $count ) . '</span>' : 'VRTs',
			'manage_options',
			'vrts',
			'',
			vrts()->get_plugin_logo_icon(),
			80
		);
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param array $links Plugin Action links.
	 *
	 * @return array $links Plugin Action links.
	 */
	public function plugin_action_links( $links ) {
		$links['tests'] = '<a href="' . esc_url( admin_url( 'admin.php?page=vrts' ) ) . '" aria-label="' . esc_attr__( 'Tests', 'visual-regression-tests' ) . '">' . esc_html__( 'Tests', 'visual-regression-tests' ) . '</a>';
		$links['settings'] = '<a href="' . esc_url( admin_url( 'admin.php?page=vrts-settings' ) ) . '" aria-label="' . esc_attr__( 'Settings', 'visual-regression-tests' ) . '">' . esc_html__( 'Settings', 'visual-regression-tests' ) . '</a>';
		return $links;
	}
}
