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
					<div class="vrts-test-settings-modal__ai-panel" hidden>
						<div class="vrts-test-settings-modal__ai-summary">
							<span><strong data-ai-count></strong> <?php esc_html_e( 'selectors added by AI', 'visual-regression-tests' ); ?></span>
							<button type="button" class="vrts-test-settings-modal__ai-toggle" data-ai-toggle><?php esc_html_e( 'Details', 'visual-regression-tests' ); ?><?php vrts()->icon( 'arrow-down' ); ?></button>
						</div>
						<div class="vrts-test-settings-modal__ai-details"></div>
					</div>
					<div class="vrts-test-settings-modal__action">
						<div class="vrts-test-settings-modal__ai-suggest">
							<button type="button" class="button vrts-test-settings-modal__ai-button"><span class="vrts-gradient-border"></span><span><?php esc_html_e( 'AI Suggest', 'visual-regression-tests' ); ?></span></button>
							<span class="spinner"></span>
						</div>
						<div class="vrts-test-settings-modal__save">
							<span class="vrts-test-settings-modal__action-success"><?php esc_html_e( 'Saved successfully.', 'visual-regression-tests' ); ?></span>
							<span class="spinner"></span>
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Changes', 'visual-regression-tests' ); ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</vrts-modal>
</vrts-test-settings>
