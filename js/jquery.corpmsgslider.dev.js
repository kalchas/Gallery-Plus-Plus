(function (win, doc, $, undefined) {
	
	'use strict';
	
	var
		
		//We don't really need to validate very much apart from a url, so we've borrowed this Regex from the jQuery validation plugin.
		
		url_regex = /^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
	
		methods = {
		
			init : function (options) {
				
				var 
					$this = $(this),
					
					data = $this.corpmsgslider('get_element_data'),
					
					defaults = {
						
						"auto_play" : true,
						"border" : "3px solid #000",
						"menu" : {
							
							"background-color" : "rgba(0, 0, 0, 0.5)",
							"height" : "10%",
							"width" : "65%"
							
						},
						"caption" : {
							
							"width" : "35%"
							
						},
						"slides_url" : false,
						"play_speed" : 1500,
						"transition_speed" : 2000,
						"height" : 366,
						"image_x" : "100%",
						"image_y" : "0%",
						'width' : 850
						
					};//End init var
				
				data.opts = $.extend(true, defaults, options);
				
				data.opts.play_speed = (data.opts.play_speed < data.opts.transition_speed) ? data.opts.transition_speed : data.opts.play_speed;
				
				$('.no-js', $this).hide();
				
				$this.corpmsgslider('set_element_data', data);
				
				if (false !== data.opts.slides_url) {

					$this.corpmsgslider('wireframe');
					$this.corpmsgslider('get_slides', data.opts.slides_url);
					
				}
				
				return $this;
				
			},
			
			get_slides : function (slides_url) {
				
				var
				
					$this = $(this),
					
					data = $this.corpmsgslider('get_element_data');//End get_slides var
				
				$.ajax({
					
					"dataType" : "json",
					
					success : function (xmlHttpRequest, textStatus) {
						
						var
						
							iter;//End success var.
						
						data.slides = (undefined === data.slides) ? [] : data.slides;
						
						iter = data.slides.length;
						
						$(xmlHttpRequest.slides).each(function (i, e) {
							
							data.slides[iter + i] = e;
							
							$this.corpmsgslider('button_factory', iter + i, data);
							
						});
						
						$this.corpmsgslider('set_element_data', data);
						
						$this.corpmsgslider('swap_slides', data.slides.length - (iter + 1));
						
						if (true === data.opts.auto_play) {
							
							$this.corpmsgslider('play_slides');
							
						}
						
						return $this;
						
					},
					
					url : slides_url
					
				});
				
				return $this;
				
			},
			
			play_slides : function () {
				
				var
				
					$this = $(this),
					
					data = $this.corpmsgslider('get_element_data'),
				
					play = function () {
					
						var
						
							data = $this.corpmsgslider('get_element_data'); //End play var
						
						data.current_slide = (undefined === data.current_slide) ? 1 : data.current_slide;
						
						if (data.slides.length > data.current_slide + 1) {
							
							data.current_slide += 1;
							
							$this.corpmsgslider('swap_slides', data.current_slide);
							
							$this.corpmsgslider('set_element_data', data);
							
							setTimeout(play, data.opts.play_speed);
							
						} else {
							
							data.current_slide = 0;
							
							$this.corpmsgslider('swap_slides');
							
							data.menu.hide();
							
							data.menu.css("visibility", "visible");
							
							data.menu.slideDown(500);
							
						}
						
					};//End play_slides var
				
				setTimeout(play, data.opts.play_speed);
				
				return $this;
				
			},
			
			swap_slides : function (new_slide) {
				
				var
				
					$this = $(this),
					
					data = $this.corpmsgslider('get_element_data');//End swap_slides var
				
				if (undefined === data.current_slide) {
					
					data.current_slide = 0;
					
				} else {
					
					data.current_slide = (undefined === new_slide) ? data.current_slide : parseInt(new_slide, 10);
					
				}
				
				if (undefined === data.slides[data.current_slide][0] || 'DIV' !== data.slides[data.current_slide][0].tagName) {
					
					data.slides[data.current_slide] = $this.corpmsgslider('slide_factory', data.slides[data.current_slide], data);
					
				}
				
				data.slides[data.current_slide].hide();
				
				data.slides[data.current_slide].appendTo($this);

				data.slides[data.current_slide].prev().fadeTo(data.opts.transition_speed, 0, function () {
					
					$(this).detach();
					
				});
				
				data.slides[data.current_slide].fadeTo(data.opts.transition_speed, 1);
				
				data.buttons[data.current_slide].addClass('cpmsl-btn-active');
				
				data.buttons[data.current_slide].siblings().removeClass('cpmsl-btn-active');
				
				$this.corpmsgslider('set_element_data', data);
				
				return $this;
				
			},
			
			wireframe : function () {
			
				var
				
					$this = $(this),
					
					data = $this.corpmsgslider('get_element_data');//End wireframe var
				
				data.menu = $('<div />', {
					
					"class" : "cpmsl-menu",
					
					"css" : {

						"background-color" : data.opts.menu["background-color"],
						"height" : (parseInt(data.opts.menu.height, 10) / 100) * data.opts.height + 'px',
						"width" : data.opts.menu.width
						
					},
					
					"html" : "&nbsp;"
					
				});
				
				$this.hide();
				
				$this.addClass('cpmsl');
				
				$this.css({
				
					"border" : data.opts.border,
					"height" : data.opts.height + 'px',
					"width" : data.opts.width + 'px'
					
				});
				
				//Save the original HTML, then clobber it. This might be useful when I get around to writing in a non-AJAX API.
				data.original_html = $this.html();
				
				$this.html('');
				
				//Insert a buffer so that we don't wind up clobbering the menu when we swap slides.
				if (0 === $this.children().length) {
					
					$this.append('<div />');
					
				}
				
				//Now insert the menu.
				data.menu.prependTo($this);
				
				if (true === data.opts.auto_play) {
					
					data.menu.css("visibility", "hidden");
					
				}
				
				$this.slideDown(1500);
				
				$this.corpmsgslider('set_element_data', data);
				
				return $this;
				
			},
			
			//Utility methods. These shouldn't have to be called directly, except in tests.
			get_element_data : function (key, element) {
				
				var
				
					$this = $(this),
				
					data;//End get_element_data var
				
				//The element should default to $this.
				
				if (undefined === element) {
					
					element = $this;
					
				}
				
				if (undefined === key) {
					
					key = 'corpmsgslider';
					
				}
				
				//Get the data associated with the element if it's available, or an empty object if it's not.
				
				data = (undefined === $(element).data(key) || null === $(element).data(key)) ? {} : $(element).data(key);
				
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
					
					key = 'corpmsgslider';
					
				}
				
				//Get the data already associated with the element.
				
				old_data = $this.corpmsgslider('get_element_data', key, element);
				
				//Add the new data into the old data.
				
				data = $.extend(old_data, new_data);
				
				$(element).data(key, data);
				
				return data;
				
			},
			
			button_factory : function (index, data) {
				
				var
				
					$this = $(this),
					
					button = $('<div />', {

						"class" : "cpmsl-btn",

						"text" : index + 1

					}).bind('click', function () {

						var 

							data = $this.corpmsgslider('get_element_data');//End click handler var

						if (index !== data.current_slide) {

							$this.corpmsgslider('swap_slides', index);

						}

					}),
					
					menu_height = (parseInt(data.opts.menu.height, 10) / 100) * data.opts.height;//End button_factory var
				
				button.appendTo(data.menu).css({
				
					"margin-top" : 0.5 + (menu_height - button.height()) / 2 + 'px',
					"width" : button.height() + 'px'
					
				}).after('&nbsp;');
				
				if (true !== data.opts.auto_play && 0 === index) {
					
					button.addClass('cpmsl-btn-active');
					
				}
				
				data.buttons = (undefined === data.buttons) ? [] : data.buttons;
				
				data.buttons[index] = button;
				
				$this.corpmsgslider('set_element_data', data);
				
			},
			
			slide_factory : function (slide, data) {
				
				var
					
					slide_element = $('<div />', {

						"class" : "cpmsl-slide",
						
						"css" : {

							"background-color" : '#' + slide["bgcolor"],
							"background-image" : 'url(' + slide.img + ')',
							"background-position" :  data.opts.image_x + ' ' + data.opts.image_y,
							"height" : data.opts.height + 'px',
							"width" : data.opts.width + 'px'
 
						}

					}),
					
					caption = $('<div />', {
						
						"class" : "cpmsl-slide-caption",
						
						"css" : {

							"color" : '#' + slide.color,
							"font-family" : slide["font-family"],
							"font-size" : slide["font-size"],
							"letter-spacing" : slide["letter-spacing"],
							"width" : data.opts.caption.width
							
						},
						
						"html" : '<span>' + slide.caption + '</span>'
						
					}),
					
					placer = $('<div />', {
						
						"class" : "cpmsl-slide-placer"
						
					});//End slide_factory var
				
				if (url_regex.test(slide.url)) {
					
					slide_element.bind('click', function () {
					
						win.open(slide.url);
						return false;
						
					});
					
					slide_element.css("cursor", "pointer");
					$('span', caption).css("cursor", "pointer");
					
				}
				
				placer.appendTo(slide_element);
				
				caption.appendTo(placer);
				
				return slide_element;
				
			}
			
		};//End module var
	
	$.fn.corpmsgslider = function (method) {
		
		if (undefined !== methods[method]) {
			
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
			
		} else if ('object' === typeof method  || !method) {
			
			return methods.init.apply(this, arguments);
			
		} else {
			
			$.error('Method ' + method + ' is not available for jQuery.corpmsgslider');
		}
		
	};
	
}(window, document, jQuery));