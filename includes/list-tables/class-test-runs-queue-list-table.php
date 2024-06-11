<?php

namespace Vrts\List_Tables;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Models\Test;
use Vrts\Models\Test_Run;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class.
 */
class Test_Runs_Queue_List_Table extends \WP_List_Table {

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
	 * Gets the list of views available on this table.
	 *
	 * @return array
	 */
	public function get_views() {
		$base_link = admin_url( 'admin.php?page=vrts-runs' );

		$links = [
			'all' => [
				'title' => esc_html__( 'Queue', 'visual-regression-tests' ),
				'link' => $base_link,
				'count' => count( Test_Run::get_queued_items() ),
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

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- It's the list search query parameter.
		$filter_status_query = isset( $_REQUEST['status'] ) && '' !== $_REQUEST['status'] ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : null;

		$args = [
			'number' => -1,
			'filter_status' => $filter_status_query,
		];

		$this->items = Test_Run::get_queued_items( $args );
		$total_items = count( $this->items );

		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page' => 100000,
		]);
	}

	/**
	 * Generates content for a single row of the table.
	 *
	 * @param object|array $item The current item.
	 */
	public function single_row( $item ) {
		$classes = 'iedit';
		?>
		<tr id="test-<?php echo esc_attr( $item->id ); ?>" class="<?php echo esc_attr( $classes ); ?>">
			<?php $this->single_row_columns( $item ); ?>
		</tr>
		<?php
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
			'scheduled' => 'clock',
			'running' => 'update',
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
		$status = Test_Run::get_calculated_status( $item );

		if ( 'scheduled' === $item->trigger && empty( $item->started_at ) ) {
			$tests_count = Test::get_total_items();
		}

		$actions['tests'] = sprintf(
			'<span>%s</span>',
			esc_html (
				sprintf(
					_n( '%s Test', '%s Tests', $tests_count, 'visual-regression-tests' ),
					$tests_count
				)
			)
		);

		$title = Date_Time_Helpers::get_formatted_relative_date_time( $item->scheduled_at );

		if ( 'running' === $status ) {
			$title = __( 'In Progress', 'visual-regression-tests' );
		}

		$row_actions = sprintf(
			'<strong><span class="row-title vrts-testing-status--%3$s">%1$s</a></strong> %2$s',
			$title,
			$this->row_actions( $actions ),
			$status
		);

		return $row_actions;
	}

	public function column_trigger( $item ) {
		$triggerTitles = [
			'manual' => esc_html__( 'Manual', 'visual-regression-tests' ),
			'scheduled' => esc_html__( 'Scheduled', 'visual-regression-tests' ),
			'api' => esc_html__( 'API', 'visual-regression-tests' ),
			'core' => esc_html__( 'WordPress Core', 'visual-regression-tests' ),
			'plugin' => esc_html__( 'WordPress Plugin', 'visual-regression-tests' ),
		];

		$triggerTitle = $triggerTitles[ $item->trigger ] ?? __( 'Unknown', 'visual-regression-tests' );

		return sprintf(
			'<span class="vrts-test-run-trigger vrts-test-run-trigger--%s">%s</span><p class="vrts-test-run-trigger-notes">%s</p>',
			esc_attr( $item->trigger ),
			esc_html( $triggerTitle ),
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
		return 'status: ' . $item->id;
		$status_data = Test_Run::get_status_data( $item );

		return sprintf(
			'<div class="vrts-testing-status-wrapper"><p class="vrts-testing-status"><span class="%s">%s</span></p><p class="vrts-testing-status">%s</p></div>',
			'vrts-testing-status--' . $status_data['class'],
			$status_data['text'],
			$status_data['instructions']
		);
	}
}
