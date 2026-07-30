<?php

use Vrts\Core\Utilities\Image_Helpers;

?>

<vrts-comparisons class="vrts-comparisons postbox">
	<div class="vrts-comparisons__header postbox-header">
		<div class="vrts-comparisons__title"><?php echo esc_html( get_the_title( $data['alert']->post_id ) ); ?></div>
		<div class="vrts-comparisons__info">
			<div class="vrts-comparisons__difference">
				<?php /* translators: %s: the count of pixels with a visual difference. */ ?>
				<?php echo esc_html( sprintf( __( '%spx Difference', 'visual-regression-tests' ), esc_html( number_format_i18n( ceil( $data['alert']->differences ) ) ) ) ); ?>
			</div>
			<button type="button" title="<?php esc_html_e( 'Expand', 'visual-regression-tests' ); ?>" class="vrts-comparisons__expand-button" data-vrts-fullscreen-open>
				<?php vrts()->icon( 'expand' ); ?>
				<?php vrts()->icon( 'compress' ); ?>
			</button>
			<?php vrts()->component( 'alert-actions', $data ); ?>
		</div>
	</div>

	<div class="vrts-comparisons__content-container">
		<div class="vrts-comparisons__content">
			<figure class="vrts-comparisons__figure" data-vrts-comparisons-slot="comparison">
				<img class="vrts-comparisons__figure-image" <?php echo wp_kses_post( Image_Helpers::alert_image_hwstring( $data['alert'] ) ); ?> crossorigin="anonymous" src="<?php echo esc_url( Image_Helpers::get_screenshot_url( $data['alert'], 'comparison' ) ); ?>" alt="<?php esc_attr_e( 'Difference', 'visual-regression-tests' ); ?>" />
				<span class="vrts-comparisons__slider-divider-clone"></span>
			</figure>
			<div class="vrts-comparisons__diff-track">
				<canvas class="vrts-comparisons__diff-inidicator" data-vrts-comparisons-diff-inidicator></canvas>
				<button type="button" class="vrts-comparisons__jump vrts-comparisons__jump--prev" data-vrts-comparisons-jump="prev" disabled title="<?php esc_attr_e( 'Scroll to previous change', 'visual-regression-tests' ); ?>">
					<svg class="vrts-comparisons__jump-shape" viewBox="0 0 44 60" aria-hidden="true" focusable="false">
						<path class="vrts-comparisons__jump-shape-cover" d="M15 0 H16 V60 H15 Z M28 0 H29 V60 H28 Z" />
						<path class="vrts-comparisons__jump-shape-line vrts-comparisons__jump-shape-line--start" d="M15.5 0 V10 C15.5 17.5 7.5 20 7.5 30 C7.5 40 15.5 42.5 15.5 50 V60" />
						<path class="vrts-comparisons__jump-shape-line vrts-comparisons__jump-shape-line--end" d="M28.5 0 V10 C28.5 17.5 36.5 20 36.5 30 C36.5 40 28.5 42.5 28.5 50 V60" />
					</svg>
					<?php vrts()->icon( 'chevron-up' ); ?>
				</button>
				<button type="button" class="vrts-comparisons__jump vrts-comparisons__jump--next" data-vrts-comparisons-jump="next" disabled title="<?php esc_attr_e( 'Scroll to next change', 'visual-regression-tests' ); ?>">
					<svg class="vrts-comparisons__jump-shape" viewBox="0 0 44 60" aria-hidden="true" focusable="false">
						<path class="vrts-comparisons__jump-shape-cover" d="M15 0 H16 V60 H15 Z M28 0 H29 V60 H28 Z" />
						<path class="vrts-comparisons__jump-shape-line vrts-comparisons__jump-shape-line--start" d="M15.5 0 V10 C15.5 17.5 7.5 20 7.5 30 C7.5 40 15.5 42.5 15.5 50 V60" />
						<path class="vrts-comparisons__jump-shape-line vrts-comparisons__jump-shape-line--end" d="M28.5 0 V10 C28.5 17.5 36.5 20 36.5 30 C36.5 40 28.5 42.5 28.5 50 V60" />
					</svg>
					<?php vrts()->icon( 'chevron-down' ); ?>
				</button>
			</div>
			<div class="vrts-comparisons__slider" style="--vrts-comparisons-slider-aspect-ratio: <?php echo esc_attr( Image_Helpers::alert_image_aspect_ratio( $data['alert'] ) ); ?>">
				<figure class="vrts-comparisons__figure" data-vrts-comparisons-slot="base">
					<img class="vrts-comparisons__figure-image" <?php echo wp_kses_post( Image_Helpers::alert_image_hwstring( $data['alert'] ) ); ?> crossorigin="anonymous" src="<?php echo esc_url( Image_Helpers::get_screenshot_url( $data['alert'], 'base' ) ); ?>" alt="<?php esc_attr_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
				</figure>
				<figure class="vrts-comparisons__figure" data-vrts-comparisons-slot="target">
					<img class="vrts-comparisons__figure-image" <?php echo wp_kses_post( Image_Helpers::alert_image_hwstring( $data['alert'] ) ); ?> crossorigin="anonymous" src="<?php echo esc_url( Image_Helpers::get_screenshot_url( $data['alert'], 'target' ) ); ?>" alt="<?php esc_attr_e( 'Screenshot', 'visual-regression-tests' ); ?>" />
				</figure>
				<span class="vrts-comparisons__slider-divider"></span>
				<div class="vrts-comparisons__slider-handle">
					<?php vrts()->icon( 'grip-dots' ); ?>
				</div>
				<input type="range" step="0.078125" class="vrts-comparisons__slider-control" data-vrts-comparisons-slider-control>
			</div>
		</div>
	</div>
	<span class="vrts-gradient-loader"></span>
</vrts-comparisons>
