<?php

class NSGalleryPostType extends NSCustomPostType {
	
	private static $instance;
	
	protected $metaboxes = array(
		
		'nsgallery-content' => array(
			
			'id' => 'nsgallery-content',
			'callback' => array('NSGalleryPostType', 'main_canvas'),
			'page' => array('nsgallery'),
			'context' => 'normal',
			'priority' => 'high',
			'callback_args' => array()			
			
		),
		
		'nsgallery-props' => array(
			
			'id' => 'nsgallery-props',
			'callback' => array('NSCustomPostType', 'print_meta_box'),
			'page' => array('nsgallery'),
			'context' => 'side',
			'priority' => 'high',
			'callback_args' => array(
				
				'fields' => array(
					
					'width' => array(
						
						'type' => 'text'
						
					),
					
					'height' => array(
						
						'type' => 'text'
						
					),
					
					'bg-color' => array(
						
						'type' => 'colorpicker'
						
					),
					
					'transition-speed' => array(
						
						'type' => 'slider'
						
					),
					
					'play-speed' => array(
						
						'type' => 'slider'
						
					)
					
				)
				
			)			
			
		),
		
		'nsmenu-props' => array(
			
			'id' => 'nsmenu-props',
			'callback' => array('NSCustomPostType', 'print_meta_box'),
			'page' => array('nsgallery'),
			'context' => 'side',
			'priority' => 'high',
			'callback_args' => array(
				
				'fields' => array(
					
					'menu-width' => array(
						
						'type' => 'text'
						
					),
					
					'menu-height' => array(
						
						'type' => 'text'
						
					),
					
					'menu-bg-color' => array(
						
						'type' => 'colorpicker'
						
					),
					
					'opacity' => array(
						
						'type' => 'slider'
						
					)
					
				)
				
			)
			
		),
		
		'nscaptionarea-props' => array(
			
			'id' => 'nscaptionarea-props',
			'callback' => array('NSCustomPostType', 'print_meta_box'),
			'page' => array('nsgallery'),
			'context' => 'side',
			'priority' => 'high',
			'callback_args' => array(
				
				'fields' => array(
					
					'caption-width' => array(
						
						'type' => 'text'
						
					),
					
					'caption-height' => array(
						
						'type' => 'text'
						
					)
					
				)
				
			)
			
		)
		
	);
	
	protected $post_type_name = 'nsgallery';
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	protected function __construct () {
		
		parent::__construct();
		
		$this->setup_meta_box_labels();
		
		add_action('admin_init', array('NSGalleryPostType', 'ajax_handler'));
		
		add_action('save_post', array('NSGalleryPostType', 'save_post'));
		
	}
	
