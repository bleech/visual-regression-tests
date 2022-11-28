<div class="wrap vrts_list_table_page">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Alerts', 'visual-regression-tests' ); ?>
	</h1>

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
		?>
	</form>
</div>
