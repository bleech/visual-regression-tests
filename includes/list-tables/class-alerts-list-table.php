<?php

namespace Vrts\List_Tables;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Features\Alerts_Page;
use Vrts\Models\Alert;
use Vrts\Features\Service;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class.
 */
class Alerts_List_Table extends \WP_List_Table {

	/**
	 * Parent construct.
	 */
	public function __construct() {
		parent::__construct([
			'singular' => 'alert',
			'plural' => 'alerts',
			'ajax' => false,
		]);
	}

	/**
	 * Get table classes.
	 */
	public function get_table_classes() {
		return [ 'widefat', 'fixed', 'striped', $this->_args['plural'] ];
	}

	/**
	 * Message to show if no designation found.
	 *
	 * @return void
	 */
	public function no_items() {
		esc_attr_e( 'No alerts found.', 'visual-regression-tests' );
	}

	/**
	 * Default column values if no callback found.
	 *
	 * @param object $item comment column item.
	 * @param string $column_name column name.
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'title':
				return $item->title;

			case 'tested_url':
				$parsed_tested_url = wp_parse_url( get_permalink( $item->post_id ) );
				$tested_url = $parsed_tested_url['path'];

				return sprintf(
					'<strong><a href="%1$s" title="%2$s" target="_blank">%3$s</a></strong>',
					get_post_permalink( $item->post_id ),
					__( 'Open the page in a new tab', 'visual-regression-tests' ),
					$tested_url
				);

			case 'differences':
				$differences = ceil( $item->differences / 4 );
				return sprintf(
					'<span class="testing-status--paused">%1$s</span>',
					/* translators: %s: the count of pixels with a visual difference. */
					esc_html( sprintf( _n( '%s pixel', '%s pixels', $differences, 'visual-regression-tests' ), $differences ) )
				);

			case 'target_screenshot_finish_date':
				$status = esc_html__( 'In progress', 'visual-regression-tests' );
				$date_time = '';
				if ( $item->target_screenshot_finish_date ) {
					$status = esc_html__( 'Detected', 'visual-regression-tests' ) . '<br />';
					$date_time = Date_Time_Helpers::get_formatted_date_time( $item->target_screenshot_finish_date );
				}
				return $status . $date_time;

			default:
				return isset( $item->$column_name ) ? $item->$column_name : '';
		}//end switch
	}

	/**
	 * Get the column names.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'title' => esc_html__( 'Title', 'visual-regression-tests' ),
			'tested_url' => esc_html__( 'Path', 'visual-regression-tests' ),
			'differences' => esc_html__( 'Visual Difference', 'visual-regression-tests' ),
			'target_screenshot_finish_date' => esc_html__( 'Date', 'visual-regression-tests' ),
		];

		return $columns;
	}

	/**
	 * Render the designation name column.
	 *
	 * @param object $item column item.
	 *
	 * @return string
	 */
	public function column_title( $item ) {
		$actions = [];
		$base_link = admin_url( 'admin.php?page=vrts-alerts' );
		$is_connected = Service::is_connected();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's status request.
		$filter_status_query = ( isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all' );
		if ( 'resolved' === $filter_status_query ) {
			// Actions Status "Resolved".
			$actions['view'] = sprintf(
				'<a href="%s" data-id="%d" title="%s">%s</a>',
				$base_link . '&action=view&alert_id=' . $item->id,
				$item->id,
				__( 'View this alert', 'visual-regression-tests' ),
				__( 'View', 'visual-regression-tests' )
			);

			$actions['delete'] = sprintf(
				'<a href="%s" data-id="%d" title="%s">%s</a>',
				$base_link . '&action=delete&alert_id=' . $item->id,
				$item->id,
				__( 'Delete this alert permanently', 'visual-regression-tests' ),
				__( 'Delete permanently', 'visual-regression-tests' )
			);

			return sprintf(
				'<strong>%1$s</strong> %2$s',
				$item->title,
				$this->row_actions( $actions )
			);

		} else {
			// Actions Status "Open".
			$actions['edit'] = sprintf(
				'<a href="%s" data-id="%d" title="%s">%s</a>',
				$base_link . '&action=edit&alert_id=' . $item->id,
				$item->id,
				__( 'Edit this alert', 'visual-regression-tests' ),
				__( 'Edit', 'visual-regression-tests' )
			);

			if ( $is_connected ) {
				$actions['trash'] = sprintf(
					'<a href="%s" data-id="%d" title="%s">%s</a>',
					$base_link . '&action=resolve&alert_id=' . $item->id,
					$item->id,
					__( 'Resolve this alert', 'visual-regression-tests' ),
					__( 'Resolve', 'visual-regression-tests' )
				);
			}

			return sprintf(
				'<strong><a class="row-title" href="%1$s" title="%2$s">%3$s</a></strong> %4$s',
				$base_link . '&action=edit&alert_id=' . $item->id,
				__( 'Edit', 'visual-regression-tests' ),
				$item->title,
				$this->row_actions( $actions )
			);
		}//end if
	}

	/**
	 * Render the designation name column.
	 *
	 * @param object $item column item.
	 *
	 * @return string
	 */
	public function column_differences( $item ) {
		$is_connected = Service::is_connected();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's status request.
		$filter_status_query = ( isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all' );
		if ( 'resolved' === $filter_status_query ) {
			// Status "Resolved".
			$differences = ceil( $item->differences / 4 );
			/* translators: %s: the count of pixels with a visual difference. */
			return esc_html( sprintf( _n( '%s pixel', '%s pixels', $differences, 'visual-regression-tests' ), $differences ) );
		} else {
			// Status "Open".
			$differences = ceil( $item->differences / 4 );
			if ( $is_connected ) {
				return sprintf(
					'%s<br>%s',
					/* translators: %s: the count of pixels with a visual difference. */
					esc_html( sprintf( _n( '%s pixel', '%s pixels', $differences, 'visual-regression-tests' ), $differences ) ),
					sprintf(
						/* translators: %s: link wrapper */
						esc_html__( 'Tests on %1$spage%2$s are %3$spaused%4$s', 'visual-regression-tests' ),
						'<a href="' . esc_url( get_edit_post_link( $item->post_id ) ) . '" target="_blank">',
						'</a>',
						'<span class="testing-status--paused">',
						'</span>'
					)
				);
			} else {
				return sprintf(
					'%s<br>%s',
					/* translators: %s: the count of pixels with a visual difference. */
					esc_html( sprintf( _n( '%s pixel', '%s pixels', $differences, 'visual-regression-tests' ), $differences ) ),
					sprintf(
						/* translators: %s: link wrapper */
						esc_html__( 'Tests on %1$spage%2$s are %3$snot running until connection with the service is fixed.%4$s', 'visual-regression-tests' ),
						'<a href="' . esc_url( get_edit_post_link( $item->post_id ) ) . '" target="_blank">',
						'</a>',
						'<span class="testing-status--paused">',
						'</span>'
					)
				);
			}//end if
		}//end if
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = [
			'title' => [ 'title', true ],
			'differences' => [ 'differences', true ],
			'target_screenshot_finish_date' => [ 'target_screenshot_finish_date', true ],
		];

		return $sortable_columns;
	}

	/**
	 * Set the bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [];

		// phpcs:ignore Processing form data without nonce verification, WordPress.Security.NonceVerification.Recommended -- Should be okay for now.
		$filter_status_query = ( isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all' );
		if ( 'resolved' === $filter_status_query ) {
			$actions = [
				'delete' => esc_html__( 'Delete permanently', 'visual-regression-tests' ),
			];
		} else {
			// Actions Status "Open".
			$actions = [
				'set-status-resolved' => esc_html__( 'Resolve', 'visual-regression-tests' ),
			];
		}
		return $actions;
	}

	/**
	 * Process bulk actions.
	 *
	 * @return void
	 */
	public function process_bulk_action() {

		// verify the nonce.
		$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
			return;
		}

		if ( 'set-status-resolved' === $this->current_action() ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Should be okay for now.
			$alert_ids = wp_unslash( $_POST['id'] ?? '' );

			foreach ( $alert_ids as $alert_id ) {
				$alert_id = intval( $alert_id );
				Alerts_Page::resolve_alert( $alert_id );
			}
		}

		if ( 'delete' === $this->current_action() ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Should be okay for now.
			$alert_ids = wp_unslash( $_POST['id'] ?? '' );

			foreach ( $alert_ids as $alert_id ) {
				$alert_id = intval( $alert_id );
				Alerts_Page::delete_alert( $alert_id );
			}
		}
	}

	/**
	 * Render the checkbox column
	 *
	 * @param object $item column item.
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="id[]" value="%d" />',
			$item->id
		);
	}

	/**
	 * Gets the list of views available on this table.
	 *
	 * @return array
	 */
	public function get_views() {
		$base_link = admin_url( 'admin.php?page=vrts-alerts' );

		$links = [
			'all' => [
				'title' => esc_html__( 'Open', 'visual-regression-tests' ),
				'link' => $base_link,
				'count' => Alert::get_total_items(),
			],
			'resolved' => [
				'title' => esc_html__( 'Resolved', 'visual-regression-tests' ),
				'link' => "{$base_link}&status=resolved",
				'count' => Alert::get_total_items( 'resolved' ),
			],
		];

		$status_links = [];
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's status request.
		$filter_status_query = ( isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : 'all' );
		foreach ( $links as $key => $link ) {
			$current_class = ( $filter_status_query === $key ? 'class="current" ' : '' );
			$link = sprintf(
				'<a %shref="%s">%s <span class="count">(%s)</span></a>',
				$current_class,
				$link['link'],
				$link['title'],
				$link['count']
			);
			$status_links[ $key ] = $link;
		}

		return $status_links;
	}

	/**
	 * Add extra markup in the toolbars before or after the list.
	 *
	 * @param string $which helps you decide if you add the markup after (bottom) or before (top) the list.
	 */
	public function extra_tablenav( $which ) {
	}

	/**
	 * Prepare the class items
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$per_page = $this->get_items_per_page( 'vrts_alerts_per_page', 20 );
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's the list order parameter.
		$order = isset( $_REQUEST['order'] ) && 'asc' === $_REQUEST['order'] ? 'ASC' : 'DESC';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's the list order by parameter.
		$order_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- It's the list search query parameter.
		$search_query = isset( $_POST['s'] ) && '' !== $_POST['s'] ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : null;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- It's the list search query parameter.
		$filter_status_query = isset( $_REQUEST['status'] ) && '' !== $_REQUEST['status'] ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : null;

		$args = [
			'offset' => $offset,
			'number' => $per_page,
			'order' => $order,
			'orderby' => $order_by,
			's' => $search_query,
			'filter_status' => $filter_status_query,
		];

		// Process any bulk actions.
		$this->process_bulk_action();

		$this->items = Alert::get_items( $args );

		$total_items = 0;
		if ( null !== $args['filter_status'] ) {
			$total_items = Alert::get_total_items( $filter_status_query );
		} else {
			$total_items = Alert::get_total_items();
		}

		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page' => $per_page,
		]);
	}
}
