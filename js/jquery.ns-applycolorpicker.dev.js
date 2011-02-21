(function (win, doc, $) {
	
	$.fn.applycolorpicker = function (options) {
		
		var 
		
			defaults = {
			
				"cp_class" : "ns-colorpicker",
				"default_color"	: '#0000ff',
				"input_suffix" : "-input"
			
			},
		
			opts,
			
			start_color
		;
		
		opts = $.extend(true, defaults, options);
		
		$('.' + opts.cp_class).each(function (i, e) {
		
			var
			
				input_id  = $(e).attr('id') + opts.input_suffix,

				start_color = '#' + $('#' + input_id).attr('value')
			
			;
			
			start_color = ('#' === start_color) ? $('span:first-child', e).css('background-color') : start_color;
			
			start_color = ('transparent' == start_color) ? opts.default_color : start_color;
			
			
			
			$('span:first-child', e).css('background-color', start_color);
			
			$('#' + $(e).attr('id') + opts.input_suffix).attr('value', start_color.replace('#', ''));
		
			$(e).ColorPicker({

				color: start_color,

				onShow: function (colpkr) {

					$(colpkr).fadeIn(500);

					return false;

				},

				onHide: function (colpkr) {

					$(colpkr).fadeOut(500);

					return false;

				},

				onChange: function (hsb, hex, rgb, el) {
					
					var
					
						input_id  = $(el).attr('id') + opts.input_suffix;
					
					;

					$('span', $(el)).css('background-color', '#' + hex);
					
					$('#' + input_id).attr('value', hex).trigger('change');

				}
				
			});
			
		});
		
	};
	
	$(function () {
	
		$(doc).applycolorpicker();
		
	});
	
}(window, document, jQuery, undefined));