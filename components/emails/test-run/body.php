<?php

use Vrts\Models\Test_Run;
?>
<table cellpadding="0" cellspacing="0" border="0" role="presentation" style="width: 100%; padding: 15px; border-spacing: 0; background-color: #ffffff; border-radius: 2px; box-shadow: 0px 5px 5px rgba(0, 0, 0, 0.08);">
	<tr>
		<td>
			<p style="font-size: 14px; line-height: 16px; color: #262626; display: block; mso-line-height-rule: exactly"><?php esc_html_e('Hey there 👋', 'visual-regression-tests') ?></p>
			<p style="font-size: 14px; line-height: 16px; color: #262626; display: block; mso-line-height-rule: exactly"><?php esc_html_e("We've detected visual changes on your website during a recent test run:", 'visual-regression-tests') ?></p>
		</td>
	</tr>
	<tr>
		<td align="left" style="padding-top: 15px;">
			<table cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin: 0; border-spacing: 0">
				<tr>
					<td width="85">
						<p style="font-size: 16px; font-weight: 700; color: #262626; margin: 0; padding: 0; width: 85px; line-height: 23px; mso-line-height-alt: 24px"><?php printf(esc_html_x('Run #%1$d', 'Run id', 'visual-regression-tests'), $test_run->id); ?></p>
					</td>
					<td width="10" style="padding-left: 10px;">
						<table cellpadding="0" cellspacing="0" border="0" role="presentation" bgcolor="#f0e8f8" width="100.00%" style="border-radius: 100px; background-color: <?php echo Test_Run::get_trigger_background_color($test_run); ?>; width: 100%;  border-spacing: 0; border-collapse: separate">
							<tr>
								<td align="center" valign="middle">
									<p style="font-size: 14px; font-weight: 400; line-height: 16px; color: <?php echo Test_Run::get_trigger_text_color($test_run); ?>; mso-line-height-alt: 16px; margin: 0; padding: 4px 10px"><?php esc_html_e(Test_Run::get_trigger_title($test_run), 'visual-regression-tests') ?></p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td style="padding-top: 5px;">
			<table cellpadding="0" cellspacing="0" border="0" role="presentation" width="100.00%" style="width: 100%; border-spacing: 0">
				<tr>
					<td valign="top" width="85">
						<p style="font-size: 14px; font-weight: 400; color: #262626; margin: 0; padding: 0; line-height: 20px; mso-line-height-alt: 20px"><?php printf(esc_html_x('%1$d Tests', 'Test count', 'visual-regression-tests'), count($tests)); ?></p>
					</td>
					<td valign="top" style="padding-left: 10px;">
						<p style="font-size: 14px; font-weight: 400; text-align: left; line-height: 20px; color: rgba(38, 38, 38, 0.6); mso-line-height-rule: exactly; margin: 0; padding: 0;"><?php esc_html_e('(trigger meta info that can be multiple lines long and look like this if it has a more of information)', 'visual-regression-tests') ?></p>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php if (count($tests_with_alerts) > 0) include __DIR__ . '/tests-with-alerts.php'; ?>
	<?php if (count($tests_without_alerts) > 0) include __DIR__ . '/tests-without-alerts.php'; ?>
	<tr>
		<td style="padding-top: 25px;">
			<p style="font-size: 14px; line-height: 16px; color: #262626; display: block; mso-line-height-rule: exactly"><?php esc_html_e('Your VRTs squad! 🚀', 'visual-regression-tests'); ?></p>
		</td>
	</tr>
</table>