	public static function ajax_handler () {

		if (isset($_GET['nsgalleryposttype']) && wp_verify_nonce($_GET['nsgalleryposttype'], __FILE__)) {
			
			switch ($_GET['ns-action']) {
				
				case 'fetch-id-and-thumb' :
					
					$id = self::get_attachment_id_from_src($_GET['img']);

					$thumb = wp_get_attachment_image($id, array(36, 36), false);
					
					header("Content-type: application/json");

					echo json_encode(array('ID' => $id, 'thumb' => $thumb));

					exit(0);
					
					break;
					
				default :
					
					break;
					
				
			}
			
		}
		
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
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public static function main_canvas ($post) {
		
		$meta = get_post_meta($post->ID, $post->post_type, true);
		
		$slides = false;
		
		if (is_array($meta) && isset($meta['slides'])) {
		
			$slides = json_decode($meta['slides']);
			
		}
		
		?>
		<script id="gallery-image" type="text/x-jquery-tmpl">
		
			<tr data-slide="${ID}">
				<td style="text-align:center;vertical-align:middle;">
					{{html thumb}}
				</td>
				<td style="vertical-align:middle;">
					<input name="caption" type="text" value="${caption}" />
				</td>
				<td style="vertical-align:middle;">
					<input name="link" type="text" value="${link}" />
				</td>
				<td style="text-align:center;vertical-align:middle;">
					<span id="${colorpickerID}" class="ns-colorpicker">
						<span></span>
					</span>
					<input id="${colorpickerID}-input" name="bgcolor" type="hidden" value="${bgcolor}"
				</td>
				<td style="text-align:center;vertical-align:middle;">
					<span id="${fontcolorpickerID}" class="ns-colorpicker">
						<span></span>
					</span>
					<input id="${fontcolorpickerID}-input" name="bgcolor" type="hidden" value="${bgcolor}"
				</td>
			</tr>
			
		</script>
		<div id="media-buttons" class="hide-if-no-js">	
		<?php
		
		echo __('Upload/Insert') . ' <a href="'. $_SERVER['PHP_SELF'] . '" id="ns-image-upload"><img alt="' . __('Add an Image') . '" src="' . get_admin_url() . 'images/media-button-image.gif?ver=20100531" /></a>';
		
		?>
		</div>
		<p class="help"></p>
		<table class="widefat">
			<thead>
				<th style="width:20%;"><?php echo __('Image'); ?></th>
				<th style="width:20%;"><?php echo __('Caption'); ?></th>
				<th style="width:20%;"><?php echo __('Link'); ?></th>
				<th style="text-align:center;width:20%;"><?php echo __('Background Color'); ?></th>
				<th style="text-align:center;width:20%;"><?php echo __('Font Color'); ?></th>
			</thead>
			<tbody id="gallery-list">
				<?php
				
					if ($slides && is_array($slides)) {
						
						foreach ($slides as $key => $value) {
							
							?>
			
							<tr data-slide='<?php echo json_encode($value); ?>'>
								<td style="text-align:center;vertical-align:middle;">
									<?php

									echo wp_get_attachment_image($value->ID, array(36, 36), false);	
									
									?>
								</td>
								<td style="vertical-align:middle;">
									<input name="caption" type="text" value="<?php if (property_exists($value, 'caption')) { echo $value->caption; } ?>" />
								</td>
								<td style="vertical-align:middle;">
									<input name="link" type="text" value="<?php if (property_exists($value, 'link')) { echo $value->link; } ?>" />
								</td>
								<td style="text-align:center;vertical-align:middle;">
									<span id="nsslide-<?php echo $key;?>" class="ns-colorpicker">
										<span></span>
									</span>
									<input id="nsslide-<?php echo $key;?>-input" name="bgcolor" type="hidden" value="<?php echo $value->bgcolor; ?>"
								</td>
								<td style="text-align:center;vertical-align:middle;">
									<span id="nsslide-<?php echo $key;?>-font" class="ns-colorpicker">
										<span></span>
									</span>
									<input id="nsslide-<?php echo $key;?>-font-input" name="color" type="hidden" value="<?php echo $value->color; ?>"
								</td>
							</tr>
							
							<?php
							
						}
						
					}
				
				?>
			</tbody>
		</table>
		<input id="nsgallery" name="nsgallery[slides]" type="hidden" value='<?php echo $meta['slides']; ?>'>
		<p>
			<label><?php echo __('Slideshow Type:'); ?> 
				<select id="nsgpp-js" name="nsgallery[javascript]">
					<option value="corpmsgslider">Corporate Message Slider</option>
					<option value="galleryview">Gallery View</option>
				</select>
			</label>
		</p>
		<div id="nsgallery-trash"></div>
		<script>
		
			jQuery(function ($) {
			
				var gallery_list = $('#gallery-list').galleryslidesorter();
				
				$('#ns-image-upload').wpmediabox({
					
					"callback" : function (html) {
					
						gallery_list.galleryslidesorter('add_slide', {
						
							"url" : window.location,
							"ns-action" : "fetch-id-and-thumb",
							"img" : $(html).attr('href'),
							"nsgalleryposttype" : "<?php echo wp_create_nonce(__FILE__); ?>",
							"template" : $('#gallery-image')
						
						});
					
					}
				
				});
				
			});
		
		</script>
		<?php
		
	}
	
	public static function save_post ($post_id) {
		
		global $wpdb;

		$wpdb->update( $wpdb->posts, array( 'post_content' => "[nsgalleryplusplus id='$post_id']" ), array("ID" => $post_id ));
		
		return $post_id;
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public function setup_admin_scripts () {
		
		wp_enqueue_script('json2', NSGPP_DIR_URL . 'js/json2.dev.js', array(), false, false);
		wp_enqueue_script('jquery-colorpicker', NSGPP_DIR_URL . 'js/colorpicker.dev.js', array('jquery'), false, false);
		wp_enqueue_script('jquery-tmpl', NSGPP_DIR_URL . 'js/jquery.tmpl.dev.js', array('jquery'), false, false);
		//wp_enqueue_script('jquery-bbq', NSGPP_DIR_URL . 'js/jquery.ba-bbq.dev.js', array('jquery'), false, false);
		wp_enqueue_script('jquery-ns-applycolorpicker', NSGPP_DIR_URL . 'js/jquery.ns-applycolorpicker.dev.js', array('jquery-colorpicker'), false, false);
		wp_enqueue_script('jquery-ns-galleryslidesorter', NSGPP_DIR_URL . 'js/jquery.ns-galleryslidesorter.dev.js', array('jquery-ui-sortable', 'json2', 'jquery-ns-applycolorpicker', 'jquery-ui-droppable'), false, false);
		wp_enqueue_script('jquery-ns-mediabox', NSGPP_DIR_URL . 'js/jquery.ns-mediabox.dev.js', array('jquery'), false, false);
		wp_enqueue_script('jquery-ui-slider', NSGPP_DIR_URL . 'js/jquery.ui.slider.dev.js', array('jquery', 'jquery-ui-core'), false, false);
		wp_enqueue_script('jquery-ns-applyslider', NSGPP_DIR_URL . 'js/jquery.ns-applyslider.dev.js', array('jquery-ui-slider'), false, false);
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	public function setup_admin_styles () {
		
		wp_enqueue_style('gallery-plus-plus-style', NSGPP_DIR_URL . 'css/gallery-plus-plus.dev.css');
		wp_enqueue_style('jquery-colorpicker-style', NSGPP_DIR_URL . 'css/colorpicker.dev.css');
		wp_enqueue_style('jquery-ui-slider', NSGPP_DIR_URL . 'css/jquery.ui.slider.dev.css');
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	protected function setup_args ($labels = array()) {
		
		$args = array(
			
			'can_export' => true,
			'capability_type' => 'post',
			'description' => __('An image gallery.'),
			'has_archive' => true,
			'hierarchical' => true,
			'exclude_from_search' => false,
			'labels' => $labels,
			'menu_position' => 10,
			'public' => true,
			'publicly_queryable' => true,
			'register_meta_box_cb' => array($this, 'register_metaboxes'),
			'rewrite' => array(
				
				'feeds' => true,
				'pages' => true,
				'slug' => 'gallery',
				'with_front' => true
				
			),
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'show_ui' => true,
			'supports' => array('author', 'thumbnail', 'title'),
			'taxonomies' => array()
			
		);
		
		return $args;
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	protected function setup_labels () {
		
		$labels = array(
			
			'add_new' => _x('Add New', 'gallery'),
			'add_new_item' => __('Add New Gallery'),
			'edit_item' => __('Edit Gallery'),
			'menu_name' => __('Galleries'),
			'name' => __('Galleries'),
			'new_item' => __('New Gallery'),
			'not_found' => __('No galleries found.'),
			'not_found_in_trash' => __('No galleries found in trash.'),
			'parent_item_colon' => __('Parent Gallery'),
			'search_items' => __('Search Galleries'),
			'singular_name' => __('Gallery'),
			'view_item' => __('View Gallery')
			
		);
		
		return $labels;
		
	}
	
	/**
	 * @since 0.1
	 * @author jameslafferty
	 */
	protected function setup_meta_box_labels () {
		
		$this->metaboxes['nsgallery-content']['title'] = __('Gallery Content');
		
		$this->metaboxes['nscaptionarea-props']['title'] = __('Caption Area Properties');
		$this->metaboxes['nscaptionarea-props']['callback_args']['fields']['caption-width']['label'] = __('Width (in %)');
		$this->metaboxes['nscaptionarea-props']['callback_args']['fields']['caption-width']['name'] = 'caption-width';
		$this->metaboxes['nscaptionarea-props']['callback_args']['fields']['caption-height']['label'] = __('Height (in %)');
		$this->metaboxes['nscaptionarea-props']['callback_args']['fields']['caption-height']['name'] = 'caption-height';
		$this->metaboxes['nscaptionarea-props']['callback_args']['post_type_name'] = $this->post_type_name;
		
		$this->metaboxes['nsgallery-props']['title'] = __('Gallery Properties');
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['width']['label'] = __('Width (in px)');
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['width']['name'] = 'width';
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['height']['label'] = __('Height (in px)');
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['height']['name'] = 'height';
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['bg-color']['label'] = __('Background Color');
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['bg-color']['name'] = 'bg-color';
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['play-speed']['label'] = __('Play Speed');
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['play-speed']['label_right'] = __('Slower');
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['play-speed']['label_left'] = __('Faster');
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['play-speed']['name'] = 'play-speed';
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['transition-speed']['label'] = __('Transition Speed');
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['transition-speed']['label_right'] = __('Slower');
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['transition-speed']['label_left'] = __('Faster');
		$this->metaboxes['nsgallery-props']['callback_args']['fields']['transition-speed']['name'] = 'transition-speed';
		$this->metaboxes['nsgallery-props']['callback_args']['post_type_name'] = $this->post_type_name;
		
		$this->metaboxes['nsmenu-props']['title'] = __('Menu Properties');
		$this->metaboxes['nsmenu-props']['callback_args']['fields']['menu-width']['label'] = __('Width (in %)');
		$this->metaboxes['nsmenu-props']['callback_args']['fields']['menu-width']['name'] = 'menu-width';
		$this->metaboxes['nsmenu-props']['callback_args']['fields']['menu-height']['label'] = __('Height (in %)');
		$this->metaboxes['nsmenu-props']['callback_args']['fields']['menu-height']['name'] = 'menu-height';
		$this->metaboxes['nsmenu-props']['callback_args']['fields']['menu-bg-color']['label'] = __('Background Color');
		$this->metaboxes['nsmenu-props']['callback_args']['fields']['menu-bg-color']['name'] = 'menu-bg-color';
		$this->metaboxes['nsmenu-props']['callback_args']['fields']['opacity']['label'] = __('Opacity');
		$this->metaboxes['nsmenu-props']['callback_args']['fields']['opacity']['label_right'] = __('More Transparent');
		$this->metaboxes['nsmenu-props']['callback_args']['fields']['opacity']['label_left'] = __('More Opaque'); 
		$this->metaboxes['nsmenu-props']['callback_args']['fields']['opacity']['name'] = 'opacity';
		$this->metaboxes['nsmenu-props']['callback_args']['post_type_name'] = $this->post_type_name;
		
	}
	
	protected static function get_attachment_id_from_src ($image_src) {
		
		global $wpdb;

		$query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";

		$id = $wpdb->get_var($query);

		return $id;
		
	}
	
}

?>