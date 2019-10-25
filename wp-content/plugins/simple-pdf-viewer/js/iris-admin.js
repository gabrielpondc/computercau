jQuery(function($) {
	// Colorpicker

	if(!$('input[name="s_pdf_options[bttn_style]"]').is(':checked')){
		$('#s_pdf_colorpicker').attr('disabled', 'disabled');
	}

	$('input[name="s_pdf_options[bttn_style]"]').on('change', function(){
		if($(this).is(':checked')){
			$('#s_pdf_colorpicker').removeAttr('disabled');
		}else{
			$('#s_pdf_colorpicker').attr('disabled', 'disabled');
		}
	});

	if(s_pgf_bttn_color === null){
		$('#s_pdf_colorpicker').attr('value', '#000');
    	$("#s_pdf_colorpicker_show").css('background', '#000');
	}else{
		var buttonColor = $('#s_pdf_colorpicker').val();
    	$("#s_pdf_colorpicker_show").css('background', buttonColor);
	}

    $('#s_pdf_colorpicker').iris({
    	palettes: false,
    	change: function(event, ui) {
	        $("#s_pdf_colorpicker_show").css('background', ui.color.toString());
	    }
    });

    $('#s_pdf_colorpicker').on('click', function(e){
    	$('.iris-picker').show();
    });

    $('body').on('click', function(e){
    	var inputColor = $("#s_pdf_colorpicker");

    	if (!inputColor.is(e.target) && inputColor.has(e.target).length === 0){
    		$('.iris-picker').hide();
    	}
    });

});
