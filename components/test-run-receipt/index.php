<?php

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Core\Utilities\Url_Helpers;
use Vrts\Models\Test_Run;

$alerts_post_ids = wp_list_pluck( $data['alerts'], 'post_id' );
$tests_post_ids = wp_list_pluck( $data['tests'], 'post_id' );

$trigger_note = Test_Run::get_trigger_note( $data['run'] );

?>

<div class="vrts-test-run-receipt">
	<?php if ( $data['alerts'] ) : ?>
		<a id="vrts-alert-receipt" class="vrts-test-run-receipt__link" data-vrts-alert="receipt" href="<?php echo esc_url( Url_Helpers::get_alert_page( 'receipt', $data['run']->id ) ); ?>"></a>
	<?php endif; ?>
	<div class="vrts-test-run-receipt__header">
		<div class="vrts-test-run-receipt__header-logo">
			<?php vrts()->logo(); ?>
			<?php esc_html_e( 'VRTs Plugin', 'visual-regression-tests' ); ?>
		</div>
		<div class="vrts-test-run-receipt__header-info">
			<?php esc_html_e( 'Test Receipt', 'visual-regression-tests' ); ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( home_url( '/' ) ); ?></a>
		</div>
	</div>
	<div class="vrts-test-run-receipt__info">
		<?php
			// translators: %s: the run number.
			printf( esc_html__( 'Run #%s', 'visual-regression-tests' ), esc_html( $data['run']->id ) );
		?>
		<?php echo wp_kses( Date_Time_Helpers::get_formatted_relative_date_time( $data['run']->finished_at ), [ 'time' => [ 'datetime' => true ] ] ); ?>
	</div>
	<div class="vrts-test-run-receipt__pages-status">
		<div class="vrts-test-run-receipt__pages-status-heading">
			<span><?php esc_html_e( 'Page', 'visual-regression-tests' ); ?></span>
			<span><?php esc_html_e( 'Difference', 'visual-regression-tests' ); ?></span>
		</div>
		<?php
		foreach ( $data['tests'] as $test ) :
			$alert = array_values( array_filter( $data['alerts'], static function( $alert ) use ( $test ) {
				return $alert->post_id === $test->post_id;
			} ) );
			$difference = $alert ? ceil( $alert[0]->differences / 4 ) : 0;
			?>
			<div class="vrts-test-run-receipt__pages-status-row">
				<a href="<?php echo esc_url( get_permalink( $test->post_id ) ); ?>"><?php echo esc_html( Url_Helpers::get_relative_permalink( $test->post_id ) ); ?></a>
				<span>
					<?php
					printf(
						/* translators: %s. Test run receipt diff in pixels */
						esc_html_x( '%spx', 'test run receipt difference', 'visual-regression-tests' ),
						esc_html( number_format_i18n( $difference ) )
					);
					?>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="vrts-test-run-receipt__total">
		<div class="vrts-test-run-receipt__total-heading">
			<span><?php esc_html_e( 'Total', 'visual-regression-tests' ); ?></span>
			<span>
				<?php
				printf(
					/* translators: %s. Number of tests */
					esc_html( _n( '%s Test', '%s Tests', count( $data['tests'] ), 'visual-regression-tests' ) ), count( $data['tests'] )
				);
				?>
			</span>
		</div>
		<div class="vrts-test-run-receipt__total-row vrts-test-run-receipt__total-row--success">
			<span><?php esc_html_e( 'Passed', 'visual-regression-tests' ); ?></span>
			<span>
				<?php
				$passed_tests_count = count( $tests_post_ids ) - count( $alerts_post_ids );
				printf(
					/* translators: %s. Number of tests */
					esc_html( _n( '%s Test', '%s Tests', $passed_tests_count, 'visual-regression-tests' ) ), esc_html( $passed_tests_count )
				);
				?>
			</span>
		</div>
		<div class="vrts-test-run-receipt__total-row vrts-test-run-receipt__total-row--failed">
			<span><?php esc_html_e( 'Changes detected', 'visual-regression-tests' ); ?></span>
			<span>
			<?php
				$changed_detected_count = count( $alerts_post_ids );
				printf(
					/* translators: %s. Number of tests */
					esc_html( _n( '%s Test', '%s Tests', $changed_detected_count, 'visual-regression-tests' ) ), esc_html( $changed_detected_count )
				);
				?>
			</span>
		</div>
	</div>
	<div class="vrts-test-run-receipt__trigger">
		<?php esc_html_e( 'Trigger', 'visual-regression-tests' ); ?>
		<span class="vrts-test-run-trigger vrts-test-run-trigger--<?php echo esc_attr( $data['run']->trigger ); ?>">
			<?php echo esc_html( Test_Run::get_trigger_title( $data['run'] ) ); ?>
		</span>
		<?php if ( ! empty( $trigger_note ) ) : ?>
		<span class="vrts-test-run-receipt__trigger-notes" title="<?php echo esc_attr( $trigger_note ); ?>">
			<?php echo esc_html( $trigger_note ); ?>
		</span>
		<?php endif; ?>
	</div>
	<div class="vrts-test-run-receipt__footer">
		<?php esc_html_e( 'Test Completed!', 'visual-regression-tests' ); ?>
	</div>
</div>
