<?php

namespace Vrts\List_Tables;

use Vrts\Core\Utilities\Url_Helpers;
use Vrts\Models\Test;
use Vrts\Features\Service;
use Vrts\Features\Subscription;
use Vrts\Services\Test_Service;

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
		return [ 'widefat', 'fixed', $this->_args['plural'] ];
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
				return $this->render_column_status( $item );

			case 'base_screenshot_date':
				return $this->render_column_snapshot( $item );

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
			'base_screenshot_date' => esc_html__( 'Snapshot', 'visual-regression-tests' ),
			'status' => esc_html__( 'Test Status', 'visual-regression-tests' ),
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

		$actions['inline hide-if-no-js'] = sprintf(
			'<button type="button" class="button-link editinline" aria-label="%s" aria-expanded="false">%s</button>',
			/* translators: %s: Page Title. */
			esc_attr( sprintf( __( 'Quick edit &#8220;%s&#8221; inline', 'visual-regression-tests' ), $item->post_title ) ),
			__( 'Quick&nbsp;Edit', 'visual-regression-tests' )
		);

		if ( $is_connected ) {
			$actions['trash'] = sprintf(
				'<a href="%s" data-id="%d" title="%s">%s</a>',
				Url_Helpers::get_disable_testing_url( $item->id ),
				$item->id,
				esc_html__( 'Disable testing for this page', 'visual-regression-tests' ),
				esc_html__( 'Disable Testing', 'visual-regression-tests' )
			);
		}

		$row_actions = sprintf(
			'<strong><a class="row-title" href="%1$s" title="%2$s">%3$s</a></strong> %4$s',
			get_edit_post_link( $item->post_id ),
			esc_html__( 'Edit this page', 'visual-regression-tests' ),
			$item->post_title,
			$this->row_actions( $actions )
		);

		$quickedit_hidden_fields = "
		<div class='hidden' id='inline_{$item->id}'>
			<div class='hide_css_selectors'>$item->hide_css_selectors</div>
		</div>";

		return $row_actions . $quickedit_hidden_fields;
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
			'base_screenshot_date' => [ 'base_screenshot_date', true ],
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
			'set-status-disable' => esc_html__( 'Disable Testing', 'visual-regression-tests' ),
		];
		if ( Subscription::get_subscription_status() && count( Test::get_all_running() ) > 0 ) {
			$actions = array_merge(
				[ 'run-manual-test' => esc_html__( 'Run Test', 'visual-regression-tests' ) ],
				$actions
			);
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

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Should be okay for now.
		$test_ids = wp_unslash( $_POST['id'] ?? 0 );
		if ( 0 === $test_ids ) {
			return;
		}

		if ( 'set-status-disable' === $this->current_action() ) {
			foreach ( $test_ids as $test_id ) {
				$item = Test::get_item( $test_id );
				if ( $item ) {
					$service = new Test_Service();
					$service->delete_test( (int) $item->id );
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
		$base_link = Url_Helpers::get_page_url( 'tests' );

		$links = [
			'all' => [
				'title' => esc_html__( 'All', 'visual-regression-tests' ),
				'link' => $base_link,
				'count' => Test::get_total_items(),
			],
			'changes-detected' => [
				'title' => esc_html__( 'Changes detected', 'visual-regression-tests' ),
				'link' => "{$base_link}&status=changes-detected",
				'count' => Test::get_total_items( 'changes-detected' ),
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

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's the list order parameter.
		$order = isset( $_REQUEST['order'] ) && 'asc' === $_REQUEST['order'] ? 'ASC' : 'DESC';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's the list order by parameter.
		$order_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- It's the list search query parameter.
		$search_query = isset( $_POST['s'] ) && '' !== $_POST['s'] ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : null;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- It's the list search query parameter.
		$filter_status_query = isset( $_REQUEST['status'] ) && '' !== $_REQUEST['status'] ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : null;

		$args = [
			'number' => -1,
			'order' => $order,
			'orderby' => $order_by,
			's' => $search_query,
			'filter_status' => $filter_status_query,
		];

		// Process any bulk actions.
		$this->process_bulk_action();
		$this->items = Test::get_items( $args );

		$total_items = count( $this->items );

		$this->set_pagination_args([
			'total_items' => $total_items,
			// we set it to a high number to avoid pagination.
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
	 * Outputs the hidden row displayed when inline editing
	 *
	 * @global string $mode List table view mode.
	 */
	public function inline_edit() {
		$screen = $this->screen;
		?>

		<form method="get">
		<table style="display: none"><tbody id="inlineedit">
		<?php
		$hclass              = 'test';
		$inline_edit_classes = "inline-edit-row inline-edit-row-$hclass";
		$quick_edit_classes  = "quick-edit-row quick-edit-row-$hclass inline-edit-{$screen->post_type}";
		$classes  = $inline_edit_classes . ' ' . $quick_edit_classes;
		?>
		<tr id="inline-edit" style="display: none" class="<?php echo esc_attr( $classes ); ?>">
			<td colspan="<?php echo esc_attr( $this->get_column_count() ); ?>" class="colspanchange">
				<div class="inline-edit-wrapper" role="region" aria-labelledby="quick-edit-legend">
					<fieldset class="inline-edit-col-left">
						<legend class="inline-edit-legend" id="quick-edit-legend"><?php esc_html_e( 'Quick Edit', 'visual-regression-tests' ); ?></legend>
						<div class="inline-edit-col">
							<label><?php esc_html_e( 'Hide elements from VRTs' ); ?></label>
							<textarea name="hide_css_selectors" placeholder="<?php esc_html_e( 'e.g.: .lottie, #ads', 'visual-regression-tests' ); ?>" rows="4" cols="50"></textarea>
							<p>
							<?php
							printf(
								/* translators: %1$s, %2$s: strong element wrapper. */
								esc_html__( '%1$sExclude elements on this page:%2$s ', 'visual-regression-tests' ),
								'<strong>',
								'</strong>'
							);
							printf(
								/* translators: %1$s, %2$s: link wrapper. */
								esc_html__( 'Add %1$sCSS selectors%2$s (as comma separated list) to exclude elements from VRTs when a new snapshot gets created.', 'visual-regression-tests' ),
								'<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Selectors" target="_blank">',
								'</a>'
							);
							?>
							</p>
						</div>
					</fieldset>
					<div class="submit inline-edit-save">
					<?php wp_nonce_field( 'vrts_test_quick_edit', '_vrts_test_quick_edit_nonce', false ); ?>
					<button type="button" class="button button-primary save"><?php esc_html_e( 'Update' ); ?></button>
					<button type="button" class="button cancel"><?php esc_html_e( 'Cancel' ); ?></button>
					<span class="spinner"></span>
					<input type="hidden" name="screen" value="<?php echo esc_attr( $screen->id ); ?>" />
					<div class="notice notice-error notice-alt inline hidden">
						<p class="error"></p>
					</div>
				</div>
				</div>
			</td>
		</tr>
		</tbody>
		</table>
		</form>
		<?php
	}

	/**
	 * Render the status column.
	 *
	 * @param object $item column item.
	 *
	 * @return string
	 */
	private function render_column_status( $item ) {
		$status_data = Test::get_status_data( $item );

		return sprintf(
			'<div class="vrts-testing-status-wrapper"><p class="vrts-testing-status"><span class="%s">%s</span></p><p class="vrts-testing-status">%s</p></div>',
			'vrts-testing-status--' . $status_data['class'],
			$status_data['text'],
			$status_data['instructions']
		);
	}

	/**
	 * Render the snapshot column.
	 *
	 * @param object $item column item.
	 *
	 * @return string
	 */
	private function render_column_snapshot( $item ) {
		$screenshot_data = Test::get_screenshot_data( $item );

		return sprintf(
			'<div class="vrts-testing-status-wrapper"><p class="vrts-testing-status">%s</p><p class="vrts-testing-status">%s</p></div>',
			$screenshot_data['text'],
			$screenshot_data['instructions']
		);
	}
}
