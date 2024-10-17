<?php

use Vrts\Core\Utilities\Url_Helpers;

?>
<nav class="vrts_admin_header_menu">
	<a class="vrts_logo_link" href="<?php echo esc_url( Url_Helpers::get_page_url( 'tests' ) ); ?>">
		<h2>
			<span class="vrts_logo"><?php vrts()->logo(); ?></span>
			<?php echo esc_html( $data['plugin_name'] ); ?>
		</h2>
	</a>
	<ul class="vrts_navigation">
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
				'<li class="vrts_navigation_item %1$s"><a class="vrts_navigation_link" href="%2$s" title="%3$s">%4$s</a></li>',
				! empty( $menu_item['is_active'] ) ? ' is-active' : '',
				esc_url( $menu_item['url'] ),
				wp_kses( $menu_item['text'], $allowed_html_into_title ),
				wp_kses( $menu_item['text'], $allowed_html_into_text )
			);
		}
		?>
	</ul>
</nav>
