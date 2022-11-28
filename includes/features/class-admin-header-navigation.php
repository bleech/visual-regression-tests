<?php

namespace Vrts\Features;

class Admin_Header_Navigation {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action('current_screen', function() {
			$current_screen = get_current_screen();
			if ( isset( $current_screen->id ) && strpos( $current_screen->id, 'vrts' ) !== false ) {
				add_action( 'in_admin_header', [ $this, 'add_navigation' ] );
			}
		});
	}

	/**
	 * Add header navigation.
	 */
	public function add_navigation() {
		global $submenu, $submenu_file, $plugin_page;

		$menu_items = [];
		$base_slug = 'vrts';

		if ( isset( $submenu[ $base_slug ] ) ) {

			foreach ( $submenu[ $base_slug ] as $sub_item ) {
				if ( isset( $sub_item[2] ) && strpos( $sub_item[2], $base_slug ) !== false ) {
					$url = admin_url( "admin.php?page={$sub_item[2]}" );
				} else {
					$url = admin_url( $sub_item[2] );
				}

				// Setup tab.
				$menu_item = [
					'text' => $sub_item[0],
					'url'  => isset( $url ) ? $url : $sub_item[2],
				];

				// Add state.
				if ( $submenu_file === $sub_item[2] || $plugin_page === $sub_item[2] ) {
					$menu_item['is_active'] = true;
				}

				$menu_items[] = $menu_item;
			}
		}//end if

		vrts()->component( 'admin-header-navigation', [
			'plugin_name' => vrts()->get_plugin_info( 'name' ),
			'menu_items' => $menu_items,
		]);
	}
}
