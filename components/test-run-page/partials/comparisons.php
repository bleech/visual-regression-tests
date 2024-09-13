<div class="vrts-comparisons postbox">
	<div class="vrts-comparisons__header postbox-header">
		<div role="tablist" class="vrts-comparisons__tabs">
			<button type="button" role="tab" aria-selected="false" aria-controls="vrts-comparisons-difference">
				<span><?php esc_html_e( 'Difference', 'visual-regression-tests' ); ?></span>
			</button>
			<button type="button" role="tab" aria-selected="false" aria-controls="vrts-comparisons-split">
				<span><?php esc_html_e( 'Split', 'visual-regression-tests' ); ?></span>
			</button>
			<button type="button" role="tab" aria-selected="true" aria-controls="vrts-comparisons-side-by-side">
				<span><?php esc_html_e( 'Side by Side', 'visual-regression-tests' ); ?></span>
			</button>
		</div>
		<div class="vrts-comparisons__info">
			<div class="vrts-comparisons__difference">
				<?php /* translators: %s: the count of pixels with a visual difference. */ ?>
				<?php echo esc_html( sprintf( __( '%spx Difference', 'visual-regression-tests' ), esc_html( ceil( $data['alert']->differences / 4 ) ) ) ); ?>
			</div>
			<?php require dirname( __FILE__ ) . '/alert-actions.php'; ?>
		</div>
	</div>

	<div role="tabpanel" id="vrts-comparisons-difference" class="vrts-comparisons__panel" hidden>
		<figure class="vrts-comparisons__figure">
			<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->comparison_screenshot_url ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
		</figure>
	</div>

	<div role="tabpanel" id="vrts-comparisons-split" class="vrts-comparisons__panel" hidden>
		<img-comparison-slider class="vrts-comparison-slider">
			<figure slot="first" class="vrts-comparisons__figure">
				<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->base_screenshot_url ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
				<figcaption class="vrts-comparisons__figure-caption" data-position="first"><?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?></figcaption>
			</figure>
			<figure slot="second" class="vrts-comparisons__figure">
				<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->target_screenshot_url ); ?>" alt="<?php esc_html_e( 'Screenshot', 'visual-regression-tests' ); ?>" />
				<figcaption class="vrts-comparisons__figure-caption" data-position="second"><?php esc_html_e( 'Alert', 'visual-regression-tests' ); ?></figcaption>
			</figure>
			<svg slot="handle" width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle cx="24" cy="20" r="15.5" fill="var(--default-handle-background-color)" stroke="var(--default-handle-color)"/>
				<path d="M20.6667 15.8332H22.3334V14.1665H20.6667V15.8332ZM20.6667 20.8332H22.3334V19.1665H20.6667V20.8332ZM20.6667 25.8332H22.3334V24.1665H20.6667V25.8332ZM25.6667 14.1665V15.8332H27.3334V14.1665H25.6667ZM25.6667 20.8332H27.3334V19.1665H25.6667V20.8332ZM25.6667 25.8332H27.3334V24.1665H25.6667V25.8332Z" fill="var(--default-handle-color)"/>
			</svg>
		</img-comparison-slider>
	</div>

	<div role="tabpanel" id="vrts-comparisons-side-by-side" class="vrts-comparisons__panel">
		<figure class="vrts-comparisons__figure">
			<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->base_screenshot_url ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
			<figcaption class="vrts-comparisons__figure-caption"><?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?></figcaption>
		</figure>

		<img-comparison-slider class="vrts-comparison-slider">
			<figure slot="first" class="vrts-comparisons__figure">
				<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->comparison_screenshot_url ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
				<figcaption class="vrts-comparisons__figure-caption" data-position="first"><?php esc_html_e( 'Alert', 'visual-regression-tests' ); ?></figcaption>
			</figure>
			<figure slot="second" class="vrts-comparisons__figure">
				<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->target_screenshot_url ); ?>" alt="<?php esc_html_e( 'Screenshot', 'visual-regression-tests' ); ?>" />
				<figcaption class="vrts-comparisons__figure-caption" data-position="first"><?php esc_html_e( 'Alert', 'visual-regression-tests' ); ?></figcaption>
			</figure>
			<svg slot="handle" width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle cx="24" cy="20" r="15.5" fill="var(--default-handle-background-color)" stroke="var(--default-handle-color)"/>
				<path d="M20.6667 15.8332H22.3334V14.1665H20.6667V15.8332ZM20.6667 20.8332H22.3334V19.1665H20.6667V20.8332ZM20.6667 25.8332H22.3334V24.1665H20.6667V25.8332ZM25.6667 14.1665V15.8332H27.3334V14.1665H25.6667ZM25.6667 20.8332H27.3334V19.1665H25.6667V20.8332ZM25.6667 25.8332H27.3334V24.1665H25.6667V25.8332Z" fill="var(--default-handle-color)"/>
			</svg>
		</img-comparison-slider>
	</div>

</div>
