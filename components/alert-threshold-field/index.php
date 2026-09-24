<?php

use Vrts\Models\Test;

$field_name = $data['name'] ?? 'alert_threshold';
$field_id = $data['id'] ?? 'vrts-alert-threshold';
$field_value = Test::sanitize_alert_threshold( $data['value'] ?? null );
?>

<div class="vrts-alert-threshold-field">
	<label for="<?php echo esc_attr( $field_id ); ?>" class="vrts-alert-threshold-field__label">
		<span><?php esc_html_e( 'Alert threshold', 'visual-regression-tests' ); ?></span>
		<span class="vrts-tooltip">
			<span class="vrts-tooltip-icon dashicons dashicons-info-outline"></span>
			<span class="vrts-tooltip-content">
				<span class="vrts-tooltip-content-inner"><?php esc_html_e( 'Only alert when the most-changed screen of the page differs by more than this.', 'visual-regression-tests' ); ?></span>
			</span>
		</span>
	</label>
	<select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>">
		<?php foreach ( Test::get_alert_threshold_options() as $option_value => $option_label ) : ?>
			<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( (string) $field_value, (string) $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
