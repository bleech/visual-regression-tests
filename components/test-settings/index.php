<?php

$code_editor_settings = [
	'css' => wp_enqueue_code_editor( [ 'type' => 'text/css' ] ),
	// JSHint cannot parse the top-level await the screenshotter supports.
	'js' => wp_enqueue_code_editor( [
		'type' => 'application/javascript',
		'codemirror' => [
			'lint' => false,
			'gutters' => [],
		],
	] ),
];

$code_fields = [
	[
		'name' => 'custom_css',
		'id' => 'vrts-test-settings-css',
		'language' => 'CSS',
		'filename' => 'style.css',
		'icon' => '#',
		'label' => __( 'Custom CSS', 'visual-regression-tests' ),
		'description' => __( 'Adjust styles or hide changing elements before the snapshot.', 'visual-regression-tests' ),
		'snippets' => [
			__( 'Hide element', 'visual-regression-tests' ) => "/* Keeps its space. */\n.selector {\n\tvisibility: hidden !important;\n}\n",
			__( 'Remove element', 'visual-regression-tests' ) => "/* Frees its space; content below moves up. */\n.selector {\n\tdisplay: none !important;\n}\n",
			__( 'Pause animations', 'visual-regression-tests' ) => "*,\n*::before,\n*::after {\n\tanimation: none !important;\n\ttransition: none !important;\n}\n",
		],
	],
	[
		'name' => 'custom_js',
		'id' => 'vrts-test-settings-js',
		'language' => 'JS',
		'filename' => 'script.js',
		'icon' => 'JS',
		'label' => __( 'Custom JavaScript', 'visual-regression-tests' ),
		'description' => __( 'Run actions after page load, before capture. Supports await (15-second limit); errors do not stop the snapshot.', 'visual-regression-tests' ),
		'snippets' => [
			__( 'Remove element', 'visual-regression-tests' ) => "document.querySelector( '.selector' )?.remove();\n",
			__( 'Click element', 'visual-regression-tests' ) => "// For cookie banners, use the click selector in Settings instead.\ndocument.querySelector( '.selector' )?.click();\n",
			__( 'Wait for element', 'visual-regression-tests' ) => "// Waits up to 5 seconds.\nfor ( let i = 0; i < 50; i++ ) {\n\tif ( document.querySelector( '.selector' ) ) {\n\t\tbreak;\n\t}\n\tawait new Promise( ( r ) => setTimeout( r, 100 ) );\n}\n",
		],
	],
];
?>

