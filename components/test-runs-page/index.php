<?php

use Vrts\Features\Admin_Notices;
use Vrts\Services\Manual_Test_Service;

?>
<vrts-test-runs-page class="wrap vrts-list-table-page vrts-test-runs-page">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Runs', 'visual-regression-tests' ); ?>
	</h1>

	<hr class="wp-header-end">

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
	<?php
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
	?>
</vrts-test-runs-page>
