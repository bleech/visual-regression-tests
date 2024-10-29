<?php

use Vrts\Core\Utilities\Url_Helpers;

?>
<div class="vrts-metabox-notice vrts-metabox-notice-is-error">
	<p><strong><?php esc_html_e( 'Connection failed', 'visual-regression-tests' ); ?></strong></p>
	<p><?php esc_html_e( 'Something went wrong while trying to connect to the external service.', 'visual-regression-tests' ); ?></p>
	<p><a href="<?php echo esc_attr( Url_Helpers::get_page_url( 'tests' ) ); ?>"><?php esc_html_e( 'Go to plugin page', 'visual-regression-tests' ); ?></a></p>
</div>
