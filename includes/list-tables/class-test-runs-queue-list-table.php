<?php

namespace Vrts\List_Tables;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Core\Utilities\Url_Helpers;
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
		return [ 'vrts-test-runs-list-table', 'vrts-test-runs-list-queue-table', 'widefat', 'fixed', $this->_args['plural'] ];
	}

	/**
	 * Message to show if no designation found.
	 *
	 * @return void
	 */
	public function no_items() {
		printf(
			/* translators: %1$s, %2$s link wrapper. */
			esc_html__( 'No Runs in the queue. %1$sAdd Tests%2$s to get started.', 'visual-regression-tests' ),
			'<a href="' . esc_url( admin_url( 'admin.php?page=vrts-tests' ) ) . '">',
			'</a>'
		);
	}

	/**
	 * Get the column names.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'cb' => '',
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
		$status = Test_Run::get_calculated_status( $item );
		?>
		<tr id="test-<?php echo esc_attr( $item->id ); ?>" class="<?php echo esc_attr( $classes ); ?>" data-vrts-test-run-status="<?php echo esc_attr( $status ); ?>" data-test-run-id="<?php echo esc_attr( $item->id ); ?>">
			<?php $this->single_row_columns( $item ); ?>
		</tr>
		<?php
	}

	/**
	 * Generates the table navigation above or below the table
	 *
	 * @param string $which The location of the navigation: Either 'top' or 'bottom'.
	 */
	protected function display_tablenav( $which ) {
		// Don't display the table nav.
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
		$status = Test_Run::get_calculated_status( $item );

		if ( 'running' === $status ) {
			$scheduled_at = __( 'In Progress', 'visual-regression-tests' );
		} else {
			$scheduled_at = Date_Time_Helpers::get_formatted_relative_date_time( $item->scheduled_at );
		}

		$row_actions = sprintf(
			'<strong><span class="row-title">%1$s</a></strong><div class="row-title-subline">%2$s</div>',
			$item->title,
			$scheduled_at
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
			'<span class="vrts-test-run-trigger vrts-test-run-trigger--%s">%s</span><p class="vrts-test-run-trigger-notes" title="%3$s">%3$s</p>',
			esc_attr( $item->trigger ),
			esc_html( $trigger_title ),
			$trigger_note
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
		$test_run_status = Test_Run::get_calculated_status( $item );
		$number_of_tests = count( maybe_unserialize( $item->tests ) ?? [] );
		if ( 0 === $number_of_tests ) {
			$number_of_tests = Test::get_all_running( true );
		}

		if ( 'running' === $test_run_status ) {
			$class = 'waiting';
			$text = '';
			$instructions = sprintf(
				'<span>%s</span>',
				sprintf(
					// translators: %1$s: link start to test runs page. %2$s: link end to test runs page.
					wp_kses( __( '%1$sRefresh page%2$s to see results', 'visual-regression-tests' ), [ 'a' => [ 'href' => [] ] ] ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=vrts-runs' ) ) . '">',
					'</a>'
				)
			);
		} else {
			$class = 'waiting';
			$text = esc_html__( 'Pending', 'visual-regression-tests' );
			$instructions = sprintf(
				'<a href="%1$s">%2$s</a> | <a href="%3$s">%4$s</a>',
				// translators: %s: number of tests.
				esc_url( Url_Helpers::get_tests_url() ),
				sprintf(
					/* translators: %s Test. Test count */
					esc_html( _n( '%s Test', '%s Tests', $number_of_tests, 'visual-regression-tests' ) ),
					$number_of_tests
				),
				esc_url( admin_url( 'admin.php?page=vrts-settings' ) ),
				esc_html__( 'Edit Configuration', 'visual-regression-tests' )
			);
		}//end if
		return sprintf(
			'<div class="vrts-testing-status-wrapper"><div class="vrts-testing-status"><span class="%s">%s</span></div><div>%s</div></div>',
			'vrts-testing-status--' . $class,
			$text,
			$instructions
		);
	}
}
