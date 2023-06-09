<div class="wrap vrts_edit_alert_page">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'View Alert', 'visual-regression-tests' ); ?>
		<?php echo esc_attr( $data['alert']->title ); ?>
	</h1>
	<hr class="wp-header-end">

	<div class="slug-and-pagination isSticky">
		<div class="slug">
			<strong><?php esc_html_e( 'Permalink:', 'visual-regression-tests' ); ?></strong>
			<span id="permalink"><a href="<?php echo esc_url( $data['permalink'] ); ?>" target="_blank"><?php echo esc_url( $data['permalink'] ); ?></a></span>
		</div>
		<div class="pagination">
			<span class="screen-reader-text"><?php esc_html_e( 'Current Page', 'visual-regression-tests' ); ?></span>
				<span class="tablenav-paging-text">
					<?php
					printf(
						/* translators: %d: pages. */
						esc_html_x( '%1$d of %2$d', 'e.g. 1 of 2', 'visual-regression-tests' ),
						esc_html( $data['pagination']['current'] ),
						esc_html( $data['pagination']['total'] )
					);
					?>
				</span>
			</span>
			<a class="prev-page button <?php echo ( 0 === $data['pagination']['prev_next_alert_id'] ) ? 'button-disabled' : ''; ?>"
				<?php echo ( 0 !== $data['pagination']['prev_next_alert_id'] ) ? 'href="' . esc_url( $data['pagination']['prev_link'] ) . '"' : ''; ?>>
				<span class="screen-reader-text"><?php esc_html_e( 'Previous alert', 'visual-regression-tests' ); ?></span>
				<span aria-hidden="true">‹</span>
			</a>
			<a class="next-page button <?php echo ( 0 === $data['pagination']['next_alert_id'] ) ? 'button-disabled' : ''; ?>"
				<?php echo ( 0 !== $data['pagination']['next_alert_id'] ) ? 'href="' . esc_url( $data['pagination']['next_link'] ) . '"' : ''; ?>>

				<span class="screen-reader-text"><?php esc_html_e( 'Next alert', 'visual-regression-tests' ); ?></span>
				<span aria-hidden="true">›</span>
			</a>
		</div>
	</div>

	<form action="" method="post">
		<?php wp_nonce_field( 'vrts_page_alerts_nonce' ); ?>
		<input type="hidden" name="alert_id" value="<?php echo esc_attr( $data['alert']->id ); ?>">

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<?php require dirname( __FILE__ ) . '/alert-content.php'; ?>
				</div><!-- /post-body-content -->

				<div id="postbox-container-1" class="postbox-container isSticky">
					<div id="submitdiv" class="postbox">
						<div class="postbox-header">
							<h2><?php esc_html_e( 'Details', 'visual-regression-tests' ); ?></h2>
						</div>
						<div class="inside">
							<div class="submitbox" id="submitpost">

								<div id="minor-publishing">

									<div id="misc-publishing-actions">
										<div class="misc-pub-section misc-pub-section-icon">
											<i class="dashicons dashicons-calendar"></i>
											<?php esc_html_e( 'Detected:', 'visual-regression-tests' ); ?>
											<strong>
												<?php echo esc_html( $data['target_screenshot_finish_date'] ); ?>
											</strong>
										</div>

										<div class="misc-pub-section misc-pub-section-icon">
											<i class="dashicons dashicons-image-flip-horizontal"></i>
											<?php esc_html_e( 'Visual Difference:', 'visual-regression-tests' ); ?>
											<strong>
												<?php /* translators: %s: the count of pixels with a visual difference. */ ?>
												<?php echo esc_html( sprintf( _n( '%s pixel', '%s pixels', esc_html( ceil( $data['alert']->differences / 4 ) ), 'visual-regression-tests' ), esc_html( ceil( $data['alert']->differences / 4 ) ) ) ); ?>
											</strong>
										</div>
									</div>
									<div class="clear"></div>
								</div>

								<div id="major-publishing-actions">
									<div id="publishing-action">
										<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=vrts-alerts&status=resolved' ) ); ?>"> <?php esc_html_e( 'Go Back', 'visual-regression-tests' ); ?> </a>
									</div>
									<div class="clear"></div>
								</div>

							</div>
						</div>
					</div>
				</div>
			</div><!-- /post-body -->
			<br class="clear">
		</div><!-- /poststuff -->
	</form>
</div>
