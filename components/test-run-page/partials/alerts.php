<div class="vrts-test-run-alerts">
	<div class="vrts-test-run-alerts__heading">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=vrts-runs' ) ); ?>">
			<svg width="6" height="12" viewBox="0 0 6 12" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5.95385 9.72248L2.23269 6.00132L5.95385 2.28017L5.20962 0.791707L0 6.00132L5.20962 11.2109L5.95385 9.72248Z" fill="currentColor"/>
			</svg>
			<?php esc_html_e( 'Back', 'visual-regression-tests' ); ?>
		</a>
		<?php if ( $data['alerts'] ) : ?>
			<button type="button" class="s-test-run-alerts__heading-">
				<svg width="19" height="18" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9.10755 1.25781L15.6425 5.14772C15.8084 5.25563 15.9392 5.3976 16.0347 5.57361C16.13 5.74962 16.1777 5.93685 16.1777 6.13532V13.9812C16.1777 14.3571 16.0475 14.6754 15.787 14.9358C15.5265 15.1963 15.2083 15.3266 14.8324 15.3266H3.38274C3.00678 15.3266 2.68856 15.1963 2.42807 14.9358C2.16759 14.6754 2.03735 14.3571 2.03735 13.9812V6.13532C2.03735 5.93685 2.08505 5.74962 2.18043 5.57361C2.27594 5.3976 2.40668 5.25563 2.57264 5.14772L9.10755 1.25781ZM9.10755 9.48435L14.9125 6.02368L9.10755 2.56301L3.30255 6.02368L9.10755 9.48435ZM9.10755 10.7895L3.1537 7.23157V13.9812C3.1537 14.048 3.17516 14.1029 3.21808 14.1458C3.26099 14.1888 3.31588 14.2102 3.38274 14.2102H14.8324C14.8992 14.2102 14.9541 14.1888 14.997 14.1458C15.0399 14.1029 15.0614 14.048 15.0614 13.9812V7.23157L9.10755 10.7895Z" fill="currentColor"/>
				</svg>
				<?php esc_html_e( 'Mark all as Read', 'visual-regression-tests' ); ?>
			</button>
		<?php endif; ?>
	</div>
	<?php if ( $data['alerts'] ) : ?>
		<div class="vrts-test-run-alerts__list">
			<?php
			foreach ( $data['alerts'] as $alert ) :
				// print_r($alert);
				$alert_link = add_query_arg( [
					'run_id' => $data['run']->id,
					'alert_id' => $alert->id,
				], admin_url( 'admin.php?page=vrts-runs' ) );

				$parsed_tested_url = wp_parse_url( get_permalink( $alert->post_id ) );
				$tested_url = $parsed_tested_url['path'];

				?>
				<a id="vrts-alert-<?php echo esc_attr( $alert->id ); ?>" href="<?php echo esc_url( $alert_link ); ?>" class="vrts-test-run-alerts__card" data-current="<?php echo esc_attr( $data['alert']->id === $alert->id ? 'true' : 'false' ); ?>">
					<figure class="vrts-test-run-alerts__card-figure">
						<img class="vrts-test-run-alerts__card-image" src="<?php echo esc_url( $alert->comparison_screenshot_url ); ?>" alt="<?php esc_attr_e( 'Comparison Screenshot', 'visual-regression-tests' ); ?>">
					</figure>
					<span class="vrts-test-run-alerts__card-title"><?php echo get_the_title( $alert->post_id ); ?></span>
					<span class="vrts-test-run-alerts__card-path"><?php echo esc_html( $tested_url ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
	<?php endif; ?>
</div>
