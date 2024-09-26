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
				<?php vrts()->icon( 'compress' ); ?>
			</button>
			<?php vrts()->component( 'alert-actions', $data ); ?>
		</div>
	</div>

	<div class="vrts-comparisons__content">
		<figure class="vrts-comparisons__figure" data-vrts-comparisons-slot="comparison">
			<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->comparison_screenshot_url ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
			<span class="vrts-comparisons__slider-divider-clone"></span>
		</figure>
		<canvas class="vrts-comparisons__diff-inidicator" data-vrts-comparisons-diff-inidicator></canvas>
		<div class="vrts-comparisons__slider">
			<figure class="vrts-comparisons__figure" data-vrts-comparisons-slot="base">
				<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->base_screenshot_url ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
			</figure>
			<figure class="vrts-comparisons__figure" data-vrts-comparisons-slot="target">
				<img class="vrts-comparisons__figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['alert']->target_screenshot_url ); ?>" alt="<?php esc_html_e( 'Screenshot', 'visual-regression-tests' ); ?>" />
			</figure>
			<span class="vrts-comparisons__slider-divider"></span>
			<div class="vrts-comparisons__slider-handle">
				<?php vrts()->icon( 'grip-dots' ); ?>
			</div>
			<input type="range" step="0.078125" class="vrts-comparisons__slider-control" data-vrts-comparisons-slider-control>
		</div>
	</div>
	<span class="vrts-comparisons__loader"></span>
</vrts-comparisons>
