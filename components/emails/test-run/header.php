<table cellpadding="0" cellspacing="0" border="0" role="presentation" style="width: 100%; padding-bottom: 25px; border-spacing: 0;">
	<tr>
		<td width="1%" valign="middle">
			<img src="<?php echo esc_url(vrts()->get_plugin_url('assets/icons/vrts-logo-icon.svg')); ?>" width="40" height="40" alt="VRTs logo" style="width: 40px; height: 40px; display: block" />
		</td>
		<td width="99%" valign="middle" style="padding-left: 10px;">
			<table border="0" cellspacing="0" width="100%">
				<tr>
					<td valign="middle">
						<p style="font-size: 16px; font-weight: 600; color: #262626; margin: 0; padding: 0; line-height: 26px; mso-line-height-alt: normal"><?php esc_html_e('VRTs Plugin', 'visual-regression-tests')?></p>
					</td>
					<td valign="bottom" style="text-align: right;">
						<p style="font-size: 14px; font-weight: 400; color: #262626; margin: 0; padding: 0; line-height: 20px; mso-line-height-alt: 20px"><?php printf(esc_html_x('Run #%1$d', 'Run id', 'visual-regression-tests'), $test_run->id); ?></p>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
