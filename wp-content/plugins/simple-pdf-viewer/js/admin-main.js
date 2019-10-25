jQuery(function($) {

	

	var s_pdf_check_width_percent;
	var s_pdf_act_width_percent;

	if(s_pgf_docw == '' || s_pgf_docw === null){
		s_pgf_docw = 400;
	}

	if(s_pgf_docw_percent != 1){
		s_pdf_check_width_percent = '';
	}else{
		s_pdf_check_width_percent = 'checked';
	}

	if(s_pgf_docw_percent != 1){
		s_pdf_act_width_percent = 'active';
	}else{
		s_pdf_act_width_percent = '';
	}

	if(s_pgf_docw_percent != 1 && (s_pgf_docw == '' || s_pgf_docw === null)){
		s_pgf_docw = 400;
	}

	if(s_pgf_doch == '' || s_pgf_doch === null){
		s_pgf_doch = 600;
	}

	if(s_pgf_bttn == '' || s_pgf_bttn === null){
		s_pgf_bttn = 'Download';
	}

	$('#spdf-media-button').click(function(){
		spdf_choose_link_type();
	});

	if($('.s_pdf_wrap').length != 0){
		if(!$('input[name="s_pdf_options[doc_width_percent]"]').is(':checked')){
			$('input[name="s_pdf_options[doc_width]"]').removeAttr('disabled');
		}else{
			$('input[name="s_pdf_options[doc_width]"]').attr('disabled', 'disabled');
		}

		$('input[name="s_pdf_options[doc_width_percent]"]').change(function(){
			if(!$(this).is(':checked')){
				$('input[name="s_pdf_options[doc_width]"]').removeAttr('disabled');
			}else{
				$('input[name="s_pdf_options[doc_width]"]').attr('disabled', 'disabled');
			}
		});
	}

	function spdf_middle_popup(popup){
		var popupHeight = popup.height();
		if($(window).height() - 150 > popupHeight){
			popup.css('margin-top', -popupHeight/1.8 + 'px');
			popup.css('top', '50%');
		}else{
			popup.css('margin-top', '0');
			popup.css('top', '15%');
		}
	}

	function spdf_choose_link_type(){
		$('body').prepend('<div class="spdf-popup spdf-popup-type" />');
		$('body').find('.spdf-popup').append('<div class="spdf-popup-content spdf-link-settings" />');
		$('body').find('.spdf-popup-content').append('<div class="spdf-popup-close-bttn" />');
		$('body').find('.spdf-popup-content').append('<div class="spdf-popup-title" />');
		$('body').find('.spdf-popup-title').append('<p>Choose PDF link type</p>');
		$('body').find('.spdf-popup-content').append('<div class="spdf-choose-link" />');
		$('body').find('.spdf-choose-link').append('<div class="icon spdf-mediafiles" />');
		$('body').find('.spdf-choose-link').append('<div class="icon spdf-link" />');
		$('body').find('.icon.spdf-mediafiles').append('<p class="tit">Mediafiles</p>');
		$('body').find('.icon.spdf-link').append('<p class="tit">External Link</p>');
		$('body').find('.icon.spdf-link').append('<div class="spdf-pro-v"><p class="pro">Pro Version</p><a href="https://sellfy.com/p/a3uM/" target="_blank" class="getpro">Get Pro</a></div>');

		$('body').find('.spdf-popup').find('.spdf-popup-close-bttn').on('click', function(){
			$('body').find('.spdf-popup-type').remove();
		});

		$('body').find('.spdf-popup').find('.spdf-mediafiles').on('click', function(){
			$('body').find('.spdf-popup-type').remove();
		});

		$('body').find('.spdf-popup').find('.spdf-mediafiles').on('click', spdf_open_media_window);

		spdf_middle_popup($('body').find('.spdf-popup-content'));
	}

	function spdf_document_settings(link){
		$('body').prepend('<div class="spdf-popup" />');
		$('body').find('.spdf-popup').append('<div class="spdf-popup-content" />');
		$('body').find('.spdf-popup-content').append('<div class="spdf-popup-close-bttn" />');
		$('body').find('.spdf-popup-content').append('<div class="spdf-popup-title" />');
		$('body').find('.spdf-popup-title').append('<p>PDF Document Settings</p>');
		$('body').find('.spdf-popup-content').append('<div class="spdf-enable-download spdf-popup-opiton" />');
		$('body').find('.spdf-enable-download').append('<input id="spdf-enable-download-option" type="checkbox" name="spdf_enable_download_option" value="yes" />');
		$('body').find('.spdf-enable-download').append('<label class="checkbox-label" for="spdf-enable-download-option">Enable Download Button</label>');
		$('body').find('.spdf-enable-download').append('<div class="spdf-enable-download-check" />');
		$('body').find('.spdf-enable-download-check').append('<div class="spdf-enable-download-link" />');
		$('body').find('.spdf-enable-download-link').append('<div class="download-link-pro"><p class="pro">Pro Version</p><a href="https://sellfy.com/p/a3uM/" target="_blank" class="getpro">Get Pro</a></div>');

		$('body').find('.spdf-enable-download-check').append('<label>Download Button Text</label>');
		$('body').find('.spdf-enable-download-check').append('<input type="text" name="spdf_download_bttn_text" value="' + s_pgf_bttn + '" />');

		$('body').find('.spdf-popup-content').append('<div class="spdf-custom-size spdf-popup-opiton" />');
		$('body').find('.spdf-custom-size').append('<input id="spdf-custom-size-option" type="checkbox" name="spdf_custom_size_option" value="yes" />');
		$('body').find('.spdf-custom-size').append('<label class="checkbox-label" for="spdf-custom-size-option">Special Window Size</label>');
		$('body').find('.spdf-custom-size').append('<div class="spdf-custom-size-check" />');
		$('body').find('.spdf-custom-size-check').append('<div class="spdf-width-percent" />');
		$('body').find('.spdf-width-percent').append('<input id="spdf_width_percent" type="checkbox" name="spdf_custom_size_width_percent" value="1" ' + s_pdf_check_width_percent + ' />');
		$('body').find('.spdf-width-percent').append('<label class="checkbox-label" for="spdf_width_percent">Document Width 100%</label>');
		$('body').find('.spdf-custom-size-check').append('<div class="spdf-width-percent-check '+ s_pdf_act_width_percent +'" />');
		$('body').find('.spdf-width-percent-check').append('<label>Document Width (px)</label>');
		$('body').find('.spdf-width-percent-check').append('<input type="number" name="spdf_custom_size_width" value="' + s_pgf_docw + '" />');
		$('body').find('.spdf-custom-size-check').append('<label>Document Height (px)</label>');
		$('body').find('.spdf-custom-size-check').append('<input type="number" name="spdf_custom_size_height" value="' + s_pgf_doch + '" />');

		$('body').find('.spdf-popup-content').append('<button class="button button-primary button-save">Save</button>');
		spdf_middle_popup($('body').find('.spdf-popup-content'));

		$('body').find('.spdf-popup').find('.spdf-popup-close-bttn').on('click', function(){
			$('body').find('.spdf-popup').remove();
			wp.media.editor.insert('[googlepdf url="' + link + '"]');
		});

		$('body').find('.spdf-popup').find('input[name="spdf_enable_download_option"]').on('change', function(){
			$('body').find('.spdf-popup').find('.spdf-enable-download-check').toggleClass('active');
			spdf_middle_popup($('body').find('.spdf-popup-content'));
		});

		$('body').find('.spdf-popup').find('input[name="spdf_custom_size_option"]').on('change', function(){
			$('body').find('.spdf-popup').find('.spdf-custom-size-check').toggleClass('active');
			spdf_middle_popup($('body').find('.spdf-popup-content'));
		});

		$('body').find('.spdf-popup').find('input[name="spdf_custom_size_width_percent"]').on('change', function(){
			$('body').find('.spdf-popup').find('.spdf-width-percent-check').toggleClass('active');
			spdf_middle_popup($('body').find('.spdf-popup-content'));
		});

		$('body').find('.spdf-popup').find('.button-save').on('click', function(){
			var download = '';
			var customSize = '';
			var buttonText = $('body').find('.spdf-popup').find('input[name="spdf_download_bttn_text"]').val();
			var customSizeWidth = $('body').find('.spdf-popup').find('input[name="spdf_custom_size_width"]').val();
			var customSizeHeight = $('body').find('.spdf-popup').find('input[name="spdf_custom_size_height"]').val();
			if($('body').find('.spdf-popup').find('input[name="spdf_enable_download_option"]').is(':checked')){
				if(buttonText != ''){
					download = 'download="' + buttonText + '"';
				}else{
					download = 'download="Download"';
				}
			}

			if($('body').find('.spdf-popup').find('input[name="spdf_custom_size_option"]').is(':checked')){
				if(customSizeWidth != '' && customSizeHeight != ''){
					customSize = 'width="' + customSizeWidth + '" height="' + customSizeHeight + '"';
					if($('body').find('.spdf-popup').find('input[name="spdf_custom_size_width_percent"]').is(':checked')){
						customSize = 'width="100%" height="' + customSizeHeight + '"';
					}
				}else if(customSizeWidth == '' || customSizeHeight == ''){
					customSize = 'width="' + s_pgf_docw + '" height="' + s_pgf_doch + '"';
					if($('body').find('.spdf-popup').find('input[name="spdf_custom_size_width_percent"]').is(':checked')){
						customSize = 'width="100%" height="' + s_pgf_doch + '"';
					}
				}
			}

			$('body').find('.spdf-popup').remove();
			wp.media.editor.insert('[googlepdf url="' + link + '" ' + download + ' ' + customSize + ']');
		});
	}

	function spdf_open_media_window() {
		if (this.window === undefined) {
			this.window = wp.media({
				title: 'Insert a PDF file',
				multiple: false,
				library: {
	                type: 'text/plain,text/richtext,text/css,application/javascript,application/pdf,application/postscript,image/tiff,image/tiff,application/msword,application/vnd.ms-powerpoint,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.wordprocessingml.template,application/vnd.ms-word.template.macroEnabled.12,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel.sheet.macroEnabled.12,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.openxmlformats-officedocument.presentationml.slideshow,application/vnd.apple.pages,image/svg+xml'
	            },
				button: {text: 'Insert'}
			});

			var self = this;
			this.window.on('select', function() {

				var first = self.window.state().get('selection').first().toJSON();
				spdf_document_settings(first.url);
				
			});
		}

		this.window.open();
		return false;
	}

});