<?php
/*
 * Plugin Name: Remove "Powered by Wordpress"
 * Version: 1.2.8
 * Plugin URI: https://webd.uk/remove-powered-by-wp/
 * Description: Removes the Wordpress credit on the default Wordpress theme and inserts a widget area
 * Author: webd.uk
 * Author URI: https://webd.uk
 * Text Domain: remove-powered-by-wp
 */



if (!class_exists('remove_powered_by_wp_class')) {

	class remove_powered_by_wp_class {

		function __construct() {

            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'rpbw_add_plugin_settings_link'));
            add_action('customize_register', array($this, 'rpbw_customize_register'));
            add_action('wp_head' , array($this, 'rpbw_header_output'));
            if (get_template() != 'twentynineteen' && get_template() != 'twentynineteen-master') { add_action('widgets_init', array($this, 'rpbw_site_info_sidebar_init')); }
            add_action('admin_notices', array($this, 'rpbw_admin_notice'));
            add_action('wp_ajax_dismiss_rpbw_notice_handler', array($this, 'rpbw_ajax_notice_handler'));
            add_action('plugins_loaded', array($this, 'rpbw_load_plugin_textdomain'));

		}

		function rpbw_add_plugin_settings_link($links) {

			$settings_link = '<a href="' . rpbw_home_root() . 'wp-admin/customize.php?autofocus[section]=theme_options" title="' . __('Settings', 'remove-powered-by-wp') . '">' . __('Settings', 'remove-powered-by-wp') . '</a>';
			array_unshift( $links, $settings_link );
			return $links;

		}

        function rpbw_customize_register($wp_customize) {

            if (get_template() != 'twentyseventeen') {

                $wp_customize->add_section('theme_options', array(
                    'title'    => __('Theme Options', 'remove-powered-by-wp'),
                    'priority' => 130
                ));

            }

            $wp_customize->add_setting('remove_powered_by_wordpress', array(
                'default'       => false,
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'rpbw_sanitize_boolean')
            ));

            if (get_template() == 'twentynineteen' || get_template() == 'twentynineteen-master') {

                $description = __('Removes the "Proudly powered by WordPress" text displayed in the website footer.', 'remove-powered-by-wp');

            } else {

                $description = __('Removes the "Proudly powered by WordPress" text displayed in the website footer and replaces with the content of the "Site Info" widget area.', 'remove-powered-by-wp');

            }

            $wp_customize->add_control('remove_powered_by_wordpress', array(
                'label'         => __('Remove Powered by WordPress', 'remove-powered-by-wp'),
                'description'   => $description,
                'section'       => 'theme_options',
                'settings'      => 'remove_powered_by_wordpress',
                'type'          => 'checkbox'
            ));

        }

        function rpbw_sanitize_boolean($input) {

            if (isset($input)) {

                if ($input == true) {

                    return true;

                } else {

                    return false;

                }

            } else {

                return false;

            }

        }

        function rpbw_header_output() {

?>
<!--Customizer CSS--> 
<style type="text/css">
<?php

            if (get_theme_mod('remove_powered_by_wordpress')) {

                switch (get_template()) {

                    case 'twentyten':
                        add_action('twentyten_credits', array($this, 'rpbw_get_site_info_sidebar'));
                        $this->rpbw_generate_css('#footer #site-generator>a', 'display', 'remove_powered_by_wordpress', '', '', 'none');
?>
#site-generator a {
    background-image: none;
    display: inline;
    padding-left: 0;
}
#site-generator p {
    margin: 0;
}
<?php
                        break;

                    case 'twentyeleven':
                        add_action('twentyeleven_credits', array($this, 'rpbw_get_site_info_sidebar'));
?>
#site-generator>span {
    display: none;
}
#site-generator>a:last-child {
    display: none;
}
#site-generator p {
    margin: 0;
}
<?php
                        break;

                    case 'twentytwelve':
                        add_action('twentytwelve_credits', array($this, 'rpbw_get_site_info_sidebar'));
