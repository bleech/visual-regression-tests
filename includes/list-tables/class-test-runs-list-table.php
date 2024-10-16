<?php

namespace Vrts\List_Tables;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Core\Utilities\Url_Helpers;
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
			'singular' => __( 'Run', 'visual-regression-tests' ),
			'plural'   => __( 'Runs', 'visual-regression-tests' ),
			'ajax' => false,
		]);
	}

	/**
	 * Get table classes.
	 */
	public function get_table_classes() {
		return [ 'vrts-test-runs-list-table', 'widefat', 'fixed', $this->_args['plural'] ];
	}

	/**
	 * Message to show if no designation found.
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No Runs finished.', 'visual-regression-tests' );
	}

	/**
	 * Get the column names.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'cb' => '',
			'title' => esc_html__( 'Test Run', 'visual-regression-tests' ),
			'trigger' => esc_html__( 'Trigger', 'visual-regression-tests' ),
			'status' => esc_html__( 'Status', 'visual-regression-tests' ),
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
			'title' => [ 'finished_at', true ],
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
		$order_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'finished_at';

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
		$test_run_ids = wp_list_pluck( $this->items, 'id' );
		$alert_counts = [];
		foreach ( Alert::get_unread_count_by_test_run_ids( $test_run_ids ) as $alert_count ) {
			$alert_counts[ $alert_count->test_run_id ] = $alert_count->count;
		}
		foreach ( $this->items as $item ) {
			$item->alerts_count = $alert_counts[ $item->id ] ?? 0;
		}

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
	}

	/**
	 * Generates content for a single row of the table.
	 *
	 * @param object|array $item The current item.
	 */
	public function single_row( $item ) {
		$classes = 'iedit test-run-row';
		$current_time = time();
		$finished_at = strtotime( $item->finished_at );
		$is_new_run = $current_time - $finished_at < 20 * MINUTE_IN_SECONDS;
		?>
		<tr
			id="test-<?php echo esc_attr( $item->id ); ?>"
			class="<?php echo esc_attr( $classes ); ?>"
			data-test-run-id="<?php echo esc_attr( $item->id ); ?>"
			data-test-run-new="<?php echo esc_attr( $is_new_run ? 'true' : 'false' ); ?>"
			<?php echo $item->alerts_count > 0 ? 'data-has-alerts' : ''; ?>
		>
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

		// $output .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' .
		// * translators: Hidden accessibility text. */
		// esc_html__( 'Show more details', 'visual-regression-tests' ) .
		// '</span></button>';

		return $output;
	}

	/**
	 * Render the status icon column
	 *
	 * @param object $item column item.
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
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

		$actions['details'] = sprintf(
			'<a class="vrts-show-test-run-details" href="%s">%s</a>',
			esc_url( Url_Helpers::get_test_run_page( $item->id ) ),
			esc_html__( 'Show Details', 'visual-regression-tests' )
		);

		$row_actions = sprintf(
			'<strong><a class="row-title vrts-show-test-run-details" href="%1$s">%2$s</a></strong><div class="vrts-test-runs-title-subline">%3$s</div> %4$s',
			esc_url( Url_Helpers::get_test_run_page( $item->id ) ),
			sprintf( $item->title ),
			Date_Time_Helpers::get_formatted_relative_date_time( $item->finished_at ),
			$this->row_actions( $actions, true )
		);

		return $row_actions;
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
		$trigger_note = Test_Run::get_trigger_note( $item );

		return sprintf(
			'<span class="vrts-test-run-trigger vrts-test-run-trigger--%s">%s</span>%s',
			esc_attr( $item->trigger ),
			esc_html( $trigger_title ),
			empty( $trigger_note ) ? '' : sprintf('<p class="vrts-test-run-trigger-notes" title="%1$s">%1$s</p>',
				$trigger_note
			)
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
		$alerts_count = count( maybe_unserialize( $item->alerts ) ?? [] );
		$tests_count = count( maybe_unserialize( $item->tests ) ?? [] );
		if ( $alerts_count > 0 ) {
			$status_class = 'paused';
			$status_text = esc_html__( 'Changes detected ', 'visual-regression-tests' ) . sprintf( '(%s)', $alerts_count );

		} else {
			$status_class = 'running';
			$status_text = esc_html__( 'No changes', 'visual-regression-tests' );
		}
		$status_instructions = sprintf(
			// translators: %s: number of alerts, %s: number of tests.
			esc_html( _n( '%s Test', '%s Tests', $tests_count, 'visual-regression-tests' ) ),
			$tests_count
		);

		return sprintf(
			'<div class="vrts-testing-status-wrapper"><p class="vrts-testing-status"><span class="%s">%s</span></p><p class="vrts-testing-status">%s</p></div>',
			'vrts-testing-status--' . $status_class,
			$status_text,
			$status_instructions
		);
	}
}
