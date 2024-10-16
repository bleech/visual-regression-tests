<fieldset <?php echo isset( $args['is_pro'] ) && false === $args['is_pro'] ? 'data-a11y-dialog-show="vrts-modal-pro-settings"' : ''; ?>>
	<select
		name="<?php echo esc_attr( $args['id'] ); ?>"
		id="<?php echo esc_attr( $args['id'] ); ?>"
		<?php wp_readonly( isset( $args['readonly'] ) && $args['readonly'] ); ?>
		<?php disabled( isset( $args['disabled'] ) && $args['disabled'] ); ?>>
	<?php
	foreach ( $args['choices'] as $key => $name ) :
		?>
		<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value ); ?>><?php echo esc_html( $name ); ?></option>
	<?php endforeach; ?>
	</select>
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