?>
.site-info>span {
    display: none;
}
.site-info>a:last-child {
    display: none;
}
<?php
                        break;

                    case 'twentythirteen':
                        add_action('twentythirteen_credits', array($this, 'rpbw_get_site_info_sidebar'));
?>
.site-info>span {
    display: none;
}
.site-info>a:last-child {
    display: none;
}
.site-info p {
    margin: 0;
}
<?php
                        break;

                    case 'twentyfourteen':
                        add_action('twentyfourteen_credits', array($this, 'rpbw_get_site_info_sidebar'));
?>
.site-info>span {
    display: none;
}
.site-info>a:last-child {
    display: none;
}
.site-info p {
    margin: 0;
}
<?php
                        break;

                    case 'twentyfifteen':
                        add_action('twentyfifteen_credits', array($this, 'rpbw_get_site_info_sidebar'));
?>
.site-info>span {
    display: none;
}
.site-info>a:last-child {
    display: none;
}
<?php
                        break;

                    case 'twentysixteen':
                        add_action('twentysixteen_credits', array($this, 'rpbw_get_site_info_sidebar'));
?>
.site-footer span[role=separator] {
    display: none;
}
.site-info>a:last-child {
    display: none;
}
.site-footer .site-title:after {
    display: none;
}
<?php
                        break;

                    case 'twentyseventeen':
                        add_action('get_template_part_template-parts/footer/site', array($this, 'rpbw_get_site_info_sidebar'));
?>
.site-info:last-child a:last-child {
    display: none;
}
.site-info:last-child span {
    display: none;
}
.site-info p {
    margin: 0;
}
<?php
                        break;

                    case 'twentynineteen':
                        add_action('wp_footer', array($this, 'rpbw_remove_site_info_comma'));
?>
.site-info>.imprint {
    display: none;
}
.site-name {
    margin-right: 1rem;
}
<?php
                        break;

                    case 'twentynineteen-master':
?>
.site-info>.imprint {
    display: none;
}
.site-name {
    margin-right: 1rem;
}
<?php
                        break;

                }

            }

