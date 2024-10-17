<?php

use Vrts\Core\Utilities\Url_Helpers;

?>
<vrts-settings class="wrap vrts-settings">
	<h1><?php echo esc_html( $data['title'] ); ?></h1>
	<form method="post" action="options.php">
	<?php
		settings_fields( $data['settings_fields'] );
		do_settings_sections( $data['settings_sections'] );
		submit_button();
	?>
	</form>
	<vrts-modal id="vrts-modal-pro-settings" class="vrts-modal vrts-settings__modal" aria-hidden="true">
		<div class="vrts-modal__overlay" data-a11y-dialog-hide></div>
		<div class="vrts-modal__content" role="document">
			<button type="button" class="vrts-modal__close" data-a11y-dialog-hide aria-label="<?php esc_attr_e( 'Close', 'visual-regression-tests' ); ?>"></button>
			<h2 class="vrts-modal__title">
				<?php esc_html_e( '🚀 Go Pro for More Testing Power', 'visual-regression-tests' ); ?>
			</h2>
			<div class="vrts-modal__content-inner">
				<p class="vrts-settings__modal-info"><?php esc_html_e( 'Upgrade your plan and unlock automatic testing after updates, plus more tools like manual testing to keep your sites running smoothly.', 'visual-regression-tests' ); ?></p>
				<a href="<?php echo esc_url( Url_Helpers::get_page_url( 'upgrade' ) ); ?>" class="button button-primary vrts-settings__modal-link"><?php esc_html_e( 'Unlock Now', 'visual-regression-tests' ); ?></a>
			</div>
		</div>
	</vrts-modal>
</vrts-settings>
