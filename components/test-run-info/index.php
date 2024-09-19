<?php

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Models\Test_Run;

$trigger_note = Test_Run::get_trigger_note( $data );

?>
<div class="vrts-test-run-info">
	<strong>
		<?php
			// translators: %s: the run number.
			printf( esc_html__( 'Run #%s', 'visual-regression-tests' ), $data->id );
		?>
	</strong>
	<?php echo Date_Time_Helpers::get_formatted_relative_date_time( $data->finished_at ); ?>
	<span class="vrts-test-run-info__trigger">
		<span class="vrts-test-run-trigger vrts-test-run-trigger--<?php echo esc_attr( $data->trigger ); ?>">
			<?php echo esc_html( Test_Run::get_trigger_title( $data ) ); ?>
		</span>
		<?php if ( ! empty( $trigger_note ) ) : ?>
			<span class="vrts-test-run-trigger-notes" title="<?php echo esc_attr( $trigger_note ); ?>">
				<?php echo esc_html( $trigger_note ); ?>
			</span>
		<?php endif; ?>
	</span>
</div>
