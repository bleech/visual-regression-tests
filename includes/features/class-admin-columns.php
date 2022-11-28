<?php

namespace Vrts\Features;

use Vrts\Models\Test;

class Admin_Columns {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'init_admin_columns' ] );
	}

	/**
	 * Init admin columns.
	 */
	public function init_admin_columns() {
		if ( current_user_can( 'manage_options' ) ) {
			$custom_post_types = get_post_types([
				'public' => true,
				'_builtin' => false,
			]);

			$post_types = array_merge( [ 'post', 'page' ], $custom_post_types );

			foreach ( $post_types as $post_type ) {
				add_filter( 'manage_' . $post_type . '_posts_columns', [ $this, 'column_heading' ], 10, 1 );
				add_action( 'manage_' . $post_type . '_posts_custom_column', [ $this, 'column_content' ], 10, 2 );
			}
		}
	}

	/**
	 * Add custom columns to the list table.
	 *
	 * @param array $columns Array of columns.
	 *
	 * @return array
	 */
	public function column_heading( $columns ) {
		$added_columns = [];
		$added_columns['vrts_testing_status'] = sprintf(
			'<span class="vrts-status" title="%2$s %3$s">%1$s<span class="screen-reader-text">%2$s %3$s</span></span>',
			vrts()->get_plugin_logo_icon( false ),
			vrts()->get_plugin_info( 'name' ),
			__( 'Status', 'visual-regression-tests' )
		);
		return array_merge( $columns, $added_columns );
	}

	/**
	 * Display the content for the given column.
	 *
	 * @param string $column_name Column to display the content for.
	 * @param int    $post_id     Post to display the column content for.
	 */
	public function column_content( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'vrts_testing_status':
				$test_id = Test::get_item_id( $post_id );
				$item = (object) Test::get_item( $test_id );

				if ( property_exists( $item, 'current_alert_id' ) ) {
					$class = null === $item->current_alert_id ? 'vrts-icon-status--running' : 'vrts-icon-status--paused';
					$text = null === $item->current_alert_id
						? vrts()->get_plugin_info( 'name' ) . '&#13;' . esc_html__( 'Status: Running', 'visual-regression-tests' )
						: vrts()->get_plugin_info( 'name' ) . '&#13;' . esc_html__( 'Status: Paused', 'visual-regression-tests' );

					echo sprintf(
						'<div aria-hidden="true" title="%s" class="vrts-icon-status %s"></div>
						<span class="screen-reader-text">%s</span>',
						esc_html( $text ),
						esc_html( $class ),
						esc_html( $text )
					);
				} else {
					echo sprintf(
						'<div aria-hidden="true" title="%s" class="vrts-icon-status"></div>
						<span class="screen-reader-text">%s</span>',
						esc_html( vrts()->get_plugin_info( 'name' ) . '&#13;' . esc_html__( 'Status: Testing not activated', 'visual-regression-tests' ) ),
						esc_html( vrts()->get_plugin_info( 'name' ) . '&#13;' . esc_html__( 'Status: Testing not activated', 'visual-regression-tests' ) )
					);
				}//end if
				return;
		}//end switch
	}
}
