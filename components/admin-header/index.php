<?php

use Vrts\Core\Utilities\Url_Helpers;

?>
<nav class="vrts-admin-header">
	<a class="vrts-admin-header__logo" href="<?php echo esc_url( Url_Helpers::get_page_url( 'tests' ) ); ?>">
		<span class="vrts-admin-header__logo-icon"><?php vrts()->logo(); ?></span>
		<h2 class="vrts-admin-header__logo-text"><?php echo esc_html( $data['plugin_name'] ); ?></h2>
	</a>
	<ul class="vrts-admin-header__navigation">
		<?php
		$allowed_html_into_text = [
			'span' => [
				'class' => [],
				'title' => [],
			],
		];
		$allowed_html_into_title = [];
		foreach ( $data['menu_items'] as $menu_item ) {
			printf(
				'<li class="vrts-admin-header__navigation-item" aria-current="%1$s"><a class="vrts-admin-header__navigation-%5$s" href="%2$s" title="%3$s">%4$s</a></li>',
				! empty( $menu_item['is_active'] ) ? 'true' : 'false',
				esc_url( $menu_item['url'] ),
				wp_kses( $menu_item['text'], $allowed_html_into_title ),
				wp_kses( $menu_item['text'], $allowed_html_into_text ),
				strpos( $menu_item['url'], 'vrts-upgrade' ) !== false ? 'button' : 'link'
			);
		}
		?>
	</ul>
</nav>
