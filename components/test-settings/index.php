<vrts-test-settings class="vrts-test-settings">
	<vrts-modal id="vrts-modal-hide-elements" class="vrts-modal vrts-test-settings-modal" aria-hidden="true">
		<div class="vrts-modal__overlay" data-a11y-dialog-hide></div>
		<div class="vrts-modal__content" role="document">
			<button type="button" class="vrts-modal__close" data-a11y-dialog-hide aria-label="<?php esc_attr_e( 'Close', 'visual-regression-tests' ); ?>"></button>
			<h2 class="vrts-modal__title">
				<?php vrts()->icon( 'hidden' ); ?>
				<?php esc_html_e( 'Hide elements', 'visual-regression-tests' ); ?>
			</h2>
			<div class="vrts-modal__content-inner">
				<form data-vrts-test-settings-form method="put">
					<input type="hidden" name="post_id" value="">
					<input type="hidden" name="test_id" value="">
					<textarea class="widefat" name="hide_css_selectors" placeholder="<?php esc_html_e( 'e.g.: .lottie, #ads', 'visual-regression-tests' ); ?>" rows="4"></textarea>
					<p class="description"><?php esc_html_e( 'Hide elements on snapshots to exclude them from comparisons.', 'visual-regression-tests' ); ?></p>
					<div class="vrts-test-settings-modal__ai-panel" hidden
						data-text-loading="<?php esc_attr_e( 'AI is analyzing your site…', 'visual-regression-tests' ); ?>"
						data-text-empty="<?php esc_attr_e( 'No selectors were suggested by AI', 'visual-regression-tests' ); ?>"
						<?php /* translators: %d: number of selectors */ ?>
						data-text-added-singular="<?php echo esc_attr( __( '%d selector was added by AI', 'visual-regression-tests' ) ); ?>"
						data-text-added-plural="<?php echo esc_attr( __( '%d selectors were added by AI', 'visual-regression-tests' ) ); ?>"
						data-text-suggested-singular="<?php echo esc_attr( __( '%d selector was suggested by AI', 'visual-regression-tests' ) ); ?>"
						data-text-suggested-plural="<?php echo esc_attr( __( '%d selectors were suggested by AI', 'visual-regression-tests' ) ); ?>"
					>
						<div class="vrts-gradient-border"></div>
						<button type="button" class="vrts-test-settings-modal__ai-summary" data-ai-toggle aria-expanded="false" aria-controls="vrts-ai-details">
							<span data-ai-label></span>
							<span class="vrts-test-settings-modal__ai-toggle"><?php esc_html_e( 'Details', 'visual-regression-tests' ); ?><?php vrts()->icon( 'chevron-down' ); ?></span>
						</button>
						<div class="vrts-test-settings-modal__ai-details" id="vrts-ai-details" role="region" aria-label="<?php esc_attr_e( 'AI selectors', 'visual-regression-tests' ); ?>"></div>
					</div>
					<div class="vrts-test-settings-modal__action">
						<div class="vrts-test-settings-modal__save">
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'visual-regression-tests' ); ?></button>
							<span class="spinner"></span>
							<span class="vrts-test-settings-modal__action-success"><?php esc_html_e( 'Saved successfully.', 'visual-regression-tests' ); ?></span>
						</div>
					</div>
				</form>
			</div>
		</div>
	</vrts-modal>
</vrts-test-settings>
