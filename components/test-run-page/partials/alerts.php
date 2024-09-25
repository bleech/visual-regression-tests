<?php

use Vrts\Core\Utilities\Url_Helpers;
use Vrts\Models\Alert;

$unread_alerts = Alert::get_unread_count_by_test_run_ids( $data['run']->id );
$unread_count = $unread_alerts[0]->count ?? 0;

?>
<div class="vrts-test-run-alerts">
	<div class="vrts-test-run-alerts__heading">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=vrts-runs' ) ); ?>" class="vrts-test-run-alerts__heading-link">
			<svg width="6" height="12" viewBox="0 0 6 12" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5.95385 9.72248L2.23269 6.00132L5.95385 2.28017L5.20962 0.791707L0 6.00132L5.20962 11.2109L5.95385 9.72248Z" fill="currentColor"/>
			</svg>
			<?php esc_html_e( 'Back', 'visual-regression-tests' ); ?>
		</a>
		<?php if ( $data['alerts'] ) : ?>
			<?php if ( $unread_count > 0 ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url( Url_Helpers::get_mark_as_read_url( $data['run']->id ), 'mark_as_read') ); ?>" class="vrts-test-run-alerts__heading-link vrts-test-run-alerts__heading-link--button">
					<svg width="19" height="18" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M9.10755 1.25781L15.6425 5.14772C15.8084 5.25563 15.9392 5.3976 16.0347 5.57361C16.13 5.74962 16.1777 5.93685 16.1777 6.13532V13.9812C16.1777 14.3571 16.0475 14.6754 15.787 14.9358C15.5265 15.1963 15.2083 15.3266 14.8324 15.3266H3.38274C3.00678 15.3266 2.68856 15.1963 2.42807 14.9358C2.16759 14.6754 2.03735 14.3571 2.03735 13.9812V6.13532C2.03735 5.93685 2.08505 5.74962 2.18043 5.57361C2.27594 5.3976 2.40668 5.25563 2.57264 5.14772L9.10755 1.25781ZM9.10755 9.48435L14.9125 6.02368L9.10755 2.56301L3.30255 6.02368L9.10755 9.48435ZM9.10755 10.7895L3.1537 7.23157V13.9812C3.1537 14.048 3.17516 14.1029 3.21808 14.1458C3.26099 14.1888 3.31588 14.2102 3.38274 14.2102H14.8324C14.8992 14.2102 14.9541 14.1888 14.997 14.1458C15.0399 14.1029 15.0614 14.048 15.0614 13.9812V7.23157L9.10755 10.7895Z" fill="currentColor"/>
					</svg>
					<?php esc_html_e( 'Mark all as Read', 'visual-regression-tests' ); ?>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( wp_nonce_url( Url_Helpers::get_mark_as_unread_url( $data['run']->id ), 'mark_as_read') ); ?>" class="vrts-test-run-alerts__heading-link vrts-test-run-alerts__heading-link--button">
					<svg width="19" height="18" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M3.38249 14.5829C3.00653 14.5829 2.68831 14.4526 2.42783 14.1922C2.16735 13.9317 2.03711 13.6135 2.03711 13.2375V4.76481C2.03711 4.38885 2.16735 4.07063 2.42783 3.81015C2.68831 3.54967 3.00653 3.41943 3.38249 3.41943H10.8419C10.8067 3.60548 10.7878 3.79036 10.7854 3.97406C10.783 4.15777 10.7971 4.345 10.8276 4.53577H3.26788L9.1073 8.25693L11.7951 6.5424C11.9258 6.66545 12.0649 6.77634 12.2122 6.87508C12.3597 6.97381 12.5141 7.06182 12.6753 7.13909L9.1073 9.41625L3.15346 5.60914V13.2375C3.15346 13.3044 3.17491 13.3592 3.21783 13.4022C3.26075 13.4451 3.31564 13.4665 3.38249 13.4665H14.8321C14.899 13.4665 14.9539 13.4451 14.9968 13.4022C15.0397 13.3592 15.0611 13.3044 15.0611 13.2375V7.5313C15.2749 7.48355 15.4736 7.4206 15.6573 7.34245C15.8409 7.26418 16.0143 7.1683 16.1775 7.05481V13.2375C16.1775 13.6135 16.0473 13.9317 15.7868 14.1922C15.5263 14.4526 15.2081 14.5829 14.8321 14.5829H3.38249ZM14.3169 6.12452C13.7492 6.12452 13.2662 5.92531 12.8679 5.5269C12.4695 5.12849 12.2703 4.64548 12.2703 4.07788C12.2703 3.51016 12.4695 3.02709 12.8679 2.62868C13.2662 2.23039 13.7492 2.03125 14.3169 2.03125C14.8846 2.03125 15.3676 2.23039 15.7659 2.62868C16.1643 3.02709 16.3636 3.51016 16.3636 4.07788C16.3636 4.64548 16.1643 5.12849 15.7659 5.5269C15.3676 5.92531 14.8846 6.12452 14.3169 6.12452Z" fill="currentColor"/>
					</svg>
					<?php esc_html_e( 'Mark all as Unread', 'visual-regression-tests' ); ?>
				</a>
			<?php endif; ?>
		<?php else : ?>
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
				<a
					id="vrts-alert-<?php echo esc_attr( $alert->id ); ?>"
					href="<?php echo esc_url( $alert_link ); ?>"
					class="vrts-test-run-alerts__card"
					data-current="<?php echo esc_attr( $data['alert']->id === $alert->id ? 'true' : 'false' ); ?>"
					data-state="<?php echo esc_attr( intval( $alert->alert_state ) === 0 ? 'unread' : 'read' ); ?>">
					<figure class="vrts-test-run-alerts__card-figure">
						<img class="vrts-test-run-alerts__card-image" src="<?php echo esc_url( Url_Helpers::get_thumbnail_url_for_comparison( $alert ) ); ?>" alt="<?php esc_attr_e( 'Comparison Screenshot', 'visual-regression-tests' ); ?>">
						<?php if ( $alert->is_false_positive ) : ?>
							<svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3.57227 9.44481C3.57227 9.44481 4.18622 9.02864 6.02819 9.02864C7.87016 9.02864 9.09807 9.65388 10.94 9.65388C12.782 9.65388 13.3961 9.44481 13.3961 9.44481V3.81851C13.3961 3.81851 12.782 4.02759 10.94 4.02759C9.09807 4.02759 7.87016 3.40234 6.02819 3.40234C4.18622 3.40234 3.57227 3.81851 3.57227 3.81851V9.44481Z" fill="#B32D2E"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M3.96947 3.09004C4.40546 2.97183 5.06734 2.86328 6.02839 2.86328C7.00348 2.86328 7.81548 3.02867 8.57604 3.18358L8.59126 3.18668C9.36125 3.34351 10.0799 3.48852 10.9402 3.48852C11.8414 3.48852 12.4309 3.43735 12.7867 3.38889C12.9645 3.36468 13.0833 3.34123 13.153 3.32541C13.1878 3.31751 13.2103 3.31152 13.2217 3.30833L13.2301 3.30589C13.392 3.25305 13.5695 3.2803 13.7082 3.3796C13.8488 3.48021 13.9322 3.64245 13.9322 3.8153V9.44159C13.9322 9.67098 13.7862 9.87492 13.569 9.94885L13.3963 9.44159C13.569 9.94885 13.5688 9.94892 13.5686 9.949L13.5681 9.94916L13.5671 9.9495L13.5649 9.95021L13.56 9.95184L13.5474 9.95583C13.5378 9.95881 13.5257 9.9624 13.511 9.96653C13.4816 9.97477 13.4417 9.98512 13.3902 9.99681C13.2872 10.0202 13.1373 10.049 12.9313 10.0771C12.5196 10.1331 11.8811 10.1865 10.9402 10.1865C9.96516 10.1865 9.15317 10.0211 8.3926 9.86621L8.37738 9.86311C7.6074 9.70629 6.88877 9.56127 6.02839 9.56127C5.14748 9.56127 4.5814 9.66081 4.24991 9.75068C4.08424 9.7956 3.97694 9.83816 3.91784 9.86487C3.88826 9.87824 3.87061 9.8877 3.86363 9.89159C3.86281 9.89205 3.86213 9.89243 3.8616 9.89274C3.69909 9.99691 3.49262 10.0058 3.32145 9.915C3.1462 9.82208 3.03662 9.63995 3.03662 9.44159V3.8153C3.03662 3.6376 3.12472 3.47146 3.27181 3.37175L3.57247 3.8153C3.27181 3.37175 3.27211 3.37154 3.27242 3.37134L3.27304 3.37091L3.27434 3.37004L3.2771 3.3682L3.28334 3.36412L3.2987 3.35444C3.31023 3.34733 3.32437 3.33896 3.34126 3.32953C3.37505 3.31067 3.41976 3.28762 3.47652 3.26197C3.5901 3.21064 3.75141 3.14916 3.96947 3.09004ZM4.10831 4.16639V8.68097C4.54197 8.57734 5.16424 8.48958 6.02839 8.48958C7.00348 8.48958 7.81547 8.65496 8.57604 8.80988L8.59126 8.81298C9.36125 8.9698 10.0799 9.11482 10.9402 9.11482C11.8414 9.11482 12.4309 9.06365 12.7867 9.01518C12.8125 9.01167 12.8371 9.00817 12.8605 9.0047V4.46011C12.4476 4.51263 11.8287 4.56022 10.9402 4.56022C9.96516 4.56022 9.15317 4.39483 8.3926 4.23992L8.37738 4.23682C7.6074 4.07999 6.88877 3.93497 6.02839 3.93497C5.14748 3.93497 4.5814 4.03451 4.24991 4.12439C4.19683 4.13878 4.14974 4.15293 4.10831 4.16639Z" fill="#B32D2E"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M4.1088 7.91016V16.8865H3.03711V7.91016H4.1088Z" fill="#B32D2E"/>
							</svg>
						<?php endif; ?>
					</figure>
					<span class="vrts-test-run-alerts__card-title">
						<span class="vrts-test-run-alerts__card-title-inner"><?php echo get_the_title( $alert->post_id ); ?></span>
					</span>
					<span class="vrts-test-run-alerts__card-path"><?php echo esc_html( $tested_url ); ?></span>
				</a>
			<?php endforeach; ?>
			<script>
				const urlParams = new URLSearchParams( window.location.search );
				const currentAlertId = urlParams.get( 'alert_id' );

				if ( currentAlertId ) {
					const $sidebar = document.querySelector(
						'.vrts-test-run-page__sidebar'
					);
					const $alert = document.getElementById(
						`vrts-alert-${ currentAlertId }`
					);

					if ( $alert ) {
						$sidebar.scrollTo( {
							// behavior: 'smooth',
							left: 0,
							top: $alert.offsetTop - 100,
						} );
					}
				}
			</script>
		</div>
	<?php else : ?>
	<?php endif; ?>
</div>
