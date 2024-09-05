<?php

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Models\Test_Run;

?>
<div class="vrts-test-run-info">
	<strong>
		<?php
			// translators: %s: the run number.
			printf( esc_html__( 'Run #%s', 'visual-regression-tests' ), $data['run']->id );
		?>
	</strong>
	<?php echo Date_Time_Helpers::get_formatted_relative_date_time( $data['run']->finished_at ); ?>
	<span class="vrts-test-run-info__trigger">
		<span class="vrts-test-run-trigger vrts-test-run-trigger--<?php echo esc_attr( $data['run']->trigger ); ?>">
			<?php echo esc_html( Test_Run::get_trigger_title( $data['run'] ) ); ?>
		</span>
		<?php if ( ! empty( $data['run']->trigger_notes ) ) : ?>
			<span class="vrts-test-run-trigger-notes">
				<?php echo esc_html( $data['run']->trigger_notes ); ?>
			</span>
		<?php endif; ?>
	</span>
</div>
