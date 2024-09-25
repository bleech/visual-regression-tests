<fieldset <?php echo isset( $args['is_pro'] ) && false === $args['is_pro'] ? 'data-a11y-dialog-show="vrts-modal-pro-settings"' : ''; ?>>
	<legend class="screen-reader-text"><?php echo esc_html( $args['title'] ); ?></legend>
	<label>
		<input
			type="checkbox"
			id="<?php echo esc_attr( $args['id'] ); ?>"
			name="<?php echo esc_html( $args['id'] ); ?>"
			value="1"
			<?php checked( $value, 1 ); ?>
			<?php wp_readonly( isset( $args['readonly'] ) && $args['readonly'] ); ?>
			<?php disabled( isset( $args['disabled'] ) && $args['disabled'] ); ?>>
		<?php echo esc_html( $args['label'] ); ?>
	</label>
	<?php
	if ( isset( $args['is_pro'] ) && false === $args['is_pro'] ) :
		?>
		<span class="vrts-settings__pro-label"><?php esc_html_e( 'Pro', 'visual-regression-tests' ); ?></span>
		<?php
	endif; ?>
</fieldset>

<?php
if ( isset( $args['description'] ) ) :
	?>
	<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
	<?php
endif;