?>
</style> 
<!--/Customizer CSS-->
<?php

        }

        function rpbw_site_info_sidebar_init() {

        	register_sidebar( array(
        		'name'          => __('Site Info', 'remove-powered-by-wp'),
        		'id'            => 'site-info',
        		'description'   => __('Add widgets here to appear in your footer site info.', 'remove-powered-by-wp'),
		        'before_widget' => '',
        		'after_widget'  => '',
        		'before_title'  => '<h2 class="widget-title">',
        		'after_title'   => '</h2>',
        	) );

        }

        function rpbw_get_site_info_sidebar() {

            if (is_active_sidebar('site-info')) {

                switch (get_template()) {

                    case 'twentyten':
                        dynamic_sidebar('site-info');
                        break;

                    case 'twentyeleven':
                        dynamic_sidebar('site-info');
                        break;

                    case 'twentytwelve':
                        dynamic_sidebar('site-info');
                        break;

                    case 'twentythirteen':
                        dynamic_sidebar('site-info');
                        break;

                    case 'twentyfourteen':
                        dynamic_sidebar('site-info');
                        break;

                    case 'twentyfifteen':
                        dynamic_sidebar('site-info');
                        break;

                    case 'twentysixteen':
                        dynamic_sidebar('site-info');
                        break;

                    case 'twentyseventeen':
                        echo('<div class="site-info">');
                        dynamic_sidebar('site-info');
                        echo('</div>');
                        break;

                }

            }

        }

        function rpbw_remove_site_info_comma() {

?>
<script type="text/javascript">
    (function() {
        document.getElementsByClassName('site-info')[0].innerHTML = document.getElementsByClassName('site-info')[0].innerHTML.split('</a>,\n\t\t\t\t\t\t').join('</a>');
    })();
</script>
<?php

        }

        function rpbw_generate_css($selector, $style, $mod_name, $prefix='', $postfix='', $value='') {

            $generated_css = '';
            $mod = get_theme_mod($mod_name);

            if (!empty($mod) && $value == '') {

                $generated_css = sprintf('%s { %s: %s; }', $selector, $style, $prefix.$mod.$postfix);
                echo $generated_css;

            } elseif (!empty($mod)) {

                $generated_css = sprintf('%s { %s:%s; }', $selector, $style, $prefix.$value.$postfix);
                echo $generated_css;

            }

        }

        function rpbw_admin_notice() {

            $plugin_data = get_plugin_data(__FILE__);

            if (get_user_meta(get_current_user_id(), 'rpbw-notice-dismissed', true) != $plugin_data['Version']) {

?>

<div class="notice notice-info is-dismissible rpbw-notice">

<p><strong><?php _e('Thank you for using Remove "Powered by Wordpress" plugin', 'remove-powered-by-wp'); ?></strong><br />
<?php

    if (get_template() == 'twentyseventeen') {

        printf(
            __('We notice that you are using the Twenty Seventeen theme. We recommend you use our other plugin "%s" instead as it has lots of other theme customisations available to you as well as this one.', 'remove-powered-by-wp'),
            '<a href="' . rpbw_home_root() . 'wp-admin/plugin-install.php?s=options-for-twenty-seventeen&tab=search&type=term" title="' . __('Options for Twenty Seventeen', 'remove-powered-by-wp') . '">' . __('Options for Twenty Seventeen', 'remove-powered-by-wp') . '</a>'
        );

?>
</p>
<?php

    } elseif (get_template() == 'twentynineteen') {

        printf(
            __('We notice that you are using the Twenty Nineteen theme. We recommend you use our other plugin "%s" instead as it has lots of other theme customisations available to you as well as this one.', 'remove-powered-by-wp'),
            '<a href="' . rpbw_home_root() . 'wp-admin/plugin-install.php?s=options-for-twenty-nineteen&tab=search&type=term" title="' . __('Options for Twenty Nineteen', 'remove-powered-by-wp') . '">' . __('Options for Twenty Nineteen', 'remove-powered-by-wp') . '</a>'
        );

?>
</p>
<?php

    } {

?>
<?php _e('Can you spare some change for a poor web developer? Anything you can give will help him survive the winter, feed his children and keep him off the streets. His eyes are dim. he cannot see. He\'s just a poor old man. His legs are grey. His ears are gnarled. His eyes and legs are old and bent. His ears are grizzled ...', 'remove-powered-by-wp'); ?></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="UM35HYGBV8826">
<input type="image" src="<?= plugin_dir_url( __FILE__ ); ?>images/feed-now-button.gif" border="0" name="submit" alt="<?php _e('Feed Now', 'remove-powered-by-wp'); ?>" style="margin: 0; padding: 0 0 0.5em 0;">
<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form>
<?php

    }

?>

</div>

<script>

    jQuery(document).on('click', '.rpbw-notice .notice-dismiss', function() {

	jQuery.ajax( {

	    url: ajaxurl,
	    data: {

		action: 'dismiss_rpbw_notice_handler',
		security: '<?= wp_create_nonce('rpbw-ajax-nonce'); ?>'

	    }

	} )

    } )

</script>

<?php

            }
        }

        function rpbw_ajax_notice_handler() {

            check_ajax_referer('rpbw-ajax-nonce','security');
            $plugin_data = get_plugin_data(__FILE__);
            update_user_meta(get_current_user_id(), 'rpbw-notice-dismissed', $plugin_data['Version']);

        }

        function rpbw_load_plugin_textdomain() {

            load_plugin_textdomain('remove-powered-by-wp', FALSE, basename(dirname(__FILE__)) . '/languages/');

        }

	}

    $compatible_themes = array(
        'twentyten', 
        'twentyeleven', 
        'twentytwelve', 
        'twentythirteen', 
        'twentyfourteen', 
        'twentyfifteen', 
        'twentysixteen',
        'twentyseventeen',
        'twentynineteen',
        'twentynineteen-master'
    );

    if (in_array(get_template(), $compatible_themes)) {

    	$remove_powered_by_wp_object = new remove_powered_by_wp_class();

    } else {

        $themes = wp_get_themes();
        $compatible_theme_installed = false;

        foreach ($compatible_themes as $compatible_theme) {

            if ($themes[$compatible_theme]) {

                $compatible_theme_installed = true;

            }

        }

        if (!$compatible_theme_installed) {

            add_action('admin_notices', 'rpbw_wrong_theme_notice');

        }

        add_action('after_setup_theme', 'rpbw_is_theme_being_previewed');

    }

    function rpbw_wrong_theme_notice() {

?>

<div class="notice notice-error">

<p><strong><?php _e('Remove "Powered by Wordpress" Plugin Error', 'remove-powered-by-wp'); ?></strong><br />
<?php
        printf(
            __('This plugin requires one of the default Wordpress themes to be active or live previewed in order to function. Your theme "%s" is not compatible. Please install and activate or live preview one of these themes (or a child theme thereof):', 'remove-powered-by-wp'),
            get_template()
        );
?>

<a href="<?= rpbw_home_root(); ?>wp-admin/theme-install.php?search=twentyten" title="<?= __('Twenty Ten', 'remove-powered-by-wp') ?>"><?php
        _e('Twenty Ten', 'remove-powered-by-wp');
?></a>,
<a href="<?= rpbw_home_root(); ?>wp-admin/theme-install.php?search=twentyeleven" title="<?= __('Twenty Eleven', 'remove-powered-by-wp') ?>"><?php
        _e('Twenty Eleven', 'remove-powered-by-wp');
?></a>,
<a href="<?= rpbw_home_root(); ?>wp-admin/theme-install.php?search=twentytwelve" title="<?= __('Twenty Twelve', 'remove-powered-by-wp') ?>"><?php
        _e('Twenty Twelve', 'remove-powered-by-wp');
?></a>,
<a href="<?= rpbw_home_root(); ?>wp-admin/theme-install.php?search=twentythirteen" title="<?= __('Twenty Thirteen', 'remove-powered-by-wp') ?>"><?php
        _e('Twenty Thirteen', 'remove-powered-by-wp');
?></a>,
<a href="<?= rpbw_home_root(); ?>wp-admin/theme-install.php?search=twentyfourteen" title="<?= __('Twenty Fourteen', 'remove-powered-by-wp') ?>"><?php
        _e('Twenty Fourteen', 'remove-powered-by-wp');
?></a>,
<a href="<?= rpbw_home_root(); ?>wp-admin/theme-install.php?search=twentyfifteen" title="<?= __('Twenty Fifteen', 'remove-powered-by-wp') ?>"><?php
        _e('Twenty Fifteen', 'remove-powered-by-wp');
?></a>,
<a href="<?= rpbw_home_root(); ?>wp-admin/theme-install.php?search=twentysixteen" title="<?= __('Twenty Sixteen', 'remove-powered-by-wp') ?>"><?php
        _e('Twenty Sixteen', 'remove-powered-by-wp');
?></a>,
<a href="<?= rpbw_home_root(); ?>wp-admin/theme-install.php?search=twentyseventeen" title="<?= __('Twenty Seventeen', 'remove-powered-by-wp') ?>"><?php
        _e('Twenty Seventeen', 'remove-powered-by-wp');
?></a>,
<a href="<?= rpbw_home_root(); ?>wp-admin/theme-install.php?search=twentynineteen" title="<?= __('Twenty Nineteen', 'remove-powered-by-wp') ?>"><?php
        _e('Twenty Nineteen', 'remove-powered-by-wp');
?></a>.</p>

</div>

<?php

    }

	function rpbw_home_root() {

		$home_root = parse_url(home_url());

		if (isset($home_root['path'])) {

			$home_root = trailingslashit($home_root['path']);

		} else {

			$home_root = '/';

		}

		return $home_root;

	}

    function rpbw_activation() {

        set_theme_mod('remove_powered_by_wordpress', true);

    }
	register_activation_hook(__FILE__, 'rpbw_activation');

    function rpbw_is_theme_being_previewed() {

        global $compatible_theme_installed;

        if ($compatible_theme_installed && is_customize_preview()) {

            $remove_powered_by_wp_object = new remove_powered_by_wp_class();

        }

    }

}

?>
