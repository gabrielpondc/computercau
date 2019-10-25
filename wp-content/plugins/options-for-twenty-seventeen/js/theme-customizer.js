/**
 * This file adds some LIVE to the Theme Customizer live preview. To leverage
 * this, set your custom settings to 'postMessage' and then add your handling
 * here. Your javascript should grab settings from customizer controls, and 
 * then make any necessary changes to the page using jQuery.
 */
 
(function($) {

	wp.customize('page_max_width', function(value) {
		value.bind(function(newval) {
		    if (newval !== '') {
                $('#page').css('max-width', newval);
                $('#page').css('margin', '0 auto');
		    } else {
                $('#page').css('max-width', 'none');
                $('#page').css('margin', '0');
		    }
        });
	});

	wp.customize('page_border_color', function(value) {
		value.bind(function(newval) {
		    if (newval !== '') {
                $('#page').css('border-color', newval);
		    } else {
                $('#page').css('border-color', '#333');
		    }
        });
	});

	wp.customize('page_border_style', function(value) {
		value.bind(function(newval) {
		    if (newval !== '') {
                $('#page').css('border-style', newval);
		    } else {
                $('#page').css('border-style', 'none');
		    }
        });
	});

	wp.customize('header_width', function(value) {
		value.bind( function(newval) {
		    if (newval !== '') {
                $('.custom-header .wrap, .header-sidebar-wrap').css('max-width', newval);
		    } else {
                $('.custom-header .wrap, .header-sidebar-wrap').css('max-width', '1000px');
		    }
		});
	});

	wp.customize('remove_header_video_button', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.wp-custom-header-video-button').css('display', 'none');
		    } else {
                $('.wp-custom-header-video-button').css('display', 'block');
		    }
        });
	});

	wp.customize('site_identity_background_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-branding').css('background-color', 'transparent');
		    } else {
                $('.site-branding').css('background-color', newval);
		    }
        });
	});

	wp.customize('site_title_text_transform', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-title').css('text-transform', 'uppercase');
		    } else {
                $('.site-title').css('text-transform', newval);
		    }
		});
	});

	wp.customize('remove_site_title_letter_spacing', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.site-title').css('letter-spacing', 'normal');
		    } else {
                $('.site-title').css('letter-spacing', '0.14em');
		    }
		});
	});

	wp.customize('site_title_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-title').css('font-weight', '800');
		    } else {
                $('.site-title').css('font-weight', newval);
		    }
		});
	});

	wp.customize('site_title_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('body.has-header-image .site-title, body.has-header-video .site-title, body.has-header-image .site-title a, body.has-header-video .site-title a, .site-title a, .colors-dark .site-title a, .colors-custom .site-title a, body.has-header-image .site-title a, body.has-header-video .site-title a, body.has-header-image.colors-dark .site-title a, body.has-header-video.colors-dark .site-title a, body.has-header-image.colors-custom .site-title a, body.has-header-video.colors-custom .site-title a, .colors-dark .site-title, .colors-dark .site-title a').css('color', '#222');
		    } else {
                $('body.has-header-image .site-title, body.has-header-video .site-title, body.has-header-image .site-title a, body.has-header-video .site-title a, .site-title a, .colors-dark .site-title a, .colors-custom .site-title a, body.has-header-image .site-title a, body.has-header-video .site-title a, body.has-header-image.colors-dark .site-title a, body.has-header-video.colors-dark .site-title a, body.has-header-image.colors-custom .site-title a, body.has-header-video.colors-custom .site-title a, .colors-dark .site-title, .colors-dark .site-title a').css('color', newval);
		    }
        });
	});

	wp.customize('site_description_text_align', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-description').css('text-align', 'left');
		    } else {
                $('.site-description').css('text-align', newval);
		    }
		});
	});

	wp.customize('site_description_text_transform', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-description').css('text-transform', 'none');
		    } else {
                $('.site-description').css('text-transform', newval);
		    }
		});
	});

	wp.customize('site_description_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-description').css('font-weight', '400');
		    } else {
                $('.site-description').css('font-weight', newval);
		    }
		});
	});

	wp.customize('site_description_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.site-description, .colors-dark .site-description, body.has-header-image .site-description, body.has-header-video .site-description, .site-description, .colors-dark .site-description, .colors-custom .site-description, body.has-header-image .site-description, body.has-header-video .site-description, body.has-header-image.colors-dark .site-description, body.has-header-video.colors-dark .site-description, body.has-header-image.colors-custom .site-description, body.has-header-video.colors-custom .site-description').css('color', '#666');
		    } else {
                $('.site-description, .colors-dark .site-description, body.has-header-image .site-description, body.has-header-video .site-description, .site-description, .colors-dark .site-description, .colors-custom .site-description, body.has-header-image .site-description, body.has-header-video .site-description, body.has-header-image.colors-dark .site-description, body.has-header-video.colors-dark .site-description, body.has-header-image.colors-custom .site-description, body.has-header-video.colors-custom .site-description').css('color', newval);
		    }
        });
	});

	wp.customize('remove_header_background', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.site-header').css('background', 'none');
		    } else {
                $('.site-header').css('background-color', '#fafafa');
		    }
        });
	});

	wp.customize('full_cover_image', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.twentyseventeen-front-page.has-header-image .custom-header-media, .admin-bar.home.blog.has-header-image .custom-header-media, .admin-bar.twentyseventeen-front-page.has-header-image .custom-header-media, .has-header-image .custom-header-media img, .has-header-image.home.blog .custom-header, .has-header-image.twentyseventeen-front-page .custom-header, .has-header-image .custom-header-media img, .has-header-image .custom-header-media, .has-header-video .custom-header-media iframe, .has-header-video .custom-header-media video, .has-header-video.home.blog .custom-header, .has-header-video.twentyseventeen-front-page .custom-header, .has-header-video .custom-header-media .has-header-image.twentyseventeen-front-page .custom-header').css('position', 'static');
                $('.twentyseventeen-front-page.has-header-image .custom-header-media, .admin-bar.home.blog.has-header-image .custom-header-media, .admin-bar.twentyseventeen-front-page.has-header-image .custom-header-media, .has-header-image .custom-header-media img, .has-header-image.home.blog .custom-header, .has-header-image.twentyseventeen-front-page .custom-header, .has-header-image .custom-header-media img, .has-header-image .custom-header-media, .has-header-video .custom-header-media iframe, .has-header-video .custom-header-media video, .has-header-video.home.blog .custom-header, .has-header-video.twentyseventeen-front-page .custom-header, .has-header-video .custom-header-media .has-header-image.twentyseventeen-front-page .custom-header').css('height', 'auto');
                $('.has-header-image.twentyseventeen-front-page .site-branding, .has-header-video.twentyseventeen-front-page .site-branding, .has-header-image.home.blog .site-branding, .has-header-video.home.blog .site-branding').css('position', 'static');
                $('.has-header-image.twentyseventeen-front-page .site-branding, .has-header-video.twentyseventeen-front-page .site-branding, .has-header-image.home.blog .site-branding, .has-header-video.home.blog .site-branding').css('padding', '3em 0');
                $('.has-header-image.twentyseventeen-front-page .site-branding, .has-header-video.twentyseventeen-front-page .site-branding, .has-header-image.home.blog .site-branding, .has-header-video.home.blog .site-branding').css('display', 'block');
                $('body.has-header-image .site-title, body.has-header-video .site-title, body.has-header-image .site-title a, body.has-header-video .site-title a').css('color', '#222');
                $('body.has-header-image .site-description, body.has-header-video .site-description').css('color', '#666');
                $('.navigation-top').css('z-index', '2');
		    } else {
                $('.twentyseventeen-front-page.has-header-image .custom-header-media, .admin-bar.home.blog.has-header-image .custom-header-media, .admin-bar.twentyseventeen-front-page.has-header-image .custom-header-media, .has-header-image .custom-header-media, .has-header-image.home.blog .custom-header, .has-header-image.twentyseventeen-front-page .custom-header, .has-header-image .custom-header-media, .has-header-video.home.blog .custom-header, .has-header-video.twentyseventeen-front-page .custom-header, .has-header-video .custom-header-media .has-header-image.twentyseventeen-front-page .custom-header').css('position', 'relative');
                $('.has-header-image .custom-header-media img, .has-header-image .custom-header-media img, .has-header-video .custom-header-media iframe, .has-header-video .custom-header-media video').css('position', 'fixed');
                $('.twentyseventeen-front-page.has-header-image .custom-header-media, .admin-bar.home.blog.has-header-image .custom-header-media, .admin-bar.twentyseventeen-front-page.has-header-image .custom-header-media, .has-header-image .custom-header-media').css('height', 'calc(100vh - 32px)');
                $('.has-header-image .custom-header-media img, .has-header-image .custom-header-media img, .has-header-video .custom-header-media iframe, .has-header-video .custom-header-media video').css('height', '100%');
                $('.has-header-image.twentyseventeen-front-page .site-branding, .has-header-video.twentyseventeen-front-page .site-branding, .has-header-image.home.blog .site-branding, .has-header-video.home.blog .site-branding').css('position', 'absolute');
                $('.has-header-image.twentyseventeen-front-page .site-branding, .has-header-video.twentyseventeen-front-page .site-branding, .has-header-image.home.blog .site-branding, .has-header-video.home.blog .site-branding').css('padding-top', '0');
                $('.has-header-image.twentyseventeen-front-page .site-branding, .has-header-video.twentyseventeen-front-page .site-branding, .has-header-image.home.blog .site-branding, .has-header-video.home.blog .site-branding').css('display', 'block');
                $('body.has-header-image .site-title, body.has-header-video .site-title, body.has-header-image .site-title a, body.has-header-video .site-title a').css('color', '#fff');
                $('body.has-header-image .site-description, body.has-header-video .site-description').css('color', '#fff');
		    }
		} );
	} );

	wp.customize('nav_bar_width', function(value) {
		value.bind( function(newval) {
		    if (newval !== '') {
                $('.navigation-top .wrap').css('max-width', newval);
		    } else {
                $('.navigation-top .wrap').css('max-width', '1000px');
		    }
		});
	});

	wp.customize('navigation_text_transform', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.navigation-top a').css('text-transform', 'none');
		    } else {
                $('.navigation-top a').css('text-transform', newval);
		    }
		});
	});

	wp.customize('navigation_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.navigation-top a').css('font-weight', '600');
		    } else {
                $('.navigation-top a').css('font-weight', newval);
		    }
		});
	});

	wp.customize('nav_link_color', function(value) {
		value.bind(function(newval) {
		    if (newval !== '') {
                $('.navigation-top a').css('color', newval);
                $('.menu-toggle').css('color', newval);
		    } else {
                $('.navigation-top a').css('color', '#222');
                $('.menu-toggle').css('color', '#222');
		    }
		});
	});

	wp.customize('nav_current_link_color', function(value) {
		value.bind(function(newval) {
		    if (newval !== '') {
                $('.navigation-top .current-menu-item > a, .navigation-top .current_page_item > a').css('color', newval);
		    } else {
                $('.navigation-top .current-menu-item > a, .navigation-top .current_page_item > a').css('color', '#767676');
		    }
		});
	});

	wp.customize('nav_background_color', function(value) {
		value.bind(function(newval) {
		    if (newval !== '') {
                $('.navigation-top, .main-navigation ul').css('background-color', newval);
                $('').css('background-color', newval);
		    } else {
                $('.navigation-top, .main-navigation ul').css('background-color', '#fff');
		    }
		});
	});

	wp.customize('featured_image_caption_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.post-thumbnail>a>div>div, .single-featured-image-header>div>div').css('font-size', '2.25rem');
		    } else {
                $('.post-thumbnail>a>div>div, .single-featured-image-header>div>div').css('font-size', (newval / 1000) + 'rem');
		    }
		});
	});

	wp.customize('featured_image_caption_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.post-thumbnail>a>div>div, .single-featured-image-header>div>div').css('font-weight', '800');
		    } else {
                $('.post-thumbnail>a>div>div, .single-featured-image-header>div>div').css('font-weight', newval);
		    }
		});
	});

	wp.customize('hide_archive_featured_images', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.archive .post .post-thumbnail').css('display', 'none');
		    } else {
                $('.archive .post .post-thumbnail').css('display', 'block');
		    }
		});
	});

	wp.customize('remove_posted_on', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.posted-on, .blog .entry-meta>a, .archive .entry-meta>a').css('display', 'none');
		    } else {
                $('.posted-on, .blog .entry-meta>a, .archive .entry-meta>a').css('display', 'inline');
		    }
		});
	});

	wp.customize('page_header_title_text_align', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('text-align', 'left');
		    } else {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('text-align', newval);
		    }
		});
	});

	wp.customize('page_header_title_text_transform', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('text-transform', 'uppercase');
		    } else {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('text-transform', newval);
		    }
		});
	});

	wp.customize('remove_page_header_title_letter_spacing', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('letter-spacing', 'normal');
		    } else {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('letter-spacing', '0.14em');
		    }
		});
	});

	wp.customize('page_header_title_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('font-size', '0.875rem');
		    } else {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('font-size', (newval / 1000) + 'rem');
		    }
		});
	});

	wp.customize('page_header_title_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('font-weight', '800');
		    } else {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('font-weight', newval);
		    }
		});
	});

	wp.customize('page_header_title_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('color', '#222');
		    } else {
                $('.archive .page-header .page-title, .home .page-header .page-title').css('color', newval);
		    }
        });
	});

	wp.customize('post_entry_header_title_text_align', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.post .entry-header .entry-title').css('text-align', 'left');
		    } else {
                $('.post .entry-header .entry-title').css('text-align', newval);
		    }
		});
	});

	wp.customize('post_entry_header_title_text_transform', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.post .entry-header .entry-title').css('text-transform', 'none');
		    } else {
                $('.post .entry-header .entry-title').css('text-transform', newval);
		    }
		});
	});

	wp.customize('post_entry_header_title_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.post .entry-header .entry-title').css('font-size', '1.625rem');
		    } else {
                $('.post .entry-header .entry-title').css('font-size', (newval / 1000) + 'rem');
		    }
		});
	});

	wp.customize('post_entry_header_title_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.post .entry-header .entry-title').css('font-weight', '300');
		    } else {
                $('.post .entry-header .entry-title').css('font-weight', newval);
		    }
		});
	});

	wp.customize('post_entry_header_title_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.post .entry-header .entry-title, .archive .entry-header .entry-title a, .blog .entry-header .entry-title a').css('color', '#222');
		    } else {
                $('.post .entry-header .entry-title, .archive .entry-header .entry-title a, .blog .entry-header .entry-title a').css('color', newval);
		    }
        });
	});

	wp.customize('page_entry_header_title_text_align', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.page .entry-header .entry-title').css('text-align', 'left');
		    } else {
                $('.page .entry-header .entry-title').css('text-align', newval);
		    }
		});
	});

	wp.customize('page_entry_header_title_text_transform', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.page .entry-header .entry-title').css('text-transform', 'uppercase');
		    } else {
                $('.page .entry-header .entry-title').css('text-transform', newval);
		    }
		});
	});

	wp.customize('remove_page_entry_header_title_letter_spacing', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.page .entry-header .entry-title').css('letter-spacing', 'normal');
		    } else {
                $('.page .entry-header .entry-title').css('letter-spacing', '0.14em');
		    }
		});
	});

	wp.customize('page_entry_header_title_font_size', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.page .entry-header .entry-title').css('font-size', '1.625rem');
		    } else {
                $('.page .entry-header .entry-title').css('font-size', (newval / 1000) + 'rem');
		    }
		});
	});

	wp.customize('page_entry_header_title_font_weight', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.page .entry-header .entry-title').css('font-weight', '800');
		    } else {
                $('.page .entry-header .entry-title').css('font-weight', newval);
		    }
		});
	});

	wp.customize('page_entry_header_title_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.page .entry-header .entry-title').css('color', '#222');
		    } else {
                $('.page .entry-header .entry-title').css('color', newval);
		    }
        });
	});

	wp.customize('content_link_color', function(value) {
		value.bind(function(newval) {
		    if (newval === '') {
                $('.entry-content a').css('color', '#222');
		    } else {
                $('.entry-content a').css('color', newval);
		    }
        });
	});

	wp.customize('footer_width', function(value) {
		value.bind( function(newval) {
		    if (newval !== '') {
                $('footer .wrap').css('max-width', newval);
		    } else {
                $('footer .wrap').css('max-width', '1000px');
		    }
		});
	});

	wp.customize('footer_background_color', function(value) {
		value.bind(function(newval) {
		    if (newval !== '') {
                $('.site-footer').css('background-color', newval);
		    } else {
                $('.site-footer').css('background-color', 'transparent');
		    }
		});
	});

	wp.customize('footer_title_color', function(value) {
		value.bind(function(newval) {
		    if (newval !== '') {
                $('.site-footer h2').css('color', newval);
		    } else {
                $('.site-footer h2').css('color', 'transparent');
		    }
		});
	});

	wp.customize('footer_text_color', function(value) {
		value.bind(function(newval) {
		    if (newval !== '') {
                $('.site-footer').css('color', newval);
		    } else {
                $('.site-footer').css('color', 'transparent');
		    }
		});
	});

	wp.customize('footer_link_color', function(value) {
		value.bind(function(newval) {
		    if (newval !== '') {
                $('.site-info a, .site-footer .widget-area a').css('color', newval);
                $('.site-info a, .site-footer .widget-area a').css('-webkit-box-shadow', 'inset 0 -1px 0 ' + newval);
                $('.site-info a, .site-footer .widget-area a').css('box-shadow', 'inset 0 -1px 0 ' + newval);
		    } else {
                $('.site-info a, .site-footer .widget-area a').css('color', 'transparent');
                $('.site-info a, .site-footer .widget-area a').css('-webkit-box-shadow', 'inset 0 -1px 0 rgba(15, 15, 15, 1)');
                $('.site-info a, .site-footer .widget-area a').css('box-shadow', 'inset 0 -1px 0 rgba(15, 15, 15, 1)');
		    }
		});
	});

	wp.customize('square_social_links', function(value) {
		value.bind(function(newval) {
		    if (newval == 1) {
                $('.social-navigation a').css('-webkit-border-radius', 0);
                $('.social-navigation a').css('border-radius', 0);
                $('.social-navigation a').css('height', '34px');
                $('.social-navigation a').css('width', '34px');
                $('.social-navigation .icon').css('top', '9px');
            } else {
                $('.social-navigation a').css('-webkit-border-radius', '40px');
                $('.social-navigation a').css('border-radius', '40px');
                $('.social-navigation a').css('height', '40px');
                $('.social-navigation a').css('width', '40px');
                $('.social-navigation .icon').css('top', '12px');
		    }
        });
	});

})(jQuery);
