<?php

class NSGallery {
	
	private $ID;
	
	public function __construct ($id = null) {
		
		if (null !== $id) {
		
			$this->ID = $id;
			
			$this->get_options();
			
		}
		
	}
	
	public static function ajax_handler () {
		
		if (isset($_GET['nsnonce'])) {

			if (wp_verify_nonce($_GET['nsnonce'], __FILE__)) {
				
				header("Content-type: application/json");
				
				$gallery = new NSGallery($_GET['nsgallery']);
				
				echo $gallery->view('json', 'slides');
				
				die();
				
			}	
			
		}
		
	}
	
	public static function shortcode ($attributes) {
		
		extract(shortcode_atts(
		
			array(
				
			'id' => ''
			
		), $attributes));
		
		$gallery = new NSGallery($id);
		
		return $gallery->view();
		
	}
	
	public function delete () {}
	
	public function edit () {}
	
	public function view ($mode = 'html', $part = 'null') {
		
		if ('json' === $mode && 'slides' === $part) {
			
			$filosofo = Filosofo_Custom_Image_Sizes::get_instance();
			
			$caption_width = $this->caption_width / 100;

			$width = $this->width;

			$width = $width - ($width * $caption_width);
			
			add_image_size('nsgpp-' . $this->ID, $width, $this->height * 2, true);
			
			$slides = json_decode($this->slides);
			
			foreach ($slides as $key => $value) {
				
				$images = wp_get_attachment_image_src($value->ID, 'nsgpp-' . $this->ID);

				$slides[$key]->img = $images[0];
				
				if (isset($value->link)) {
				
					$slides[$key]->url = $value->link;
					
				}
				
			}
			
			return json_encode(array('slides' => $slides));
			
		} elseif ('html' === $mode) {
			
			$params = array(
			
				'auto_play' => $this->auto_play,
				'caption' => array(
					
					'height' => $this->caption_height . '%',
					'width' => $this->caption_width . '%'
					
				),
				'height' =>  $this->height,
				'menu' => array(
					
					'background-color' => $this->menu_bg_color,
					'height' => $this->menu_height . '%',
					'width' => $this->menu_width . '%'
					
				),
				'play_speed' => $this->play_speed,
				'slides_url' => get_bloginfo('wpurl') . '?nsgallery=' . $this->ID . '&nsnonce=' . wp_create_nonce(__FILE__),
				'transition_speed' => $this->transition_speed,
				'width' => $this->width
				
			);
			
			$content = '<div id="ns-gallery-' . $this->ID . '"></div><script>jQuery(function ($) {

				$("#ns-gallery-'. $this->ID . '").corpmsgslider(' . json_encode($params) . ');

			})</script>';

			return $content;
			
		}
		
	}
	
	private static function hex_to_rgb ($color) {
		
		if (6 === strlen($color)) {
			
			list($r, $g, $b) = array(
				
				$color[0] . $color[1],
				$color[2] . $color[3],
				$color[4] . $color[5]
				
			);
			
		} elseif (3 === strlen($color)) {
			
			list($r, $g, $b) = array(
				
				$color[0] . $color[0],
				$color[1] . $color[1],
				$color[2] . $color[2]
				
			);
			
		} else {
			
			return false;
			
		}
		
		$rgb = array(
			
			hexdec($r),
			hexdec($g),
			hexdec($b)
			
		);
		
		return $rgb;
		
	}
	
	private function get_options () {
		
		$meta = get_post_meta($this->ID, 'nsgallery', true);
		
		foreach ($meta as $key => $value) {
			
			$key = str_replace('-', '_', $key);
			$this->$key = $value;
			
		}
		
		if (property_exists($this, 'menu_bg_color')) {
			
			$rgb = self::hex_to_rgb($this->menu_bg_color);
			
			$alpha = $this->opacity/100;
			
			$this->menu_bg_color = "rgba({$rgb[0]}, {$rgb[1]}, {$rgb[2]}, $alpha)";
			
		}
		
		if (property_exists($this, 'play_speed')) {
			
			if (0 === $this->play_speed) {
				
				$this->auto_play = false;
				
			} else {
			
				$this->auto_play = true;
				
				$this->play_speed = floor((pow(1 / $this->play_speed, 2)) * 1e7);
				
			}
			
		}
		
		if (property_exists($this, 'transition_speed')) {
			
			if ($this->transition_speed < 1) {
				
				$this->transition_speed = 1;
				
			}
			
			$this->transition_speed = floor((pow(1 / $this->transition_speed, 2)) * 1e7);
			
		}
		
	}
	
	private function set_options () {}
	
}

?>