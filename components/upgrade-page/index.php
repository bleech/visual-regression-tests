<div class="wrap vrts_upgrade_page">
	<iframe
		src="https://bleech.de/en/vrts-upgrade/?current-tier=<?php echo '1' === $data['has_subscription'] ? 'pro' : 'free'; ?>"
		title="<?php echo esc_html( $data['title'] ); ?>"
		loading="lazy"
		width="100%"
		id="vrts_upgrade_iframe">
	</iframe>
</div>
