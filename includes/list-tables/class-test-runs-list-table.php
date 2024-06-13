<?php

namespace Vrts\List_Tables;

use Vrts\Models\Alert;
use Vrts\Models\Test;
use Vrts\Models\Test_Run;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class.
 */
class Test_Runs_List_Table extends \WP_List_Table {

	/**
	 * Tests.
	 *
	 * @var array
	 */
	protected $tests = [];

	/**
	 * Alerts.
	 *
	 * @var array
	 */
	protected $alerts = [];

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
		return [ 'vrts-test-runs-list-table', 'widefat', 'fixed', 'striped', $this->_args['plural'] ];
	}

	/**
	 * Message to show if no designation found.
	 *
	 * @return void
	 */
	public function no_items() {
		esc_attr_e( 'No runs found.', 'visual-regression-tests' );
	}

	/**
	 * Get the column names.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'icon' => '',
			'title' => esc_html__( 'Title', 'visual-regression-tests' ),
			'trigger' => esc_html__( 'Trigger', 'visual-regression-tests' ),
			'status' => esc_html__( 'Test Status', 'visual-regression-tests' ),
		];

		return $columns;
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = [
			'title' => [ 'title', true ],
			'status' => [ 'status', true ],
			'trigger' => [ 'trigger', true ],
		];

		return $sortable_columns;
	}

	/**
	 * Gets the list of views available on this table.
	 *
	 * @return array
	 */
	public function get_views() {
		$base_link = admin_url( 'admin.php?page=vrts-runs' );

		$links = [
			'all' => [
				'title' => esc_html__( 'All', 'visual-regression-tests' ),
				'link' => $base_link,
				'count' => Test_Run::get_total_items(),
			],
			'changes-detected' => [
				'title' => esc_html__( 'Changes detected', 'visual-regression-tests' ),
				'link' => "{$base_link}&status=changes-detected",
				'count' => Test_Run::get_total_items( 'changes-detected' ),
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
	 * Prepare the class items
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$per_page = $this->get_items_per_page( 'vrts_test_runs_per_page', 20 );
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's the list order parameter.
		$order = isset( $_REQUEST['order'] ) && 'asc' === $_REQUEST['order'] ? 'ASC' : 'DESC';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's the list order by parameter.
		$order_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- It's the list search query parameter.
		$filter_status_query = isset( $_REQUEST['status'] ) && '' !== $_REQUEST['status'] ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : null;

		$args = [
			'offset' => $offset,
			'number' => $per_page,
			'order' => $order,
			'orderby' => $order_by,
			'filter_status' => $filter_status_query,
		];

		$this->items = Test_Run::get_items( $args );

		$total_items = 0;
		if ( null !== $args['filter_status'] ) {
			$total_items = Test_Run::get_total_items( $filter_status_query );
		} else {
			$total_items = Test_Run::get_total_items();
		}

		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page' => $per_page,
		]);

		$tests_ids = [];
		$alerts_ids = [];

		foreach ( $this->items as $test_run ) {
			$tests_ids = array_unique( array_merge( $tests_ids, empty( $test_run->tests ) ? [] : maybe_unserialize( $test_run->tests ) ) );
			$alerts_ids = array_unique( array_merge( $alerts_ids, empty( $test_run->alerts ) ? [] : maybe_unserialize( $test_run->alerts ) ) );
		}

		$this->tests = empty( $test_ids ) ? [] : Test::get_items( [
			'ids' => $tests_ids,
		] );

		$this->alerts = empty( $alerts_ids ) ? [] : Alert::get_items( [
			'ids' => $alerts_ids,
		] );
	}

	/**
	 * Generates content for a single row of the table.
	 *
	 * @param object|array $item The current item.
	 */
	public function single_row( $item ) {
		$classes = 'iedit';
		?>
		<tr id="test-<?php echo esc_attr( $item->id ); ?>" class="<?php echo esc_attr( $classes ); ?>" data-vrts-test-run-details="hidden">
			<?php $this->single_row_columns( $item ); ?>
		</tr>
		<?php
	}

	/**
	 * Generates the required HTML for a list of row action links.
	 *
	 * @param string[] $actions        An array of action links.
	 * @param bool     $always_visible Whether the actions should be always visible.
	 *
	 * @return string The HTML for the row actions.
	 */
	protected function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );

		if ( ! $action_count ) {
			return '';
		}

		$mode = get_user_setting( 'posts_list_mode', 'list' );

		if ( 'excerpt' === $mode ) {
			$always_visible = true;
		}

		$output = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';

		$i = 0;

		foreach ( $actions as $action => $link ) {
			$output .= "<span class='$action'>{$link}</span>";
		}

		$output .= '</div>';

		$output .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' .
			/* translators: Hidden accessibility text. */
			esc_html__( 'Show more details', 'visual-regression-tests' ) .
		'</span></button>';

		return $output;
	}

	/**
	 * Render the status icon column
	 *
	 * @param object $item column item.
	 *
	 * @return string
	 */
	public function column_icon( $item ) {
		$status = Test_Run::get_calculated_status( $item );

		$icons = [
			'has-alerts' => 'warning',
			'passed' => 'yes-alt',
		];

		$icon = $icons[ $status ] ?? 'info';

		return sprintf(
			'<span class="dashicons dashicons-%s vrts-runs-status--%s"></span>',
			$icon,
			$status
		);
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
		$tests_count = count( maybe_unserialize( $item->tests ) );

		$actions['tests'] = sprintf(
			'<span>%s</span>',
			esc_html(
				sprintf(
					// translators: %s: number of tests.
					_n( '%s Test', '%s Tests', $tests_count, 'visual-regression-tests' ),
					$tests_count
				)
			)
		);

		$actions['details'] = sprintf(
			'<a class="vrts-show-test-run-details" href="#">%s</a>',
			esc_html__( 'Show Details', 'visual-regression-tests' )
		);

		$row_actions = sprintf(
			'<strong><span class="row-title">%1$s</span></strong> %2$s %3$s',
			sprintf( $item->title ),
			$this->row_actions( $actions, true ),
			$this->test_run_details( $item )
		);

		return $row_actions;
	}

	/**
	 * Render the test run details.
	 *
	 * @param object $item column item.
	 *
	 * @return string
	 */
	protected function test_run_details( $item ) {
		// Get tests for this test run.
		$tests = array_filter( $this->tests, function( $test ) use ( $item ) {
			return in_array( $test->id, empty( $item->tests ) ? [] : maybe_unserialize( $item->tests ), true );
		} );

		// Get alerts for this test run.
		$alerts = array_filter( $this->alerts, function( $alert ) use ( $item ) {
			return in_array( $alert->id, empty( $item->alerts ) ? [] : maybe_unserialize( $item->alerts ), true );
		} );

		$alert_post_ids = wp_list_pluck( $alerts, 'post_id' );

		$tests_passed = array_filter( $tests, function( $test ) use ( $alert_post_ids ) {
			return ! in_array( $test->post_id, $alert_post_ids, true );
		} );

		$tests_with_alerts = array_filter( $tests, function( $test ) use ( $alert_post_ids ) {
			return in_array( $test->post_id, $alert_post_ids, true );
		} );

		$titles = [
			// translators: %s: number of tests.
			'changes-detected' => __( 'Changes Detected (%s)', 'visual-regression-tests' ),
			// translators: %s: number of tests.
			'passed' => __( 'Passed (%s)', 'visual-regression-tests' ),
		];

		$data = array_filter( [
			'changes-detected' => array_map( function( $test ) {
				$parsed_internal_url = wp_parse_url( get_permalink( $test->post_id ) );
				$internal_url = $parsed_internal_url['path'];

				return sprintf(
					'<a href="%s" target="_blank">%s | %s</a>',
					esc_url( get_edit_post_link( $test->post_id ) ),
					esc_html( $test->post_title ),
					esc_url( $internal_url )
				);
			}, $tests_with_alerts ),
			'passed' => array_map( function( $test ) {
				$parsed_internal_url = wp_parse_url( get_permalink( $test->post_id ) );
				$internal_url = $parsed_internal_url['path'];

				return sprintf(
					'<a href="%s" target="_blank">%s | %s</a>',
					esc_url( get_edit_post_link( $test->post_id ) ),
					esc_html( $test->post_title ),
					esc_url( $internal_url )
				);
			}, $tests_passed ),
		] );

		return sprintf(
			'<div class="vrts-test-run-details">%s %s</div>',
			implode( '', array_map( function( $key ) use ( $data, $titles ) {
				return sprintf(
					'<div class="vrts-test-run-details-section vrts-test-run-details-section--%s"><p class="vrts-test-run-details-section-title">%s</p><ul>%s</ul></div>',
					$key,
					sprintf( $titles[ $key ], count( $data[ $key ] ) ),
					implode( '', array_map( function( $item ) {
						return sprintf( '<li>%s</li>', $item );
					}, $data[ $key ] ) )
				);
			}, array_keys( $data ) ) ),
			sprintf(
				'<button type="button" class="button button-secondary vrts-show-test-run-details">%s</button>',
				esc_html__( 'Close', 'visual-regression-tests' )
			)
		);
	}

	/**
	 * Render the trigger column.
	 *
	 * @param object $item column item.
	 *
	 * @return string
	 */
	public function column_trigger( $item ) {
		$trigger_title = Test_Run::get_trigger_title( $item );

		return sprintf(
			'<span class="vrts-test-run-trigger vrts-test-run-trigger--%s">%s</span><p class="vrts-test-run-trigger-notes">%s</p>',
			esc_attr( $item->trigger ),
			esc_html( $trigger_title ),
			esc_html( $item->trigger_notes )
		);
	}

	/**
	 * Render the status column.
	 *
	 * @param object $item column item.
	 *
	 * @return string
	 */
	public function column_status( $item ) {
		$status_data = Test_Run::get_status_data( $item );

		return sprintf(
			'<div class="vrts-testing-status-wrapper"><p class="vrts-testing-status"><span class="%s">%s</span></p><p class="vrts-testing-status">%s</p></div>',
			'vrts-testing-status--' . $status_data['class'],
			$status_data['text'],
			$status_data['instructions']
		);
	}
}
