<?php

use Vrts\Core\Utilities\Url_Helpers;

?>
<div class="vrts-notice notice notice-error is-dismissable" data-view="<?php echo esc_attr( $data['view'] ); ?>">
	<h3><?php esc_html_e( 'Ready for an Upgrade?', 'visual-regression-tests' ); ?></h3>
	<p>
		<?php
		printf(
			'%1$s <a href="%2$s" title="%3$s">%3$s</a>',
			esc_html__( 'Looks like you need a bigger plan to add more tests.', 'visual-regression-tests' ),
			esc_url( Url_Helpers::get_page_url( 'upgrade' ) ),
			esc_html__( 'Upgrade here!', 'visual-regression-tests' )
		);
		?>
	</p>
</div>
