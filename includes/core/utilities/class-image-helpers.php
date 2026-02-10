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

		return 1.25;
	}

	/**
	 * Get screenshot URL.
	 *
	 * @param object $item The alert or test object.
	 * @param string $type Image type - base, target, comparison.
	 * @param string $size The size of the image.
	 *
	 * @return string
	 */
	public static function get_screenshot_url( $item, $type, $size = 'full' ) {
		$property = "{$type}_screenshot_url";

		if ( ! property_exists( $item, $property ) ) {
			return '';
		}

		$url = 'preview' === $size ? maybe_unserialize( $item->meta )['preview_url'] ?? $item->$property : $item->$property;
		return self::get_cloudfront_url( $url );
	}

	/**
	 * Get the cloudfront URL.
	 *
	 * @param string $url The URL.
	 *
	 * @return string
	 */
	public static function get_cloudfront_url( $url ) {
		return str_replace( 'https://screenshotter-dev.s3.eu-central-1.amazonaws.com/', 'https://images.vrts.app/', $url );
	}
}
