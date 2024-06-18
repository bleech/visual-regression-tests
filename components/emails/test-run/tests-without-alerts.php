<?php
use Vrts\Core\Utilities\Url_Helpers;
?>
<tr>
	<td style="padding-top: 25px;">
		<table cellpadding="0" cellspacing="0" border="0" role="presentation" width="100.00%" style="width: 100%; border-spacing: 0">
			<tr>
				<td valign="top">
					<p style="font-size: 14px; font-weight: 600; color: #28D1B4; margin: 0; padding: 0; line-height: 20px; mso-line-height-alt: 20px"><?php printf(esc_html_x('Passed (%1$d)', 'Number of tests without changes', 'visual-regression-tests'), count($tests_without_alerts)); ?></p>
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
					<?php foreach($tests_without_alerts as $test_id): ?>
							<li style="margin-top: 10px">
								<span style="font-size: 14px; font-weight: 600; text-align: left; line-height: 20px; mso-line-height-rule: exactly"><?php echo get_the_title($test_id); ?></span><br />
								<a href="<?php echo esc_url(get_the_permalink($test_id)); ?>" style="font-size: 14px; font-weight: 400; color: #1954ed; text-align: left; line-height: 20px; mso-line-height-rule: exactly; text-decoration: none;"><?php echo esc_url(Url_Helpers::get_relative_permalink($test_id)); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</td>
			</tr>
		</table>
	</td>
</tr>
