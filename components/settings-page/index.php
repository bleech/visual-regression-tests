<div class="wrap">
	<h1><?php echo esc_html( $data['title'] ); ?></h1>
	<form method="post" action="options.php">
	<?php
		settings_fields( $data['settings_fields'] );
		do_settings_sections( $data['settings_sections'] );
		submit_button();
	?>
	</form>
</div>
