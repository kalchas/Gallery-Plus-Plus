(function (win, doc, $) {

	"use strict";
	
	var
	
		default_data_key = 'galleryslidesorter',
		
		methods = {
			
			init : function (options) {
			
				var
				
					$this = $(this),
					
					data = $this.galleryslidesorter('get_element_data'),
					
					defaults = {
						
						"input-name" : "nsgallery",
						"trashcan" : "nsgallery-trash"
						
					},
					
					trash_can
				
				;
				
				data.opts = $.extend(true, defaults, options);
				
				if (0 === $('#' + data.opts["input-name"]).length) {
				
					$('<input id="' + data.opts["input-name"] + '" name="' + data.opts["input-name"] + '[slides]" type="hidden" />').appendTo($this);
					
				}
				
				$this.sortable({
					
					connectWith : '#' + data.opts.trashcan
					
				});
				
				$this.bind('sortupdate', function () {
					
					$this.galleryslidesorter('update_slides');
					
				});
				
				trash_can = $('#' + data.opts.trashcan).droppable({

					drop : function (e, ui) { 
					
						ui.draggable.remove();
						
					},
					
					accept : function (e) {
						
						return ($this.attr('id') === $(e).parent().attr('id'));
						
					},
					
					tolerance : "touch"
 					
				});
				
				data.items_added = $this.children().length;
				
				$this.children().each(function (i, e) {
				
					$(e).delegate('input', 'change', function () {


						var

							$that = $(this),

							key = $that.attr('name'),

							value = $that.attr('value'),

							new_data = {},

							old_data = $that.parent().parent().data('nsslide')

						;

						new_data[key] = value;

						$that.parent().parent().data('nsslide', $.extend(true, old_data, new_data));
						
						$this.trigger('sortupdate');

					});
					
				});
				
				$this.galleryslidesorter('set_element_data', data);
				
				$this.trigger('sortupdate');
				
				return $this;
				
			},
			
			add_slide : function (params) {
				
				var
				
					$this = $(this)
					
				;
				
				$.get(params.url, {
					
					nsgalleryposttype : params.nsgalleryposttype,
					
					"ns-action" : params["ns-action"],
					
					"img" : params.img
					
				}, function (d) {
					
					$this.galleryslidesorter('slide_factory', d, params);
					
				});
				
				return $this;
				
			},
			
			update_slides : function () {
				
				var
				
					$this = $(this),
					
					data = $this.galleryslidesorter('get_element_data')
				
				;
				
				data.slides = [];
				
				$this.children('tr').each(function (i, e) {
				
					if (undefined === $(e).data('nsslide')) {
						
						$(e).data('nsslide', $.parseJSON($(e).attr('data-slide')));
						
					}
					
					data.slides[i] = $(e).data('nsslide');
					
				});
				
				$('#' + data.opts["input-name"]).attr("value", JSON.stringify(data.slides));
				
				$this.galleryslidesorter('set_element_data', data);
				
			},
			
			//Utility methods. These shouldn't have to be called directly, except in tests.
			
			slide_factory : function (d, params) {
				
				var
				
					$this = $(this),
					
					data = $this.galleryslidesorter('get_element_data'),
				
					new_item = $(params.template).tmpl({
						
						ID : JSON.stringify({'ID' : d.ID}),
						
						thumb : d.thumb,
						
						colorpickerID : 'nsslide-' + data.items_added,
						
						fontcolorpickerID : 'nsslide-' + data.items_added + '-font' 
						
					})
				
				;
				
				data.items_added = data.items_added + 1;
				
				$(new_item).data('nsslide', {'ID' : d.ID});
				
				new_item.delegate('input', 'change', function () {
					
				
					var
					
						$that = $(this),
						
						key = $that.attr('name'),
						
						value = $that.attr('value'),
						
						new_data = {},
						
						old_data = $that.parent().parent().data('nsslide')
					
					;
					
					new_data[key] = value;
					
					$that.parent().parent().data('nsslide', $.extend(true, old_data, new_data));
					
					$this.trigger('sortupdate');
					
				});
				
				$this.galleryslidesorter('set_element_data', data);
				
				new_item.appendTo($this);
				
				$this.applycolorpicker();
				
				$this.trigger('sortupdate');
				
			},
			
			get_element_data : function (key, element) {

				var

					$this = $(this),

					data;//End get_element_data var

				//The element should default to $this.

				if (undefined === element) {

					element = $this;

				}

				if (undefined === key) {

					key = default_data_key;

				}

				//Get the data associated with the element if it's available, or an empty object if it's not.

				data = (! $(element).data(key)) ? {} : $(element).data(key);

				return data;

			},

			set_element_data : function (new_data, key, element) {

				var

					$this = $(this),

					data,

					old_data;//End set_element_data var

				//The element should default to $this.

				if (undefined === element) {

					element = $this;

				}

				if (undefined === key) {

					key = default_data_key;

				}

				//Get the data already associated with the element.

				old_data = $this.galleryslidesorter('get_element_data', key, element);

				//Add the new data into the old data.

				data = $.extend(old_data, new_data);

				$(element).data(key, data);

				return data;

			}
			
			
		}
	
	;
	
	$.fn.galleryslidesorter = function (method) {
		
		if (undefined !== methods[method]) {
			
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
			
		} else if ('object' === typeof method  || !method) {
			
			return methods.init.apply(this, arguments);
			
		} else {
			
			$.error('Method ' + method + ' is not available for jquery.galleryslidesorter');
		}
		
	};
	
	
}(window, document, jQuery, undefined));