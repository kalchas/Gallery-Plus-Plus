<?php
/**
 * This is a slightly modified version of the custom image sizer provided by Austin Matzko in his Custom Image Sizes
 * Plugin. I've changed it to make the Image Sizer a singleton (after all, we won't ever need more than one). I've
 * retained the plugin header here for documentation, credit purposes.
 */

/*
Custom Image Sizes
http://ilfilosofo.com/blog/wordpress-plugins/filosofo-custom-image-sizes/
A plugin that creates custom image sizes for image attachments.
Austin Matzko
http://ilfilosofo.com
1.0
*/

class Filosofo_Custom_Image_Sizes {
	
	private static $instance;

	private function __construct()
	{
		add_filter('image_downsize', array(&$this, 'filter_image_downsize'), 99, 3);

	}
	
	public static function get_instance () {

		if (empty(self::$instance)) {
			
			$classname = __CLASS__;

			self::$instance = new $classname;
			
		}
		
		return self::$instance;

	}

	/**
	 * Callback for the "image_downsize" filter.
	 *
	 * @param bool $ignore A value meant to discard unfiltered info returned from this filter.
	 * @param int $attachment_id The ID of the attachment for which we want a certain size.
	 * @param string $size_name The name of the size desired.
	 */
	public function filter_image_downsize($ignore = false, $attachment_id = 0, $size_name = 'thumbnail')
	{
		global $_wp_additional_image_sizes;

		$attachment_id = (int) $attachment_id;
		
		if (! is_string($size_name)) {
			
			return; //Fixes bugs on the admin side.
			
		}
		
		$size_name = trim($size_name);
		
		$meta = wp_get_attachment_metadata($attachment_id);

		/* the requested size does not yet exist for this attachment */
		if (
			empty( $meta['sizes'] ) ||
			empty( $meta['sizes'][$size_name] )
		) {
			// let's first see if this is a registered size
			if ( isset( $_wp_additional_image_sizes[$size_name] ) ) {
				$height = (int) $_wp_additional_image_sizes[$size_name]['height'];
				$width = (int) $_wp_additional_image_sizes[$size_name]['width'];
				$crop = (bool) $_wp_additional_image_sizes[$size_name]['crop'];

			// if not, see if name is of form [width]x[height] and use that to crop
			} else if ( preg_match('#^(\d+)x(\d+)$#', $size_name, $matches) ) {
				$height = (int) $matches[2];
				$width = (int) $matches[1];
				$crop = true;
			}

			if ( ! empty( $height ) && ! empty( $width ) ) {
				$resized_path = $this->_generate_attachment($attachment_id, $width, $height, $crop);
				$fullsize_url = wp_get_attachment_url($attachment_id);

				$file_name = basename($resized_path);

				$new_url = str_replace(basename($fullsize_url), $file_name, $fullsize_url);

				if ( ! empty( $resized_path ) ) {
					$meta['sizes'][$size_name] = array(
						'file' => $file_name,
						'width' => $width,
						'height' => $height,
					);
					
					wp_update_attachment_metadata($attachment_id, $meta);

					return array(
						$new_url,
						$width,
						$height,
						true
					);
				}
			}
		}

		return false;
	}

	/**
	 * Creates a cropped version of an image for a given attachment ID.
	 *
	 * @param int $attachment_id The attachment for which to generate a cropped image.
	 * @param int $width The width of the cropped image in pixels.
	 * @param int $height The height of the cropped image in pixels.
	 * @param bool $crop Whether to crop the generated image.
	 * @return string The full path to the cropped image.  Empty if failed.
	 */
	private function _generate_attachment($attachment_id = 0, $width = 0, $height = 0, $crop = true)
	{
		$attachment_id = (int) $attachment_id;
		$width = (int) $width;
		$height = (int) $height;
		$crop = (bool) $crop;

		$original_path = get_attached_file($attachment_id);

		// fix a WP bug up to 2.9.2
		if ( ! function_exists('wp_load_image') ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		
		$resized_path = @image_resize($original_path, $width, $height, $crop);
		
		if ( 
			! is_wp_error($resized_path) && 
			! is_array($resized_path)
		) {
			return $resized_path;

		// perhaps this image already exists.  If so, return it.
		} else {
			$orig_info = pathinfo($original_path);
			$suffix = "{$width}x{$height}";
			$dir = $orig_info['dirname'];
			$ext = $orig_info['extension'];
			$name = basename($original_path, ".{$ext}"); 
			$destfilename = "{$dir}/{$name}-{$suffix}.{$ext}";
			if ( file_exists( $destfilename ) ) {
				return $destfilename;
			}
		}

		return '';
	}
}