<select name="<?php echo esc_attr( $args['id'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>">
<?php
foreach ( $args['choices'] as $key => $name ) :
	?>
	<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value ); ?>><?php echo esc_html( $name ); ?></option>
<?php endforeach; ?>
</select>

<?php
if ( isset( $args['description'] ) ) :
	?>
	<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
	<?php
endif;
