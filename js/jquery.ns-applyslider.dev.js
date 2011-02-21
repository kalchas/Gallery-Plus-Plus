(function (win, doc, $) {
	
	$(function() {

		$(".ns-slider").each(function (i, e) {
			
			var

				input_id  = $(e).attr('id') + '-input',
				start_value = $('#' + input_id).attr('value')

			;
			
			start_value = ('' === start_value) ? 50 : start_value;
			
			$('#' + input_id).attr('value', start_value);
		
			$(e).slider({
				
				value : start_value
				
			});
			
		});
		
		$(doc).delegate('.ns-slider', 'slidechange', function (e, ui) {
			
			var

				input_id  = $(this).attr('id') + '-input'

			;
			
			$('#' + input_id).attr('value', ui.value);
			
		});

	});
	
	
}(window, document, jQuery, undefined));