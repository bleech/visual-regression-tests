<fieldset>
	<?php echo wp_kses_post( $args['description'] ); ?>
	<?php
	if ( isset( $args['is_pro'] ) && false === $args['is_pro'] ) :
		?>
		<span class="vrts-settings__pro-label"><?php esc_html_e( 'Pro', 'visual-regression-tests' ); ?></span>
		<?php
	endif; ?>
</fieldset>
