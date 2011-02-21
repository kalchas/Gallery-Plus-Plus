<?php

class NSGalleryPlusPlus {
	
	private static $instance;
	
	private function __construct () {
		
		NSGalleryPostType::get_instance();
		
		if (! is_admin()) {
			
			wp_register_script('jquery-bbq', NSGPP_DIR_URL . 'js/jquery.ba-bbq.dev.js', array('jquery'));
			wp_enqueue_script('jquery-corpmsgslider', NSGPP_DIR_URL . 'js/jquery.corpmsgslider.dev.js', array('jquery-bbq'), false, false);
			wp_enqueue_style('jquery-corpmsgslider', NSGPP_DIR_URL . 'css/corpmsgslider.css');
			add_action('parse_request', array('NSGallery', 'ajax_handler'));
			
		}
		
		add_shortcode('nsgalleryplusplus', array('NSGallery', 'shortcode'));
		
	}

	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public static function get_instance () {
		
		if (empty(self::$instance)) {
			
			$classname = __CLASS__;
			self::$instance = new $classname;
			
		}
		
		return self::$instance;
		
	}
	
}

?>