<div class="alert-content postbox">
	<div class="postbox-header isSticky">
		<h2 class="alert-content-title"><?php esc_html_e( 'Snapshot vs. Alert', 'visual-regression-tests' ); ?></h2>
		<ul class="navigation">
			<li data-tab="difference" class="active">
				<span><?php esc_html_e( 'Difference', 'visual-regression-tests' ); ?></span>
			</li>
			<li data-tab="comparison">
				<span><?php esc_html_e( 'Split', 'visual-regression-tests' ); ?></span>
			</li>
			<li data-tab="side-by-side">
				<span><?php esc_html_e( 'Side by Side', 'visual-regression-tests' ); ?></span>
			</li>
		</ul>
	</div>

	<div id="difference" class="inside alert-content-inside active">
		<img crossorigin="anonymous" src="<?php echo esc_url( $data['comparison_screenshot_url'] ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
	</div>

	<div id="comparison" class="inside alert-content-inside">
		<img-comparison-slider class="img-comparison-slider">
			<figure slot="first" class="figure figure-before">
				<img class="figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['base_screenshot_url'] ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
				<figcaption class="caption caption-first"><?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?></figcaption>
			</figure>
			<figure slot="second" class="figure figure-after">
				<img class="figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['target_screenshot_url'] ); ?>" alt="<?php esc_html_e( 'Screenshot', 'visual-regression-tests' ); ?>" />
				<figcaption class="caption caption-second"><?php esc_html_e( 'Alert', 'visual-regression-tests' ); ?></figcaption>
			</figure>
			<svg slot="handle" width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle cx="24" cy="20" r="15.5" fill="var(--default-handle-background-color)" stroke="var(--default-handle-color)"/>
				<path d="M20.6667 15.8332H22.3334V14.1665H20.6667V15.8332ZM20.6667 20.8332H22.3334V19.1665H20.6667V20.8332ZM20.6667 25.8332H22.3334V24.1665H20.6667V25.8332ZM25.6667 14.1665V15.8332H27.3334V14.1665H25.6667ZM25.6667 20.8332H27.3334V19.1665H25.6667V20.8332ZM25.6667 25.8332H27.3334V24.1665H25.6667V25.8332Z" fill="var(--default-handle-color)"/>
			</svg>
		</img-comparison-slider>
	</div>

	<div id="side-by-side" class="inside alert-content-inside">
		<figure class="figure">
			<img class="figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['base_screenshot_url'] ); ?>" alt="<?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?>" />
			<figcaption class="caption"><?php esc_html_e( 'Snapshot', 'visual-regression-tests' ); ?></figcaption>
		</figure>

		<figure class="figure">
			<img class="figure-image" crossorigin="anonymous" src="<?php echo esc_url( $data['target_screenshot_url'] ); ?>" alt="<?php esc_html_e( 'Screenshot', 'visual-regression-tests' ); ?>" />
			<figcaption class="caption"><?php esc_html_e( 'Alert', 'visual-regression-tests' ); ?></figcaption>
		</figure>
	</div>

</div>
