<?php

namespace Vrts\Features;

use Vrts\Features\Subscription;

class Upgrade_Page {
	/**
	 * Page slug.
	 *
	 * @var string
	 */
	protected $page_slug = 'vrts-upgrade';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
	}

	/**
	 * Add submenu.
	 */
	public function add_submenu_page() {
		add_submenu_page(
			'vrts',
			__( 'Upgrade', 'visual-regression-tests' ),
			__( 'Upgrade', 'visual-regression-tests' ),
			'manage_options',
			$this->page_slug,
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Render upgrade page.
	 */
	public function render_page() {
		vrts()->component( 'upgrade-page', [
			'title' => esc_html__( 'Upgrade', 'visual-regression-tests' ),
			'has_subscription' => Subscription::get_subscription_status(),
			'tier_id' => Subscription::get_subscription_tier_id(),
		] );
	}
}
