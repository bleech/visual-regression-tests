<nav class="vrts_admin_header_menu">
	<h2>
		<span class="vrts_logo">
			<?php
				echo wp_kses( vrts()->get_plugin_logo_icon( false ), [
					'svg' => [
						'xmlns' => [],
						'width' => [],
						'height' => [],
						'viewbox' => [],
						'style' => [],
						'xml:space' => [],
					],
					'path' => [
						'fill' => [],
						'd' => [],
					],
				]);
				?>
		</span>
		<?php echo esc_html( $data['plugin_name'] ); ?>
	</h2>
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
