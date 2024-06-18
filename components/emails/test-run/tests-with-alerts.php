<?php
use Vrts\Core\Utilities\Date_Time_Helpers;
use Vrts\Core\Utilities\Url_Helpers;

?>
<tr>
	<td style="padding-top: 25px;">
		<table cellpadding="0" cellspacing="0" border="0" role="presentation" width="100.00%" style="width: 100%; border-spacing: 0">
			<tr>
				<td valign="top">
					<table cellpadding="0" cellspacing="0" border="0" role="presentation" style="border-spacing: 0">
						<tr>
							<td>
								<p style="font-size: 14px; font-weight: 600; color: #e80242; margin: 0; padding: 0; line-height: 20px; mso-line-height-alt: 20px"><?php printf(esc_html_x('Changes Detected (%1$d)', 'Number of tests with changes', 'visual-regression-tests'), count($tests_with_alerts)); ?></p>
							</td>
						</tr>
						<tr>
							<td style="padding-top: 5px">
								<p style="font-size: 14px; font-weight: 400; color: #262626; margin: 0; padding: 0; line-height: 20px; mso-line-height-alt: 20px"><?php echo Date_Time_Helpers::get_formatted_date_time($test_run->finished_at); ?></p>
							</td>
						</tr>
					</table>
				</td>
				<td valign="top" width="1" style="padding-left: 10px;">
					<table cellpadding="0" cellspacing="0" border="0" role="presentation" bgcolor="#1954ed" width="100.00%" style="border-radius: 2px; background-color: #1954ed; width: 100%; border-spacing: 0; border-collapse: separate">
						<tr>
							<td align="center" valign="middle">
								<a href="<?php echo Url_Helpers::get_alerts_page($test_run->id); ?>" style="font-size: 14px; font-weight: 600; color: white; margin: 0; padding: 0; line-height: 20px; mso-line-height-alt: 20px; text-decoration: none; display: block; padding: 8px 12px;"><?php esc_html_e('Review&nbsp;Alerts', 'visual-regression-tests'); ?></a>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="padding-top: 8px;">
					<div style="border-top: 1px solid #ddd;"></div>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="padding-top: 5px;">
					<ul style="list-style-type: disc; margin: 0; padding-left: 1.5em">
						<?php foreach($tests_with_alerts as $index => $test_id): ?>
							<li style="margin-top: 10px">
								<span style="font-size: 14px; font-weight: 600; text-align: left; line-height: 20px; mso-line-height-rule: exactly"><?php echo get_the_title($test_id); ?></span><br />
								<a href="<?php echo esc_url(Url_Helpers::get_alert_page($alerts[$index])); ?>" style="font-size: 14px; font-weight: 400; color: #1954ed; text-align: left; line-height: 20px; mso-line-height-rule: exactly; text-decoration: none;"><?php echo esc_url(Url_Helpers::get_relative_permalink($test_id)); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</td>
			</tr>
		</table>
	</td>
</tr>
