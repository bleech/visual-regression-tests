<?php

namespace Vrts\List_Tables;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Models\Test;
use Vrts\Features\Service;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class.
 */
class Tests_List_Table extends \WP_List_Table {


	/**
	 * Parent construct.
	 */
	public function __construct() {
		parent::__construct([
			'singular' => 'test',
			'plural' => 'tests',
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
		esc_attr_e( 'No tests found.', 'visual-regression-tests' );
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

			case 'post_title':
				return $item->post_title;

			case 'internal_url':
				$parsed_internal_url = wp_parse_url( get_permalink( $item->post_id ) );
				$internal_url = $parsed_internal_url['path'];

				return sprintf(
					'<strong><a href="%1$s" title="%2$s" target="_blank">%3$s</a></strong>',
					get_post_permalink( $item->post_id ),
					esc_html__( 'Open the page in a new tab', 'visual-regression-tests' ),
					$internal_url
				);

			case 'status':
				$is_connected = Service::is_connected();
				$class = ( null === $item->current_alert_id ) && true === (bool) $item->status && true === (bool) $is_connected ? 'testing-status--running' : 'testing-status--paused';
				$text = null === $item->current_alert_id
					? esc_html__( 'Running', 'visual-regression-tests' )
					: esc_html__( 'Paused', 'visual-regression-tests' );
				$instructions = '';
				if ( ! (bool) $is_connected ) {
					$text = esc_html__( 'Disconnected', 'visual-regression-tests' );
					$instructions = '';
				} elseif ( $item->current_alert_id ) {
					$base_link = admin_url( 'admin.php?page=vrts-alerts&action=edit&alert_id=' );
					$instructions = '<br>';
					$instructions .= sprintf(
						/* translators: %1$s and %2$s: link wrapper. */
						esc_html__( 'Resolve %1$salert%2$s to resume testing', 'visual-regression-tests' ),
						'<a href="' . $base_link . $item->current_alert_id . '" title="' . esc_attr__( 'Edit the alert', 'visual-regression-tests' ) . '">',
						'</a>'
					);
				} elseif ( false === (bool) $item->status ) {
					$text = esc_html__( 'Disabled', 'visual-regression-tests' );
					$base_link = admin_url( 'admin.php?page=vrts-upgrade' );
					$instructions = '<br>';
					$instructions .= sprintf(
						/* translators: %1$s and %2$s: link wrapper. */
						esc_html__( '%1$sUpgrade plugin%2$s to resume testing', 'visual-regression-tests' ),
						'<a href="' . $base_link . '" title="' . esc_attr__( 'Upgrade plugin', 'visual-regression-tests' ) . '">',
						'</a>'
					);
				}//end if

				return sprintf(
					'<span class="%s">%s</span>%s',
					$class,
					$text,
					$instructions
				);

			case 'snapshot_date':
				$status = esc_html__( 'In progress', 'visual-regression-tests' );
				$date_time = '';
				if ( $item->snapshot_date ) {
					$status = sprintf(
						'<a href="%s" target="_blank" data-id="%d" title="%s">%s</a><br>',
						Test::get_target_screenshot_url( $item->post_id ),
						$item->id,
						esc_html__( 'View this snapshot', 'visual-regression-tests' ),
						esc_html__( 'View Snapshot', 'visual-regression-tests' )
					);
					$date_time = Date_Time_Helpers::get_formatted_date_time( $item->snapshot_date );
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
			'post_title' => esc_html__( 'Title', 'visual-regression-tests' ),
			'internal_url' => esc_html__( 'Path', 'visual-regression-tests' ),
			'status' => esc_html__( 'Status', 'visual-regression-tests' ),
			'snapshot_date' => esc_html__( 'Snapshot', 'visual-regression-tests' ),
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
	public function column_post_title( $item ) {
		$actions = [];
		$is_connected = Service::is_connected();

		$actions['edit'] = sprintf(
			'<a href="%s" data-id="%d" title="%s">%s</a>',
			get_edit_post_link( $item->post_id ),
			$item->id,
			esc_html__( 'Edit this page', 'visual-regression-tests' ),
			esc_html__( 'Edit Page', 'visual-regression-tests' )
		);

		if ( $is_connected ) {
			$actions['trash'] = sprintf(
				'<a href="%s" data-id="%d" title="%s">%s</a>',
				admin_url( 'admin.php?page=vrts&action=disable-testing&test_id=' ) . $item->id,
				$item->id,
				esc_html__( 'Disable testing for this page', 'visual-regression-tests' ),
				esc_html__( 'Disable testing', 'visual-regression-tests' )
			);
		}

		return sprintf(
			'<strong><a class="row-title" href="%1$s" title="%2$s">%3$s</a></strong> %4$s',
			get_edit_post_link( $item->post_id ),
			esc_html__( 'Edit this page', 'visual-regression-tests' ),
			$item->post_title,
			$this->row_actions( $actions )
		);
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = [
			'post_title' => [ 'post_title', true ],
			'status' => [ 'status', true ],
			'snapshot_date' => [ 'snapshot_date', true ],
		];

		return $sortable_columns;
	}

	/**
	 * Set the bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'set-status-disable' => esc_html__( 'Disable testing', 'visual-regression-tests' ),
		];
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

		if ( 'set-status-disable' === $this->current_action() ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Should be okay for now.
			$test_ids = wp_unslash( $_POST['id'] ?? 0 );

			foreach ( $test_ids as $test_id ) {
				$item = (array) Test::get_item( $test_id );
				if ( $item ) {
					$post_id = intval( $item['post_id'] );
					Test::delete( $post_id );
				}
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
		$base_link = admin_url( 'admin.php?page=vrts' );

		$links = [
			'all' => [
				'title' => esc_html__( 'All', 'visual-regression-tests' ),
				'link' => $base_link,
				'count' => Test::get_total_items(),
			],
			'running' => [
				'title' => esc_html__( 'Running', 'visual-regression-tests' ),
				'link' => "{$base_link}&status=running",
				'count' => Test::get_total_items( 'running' ),
			],
			'paused' => [
				'title' => esc_html__( 'Paused', 'visual-regression-tests' ),
				'link' => "{$base_link}&status=paused",
				'count' => Test::get_total_items( 'paused' ),
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

		$per_page = $this->get_items_per_page( 'actions_per_page', 20 );
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's the list table status.
		$this->page_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '2';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's the list order parameter.
		$order = isset( $_REQUEST['order'] ) && 'desc' === $_REQUEST['order'] ? 'ASC' : 'DESC';

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

		$this->process_bulk_action();
		// Process any bulk actions.
		$this->items = Test::get_items( $args );

		$total_items = 0;
		if ( null !== $args['filter_status'] ) {
			$total_items = Test::get_total_items( $filter_status_query );
		} else {
			$total_items = Test::get_total_items();
		}

		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page' => $per_page,
		]);
	}
}
