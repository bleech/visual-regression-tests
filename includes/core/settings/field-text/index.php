<input name="<?php echo esc_attr( $args['id'] ); ?>" type="text" id="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>"  placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" class="regular-text">

<?php
if ( isset( $args['description'] ) ) :
	?>
	<p class="description"><?php echo wp_kses_post( $args['description'] ); ?></p>
	<?php
endif;
