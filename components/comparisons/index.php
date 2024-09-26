<vrts-comparisons class="vrts-comparisons postbox">
	<div class="vrts-comparisons__header postbox-header">
		<div class="vrts-comparisons__title"><?php echo get_the_title( $data['alert']->post_id ); ?></div>
		<div class="vrts-comparisons__info">
			<div class="vrts-comparisons__difference">
				<?php /* translators: %s: the count of pixels with a visual difference. */ ?>
				<?php echo esc_html( sprintf( __( '%spx Difference', 'visual-regression-tests' ), esc_html( ceil( $data['alert']->differences / 4 ) ) ) ); ?>
			</div>
			<button type="button" title="<?php esc_html_e( 'Expand', 'visual-regression-tests' ); ?>" class="vrts-comparisons__expand-button" data-vrts-fullscreen-open>
				<?php vrts()->icon( 'expand' ); ?>
			</button>
			<?php vrts()->component( 'alert-actions', $data ); ?>
		</div>
	</div>

	<div id="vrts-comparisons-side-by-side" class="vrts-comparisons__images">
		<figure class="vrts-comparisons__figure">
			<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->comparison_screenshot_url ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
			<span class="vrts-comparisons__slider-handle-clone"></span>
		</figure>

		<div class="vrts-comparisons__slider">
			<figure class="vrts-comparisons__figure" data-vrts-slot="first">
				<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->base_screenshot_url ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
			</figure>
			<figure class="vrts-comparisons__figure" data-vrts-slot="second">
				<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->target_screenshot_url ); ?>" alt="<?php esc_html_e( 'Screenshot', 'visual-regression-tests' ); ?>" />
			</figure>
			<div class="vrts-comparisons__slider-handle">
				<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="24" cy="20" r="15.5" fill="var(--vrts-default-handle-background-color)" stroke="var(--vrts-default-handle-color)"/>
					<path d="M20.6667 15.8332H22.3334V14.1665H20.6667V15.8332ZM20.6667 20.8332H22.3334V19.1665H20.6667V20.8332ZM20.6667 25.8332H22.3334V24.1665H20.6667V25.8332ZM25.6667 14.1665V15.8332H27.3334V14.1665H25.6667ZM25.6667 20.8332H27.3334V19.1665H25.6667V20.8332ZM25.6667 25.8332H27.3334V24.1665H25.6667V25.8332Z" fill="var(--vrts-default-handle-color)"/>
				</svg>
			</div>
			<input type="range" class="vrts-comparisons__slider-control" data-vrts-comparisons-slider-control>
		</div>
	</div>
	<span class="vrts-comparisons__loader"></span>
</vrts-comparisons>
