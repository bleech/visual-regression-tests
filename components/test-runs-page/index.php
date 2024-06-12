<?php

?>

<div class="wrap vrts-list-table-page vrts-test-runs-page">
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
</div>
