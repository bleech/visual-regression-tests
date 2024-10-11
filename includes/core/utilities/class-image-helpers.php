<?php

namespace Vrts\Core\Utilities;

class Image_Helpers {

	/**
	 * Get the image height and width string.
	 *
	 * @param object $alert The alert object.
	 *
	 * @return string
	 */
	public static function alert_image_hwstring( $alert ) {
		$meta = maybe_unserialize( $alert->meta );
		$width = $meta['width'] ?? 1265;
		$height = $meta['height'] ?? 1800;
		return image_hwstring( $width, $height );
	}

	/**
	 * Get the image aspect ratio string.
	 *
	 * @param object $alert The alert object.
	 *
	 * @return int
	 */
	public static function alert_image_aspect_ratio( $alert ) {
		$meta = maybe_unserialize( $alert->meta );

		if ( isset( $meta['width'], $meta['height'] ) ) {
			return round( $meta['width'] / $meta['height'], 2 );
		}

		return 0;
	}

	/**
	 * Get comparison thumbnail URL.
	 *
	 * @param object $alert The alert object.
	 *
	 * @return string
	 */
	public static function get_comparison_thumbnail_url( $alert ) {
		$preview_url = maybe_unserialize( $alert->meta )['preview_url'] ?? null;
		return $preview_url ? $preview_url : $alert->comparison_screenshot_url;
	}
}
