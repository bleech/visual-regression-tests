<fieldset <?php echo isset( $args['is_pro'] ) && false === $args['is_pro'] ? 'data-a11y-dialog-show="vrts-modal-pro-settings"' : ''; ?>>
	<input
		type="text" id="<?php echo esc_attr( $args['id'] ); ?>"
		class="regular-text"
		name="<?php echo esc_attr( $args['id'] ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
		placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"
		<?php wp_readonly( isset( $args['readonly'] ) && $args['readonly'] ); ?>
		<?php disabled( isset( $args['disabled'] ) && $args['disabled'] ); ?>>
	<?php
	if ( isset( $args['is_pro'] ) && false === $args['is_pro'] ) :
		?>
		<span class="vrts-settings__pro-label"><?php esc_html_e( 'Pro', 'visual-regression-tests' ); ?></span>
		<?php
	endif;
	?>
</fieldset>
<?php
if ( isset( $args['description'] ) ) :
	?>
	<p class="description"><?php echo wp_kses_post( $args['description'] ); ?></p>
	<?php
endif;
