<?php

class NSCustomPostType {
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	protected function __construct () {
		
		if ($this->is_own_admin_edit_context()) {
			
			add_action('admin_init', array($this, 'setup_admin_styles'));
			add_action('admin_init', array($this, 'setup_admin_scripts'));
			
		}
		
		add_action('save_post', array($this, 'save_meta_box'));
		
		add_action('init', array($this, 'register'));
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public static function print_meta_box($post, $args) {
		
		extract($args['args']);

		wp_nonce_field(__FILE__, $post_type_name . '[meta-box-nonce]');
		
		$post_meta = get_post_meta($post->ID, $post_type_name, true);

		if (is_array($post_meta)) {
		
			foreach ($post_meta as $key => $value) {
				
				$fields[$key]['value'] = $value;

			}
			
		}
		
		$fields = array_map(array('NSCustomPostType', 'metabox_to_form_field'), $fields, array_fill(0, count($fields), $post_type_name));
		
		echo '<p>' . implode('</p><p>', $fields) . '</p>';
		
	}
	
	public function save_meta_box($post_id) {
		
		if (! wp_verify_nonce($_POST[$this->post_type_name]['meta-box-nonce'], __FILE__)) {
			
			return $post_id;
			
		}
		
		if (! current_user_can('edit_post')) {
			
			return $post_id;
			
		}
		
		unset($_POST[$this->post_type_name]['meta-box-nonce']);
		
		$current_meta = get_post_meta($post_id, $this->post_type_name, true);
		
		$new_meta = wp_parse_args($_POST[$this->post_type_name], $current_meta);
		
		update_post_meta($post_id, $this->post_type_name, $new_meta);
		
		return $post_id;
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public function register () {
		
		$labels = $this->setup_labels();
		
		$args = $this->setup_args($labels);
		
		register_post_type($this->post_type_name, $args);
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public function register_metaboxes () {

		if (is_array($this->metaboxes) && 0 < count($this->metaboxes)) {
			
			array_walk($this->metaboxes, array($this, 'register_metabox'));
			
		}
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	private static function metabox_to_form_field ($field, $post_type_name) {
		
		extract($field);
		
		$value = (isset($value)) ? $value : '';
		
		$return = '';
		
		if (isset($type)) {
			
			switch ($type) {

				case 'text' :

					$return .= '<label>' . $label . ' : <input class="regular-text" name="' . $post_type_name . '[' . $name . ']" type="text" value="' . $value . '" /></label>';

					return $return;

					break;

				case 'colorpicker' :

					$return .= '<label>' . $label . ' : <input id="'. $post_type_name . '-' . $name . '-input" name="' . $post_type_name . '[' . $name . ']" type="hidden" value="' . $value . '" /></label><span class="ns-colorpicker" id="' . $post_type_name . '-' . $name . '"><span></span></span>';

					return $return;

					break;

				case 'slider' :

					$return .= '<label>' . $label . ' : <input id="'. $post_type_name . '-' . $name . '-input" name="' . $post_type_name . '[' . $name . ']" type="hidden" value="' . $value . '" /></label><table style="width:100%;"><tr><td colspan="2"><div class="ns-slider" id="' . $post_type_name . '-' . $name . '"></div></td></tr><tr><td style="text-align:left;">' . $label_right . '</td><td style="text-align:right">' . $label_left . '</td></tr></table>';

					return $return;

					break;

				default:

					break;
					
			}
			
		}
				
		return $return;
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	private function is_own_admin_edit_context() {
		
		if (isset($_GET['post_type']) && $_GET['post_type'] === $this->post_type_name) {
			
			return true;
			
		} elseif (isset($_GET['post']) && get_post_type($_GET['post']) === $this->post_type_name) {
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	private function register_metabox (&$value, $key) {
		
		extract($value);
		
		foreach($page as $key => $value) {
			
			add_meta_box($id, $title, $callback, $value, $context, $priority, $callback_args);
			
		}
		
	}
	
}

?>