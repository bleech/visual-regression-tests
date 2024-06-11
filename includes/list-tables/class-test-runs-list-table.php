<?php

namespace Vrts\List_Tables;

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
		$base_link = admin_url( 'admin.php?page=vrts' );

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
		return sprintf(
			'<span class="dashicons dashicons-%s"></span>',
			'yes-alt'
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
			esc_html (
				sprintf(
					_n( '%s Test', '%s Tests', $tests_count, 'visual-regression-tests' ),
					$tests_count
				)
			)
		);

		$row_actions = sprintf(
			'<strong><a class="row-title" href="%1$s" title="%2$s">%3$s</a></strong> %4$s',
			'#',
			esc_html__( 'View Tests', 'visual-regression-tests' ),
			sprintf( __( 'Run #%s', 'visual-regression-tests' ), $item->id ),
			$this->row_actions( $actions )
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
		$status_data = Test_Run::get_status_data( $item );

		return sprintf(
			'<div class="vrts-testing-status-wrapper"><p class="vrts-testing-status"><span class="%s">%s</span></p><p class="vrts-testing-status">%s</p></div>',
			'vrts-testing-status--' . $status_data['class'],
			$status_data['text'],
			$status_data['instructions']
		);
	}
}
