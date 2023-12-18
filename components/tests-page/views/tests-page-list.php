<?php

use Vrts\Features\Admin_Notices;
use Vrts\Features\Subscription;
use Vrts\Services\Manual_Test_Service;

?>

<div class="wrap vrts_list_table_page">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Tests', 'visual-regression-tests' ); ?>
	</h1>

	<menu class="page-title-actions">
		<li>
			<button type="button" class="page-title-action button-primary"
				id="<?php echo ( ! $data['is_connected'] || intval( $data['remaining_tests'] ) === 0 ) ? 'modal-add-new-disabled' : 'show-modal-add-new'; ?>"
				<?php echo ( ! $data['is_connected'] || intval( $data['remaining_tests'] ) === 0 ) ? ' disabled' : ''; ?>>
				<?php esc_html_e( 'Add New', 'visual-regression-tests' ); ?>
			</button>
		</li>
		<?php if ( Subscription::get_subscription_status() ) : ?>
			<li>
				<form method="post" id="form-run-manual-tests">
					<?php wp_nonce_field( 'submit_run_manual_tests', '_wpnonce' ); ?>
					<input type="submit" name="submit_run_manual_tests" value="<?php esc_attr_e( 'Run All Tests', 'visual-regression-tests' ); ?>"
						class="page-title-action button-secondary"
						id="<?php echo ( ! $data['is_connected'] || ! $data['running_tests_count'] ) ? 'run-manual-tests-disabled' : 'run-manual-tests'; ?>"
						<?php echo ( ! $data['is_connected'] || ! $data['running_tests_count'] ) ? ' disabled' : ''; ?>
					>
				</form>
			</li>
		<?php endif; ?>
	</menu>

	<?php if ( isset( $data['search_query'] ) && '' !== $data['search_query'] ) { ?>
		<span class="subtitle">
			<?php
			printf(
				/* translators: %s: search query. */
				esc_html__( 'Search results for: %s', 'visual-regression-tests' ),
				'<strong>' . esc_html( $data['search_query'] ) . '</strong>'
			);
			?>
		</span>
	<?php } ?>

	<hr class="wp-header-end">

	<form method="post">
		<input type="hidden" name="page" value="vrts-tests_list_table">

		<?php
		$list_table = $data['list_table'];
		$list_table->prepare_items();
		$list_table->views();
		$list_table->search_box( esc_attr__( 'Search', 'visual-regression-tests' ), 'search_id' );
		$list_table->display();

		if ( $list_table->has_items() ) {
			$list_table->inline_edit();
		}

		$vrts_manual_test_service = new Manual_Test_Service();
		if ( $vrts_manual_test_service->is_active() ) {
			$vrts_manual_test_service->delete_option();
			Admin_Notices::render_notification( 'test_started', false, [] );
		}
		?>
	</form>
</div>

<div id="wp-link-backdrop" style="display: none"></div>
<div id="wp-link-wrap" class="wp-core-ui vrts_tests_page_wp_link" style="display: none" role="dialog" aria-labelledby="link-modal-title">
	<form id="wp-link" tabindex="-1" method="post">
		<?php wp_nonce_field( 'internal-linking', '_ajax_linking_nonce', false ); ?>
		<?php wp_nonce_field( 'vrts_page_tests_nonce' ); ?>
		<h1 id="link-modal-title"><?php esc_html_e( 'Add New Test', 'visual-regression-tests' ); ?></h1>
		<button type="button" id="wp-link-close"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'visual-regression-tests' ); ?></span></button>
		<div id="link-selector">
			<div id="link-options">
				<p class="howto" id="wplink-enter-url"><?php esc_html_e( 'Destination URL', 'visual-regression-tests' ); ?></p>
				<div>
					<input id="wp-link-url" class="link-url-field" type="text" aria-describedby="wplink-enter-url" name="internal_url" readonly />
					<input id="wp-link-id" type="hidden" name="post_id" />
				</div>
			</div>
			<p class="howto" id="wplink-link-existing-content"><?php esc_html_e( 'Search', 'visual-regression-tests' ); ?></p>
			<div id="search-panel">
				<div class="link-search-wrapper">
					<input type="search" id="wp-link-search" class="link-search-field" autocomplete="off" aria-describedby="wplink-link-existing-content" />
					<span class="spinner"></span>
				</div>
				<div id="search-results" class="query-results" tabindex="0">
					<ul></ul>
					<div class="river-waiting">
						<span class="spinner"></span>
					</div>
				</div>
				<div id="most-recent-results" class="query-results" tabindex="0">
					<div class="query-notice" id="query-notice-message">
						<em class="query-notice-default"><?php esc_html_e( 'No search term specified. Showing recent items.', 'visual-regression-tests' ); ?></em>
						<em class="query-notice-hint screen-reader-text"><?php esc_html_e( 'Search or use up and down arrow keys to select an item.', 'visual-regression-tests' ); ?></em>
					</div>
					<ul></ul>
					<div class="river-waiting">
						<span class="spinner"></span>
					</div>
				</div>
			</div>
		</div>
		<div class="submitbox">
			<div id="wp-link-cancel">
				<button type="button" class="button"><?php esc_html_e( 'Cancel', 'visual-regression-tests' ); ?></button>
			</div>
			<div id="wp-link-update">
				<?php
				submit_button(
					__( 'Add New Test', 'visual-regression-tests' ),
					'button button-primary',
					'submit_add_new_test',
					false,
					[ 'id' => 'wp-link-submit' ]
				);
				?>
			</div>
		</div>
	</form>
</div>
