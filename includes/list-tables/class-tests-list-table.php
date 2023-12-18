<?php

namespace Vrts\List_Tables;

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Models\Test;
use Vrts\Features\Service;
use Vrts\Features\Subscription;
use Vrts\Models\Alert;
use Vrts\Services\Manual_Test_Service;
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
				admin_url( 'admin.php?page=vrts&action=disable-testing&test_id=' ) . $item->id,
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

		if ( 'run-manual-test' === $this->current_action() ) {
			$manual_test_service = new Manual_Test_Service();
			$manual_test_service->run_tests( $test_ids );
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

		$per_page = $this->get_items_per_page( 'vrts_tests_per_page', 20 );
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;

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

		// Process any bulk actions.
		$this->process_bulk_action();
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
		$is_connected = Service::is_connected();
		$has_subscription = Subscription::get_subscription_status();
		$no_tests_left = intval( Subscription::get_remaining_tests() ) === 0;
		$has_remote_test = ! empty( $item->service_test_id );
		$has_base_screenshot = ! empty( $item->base_screenshot_date );
		$has_comparison = ! empty( $item->last_comparison_date );
		$is_running = (bool) $item->is_running;

		$test_status = 'passed';
		if ( ! (bool) $is_connected ) {
			$test_status = 'disconnected';
		} elseif ( $item->current_alert_id ) {
			$test_status = 'has-alert';
		} elseif ( false === (bool) $item->status && ( $no_tests_left || $has_remote_test ) ) {
			$test_status = 'no_credit_left';
		} elseif ( ! $has_remote_test ) {
			$test_status = 'post_not_published';
		} elseif ( ! $has_base_screenshot ) {
			$test_status = 'waiting';
		} elseif ( $is_running ) {
			$test_status = 'running';
		} elseif ( ! $has_comparison ) {
			$test_status = 'scheduled';
		}//end if

		switch ( $test_status ) {
			case 'disconnected':
				$class = 'testing-status--paused';
				$text = esc_html__( 'Disconnected', 'visual-regression-tests' );
				$instructions = '';
				break;
			case 'has-alert':
				$alert = Alert::get_item( $item->current_alert_id );
				$class = 'testing-status--paused';
				$text = esc_html__( 'Changes detected', 'visual-regression-tests' );
				$base_link = admin_url( 'admin.php?page=vrts-alerts&action=edit&alert_id=' );
				$instructions = '<br>';
				$instructions .= Date_Time_Helpers::get_formatted_relative_date_time( $alert->target_screenshot_finish_date );
				$instructions .= '<br>';
				$instructions .= sprintf(
					/* translators: %1$s and %2$s: link wrapper. */
					esc_html__( '%1$s%2$s Resolve alert%3$s to resume test', 'visual-regression-tests' ),
					'<a href="' . $base_link . $item->current_alert_id . '" title="' . esc_attr__( 'Edit the alert', 'visual-regression-tests' ) . '">',
					'<i class="dashicons dashicons-image-flip-horizontal"></i>',
					'</a>'
				);
				break;
			case 'no_credit_left':
				$class = 'testing-status--paused';
				$text = esc_html__( 'Disabled', 'visual-regression-tests' );
				$base_link = admin_url( 'admin.php?page=vrts-upgrade' );
				$instructions = '<br>';
				$instructions .= sprintf(
					/* translators: %1$s and %2$s: link wrapper. */
					esc_html__( '%1$sUpgrade plugin%2$s to resume testing', 'visual-regression-tests' ),
					'<a href="' . $base_link . '" title="' . esc_attr__( 'Upgrade plugin', 'visual-regression-tests' ) . '">',
					'</a>'
				);
				break;
			case 'post_not_published':
				$class = 'testing-status--paused';
				$text = esc_html__( 'Disabled', 'visual-regression-tests' );
				$instructions = '<br>';
				$instructions .= esc_html__( 'Publish post to resume testing', 'visual-regression-tests' );
				break;
			case 'waiting':
				$class = 'testing-status--waiting';
				$text = esc_html__( 'Waiting', 'visual-regression-tests' );
				$instructions = '';
				break;
			case 'running':
				$class = 'testing-status--waiting';
				$text = esc_html__( 'In Progress', 'visual-regression-tests' );
				$instructions = '<br>';
				$instructions .= esc_html__( 'Refresh page to see result', 'visual-regression-tests' );
				break;
			case 'scheduled':
				$class = 'testing-status--waiting';
				$text = esc_html__( 'Scheduled', 'visual-regression-tests' );
				$instructions = '<br>';
				if ( $item->next_run_date ) {
					$instructions .= Date_Time_Helpers::get_formatted_relative_date_time( $item->next_run_date );
					$instructions .= '<br>';
				}
				if ( $has_subscription ) {
					$instructions .= sprintf(
						'<a href="%s" data-id="%d" title="%s">%s</a>',
						admin_url( 'admin.php?page=vrts&action=run-manual-test&test_id=' ) . $item->id,
						$item->id,
						esc_html__( 'Run test now', 'visual-regression-tests' ),
						'<i class="dashicons dashicons-update"></i> ' . esc_html__( 'Run test now', 'visual-regression-tests' )
					);
				}
				break;
			case 'passed':
			default:
				$class = 'testing-status--running';
				$text = esc_html__( 'Passed', 'visual-regression-tests' );
				$instructions = '<br>';
				if ( $item->last_comparison_date ) {
					$instructions .= Date_Time_Helpers::get_formatted_relative_date_time( $item->last_comparison_date );
					$instructions .= '<br>';
				}
				if ( $has_subscription ) {
					$instructions .= sprintf(
						'<a href="%s" data-id="%d" title="%s">%s</a>',
						admin_url( 'admin.php?page=vrts&action=run-manual-test&test_id=' ) . $item->id,
						$item->id,
						esc_html__( 'Run test now', 'visual-regression-tests' ),
						'<i class="dashicons dashicons-update"></i> ' . esc_html__( 'Run test now', 'visual-regression-tests' )
					);
				}
				break;
		}//end switch

		return sprintf(
			'<span class="%s">%s</span>%s',
			$class,
			$text,
			$instructions
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
		$base_screenshot_status = 'taken';
		if ( false === (bool) $item->status ) {
			$base_screenshot_status = 'paused';
		} elseif ( ! $item->base_screenshot_date ) {
			$base_screenshot_status = 'waiting';
		}//end if

		switch ( $base_screenshot_status ) {
			case 'paused':
				$output = esc_html__( 'On hold', 'visual-regression-tests' );
				break;
			case 'waiting':
				$status = esc_html__( 'In progress', 'visual-regression-tests' );
				$output = sprintf(
					'<span class="%s">%s</span><br>%s',
					'testing-status--waiting',
					$status,
					esc_html__( 'Refresh page to see snapshot', 'visual-regression-tests' )
				);
				break;
			case 'taken':
			default:
				$status = sprintf(
					'<a href="%s" target="_blank" data-id="%d" title="%s">%s</a><br>',
					Test::get_base_screenshot_url( $item->post_id ),
					$item->id,
					esc_html__( 'View this snapshot', 'visual-regression-tests' ),
					esc_html__( 'View Snapshot', 'visual-regression-tests' )
				);
				$date_time = Date_Time_Helpers::get_formatted_relative_date_time( $item->base_screenshot_date );
				$output = $status . $date_time;
				break;
		}//end switch
		return $output;
	}
}
