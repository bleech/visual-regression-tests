<?php

use Vrts\Features\Admin_Notices;
use Vrts\Models\Test;
use Vrts\Models\Test_Run;
use Vrts\Services\Manual_Test_Service;

?>
<vrts-test-runs-page class="wrap vrts-list-table-page vrts-test-runs-page">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Runs', 'visual-regression-tests' ); ?>
	</h1>

	<hr class="wp-header-end">

	<div data-vrts-refresh="<?php echo esc_attr( Test_Run::has_runs_in_progress() || Test::has_tests_in_progress() ? 'active' : 'idle' ); ?>">
		<?php
		$list_table = $data['list_queue_table'];
		$list_table->prepare_items();
		$list_table->views();
		$list_table->display();
		?>

		<form method="post">
			<?php
			$list_table = $data['list_table'];
			$list_table->prepare_items();
			$list_table->views();
			$list_table->display();

			if ( $list_table->has_items() ) {
				$list_table->inline_edit();
			}
			?>
		</form>
	</div>

	<?php
	// Skip the one-shot notice on background refresh requests so it is only
	// consumed and rendered by a real page load.
	$vrts_is_refresh_request = '1' === sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_VRTS_REFRESH'] ?? '' ) );
	if ( ! $vrts_is_refresh_request ) {
		$vrts_manual_test_service = new Manual_Test_Service();
		$test_status = $vrts_manual_test_service->get_option();
		if ( $test_status ) {
			$vrts_manual_test_service->delete_option();
			if ( '1' === $test_status ) {
				Admin_Notices::render_notification( 'test_started', false, [] );
			} elseif ( '2' === $test_status ) {
				Admin_Notices::render_notification( 'test_failed', false, [] );
			}
		}
	}
	?>
</vrts-test-runs-page>
