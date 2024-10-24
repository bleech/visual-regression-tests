<?php

use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Core\Utilities\Url_Helpers;
use Vrts\Models\Test_Run;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
	<title>Title</title>
	<meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="x-apple-disable-message-reformatting" />
	<meta content="telephone=no" name="format-detection">

	<!--[if !mso]><!-->
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<!--<![endif]-->

	<!--[if gte mso 9]>
	  <xml>
		<o:OfficeDocumentSettings xmlns:o="urn:schemas-microsoft-com:office:office">
		  <o:AllowPNG />
		  <o:PixelsPerInch>96</o:PixelsPerInch>
		</o:OfficeDocumentSettings>
	  </xml>
	<![endif]-->

	<style>
	  table,
	  tr,
	  td {
		border-collapse: separate;
		padding: 0;
	  }
	</style>

	<!--[if mso]>
	  <style type="text/css">
		v\:* {
		  behavior: url(#default#VML);
		  display: inline-block;
		}
		o\:* {
		  behavior: url(#default#VML);
		  display: inline-block;
		}
		w\:* {
		  behavior: url(#default#VML);
		  display: inline-block;
		}
		.ExternalClass {
		  width: 100%;
		}
		table {
		  mso-table-lspace: 0pt;
		  mso-table-rspace: 0pt;
		}
		img {
		  -ms-interpolation-mode: bicubic;
		}
		.ReadMsgBody {
		  width: 100%;
		}
		a {
		  background: transparent !important;
		  background-color: transparent !important;
		}

		li {
		  text-align: -webkit-match-parent;
		  display: list-item;
		  text-indent: -1em;
		}

		ul,
		ol {
		  margin-left: 1em !important;
		}

		p {
		  text-indent: 0;
		}
	  </style>
	<![endif]-->
  </head>
  <body style="width:100%;height:100%;padding:0;Margin:0">
	<table border="0" cellspacing="0" width="100%" bgcolor="#ffffff" style="background-color: #ffffff; font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, sans-serif, Tahoma, system-ui; margin-top: 20px;">
		<tr>
			<td style="padding: 0 20px;">
				<p style="font-size: 14px; line-height: 16px; color: #2C3338; display: block; mso-line-height-rule: exactly"><?php esc_html_e( 'Howdy 👋', 'visual-regression-tests' ); ?></p>
				<p style="font-size: 14px; line-height: 16px; color: #2C3338; display: block; mso-line-height-rule: exactly"><?php esc_html_e( "We've detected visual changes on your website.", 'visual-regression-tests' ); ?></p>
			</td>
		</tr>
	</table>
	<table border="0" cellspacing="0" width="100%" bgcolor="#ffffff" style="background-color: #ffffff; font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, sans-serif, Tahoma, system-ui; margin-top: 10px;">
	  <tr>
		<td width="400" style="padding: 0 20px;">
			<div style="background-color:#fff;">
				<!--[if gte mso 9]>
				<v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
					<v:fill type="tile" src="<?php echo esc_url( vrts()->get_plugin_url( 'assets/images/receipt-top.png' ) ); ?>" color="#fff"/>
				</v:background>
				<![endif]-->
				<table height="7px" width="100%" cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td valign="top" align="left" background="<?php echo esc_url( vrts()->get_plugin_url( 'assets/images/receipt-top.png' ) ); ?>"></td>
					</tr>
				</table>
			</div>
			<table cellpadding="0" cellspacing="0" border="0" role="presentation" style="width: 100%; padding: 15px; border-spacing: 0; background-color: #F6F7FB;">
				<tr>
					<td style="border-bottom: 1px dashed #c3c4c7; padding-bottom: 12px">
						<table cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-spacing: 0;">
							<tr>
								<td></td>
								<td width="22" style="padding-right: 5px;">
									<div>
										<!--[if gte mso 9]>
										<v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
											<v:fill type="tile" src="<?php echo esc_url( vrts()->get_plugin_url( 'assets/images/vrts-logo.png' ) ); ?>" color="#fff"/>
										</v:background>
										<![endif]-->
										<table height="22px" width="22px" cellpadding="0" cellspacing="0" border="0">
											<tr>
												<td valign="top" align="left" background="<?php echo esc_url( vrts()->get_plugin_url( 'assets/images/vrts-logo.png' ) ); ?>" style="background-size: 22px;"></td>
											</tr>
										</table>
									</div>
								</td>
								<td width="100">
									<p style="font-size: 16px; line-height: 20px; color: #2C3338; display: block; margin: 0; mso-line-height-rule: exactly; text-align: center;"><strong><?php esc_html_e( 'VRTs Plugin', 'visual-regression-tests' ); ?></strong></p>
								</td>
								<td></td>
							</tr>
						</table>
						<table cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-spacing: 0; padding-top: 10px">
							<tr>
								<td style="text-align: center">
									<p style="font-size: 14px; line-height: 20px; color: #2C3338; display: block; margin: 0; mso-line-height-rule: exactly"><?php esc_html_e( 'Test Receipt', 'visual-regression-tests' ); ?></p>
									<a style="font-size: 11px; line-height: 16px; display: block; margin-top: 4px; mso-line-height-rule: exactly" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( home_url( '/' ) ); ?></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="border-bottom: 1px dashed #c3c4c7; padding-top: 12px; padding-bottom: 12px; text-align: center;">
						<p style="font-size: 11px; line-height: 16px; color: #2C3338; display: block; margin: 0; mso-line-height-rule: exactly">
							<span style="padding-right: 10px">
							<?php
								// translators: %s: the run number.
								printf( esc_html__( 'Run #%s', 'visual-regression-tests' ), esc_html( $data['run']->id ) );
							?>
							</span>
							<?php echo wp_kses( Date_Time_Helpers::get_formatted_date_time( $data['run']->finished_at ), [ 'time' => [ 'datetime' => true ] ] ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<td style="border-bottom: 1px dashed #c3c4c7; padding-top: 12px; padding-bottom: 12px;">
						<table cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-spacing: 0;">
							<tr>
								<th style="font-size: 12px; padding-bottom: 4px; line-height: 16px; color: #2C3338; mso-line-height-rule: exactly; text-align: left;">Pages</th>
								<th style="font-size: 12px; padding-bottom: 4px; line-height: 16px; color: #2C3338; mso-line-height-rule: exactly; text-align: right;">Difference</th>
							</tr>
							<?php

							$alerts_post_ids = wp_list_pluck( $data['alerts'], 'post_id' );
							$tests_post_ids = wp_list_pluck( $data['tests'], 'post_id' );

							foreach ( $data['tests'] as $test ) :
								$alert = array_values( array_filter( $data['alerts'], static function( $alert ) use ( $test ) {
									return $alert->post_id === $test->post_id;
								} ) );
								$difference = $alert ? ceil( $alert[0]->differences ) : 0;
								?>
								<tr>
									<td style="padding-top: 2px; padding-bottom: 2px; vertical-align: top; font-size: 12px; line-height: 16px; color: #2C3338; mso-line-height-rule: exactly; text-align: left;">
										<a style="color: #2C3338; text-decoration: none" href="<?php echo esc_url( get_permalink( $test->post_id ) ); ?>"><?php echo esc_html( Url_Helpers::get_relative_permalink( $test->post_id ) ); ?></a>
									</td>
									<td style="padding-top: 2px; padding-bottom: 2px; vertical-align: top; font-size: 12px; line-height: 16px; color: #2C3338; mso-line-height-rule: exactly; text-align: right;">
										<?php $difference_url = $difference ? Url_Helpers::get_alert_page( $alert[0]->id, $data['run']->id ) : Url_Helpers::get_alerts_page( $data['run']->id ); ?>
										<a style="color: #2C3338; text-decoration: none" href="<?php echo esc_url( $difference_url ); ?>">
											<?php
											printf(
												/* translators: %s. Test run receipt diff in pixels */
												esc_html_x( '%spx', 'test run receipt difference', 'visual-regression-tests' ),
												esc_html( number_format_i18n( $difference ) )
											);
											?>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</table>
					</td>
				</tr>
				<tr>
					<td style="border-bottom: 1px dashed #c3c4c7; padding-top: 12px; padding-bottom: 12px;">
						<table cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-spacing: 0;">
							<tr>
								<th style="font-size: 14px; padding-bottom: 4px; line-height: 16px; color: #2C3338; mso-line-height-rule: exactly; text-align: left;">Total</th>
								<th style="font-size: 14px; padding-bottom: 4px; line-height: 16px; color: #2C3338; mso-line-height-rule: exactly; text-align: right;">
									<?php
									printf(
										/* translators: %s. Number of tests */
										esc_html(  _n( '%s Test', '%s Tests', count( $data['tests'] ), 'visual-regression-tests' ) ), count( $data['tests'] )
									); ?>
								</th>
							</tr>
							<tr>
								<td style="padding-top: 2px; padding-bottom: 2px; vertical-align: top; font-size: 12px; line-height: 16px; color: #4ab866; mso-line-height-rule: exactly; text-align: left;"><?php esc_html_e( 'Passed', 'visual-regression-tests' ); ?></td>
								<td style="padding-top: 2px; padding-bottom: 2px; vertical-align: top; font-size: 12px; line-height: 16px; color: #4ab866; mso-line-height-rule: exactly; text-align: right;"><?php echo esc_html( count( $tests_post_ids ) - count( $alerts_post_ids ) ); ?></td>
							</tr>
							<tr>
								<td style="padding-top: 2px; padding-bottom: 2px; vertical-align: top; font-size: 12px; line-height: 16px; color: #b32d2e; mso-line-height-rule: exactly; text-align: left;"><?php esc_html_e( 'Changes Detected', 'visual-regression-tests' ); ?></td>
								<td style="padding-top: 2px; padding-bottom: 2px; vertical-align: top; font-size: 12px; line-height: 16px; color: #b32d2e; mso-line-height-rule: exactly; text-align: right;"><?php echo esc_html( count( $alerts_post_ids ) ); ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="border-bottom: 1px dashed #c3c4c7; padding-top: 12px; padding-bottom: 12px; font-size: 11px; line-height: 16px; color: #2C3338; mso-line-height-rule: exactly;">
						<table>
							<tr>
								<td><?php esc_html_e( 'Trigger', 'visual-regression-tests' ); ?></td>
								<td style="padding-left: 5px;">
									<table cellpadding="0" cellspacing="0" border="0" role="presentation" bgcolor="#f0e8f8" width="100.00%" style="border-radius: 100px; background-color: <?php echo Test_Run::get_trigger_background_color( $data['run'] ); ?>; width: 100%;  border-spacing: 0; border-collapse: separate">
										<tr>
											<td align="center" valign="middle">
												<p style="font-size: 12px; font-weight: 400; line-height: 16px; color: <?php echo Test_Run::get_trigger_text_color( $data['run'] ); ?>; mso-line-height-alt: 16px; margin: 0; padding: 4px 10px"><?php echo esc_html( Test_Run::get_trigger_title( $data['run'] ) ); ?></p>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<?php
							$trigger_note = Test_Run::get_trigger_note( $data['run'] );
							if ( ! empty( $trigger_note ) ) : ?>
							<span style="color: #757575;">
								<?php echo esc_html( $trigger_note ); ?>
							</span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="padding-top: 12px; padding-bottom: 8px; text-align: center">
						<p style="font-size: 13px; line-height: 16px; text-transform: uppercase; color: #2C3338; display: block; margin: 0; mso-line-height-rule: exactly"><strong><?php esc_html_e( 'Test Completed!', 'visual-regression-tests' ); ?></strong></p>

					</td>
				</tr>
				<tr>
					<td width="1">
						<table cellpadding="0" cellspacing="0" border="0" role="presentation" bgcolor="#1954ed" width="100.00%" style="border-radius: 2px; background-color: #1954ed; width: 100%; border-spacing: 0; border-collapse: separate">
							<tr>
								<td align="center" valign="middle">
									<a href="<?php echo Url_Helpers::get_test_run_page( $data['run'] ); ?>" style="font-size: 14px; font-weight: 600; color: white; margin: 0; padding: 0; line-height: 20px; mso-line-height-alt: 20px; text-decoration: none; display: block; padding: 8px 12px;"><?php esc_html_e( 'View Details', 'visual-regression-tests' ); ?></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
		<td></td>
	  </tr>
	</table>
	<table border="0" cellspacing="0" width="100%" bgcolor="#ffffff" style="background-color: #ffffff; font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, sans-serif, Tahoma, system-ui; margin-top: 10px; margin-bottom: 20px;">
		<tr>
			<td style="padding: 0 20px;">
				<p style="font-size: 14px; line-height: 16px; color: #2C3338; display: block; mso-line-height-rule: exactly"><?php esc_html_e( 'Your VRTs squad! 🚀', 'visual-regression-tests' ); ?></p>
				<p style="font-size: 14px; line-height: 16px; color: #2C3338; display: block; mso-line-height-rule: exactly">
					<?php
						printf(
							/* translators: %s: Home URL */
							wp_kses_post( 'This alert was sent by the VRTs plugin on %s.', 'visual-regression-tests' ),
							'<a href="' . esc_url( home_url() ) .'">' . esc_html( home_url() ) . '</a>'
						);
					?>
				</p>
			</td>
		</tr>
	</table>
  </body>
</html>