<vrts-test-settings class="vrts-test-settings" data-vrts-preloaded-settings="<?php echo esc_attr( wp_json_encode( $data['preloaded'] ?? [] ) ); ?>" data-vrts-code-editor="<?php echo esc_attr( wp_json_encode( $code_editor_settings ) ); ?>">
	<vrts-modal id="vrts-modal-test-settings" class="vrts-modal vrts-test-settings-modal" aria-hidden="true" aria-labelledby="vrts-test-settings-title">
		<div class="vrts-modal__overlay" data-a11y-dialog-hide></div>
		<div class="vrts-modal__content" role="document">
			<button type="button" class="vrts-modal__close" data-a11y-dialog-hide aria-label="<?php esc_attr_e( 'Close', 'visual-regression-tests' ); ?>"></button>
			<h2 class="vrts-modal__title" id="vrts-test-settings-title">
				<?php vrts()->icon( 'settings' ); ?>
				<span class="vrts-test-settings-modal__heading">
					<span data-vrts-test-settings-heading><?php esc_html_e( 'Test settings', 'visual-regression-tests' ); ?></span>
					<span class="vrts-test-settings-modal__subtitle" data-vrts-test-settings-subtitle></span>
				</span>
			</h2>
			<div class="vrts-modal__content-inner">
				<form data-vrts-test-settings-form data-confirm-discard="<?php esc_attr_e( 'You have unsaved changes. Discard them?', 'visual-regression-tests' ); ?>">
					<fieldset disabled>
						<div class="vrts-test-settings-modal__agent">
							<div class="vrts-test-settings-modal__field-heading">
								<span class="vrts-test-settings-modal__label"><?php esc_html_e( 'Ask your AI agent', 'visual-regression-tests' ); ?></span>
								<span class="vrts-tooltip" tabindex="0" aria-label="<?php esc_attr_e( 'Ask your AI agent', 'visual-regression-tests' ); ?>" aria-describedby="vrts-test-settings-agent-description">
									<span class="vrts-tooltip-icon dashicons dashicons-info-outline" aria-hidden="true"></span>
									<span class="vrts-tooltip-content" role="tooltip" id="vrts-test-settings-agent-description">
										<span class="vrts-tooltip-content-inner"><?php esc_html_e( 'Paste it into your AI agent to get CSS and JavaScript suggestions.', 'visual-regression-tests' ); ?></span>
									</span>
								</span>
							</div>
							<button type="button" class="vrts-test-settings-modal__prompt" data-vrts-copy-prompt aria-label="<?php esc_attr_e( 'Copy prompt for your AI agent', 'visual-regression-tests' ); ?>">
								<span class="vrts-test-settings-modal__prompt-sign" aria-hidden="true"><?php vrts()->icon( 'sparkles' ); ?></span>
								<span class="vrts-test-settings-modal__prompt-text" data-vrts-prompt-preview aria-hidden="true"></span>
								<span class="vrts-test-settings-modal__prompt-action">
									<span class="vrts-test-settings-modal__prompt-icon" data-vrts-prompt-icon="copy" aria-hidden="true"><?php vrts()->icon( 'copy' ); ?></span>
									<span class="vrts-test-settings-modal__prompt-icon" data-vrts-prompt-icon="copied" aria-hidden="true"><?php vrts()->icon( 'check' ); ?></span>
								</span>
							</button>
							<span class="screen-reader-text" id="vrts-test-settings-agent-status" aria-live="polite"
								data-copied="<?php esc_attr_e( 'Prompt copied', 'visual-regression-tests' ); ?>"
								data-error="<?php esc_attr_e( 'The prompt could not be copied.', 'visual-regression-tests' ); ?>"
							></span>
						</div>
						<div class="vrts-test-settings-modal__field-heading">
							<span class="vrts-test-settings-modal__label" id="vrts-test-settings-code-label"><?php esc_html_e( 'Injected code', 'visual-regression-tests' ); ?></span>
							<span class="vrts-tooltip" tabindex="0" aria-label="<?php esc_attr_e( 'Injected code', 'visual-regression-tests' ); ?>" aria-describedby="vrts-test-settings-code-description">
								<span class="vrts-tooltip-icon dashicons dashicons-info-outline" aria-hidden="true"></span>
								<span class="vrts-tooltip-content" role="tooltip" id="vrts-test-settings-code-description">
									<span class="vrts-tooltip-content-inner"><?php esc_html_e( 'Applied after the page loads: CSS first, then JavaScript.', 'visual-regression-tests' ); ?></span>
								</span>
							</span>
						</div>
						<div class="vrts-test-settings-modal__code">
							<div class="vrts-test-settings-modal__code-tabs" role="tablist" aria-labelledby="vrts-test-settings-code-label">
								<?php foreach ( $code_fields as $index => $field ) : ?>
									<button type="button" role="tab" class="vrts-test-settings-modal__code-tab" id="<?php echo esc_attr( $field['id'] ); ?>-tab" aria-controls="<?php echo esc_attr( $field['id'] ); ?>-panel" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>" data-vrts-code-tab="<?php echo esc_attr( $field['name'] ); ?>">
										<span class="vrts-code-field__icon" data-language="<?php echo esc_attr( $field['language'] ); ?>" aria-hidden="true"><?php echo esc_html( $field['icon'] ); ?></span>
										<?php echo esc_html( $field['filename'] ); ?>
									</button>
								<?php endforeach; ?>
							</div>
							<?php foreach ( $code_fields as $index => $field ) : ?>
								<div class="vrts-test-settings-modal__field<?php echo 0 === $index ? ' is-active' : ''; ?>" id="<?php echo esc_attr( $field['id'] ); ?>-panel" role="tabpanel" aria-labelledby="<?php echo esc_attr( $field['id'] ); ?>-tab" data-vrts-code-panel="<?php echo esc_attr( $field['name'] ); ?>">
									<label class="screen-reader-text" for="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>-label"><?php echo esc_html( $field['label'] ); ?></label>
									<span class="screen-reader-text" id="<?php echo esc_attr( $field['id'] ); ?>-description"><?php echo esc_html( $field['description'] ); ?></span>
									<div class="vrts-code-field">
										<div class="vrts-code-field__bar">
											<select class="vrts-code-field__snippets" data-vrts-snippets="<?php echo esc_attr( $field['name'] ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: Field label, e.g. Custom CSS. */ __( 'Insert example into %s', 'visual-regression-tests' ), $field['label'] ) ); ?>">
												<option value=""><?php esc_html_e( 'Insert example', 'visual-regression-tests' ); ?></option>
												<?php foreach ( $field['snippets'] as $snippet_label => $snippet ) : ?>
													<option value="<?php echo esc_attr( $snippet ); ?>"><?php echo esc_html( $snippet_label ); ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<textarea id="<?php echo esc_attr( $field['id'] ); ?>" class="widefat code" name="<?php echo esc_attr( $field['name'] ); ?>" rows="6" spellcheck="false" autocomplete="off" aria-describedby="<?php echo esc_attr( $field['id'] ); ?>-description"></textarea>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</fieldset>
					<div class="vrts-test-settings-modal__action">
						<button type="button" class="button vrts-test-settings-modal__cancel" data-a11y-dialog-hide><?php esc_html_e( 'Cancel', 'visual-regression-tests' ); ?></button>
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'visual-regression-tests' ); ?></button>
						<span class="spinner"></span>
						<span class="vrts-test-settings-modal__message" data-vrts-test-settings-message aria-live="polite"
							data-success="<?php esc_attr_e( 'Saved.', 'visual-regression-tests' ); ?>"
							data-success-snapshot="<?php esc_attr_e( 'Saved. A new snapshot is being taken; future runs compare against it.', 'visual-regression-tests' ); ?>"
							data-error="<?php esc_attr_e( 'Error while saving the changes.', 'visual-regression-tests' ); ?>"
							data-load-error="<?php esc_attr_e( 'The settings could not be loaded.', 'visual-regression-tests' ); ?>"
						></span>
					</div>
				</form>
			</div>
		</div>
	</vrts-modal>
</vrts-test-settings>
