<fieldset>
	<legend class="screen-reader-text"><?php echo esc_html( $args['title'] ); ?></legend>
	<label>
		<input type="checkbox" name="<?php echo esc_html( $args['id'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" <?php checked( $value, 1 ); ?> value="1">
		<?php echo esc_html( $args['label'] ); ?>
	</label>
</fieldset>

<?php
if ( isset( $args['description'] ) ) :
	?>
	<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
	<?php
endif;
