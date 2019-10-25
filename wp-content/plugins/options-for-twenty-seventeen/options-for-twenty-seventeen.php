<?php
/*
 * Plugin Name: Options for Twenty Seventeen
 * Version: 1.9.5
 * Plugin URI: https://webd.uk/options-for-twenty-seventeen/
 * Description: Adds various options to modify the default Wordpress theme Twenty Seventeen
 * Author: webd.uk
 * Author URI: https://webd.uk
 * Text Domain: options-for-twenty-seventeen
 */



if (!defined('ABSPATH')) {
    exit('This isn\'t the page you\'re looking for. Move along, move along.');
}



if (!class_exists('options_for_twenty_seventeen_class')) {

	class options_for_twenty_seventeen_class {

        public $ofts_upgrade_link;

		function __construct() {

            $this->ofts_upgrade_link = 'https://webd.uk/product/options-for-twenty-seventeen-upgrade/?url=' . (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    
            add_action('init', array($this, 'ofts_establish_version'));
            if (get_option('ofts_trial_date') == false) { add_action('init', array($this, 'ofts_start_trial')); }
            add_action('init', array($this, 'ofts_activate_upgrade'));
            add_action('customize_register', array($this, 'ofts_customize_register'), 999);
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'ofts_add_plugin_settings_link'));
            add_action('wp_head' , array($this, 'ofts_header_output'), 11);
            add_action('customize_controls_enqueue_scripts', array($this, 'ofts_enqueue_customizer_css'));
            add_action('customize_preview_init', array($this, 'ofts_enqueue_customizer_js'));
            add_action('widgets_init', array($this, 'ofts_header_sidebar_init'));
            add_action('widgets_init', array($this, 'ofts_site_info_sidebar_init'));
            add_action('after_setup_theme', array($this, 'ofts_twentyseventeen_custom_header_setup'), 9);
            add_action('after_setup_theme', array($this, 'ofts_twentyseventeen_default_image_setup'), 11);
            add_action('admin_notices', array($this, 'ofts_admin_notice'));
            add_action('wp_ajax_dismiss_ofts_notice_handler', array($this, 'ofts_ajax_notice_handler'));

            if (get_theme_mod('ignore_en_gb_translations') && get_locale() == 'en_GB') {

                add_action('init', array($this, 'ofts_unload_textdomain'));

            }

            add_shortcode('social-links', array($this, 'ofts_social_links_shortcode'));
            add_action('wp_footer', array($this, 'ofts_fix_parallax_on_ie11'));

if ($this->ofts_request_permission('1.2.7', true) == true) {

            if (get_theme_mod('front_page_sections')) {

                add_filter('twentyseventeen_front_page_sections', array($this, 'ofts_front_page_sections'));

            }

            if (get_option('show_on_front') == 'page') {

                if (get_theme_mod('front_page_sections_menus_box')) {

                    add_filter('nav_menu_meta_box_object', array($this, 'ofts_front_page_sections_add_menu_meta_box'), 10, 1);

                }

                add_action('get_footer', array($this, 'ofts_inject_ids_to_front_page_sections'));

            }

}

            if (get_theme_mod('three_footer_sidebars')) {

                set_theme_mod('footer_sidebars', 3);
                remove_theme_mod('three_footer_sidebars');

            }

if ($this->ofts_request_permission('1.3.7', true) == true) {

            if (get_theme_mod('footer_sidebars')) {

                add_action('widgets_init', array($this, 'ofts_footer_sidebars_init'));

            }

}

if ($this->ofts_request_permission('1.4.9', true) == true) {

            add_filter('post_thumbnail_html', array($this, 'ofts_hide_featured_image'), 10, 5);
            add_action('get_footer', array($this, 'ofts_home_page_panels'), 10, 5);

}

            add_action('load-post.php', array($this, 'ofts_hide_featured_image_metabox_setup'));
            add_action('load-post-new.php', array($this, 'ofts_hide_featured_image_metabox_setup'));
            add_action('ofts_check', array($this, 'ofts_check'));

		}

		function ofts_establish_version() {

            if (get_option('ofts_free_version') === false) {

                $last_installed_version = get_user_meta(get_current_user_id(), 'ofts-notice-dismissed', true);

                if ($last_installed_version) {

                    if (version_compare($last_installed_version, '1.2.3') == -1) {

                        update_option('ofts_free_version', $last_installed_version);

                    } else {

                        update_option('ofts_free_version', '1.0');

                    }

                } else {

                        update_option('ofts_free_version', '1.0');

                }

            }


            if (get_option('ofts_purchased') == true && (get_option('ofts_check') != $_SERVER['HTTP_HOST'])) {

                $client_host = $_SERVER['HTTP_HOST'];
                $client_host = preg_replace('#^www.#', '', $client_host);
                $client_host = preg_replace('#^test.#', '', $client_host);
                $client_host = preg_replace('#^dev.#', '', $client_host);

                if (strpos($client_host, ':')) {

                    $client_host = substr($client_host, 0, strpos($client_host, ':'));

                }

                if (filter_var($client_host, FILTER_VALIDATE_IP)) {

                    $client_ip = explode('.', $client_host);

                    if (($client_ip[0] == 192 && $client_ip[1] == 168) || ($client_ip[0] == 172 && $client_ip[1] >= 16 && $client_ip[1] <= 31) || $client_ip[0] == 10) {

                        update_option('ofts_check', $_SERVER['HTTP_HOST']);

                    }

                }

                if ($client_host == 'localhost' || $client_host == '127.0.0.1') {

                    update_option('ofts_check', $_SERVER['HTTP_HOST']);

                }

                if (get_option('ofts_check') == false) {
   
                    if (!wp_next_scheduled('ofts_check')) {

                        wp_schedule_event(time(), 'daily', 'ofts_check');

                    } 
    
                }

            }


		}

		function ofts_add_plugin_settings_link($links) {

			$settings_link = '<a href="' . ofts_home_root() . 'wp-admin/customize.php" title="' . __('Settings', 'options-for-twenty-seventeen') . '">' . __('Settings', 'options-for-twenty-seventeen') . '</a>';

            if (get_option('ofts_purchased') == false) {

                $settings_link .= ' | <a href="' . $this->ofts_upgrade_link . '" title="' . __('Upgrade Options for Twenty Seventeen', 'options-for-twenty-seventeen') . '" style="color: orange; font-weight: bold;">';
                $settings_link .= __('Upgrade', 'options-for-twenty-seventeen') . '</a>';
                $settings_link .= ' | <a href="?activate-ofts=true" id="ofts_activate_upgrade" title="' . __('Activate Upgrade', 'options-for-twenty-seventeen') . '" onclick="setTimeout(function(){document.getElementById(\'ofts_activate_upgrade\').removeAttribute(\'href\');},1); return true;">' . __('Activate Upgrade', 'options-for-twenty-seventeen') . '</a>';

            } else {

                $settings_link .= ' | <strong style="color: green; display: inline;">' . __('Upgraded', 'options-for-twenty-seventeen') . '</strong>';

            }

			array_unshift( $links, $settings_link );
			return $links;

		}

        function ofts_start_trial() {

        	if (is_admin() && isset($_GET['ofts-start-trial']) && $_GET['ofts-start-trial'] == 'true' && get_option('ofts_trial_date') == false) {

                update_option('ofts_trial_date', time());

                add_action('admin_notices', array($this, 'ofts_trial_started_notice'));

        	}

        }

        function ofts_trial_started_notice() {

?>

<div class="notice notice-info">

<p><strong><?php _e('Options for Twenty Seventeen Trial Started', 'options-for-twenty-seventeen'); ?></strong><br />
<?php
        _e('Your free 7 day trial of Options for Twenty Seventeen has started.', 'options-for-twenty-seventeen');
?>
</p>

</div>

<?php

        }

        function ofts_activate_upgrade() {

        	if (is_admin() && isset($_GET['activate-ofts']) && $_GET['activate-ofts'] == 'true') {

                $http_url = 'https://webd.uk/activate/';
                $http_args = array(
                    'timeout' => 15,
                    'body' => array(
                        'url' => ((isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST']),
                        'plugin' => 'options-for-twenty-seventeen'
                    )
                );
                $request = wp_remote_post($http_url, $http_args);

                if (is_wp_error($request)) {

					delete_option('ofts_purchased');
                    add_action('admin_notices', array($this, 'ofts_upgrade_failed_cannot_connect_notice'));

                } elseif (isset($request['body']) && $request['body'] == 'True') {

					update_option('ofts_purchased', true);

				} else {

					delete_option('ofts_purchased');
                    add_action('admin_notices', array($this, 'ofts_upgrade_failed_notice'));

				}

        	}

        }

        function ofts_upgrade_failed_notice() {

?>

<div class="notice notice-error">

<p><strong><?php _e('Options for Twenty Seventeen Upgrade Activation Error', 'options-for-twenty-seventeen'); ?></strong><br />
<?= sprintf(wp_kses(__('Please <a href="%s">purchase an upgrade</a> first. If you have already purchased an upgrade for this plugin, please contact us so we can investigate the issue.', 'options-for-twenty-seventeen'), array('a' => array('href' => array()))), esc_url($this->ofts_upgrade_link)); ?>
</p>

</div>

<?php

        }

        function ofts_upgrade_failed_cannot_connect_notice() {

?>

<div class="notice notice-error">

<p><strong><?php _e('Options for Twenty Seventeen Upgrade Activation Error', 'options-for-twenty-seventeen'); ?></strong><br />
<?php
        _e('The upgrade has failed because your website cannot connect to webd.uk to confirm your purchase. Please contact your Service provider to ask them to unblock webd.uk and if they cannot help you please contact us so we can investigate further.', 'options-for-twenty-seventeen');
?>
</p>

</div>

<?php

        }

        function ofts_customize_register($wp_customize) {

            $support_url = 'https://wordpress.org/support/plugin/options-for-twenty-seventeen';
            $default_description = sprintf(wp_kses(__('If you have any requests for new options, please <a href="%s">let us know in the support forum</a>.', 'options-for-twenty-seventeen'), array('a' => array('href' => array()))), esc_url($support_url));
            $upgrade_link = '<a href="' . $this->ofts_upgrade_link . '" title="' . __('Upgrade Options for Twenty Seventeen', 'options-for-twenty-seventeen') . '">' . __('Upgrade Options for Twenty Seventeen', 'options-for-twenty-seventeen') . '</a>';

            if (!get_option('ofts_trial_date')) {

                $upgrade_link .= ' or <a href="' . ofts_home_root() . 'wp-admin/plugins.php?ofts-start-trial=true" title="' . __('Start Free Trial', 'options-for-twenty-seventeen') . '">' . __('start a 7 day free trail', 'options-for-twenty-seventeen') . '</a>';

            }

            $upgrade_nag = $upgrade_link . __(' to activate this option.', 'options-for-twenty-seventeen');

if (get_option('ofts_purchased') == false) {

    if (get_option('ofts_trial_date') && time() < (strtotime('+1 week', get_option('ofts_trial_date')))) {

        $expiring_in = ceil(abs((strtotime('+1 week', get_option('ofts_trial_date'))) - time())/60/60/24);
        $expiring_text = '<span class="attention">' . sprintf(_n('Options for Twenty Seventeen plugin trial expires in less than %s day!', 'Options for Twenty Seventeen plugin trial expires in less than %s days!', $expiring_in, 'options-for-twenty-seventeen'), $expiring_in) . '</span>';
        $section_description = '<strong>' . $expiring_text . '</strong>' . ' ' . $upgrade_link . ' ' . __('to keep using all the options after that time.', 'options-for-twenty-seventeen');

    } else {

        $section_description = '';

    }

} else {

    $section_description = $default_description;

}

            $wp_customize->add_section('theme_options', array(
                'title'     => __('Theme Options', 'twentyseventeen'),
                'description'  => __('Use these options to customise the page layout and static front page sections.', 'options-for-twenty-seventeen') . ' ' . $section_description
            ));

            $wp_customize->add_control('page_layout', array(
               'label'           => __( 'Page Layout', 'twentyseventeen' ),
               'section'         => 'theme_options',
                'type'            => 'radio',
                'description'     => __( 'When the two-column layout is assigned, the page title is in one column and content is in the other.', 'twentyseventeen' ),
                'choices'         => array(
                    'one-column' => __( 'One Column', 'twentyseventeen' ),
                    'two-column' => __( 'Two Column', 'twentyseventeen' ),
                ),
                'priority'   => 1
            ));

            $control_label = __('Search / Archive Page Layout', 'options-for-twenty-seventeen');
            $control_description = __( 'When the two-column layout is assigned, the page title is in one column and content is in the other.', 'twentyseventeen' );

if ($this->ofts_request_permission('1.3.3') == true) {

            $wp_customize->add_setting('search_archive_page_layout', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('search_archive_page_layout', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'theme_options',
                'settings'      => 'search_archive_page_layout',
                'type'            => 'radio',
                'choices'         => array(
                    'one-column' => __( 'One Column', 'twentyseventeen' ),
                    '' => __( 'Two Column', 'twentyseventeen' ),
                ),
                'priority' => 5
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'search_archive_page_layout', array(
                'label'         => $control_label,
				'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'theme_options',
				'settings'      => array(),
				'priority' => 5
			)));

}

            $control_label = __('Front Page Sections', 'options-for-twenty-seventeen');
            $control_description = __('Set the number of sections on the static home page. You will need to save and re-load the Customiser to see changes.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.2.7') == true) {

            $wp_customize->add_setting('front_page_sections', array(
                'default'       => '4',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('front_page_sections', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'theme_options',
                'settings'      => 'front_page_sections',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 10,
                    'step'  => 1
                ),
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'front_page_sections', array(
                'label'         => $control_label,
				'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'theme_options',
				'settings'      => array(),
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
			)));

}

            $control_label = __('Panel Image Height', 'options-for-twenty-seventeen');
            $control_description = __('Set the height of the frontpage section parallax images.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.7.3') == true) {

            $wp_customize->add_setting('panel_image_height', array(
                'default'       => '101',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('panel_image_height', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'theme_options',
                'settings'      => 'panel_image_height',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 101,
                    'step'  => 1
                ),
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'panel_image_height', array(
                'label'         => $control_label,
				'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'theme_options',
				'settings'      => array(),
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
			)));

}

            $control_label = __('Front Page Sections Menus Box', 'options-for-twenty-seventeen');
            $control_description = __('Add a meta box to the admin panel to add Front Page Sections to menus.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.2.7') == true) {

            $wp_customize->add_setting('front_page_sections_menus_box', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('front_page_sections_menus_box', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'theme_options',
                'settings'      => 'front_page_sections_menus_box',
                'type'          => 'checkbox',
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'front_page_sections_menus_box', array(
                'label'         => $control_label,
				'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'theme_options',
				'settings'      => array(),
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
			)));

}

            $control_label = __('Blog Featured Images', 'options-for-twenty-seventeen');
            $control_description = __('Enable featured images in front page section blog page.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.5.1') == true) {

            $wp_customize->add_setting('front_page_section_blog_thumbnails', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('front_page_section_blog_thumbnails', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'theme_options',
                'settings'      => 'front_page_section_blog_thumbnails',
                'type'          => 'checkbox',
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'front_page_section_blog_thumbnails', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'theme_options',
				'settings'      => array(),
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
			)));

}

            $control_label = __('Parallax Off', 'options-for-twenty-seventeen');
            $control_description = __('Turn on "true parallax" or turn off parallax effect altogether on front page section featured images and show full image in the usual flow of the page.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.3.3') == true) {

            $wp_customize->add_setting('front_page_sections_parallax', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_text_options')
            ));
            $wp_customize->add_control('front_page_sections_parallax', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'theme_options',
                'settings'      => 'front_page_sections_parallax',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Parallax On', 'options-for-twenty-seventeen'),
                    'true' => __('True Parallax', 'options-for-twenty-seventeen'),
                    'off' => __('Parallax Off', 'options-for-twenty-seventeen'),
                ),
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'front_page_sections_parallax', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'theme_options',
				'settings'      => array(),
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
			)));

}

            $control_label = __('Back to Top Link', 'options-for-twenty-seventeen');
            $control_description = __('Add a "Back to Top" link at the end of all pages.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.3.3') == true) {

            $wp_customize->add_setting('footer_back_to_top', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('footer_back_to_top', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'theme_options',
                'settings'      => 'footer_back_to_top',
                'type'          => 'checkbox',
                'priority' => 5
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'footer_back_to_top', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'theme_options',
				'settings'      => array(),
                'priority' => 5
			)));

}

            $control_label = __('Fixed Back to Top Link', 'options-for-twenty-seventeen');
            $control_description = __('Fix the "Back to Top" link to the bottom right of the browser window.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.5.1') == true) {

            $wp_customize->add_setting('fix_footer_back_to_top', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('fix_footer_back_to_top', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'theme_options',
                'settings'      => 'fix_footer_back_to_top',
                'type'          => 'checkbox',
                'priority' => 5
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'fix_footer_back_to_top', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'theme_options',
				'settings'      => array(),
                'priority' => 5
			)));

}

            $control_label = __('Front Page Section Back to Top Link', 'options-for-twenty-seventeen');
            $control_description = __('Add a "Back to Top" link to front page sections.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.3.1') == true) {

            $wp_customize->add_setting('front_page_sections_back_to_top', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('front_page_sections_back_to_top', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'theme_options',
                'settings'      => 'front_page_sections_back_to_top',
                'type'          => 'checkbox',
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'front_page_sections_back_to_top', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'theme_options',
				'settings'      => array(),
                'priority' => 5,
			    'active_callback' => 'twentyseventeen_is_static_front_page'
			)));

}



if (get_option('ofts_purchased') == false) {

    if (get_option('ofts_trial_date') && time() < (strtotime('+1 week', get_option('ofts_trial_date')))) {

        $section_description = '<strong>' . $expiring_text . '</strong>' . ' ' . $upgrade_link . ' ' . __('to keep using all the options after that time.', 'options-for-twenty-seventeen');

    } else {

        $section_description = '';

    }

} else {

    $section_description = $default_description;

}

            $wp_customize->add_section('ofts_general', array(
                'title'     => __('General Options', 'options-for-twenty-seventeen'),
                'description'  => __('Use these options to customise the overall site design.', 'options-for-twenty-seventeen') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_setting('ignore_en_gb_translations', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('ignore_en_gb_translations', array(
                'label'         => __('Ignore en_GB Translations', 'options-for-twenty-seventeen'),
                'description'   => __('Ignore the en_GB translations supplied by wordpress.org for this plugin because they are not as the author intended.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_general',
                'settings'      => 'ignore_en_gb_translations',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('page_max_width', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_text_options')
            ));
            $wp_customize->add_control('page_max_width', array(
                'label'         => __('Page Max Width', 'options-for-twenty-seventeen'),
                'description'   => __('Sets the maximum width of the website container.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_general',
                'settings'      => 'page_max_width',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('100% (full width)', 'options-for-twenty-seventeen'),
                    '80em' => '80em (1280px)',
                    '75em' => '75em (1200px)',
                    '62.5em' => '62.5em (1000px)',
                    '48em' => '48em (768px)',
                    '46.25em' => '46.25em (740px)'
                )
            ));

            $wp_customize->add_setting('page_border_width', array(
                'default'           => 0,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('page_border_width', array(
                'label'         => __('Page Border Width', 'options-for-twenty-seventeen'),
                'description'   => __('Set the width of the website container border.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_general',
                'settings'      => 'page_border_width',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 0,
                    'max'   => 10,
                    'step'  => 1
                ),
		    	'active_callback' => array($this, 'ofts_has_no_header_image_or_nivo_slider')
            ));

            $wp_customize->add_setting('page_border_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'page_border_color', array(
                'label'         => __('Page Border Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Set the colour of the website container border.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_general',
            	'settings'      => 'page_border_color',
		    	'active_callback' => array($this, 'ofts_has_no_header_image_or_nivo_slider')
            )));

            $wp_customize->add_setting('page_border_style', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('page_border_style', array(
                'label'         => __('Page Border Style', 'options-for-twenty-seventeen'),
                'description'   => __('Set a border style for the website container.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_general',
                'settings'      => 'page_border_style',
                'type'          => 'select',
                'choices'       => array(
                    '' => 'Default (no border)',
                    'dotted' => __('Dotted', 'options-for-twenty-seventeen'),
                    'dashed' => __('Dashed', 'options-for-twenty-seventeen'),
                    'solid' => __('Solid', 'options-for-twenty-seventeen'),
                    'double' => __('Double', 'options-for-twenty-seventeen'),
                    'groove' => __('3D Groove', 'options-for-twenty-seventeen'),
                    'ridge' => __('3D Ridge', 'options-for-twenty-seventeen'),
                    'inset' => __('3D Inset', 'options-for-twenty-seventeen'),
                    'outset' => __('3D Outset', 'options-for-twenty-seventeen')
                ),
		    	'active_callback' => array($this, 'ofts_has_no_header_image_or_nivo_slider')
            ));

            $wp_customize->add_setting('page_border_location', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_text_options')
            ));
            $wp_customize->add_control('page_border_location', array(
                'label'         => __('Page Border Location', 'options-for-twenty-seventeen'),
                'description'   => __('Set the border location for the website container.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_general',
                'settings'      => 'page_border_location',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Default (Top, Right, Bottom and Left)', 'options-for-twenty-seventeen'),
                    'border-right-width: 0; border-left-width: 0;' => __('Top and Bottom only', 'options-for-twenty-seventeen'),
                    'border-top-width: 0; border-bottom-width: 0;' => __('Right and Left only', 'options-for-twenty-seventeen')
                ),
		    	'active_callback' => array($this, 'ofts_has_no_header_image_or_nivo_slider')
            ));

            $wp_customize->add_setting('remove_link_underlines', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_link_underlines', array(
                'label'         => __('Remove Link Underlines', 'options-for-twenty-seventeen'),
                'description'   => __('Remove all box-shadow properties that create underlines on links.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_general',
                'settings'      => 'remove_link_underlines',
                'type'          => 'checkbox'
            ));

            $control_label = __('Auto Excerpt Posts', 'options-for-twenty-seventeen');
            $control_description = __('Show first 55 words of a post with "Continue reading" link on home page and archive pages.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.2.2') == true) {

            $wp_customize->add_setting('auto_excerpt', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('auto_excerpt', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_general',
                'settings'      => 'auto_excerpt',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'auto_excerpt', array(
                'label'         => $control_label,
				'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_general',
				'settings'      => array()
			)));

}

            $control_label = __('Reset Tag Cloud Widget', 'options-for-twenty-seventeen');
            $control_description = __('Reverts Twenty Seventeen Tag Cloud Widget styling.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.2.2') == true) {

            $wp_customize->add_setting('reset_tag_cloud', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('reset_tag_cloud', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_general',
                'settings'      => 'reset_tag_cloud',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'reset_tag_cloud', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_general',
				'settings'      => array()
			)));

}



if (get_option('ofts_purchased') == false) {

    if (get_option('ofts_trial_date') && time() < (strtotime('+1 week', get_option('ofts_trial_date')))) {

        $section_description = '<strong>' . $expiring_text . '</strong>' . ' ' . $upgrade_link . ' ' . __('to keep using all the options after that time.', 'options-for-twenty-seventeen');

    } else {

        $section_description = '';

    }

} else {

    $section_description = $default_description;

}

            $wp_customize->add_section('ofts_header', array(
                'title'     => __('Header Options', 'options-for-twenty-seventeen'),
                'description'  => __('Use these options to customise the header.', 'options-for-twenty-seventeen') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_setting('header_width', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_text_options')
            ));
            $wp_customize->add_control('header_width', array(
                'label'         => __('Header Width', 'options-for-twenty-seventeen'),
                'description'   => __('Change the width of the site\'s header.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'header_width',
                'type'          => 'select',
                'choices'       => array(
                    '100%' => __('100% (full width)', 'options-for-twenty-seventeen'),
                    '80em' => '80em (1280px)',
                    '75em' => '75em (1200px)',
                    '' => '62.5em (1000px)',
                    '48em' => '48em (768px)',
                    '46.25em' => '46.25em (740px)'
                )
            ));

            $control_label = __('Use Featured Image as Header Image', 'options-for-twenty-seventeen');
            $control_description = __('This option moves the featured image on single posts and pages to the header and makes the header image the same size as on the home page.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.6.0') == true) {

            $wp_customize->add_setting('featured_header_image', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('featured_header_image', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_header',
                'settings'      => 'featured_header_image',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'featured_header_image', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_header',
				'settings'      => array()
			)));

}

            $control_label = __('Header Sidebar', 'options-for-twenty-seventeen');
            $control_description = __('Add a widget ready sidebar to the header area of the theme.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.1.1') == true) {

            $wp_customize->add_setting('header_sidebar', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('header_sidebar', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_header',
                'settings'      => 'header_sidebar',
                'type'          => 'select',
                'choices'       => array(
                    '' => 'No sidebar',
                    'top-left' => __('Position: Top, Left', 'options-for-twenty-seventeen'),
                    'top-center' => __('Position: Top, Center', 'options-for-twenty-seventeen'),
                    'top-right' => __('Position: Top, Right', 'options-for-twenty-seventeen')
                )
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'header_sidebar', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_header',
				'settings'      => array()
			)));

}

            $control_label = __('Hide YouTube Until Loaded', 'options-for-twenty-seventeen');
            $control_description = __('Hide the YouTube video in the header until it has started playing. The header image will be shown instead if there is one.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.5.0') == true) {

            $wp_customize->add_setting('hide_youtube_until_loaded', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('hide_youtube_until_loaded', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_header',
                'settings'      => 'hide_youtube_until_loaded',
                'type'          => 'checkbox',
                'active_callback' => array($this, 'ofts_has_youtube_video')
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'hide_youtube_until_loaded', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_header',
				'settings'      => array(),
                'active_callback' => array($this, 'ofts_has_youtube_video')
			)));

}

            $control_label = __('Pause YouTube on Scroll', 'options-for-twenty-seventeen');
            $control_description = __('Pause the YouTube video when the user scrolls down. Play again when the user scrolls back to the top of the page.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.5.0') == true) {

            $wp_customize->add_setting('pause_youtube_on_scroll', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('pause_youtube_on_scroll', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_header',
                'settings'      => 'pause_youtube_on_scroll',
                'type'          => 'checkbox',
                'active_callback' => array($this, 'ofts_has_youtube_video')
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'pause_youtube_on_scroll', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_header',
				'settings'      => array(),
                'active_callback' => array($this, 'ofts_has_youtube_video')
			)));

}

            $wp_customize->add_setting('remove_header_video_button', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_header_video_button', array(
                'label'         => __('Remove Header Video Button', 'options-for-twenty-seventeen'),
                'description'   => __('Removes the play / pause button at the top right of the header if a video is shown.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'remove_header_video_button',
                'type'          => 'checkbox'
            ));

            $control_label = __('Site Identity Background Colour', 'options-for-twenty-seventeen');
            $control_description = __('Set the site logo, title and description background colour.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.7.4') == true) {

            $wp_customize->add_setting('site_identity_background_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'site_identity_background_color', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_header',
            	'settings'      => 'site_identity_background_color'
            )));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'site_identity_background_color', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_header',
				'settings'      => array()
			)));

}

            $wp_customize->add_setting('remove_link_hover_opacity', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_link_hover_opacity', array(
                'label'         => __('Remove Link Hover Opacity', 'options-for-twenty-seventeen'),
                'description'   => __('Removes the opaque hover effect from header title and logo.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'remove_link_hover_opacity',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('site_title_text_align', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('site_title_text_align', array(
                'label'         => __('Site Title Alignment', 'options-for-twenty-seventeen'),
                'description'   => __('Align the site title to the left, center or right.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'site_title_text_align',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Left', 'options-for-twenty-seventeen'),
                    'center' => __('Center', 'options-for-twenty-seventeen'),
                    'right' => __('Right', 'options-for-twenty-seventeen')
                )
            ));

            $wp_customize->add_setting('site_title_text_transform', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('site_title_text_transform', array(
                'label'         => __('Site Title Font Case', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font case of the site title.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'site_title_text_transform',
                'type'          => 'select',
                'choices'       => array(
                    'none' => __('None', 'options-for-twenty-seventeen'),
                    'capitalize' => __('Capitalise', 'options-for-twenty-seventeen'),
                    '' => __('Uppercase', 'options-for-twenty-seventeen'),
                    'lowercase' => __('Lowercase', 'options-for-twenty-seventeen')
                )
            ));

            $wp_customize->add_setting('remove_site_title_letter_spacing', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_site_title_letter_spacing', array(
                'label'         => __('Remove Site Title Letter Spacing', 'options-for-twenty-seventeen'),
                'description'   => __('Remove the letter spacing from the site title.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'remove_site_title_letter_spacing',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('site_title_font_size', array(
                'default'           => 2250,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('site_title_font_size', array(
                'label'         => __('Site Title Font Size', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font size of the site title.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'site_title_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 875,
                    'max'   => 3625,
                    'step'  => 125
                ),
            ));

            $wp_customize->add_setting('site_title_font_weight', array(
                'default'           => 800,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('site_title_font_weight', array(
                'label'         => __('Site Title Font Weight', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font weight of the site title.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'site_title_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                ),
            ));

            $wp_customize->add_setting('site_title_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'site_title_color', array(
                'label'         => __('Site Title Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font colour of the site title.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
            	'settings'      => 'site_title_color'
            )));

            $wp_customize->add_setting('site_description_text_align', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('site_description_text_align', array(
                'label'         => __('Site Description Alignment', 'options-for-twenty-seventeen'),
                'description'   => __('Align the site description to the left, center or right.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'site_description_text_align',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Left', 'options-for-twenty-seventeen'),
                    'center' => __('Center', 'options-for-twenty-seventeen'),
                    'right' => __('Right', 'options-for-twenty-seventeen')
                )
            ));

            $wp_customize->add_setting('site_description_text_transform', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('site_description_text_transform', array(
                'label'         => __('Site Description Font Case', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font case of the site description.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'site_description_text_transform',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('None', 'options-for-twenty-seventeen'),
                    'capitalize' => __('Capitalise', 'options-for-twenty-seventeen'),
                    'uppercase' => __('Uppercase', 'options-for-twenty-seventeen'),
                    'lowercase' => __('Lowercase', 'options-for-twenty-seventeen')
                )
            ));

            $wp_customize->add_setting('site_description_font_size', array(
                'default'           => 1000,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('site_description_font_size', array(
                'label'         => __('Site Description Font Size', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font size of the site description.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'site_description_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 500,
                    'max'   => 1500,
                    'step'  => 125
                ),
            ));

            $wp_customize->add_setting('site_description_font_weight', array(
                'default'           => 400,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('site_description_font_weight', array(
                'label'         => __('Site Description Font Weight', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font weight of the site description.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'site_description_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                ),
            ));

            $wp_customize->add_setting('site_description_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'site_description_color', array(
                'label'         => __('Site Description Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font colour of the site description.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
            	'settings'      => 'site_description_color'
            )));

            $wp_customize->add_setting('remove_header_gradient', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_header_gradient', array(
                'label'         => __('Remove Header Gradient', 'options-for-twenty-seventeen'),
                'description'   => __('Removes the grey background from the bottom of the cover image.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'remove_header_gradient',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('remove_header_background', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_header_background', array(
                'label'         => __('Remove Header Background', 'options-for-twenty-seventeen'),
                'description'   => __('Removes the grey background from the header.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_header',
                'settings'      => 'remove_header_background',
                'type'          => 'checkbox'
            ));

            if (!get_theme_mod('external_header_video')) {

                $wp_customize->add_setting('full_cover_image', array(
                    'default'           => false,
                    'type'              => 'theme_mod',
                    'transport'         => 'postMessage',
                    'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
                ));
                $wp_customize->add_control('full_cover_image', array(
                    'label'         => __('Full Cover Image', 'options-for-twenty-seventeen'),
                    'description'   => __('Forces the cover image to retain its aspect ratio.', 'options-for-twenty-seventeen'),
                    'section'       => 'ofts_header',
                    'settings'      => 'full_cover_image',
                    'type'          => 'checkbox',
    		    	'active_callback' => array($this, 'ofts_has_header_image_or_nivo_slider')
                ));

            }

            $control_label = __('True Parallax Cover Image', 'options-for-twenty-seventeen');
            $control_description = __('Turn on "true parallax" scrolling for the header cover image.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.7.6') == true) {

            $wp_customize->add_setting('true_parallax_cover_image', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('true_parallax_cover_image', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_header',
                'settings'      => 'true_parallax_cover_image',
                'type'          => 'checkbox',
                'active_callback' => array($this, 'ofts_has_header_image_or_nivo_slider')
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'true_parallax_cover_image', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_header',
				'settings'      => array(),
                'active_callback' => array($this, 'ofts_has_header_image_or_nivo_slider')
			)));

}

            $control_label = __('Slider Cover', 'options-for-twenty-seventeen');
            $control_description = sprintf(wp_kses(__('Replaces the cover image with a <a href="%s">Nivo</a> or Sliderspack Slider. Remember to set "Image Size" to "Twenty-Seventeen-featured-image" in your slider settings for best results.', 'options-for-twenty-seventeen'), array('a' => array('href' => array()))), esc_url(ofts_home_root() . 'wp-admin/plugin-install.php?s=nivo-slider-lite&tab=search&type=term'));

if ($this->ofts_request_permission('1.1') == true) {

            $wp_customize->add_setting('nivo_slider_cover', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer_options')
            ));
            $nivo_sliders = get_posts(array(
                'post_type' => array(
                    'nivoslider',
                    'wpspaios_slider'
                ),
                'posts_per_page' => -1,
            ));
            $nivo_sliders_array = array('' => 'No slider');

            foreach ($nivo_sliders as $nivo_slider) {

                $nivo_sliders_array[$nivo_slider->ID] = $nivo_slider->post_title;

            }

            $wp_customize->add_control('nivo_slider_cover', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_header',
                'settings'      => 'nivo_slider_cover',
                'type'          => 'select',
                'choices'       => $nivo_sliders_array
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'nivo_slider_cover', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_header',
				'settings'      => array()
			)));

}

            $control_label = __('Enable Nivo Captions', 'options-for-twenty-seventeen');
            $control_description = __('Overlay slide captions on Nivo Slider.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.4.3') == true) {

            $wp_customize->add_setting('enable_nivo_captions', array(
                'default'       => false,
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('enable_nivo_captions', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_header',
                'settings'      => 'enable_nivo_captions',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'enable_nivo_captions', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_header',
				'settings'      => array()
			)));

}



if (get_option('ofts_purchased') == false) {

    if (get_option('ofts_trial_date') && time() < (strtotime('+1 week', get_option('ofts_trial_date')))) {

        $section_description = '<strong>' . $expiring_text . '</strong>' . ' ' . $upgrade_link . ' ' . __('to keep using all the options after that time.', 'options-for-twenty-seventeen');

    } else {

        $section_description = '';

    }

} else {

    $section_description = $default_description;

}

            $wp_customize->add_section('ofts_navigation', array(
                'title'        => __('Nav Options', 'options-for-twenty-seventeen'),
                'description'  => __('Use these options to customise the navigation.', 'options-for-twenty-seventeen') . ' ' . $section_description,
                'priority'     => 0
            ));

            $control_label = __('Add Logo to Navigation Bar', 'options-for-twenty-seventeen');
            $control_description = __('Move or copy the Site Logo to the Navigation Bar.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.4.3') == true) {

            $wp_customize->add_setting('add_logo_to_nav', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('add_logo_to_nav', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_navigation',
                'settings'      => 'add_logo_to_nav',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Select option ...', 'options-for-twenty-seventeen'),
                    'copy' => __('Copy Logo', 'options-for-twenty-seventeen'),
                    'move' => __('Move Logo', 'options-for-twenty-seventeen')
                )
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'add_logo_to_nav', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_navigation',
				'settings'      => array()
			)));

}

            $control_label = __('Animate Logo in Navigation Bar', 'options-for-twenty-seventeen');
            $control_description = __('Shrinks the logo in the Navigation Bar when the user scrolls.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.4.3') == true) {

            $wp_customize->add_setting('animate_nav_logo', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('animate_nav_logo', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_navigation',
                'settings'      => 'animate_nav_logo',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Select option ...', 'options-for-twenty-seventeen'),
                    'home' => __('Animate on home page only', 'options-for-twenty-seventeen'),
                    'all' => __('Animate on all pages', 'options-for-twenty-seventeen')
                )
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'animate_nav_logo', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_navigation',
				'settings'      => array()
			)));

}

            $control_label = __('Move Navigation Bar to Top', 'options-for-twenty-seventeen');
            $control_description = __('Moves the main menu to the top of the custom header.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.3.9') == true) {

            $wp_customize->add_setting('move_nav_bar_to_top', array(
                'default'       => false,
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('move_nav_bar_to_top', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_navigation',
                'settings'      => 'move_nav_bar_to_top',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'move_nav_bar_to_top', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_navigation',
				'settings'      => array()
			)));

}

            $control_label = __('Fix Mobile Navigation Bar to Top', 'options-for-twenty-seventeen');
            $control_description = __('Fixes the mobile navigation bar to the top of the screen.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.8.2') == true) {

            $wp_customize->add_setting('fix_mobile_nav_bar_to_top', array(
                'default'       => false,
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('fix_mobile_nav_bar_to_top', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_navigation',
                'settings'      => 'fix_mobile_nav_bar_to_top',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'fix_mobile_nav_bar_to_top', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_navigation',
				'settings'      => array()
			)));

}

            $wp_customize->add_setting('nav_bar_width', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_text_options')
            ));
            $wp_customize->add_control('nav_bar_width', array(
                'label'         => __('Navigation Bar Width', 'options-for-twenty-seventeen'),
                'description'   => __('Change the width of the main menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
                'settings'      => 'nav_bar_width',
                'type'          => 'select',
                'choices'       => array(
                    '100%' => __('100% (full width)', 'options-for-twenty-seventeen'),
                    '80rem' => '80rem (1280px)',
                    '75rem' => '75rem (1200px)',
                    '' => '62.5em (1000px)',
                    '48rem' => '48rem (768px)',
                    '46.25rem' => '46.25rem (740px)'
                )
            ));

            $control_label = __('Navigation Responsive Breakpoint', 'options-for-twenty-seventeen');
            $control_description = __('Increase the point at which the main menu becomes a mobile menu.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.8.7') == true) {

            $wp_customize->add_setting('nav_responsive_breakpoint', array(
                'default'           => 0,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('nav_responsive_breakpoint', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_navigation',
                'settings'      => 'nav_responsive_breakpoint',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 0,
                    'max'   => 32,
                    'step'  => 1
                ),
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'nav_responsive_breakpoint', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_navigation',
				'settings'      => array()
			)));

}

            $wp_customize->add_setting('nav_remove_padding_vertical', array(
                'default'       => false,
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('nav_remove_padding_vertical', array(
                'label'         => __('Remove Navigation Vertical Padding', 'options-for-twenty-seventeen'),
                'description'   => __('Remove the padding above and below the main menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
                'settings'      => 'nav_remove_padding_vertical',
                'type'          => 'checkbox'
            ));

            $control_label = __('Navigation Alignment', 'options-for-twenty-seventeen');
            $control_description = __('Align the navigation menu items to the left, center or right.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.1.6') == true) {

            $wp_customize->add_setting('nav_text_align', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('nav_text_align', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_navigation',
                'settings'      => 'nav_text_align',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Left', 'options-for-twenty-seventeen'),
                    'center' => __('Center', 'options-for-twenty-seventeen'),
                    'right' => __('Right', 'options-for-twenty-seventeen')
                )
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'nav_text_align', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_navigation',
				'settings'      => array()
			)));

}

            $wp_customize->add_setting('navigation_text_transform', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('navigation_text_transform', array(
                'label'         => __('Navigation Font Case', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font case of the navigation menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
                'settings'      => 'navigation_text_transform',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('None', 'options-for-twenty-seventeen'),
                    'capitalize' => __('Capitalise', 'options-for-twenty-seventeen'),
                    'uppercase' => __('Uppercase', 'options-for-twenty-seventeen'),
                    'lowercase' => __('Lowercase', 'options-for-twenty-seventeen')
                )
            ));

            $wp_customize->add_setting('navigation_font_size', array(
                'default'           => 875,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('navigation_font_size', array(
                'label'         => __('Navigation Font Size', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font size of the navigation menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
                'settings'      => 'navigation_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 750,
                    'max'   => 1000,
                    'step'  => 125
                ),
            ));

            $wp_customize->add_setting('navigation_font_weight', array(
                'default'           => 600,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('navigation_font_weight', array(
                'label'         => __('Navigation Font Weight', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font weight of the navigation menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
                'settings'      => 'navigation_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                ),
            ));

            $control_label = __('Navigation Logo Alignment', 'options-for-twenty-seventeen');
            $control_description = __('Align the logo in the main menu, if present.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.5.6') == true) {

            $wp_customize->add_setting('nav_logo_align', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('nav_logo_align', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_navigation',
                'settings'      => 'nav_logo_align',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Automatic', 'options-for-twenty-seventeen'),
                    'left' => __('Left', 'options-for-twenty-seventeen'),
                    'center' => __('Center', 'options-for-twenty-seventeen'),
                    'right' => __('Right', 'options-for-twenty-seventeen')
                )
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'nav_logo_align', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_navigation',
				'settings'      => array()
			)));

}

            $wp_customize->add_setting('nav_link_padding_vertical', array(
                'default'           => 14,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('nav_link_padding_vertical', array(
                'label'         => __('Navigation Link Vertical Padding', 'options-for-twenty-seventeen'),
                'description'   => __('Change the padding above and below links in the main menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
                'settings'      => 'nav_link_padding_vertical',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 0,
                    'max'   => 31,
                    'step'  => 1
                ),
            ));

            $wp_customize->add_setting('nav_link_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_link_color', array(
                'label'         => __('Navigation Link Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the colour of links in the main menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
            	'settings'      => 'nav_link_color'
            )));

            $wp_customize->add_setting('nav_current_link_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_current_link_color', array(
                'label'         => __('Navigation Current Page Link Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the colour of current page links in the main menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
            	'settings'      => 'nav_current_link_color'
            )));

            $wp_customize->add_setting('nav_link_hover_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_link_hover_color', array(
                'label'         => __('Navigation Hover Link Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the colour of hovered links in the main menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
            	'settings'      => 'nav_link_hover_color'
            )));

            $wp_customize->add_setting('nav_link_hover_background_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_link_hover_background_color', array(
                'label'         => __('Navigation Hover Background Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the background colour of hovered links in the main menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
            	'settings'      => 'nav_link_hover_background_color'
            )));

            $wp_customize->add_setting('nav_background_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_background_color', array(
                'label'         => __('Navigation Background Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the background colour of the main menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
            	'settings'      => 'nav_background_color'
            )));

            $wp_customize->add_setting('sub_menu_background_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'sub_menu_background_color', array(
                'label'         => __('Sub Menu Background Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the background colour of dropdown menus in the main menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
            	'settings'      => 'sub_menu_background_color'
            )));

            $wp_customize->add_setting('rotate_sub_menu_arrow', array(
                'default'       => false,
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('rotate_sub_menu_arrow', array(
                'label'         => __('Rotate Sub Menu Arrow', 'options-for-twenty-seventeen'),
                'description'   => __('Rotates the arrow below a main menu item that has a sub menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
                'settings'      => 'rotate_sub_menu_arrow',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('remove_nav_scroll_arrow', array(
                'default'       => false,
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_nav_scroll_arrow', array(
                'label'         => __('Remove Navigation Scroll Down Arrow', 'options-for-twenty-seventeen'),
                'description'   => __('Removes the arrow at the end of the main menu.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_navigation',
                'settings'      => 'remove_nav_scroll_arrow',
                'type'          => 'checkbox'
            ));



if (get_option('ofts_purchased') == false) {

    if (get_option('ofts_trial_date') && time() < (strtotime('+1 week', get_option('ofts_trial_date')))) {

        $section_description = '<strong>' . $expiring_text . '</strong>' . ' ' . $upgrade_link . ' ' . __('to keep using all the options after that time.', 'options-for-twenty-seventeen');

    } else {

        $section_description = '';

    }

} else {

    $section_description = $default_description;

}

            $wp_customize->add_section('ofts_content', array(
                'title'     => __('Content Options', 'options-for-twenty-seventeen'),
                'description'  => __('Use these options to customise the content.', 'options-for-twenty-seventeen') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_setting('content_width', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_text_options')
            ));
            $wp_customize->add_control('content_width', array(
                'label'         => __('Content Width', 'options-for-twenty-seventeen'),
                'description'   => __('Change the width of the site\'s content.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'content_width',
                'type'          => 'select',
                'choices'       => array(
                    '100%' => __('100% (full width)', 'options-for-twenty-seventeen'),
                    '80em' => '80em (1280px)',
                    '75em' => '75em (1200px)',
                    '62.5em' => '62.5em (1000px)',
                    '48em' => '48em (768px)',
                    '' => '46.25em (740px)'
                )
            ));

            $wp_customize->add_setting('page_sidebar', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('page_sidebar', array(
                'label'         => __('Page Sidebar', 'options-for-twenty-seventeen'),
                'description'   => __('Adds the Blog Sidebar widget area to pages.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'page_sidebar',
                'type'          => 'checkbox'
            ));

            $control_label = __('Hide Blog Sidebar for Mobile', 'options-for-twenty-seventeen');
            $control_description = __('Hides the Blog Sidebar widget area on small screens.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.4.2') == true) {

            $wp_customize->add_setting('hide_blog_sidebar', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('hide_blog_sidebar', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'hide_blog_sidebar',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'hide_blog_sidebar', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Match Content and Sidebar Height', 'options-for-twenty-seventeen');
            $control_description = __('Matches the height of the Blog Sidebar to the Primary Content.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.1.7') == true) {

            $wp_customize->add_setting('match_primary_secondary_height', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('match_primary_secondary_height', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'match_primary_secondary_height',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'match_primary_secondary_height', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Primary Content Area Width', 'options-for-twenty-seventeen');
            $control_description = __('Change the width of the Primary Content area.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.1.8') == true) {

            $wp_customize->add_setting('primary_width', array(
                'default'           => 58,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('primary_width', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'primary_width',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 58,
                    'max'   => 70,
                    'step'  => 1
                ),
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'primary_width', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Content Margin / Gutter', 'options-for-twenty-seventeen');
            $control_description = __('Change the gap between the Primary Content area and the Blog Sidebar.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.1.8') == true) {

            $wp_customize->add_setting('content_gutter', array(
                'default'           => 7,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('content_gutter', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'content_gutter',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 7,
                    'step'  => 1
                ),
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'content_gutter', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Swap Content and Sidebar', 'options-for-twenty-seventeen');
            $control_description = __('Moves the Blog Sidebar to the left of the Primary Content.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.1.8') == true) {

            $wp_customize->add_setting('swap_content', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('swap_content', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'swap_content',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'swap_content', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Implement Yoast SEO Breadcrumbs', 'options-for-twenty-seventeen');
            $control_description = sprintf(wp_kses(__('Inject <a href="%s">Yoast SEO</a> breadcrumbs above and / or below sinlge post and page content.', 'options-for-twenty-seventeen'), array('a' => array('href' => array()))), esc_url(ofts_home_root() . 'wp-admin/plugin-install.php?s=wordpress-seo&tab=search&type=term'));

if ($this->ofts_request_permission('1.7.2') == true) {

            $wp_customize->add_setting('implement_yoast_breadcrumbs', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('implement_yoast_breadcrumbs', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'implement_yoast_breadcrumbs',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Disable Breadcrumbs', 'options-for-twenty-seventeen'),
                    'top' => __('Above Content', 'options-for-twenty-seventeen'),
                    'bottom' => __('Below Content', 'options-for-twenty-seventeen'),
                    'both' => __('Above and Below Content', 'options-for-twenty-seventeen')
                )
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'implement_yoast_breadcrumbs', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Inject Featured Image Caption', 'options-for-twenty-seventeen');
            $control_description = __('Overlays the image caption onto the featured image.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.6.4') == true) {

            $wp_customize->add_setting('inject_featured_image_caption', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('inject_featured_image_caption', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'inject_featured_image_caption',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'inject_featured_image_caption', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Featured Image Caption Font Size', 'options-for-twenty-seventeen');
            $control_description = __('Change the font size of featured image captions.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.8.1') == true) {

            $wp_customize->add_setting('featured_image_caption_font_size', array(
                'default'           => 2250,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('featured_image_caption_font_size', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'featured_image_caption_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 875,
                    'max'   => 3625,
                    'step'  => 125
                ),
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'featured_image_caption_font_size', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Featured Image Caption Font Weight', 'options-for-twenty-seventeen');
            $control_description = __('Change the font weight of featured image captions.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.8.1') == true) {

            $wp_customize->add_setting('featured_image_caption_font_weight', array(
                'default'           => 800,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('featured_image_caption_font_weight', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'featured_image_caption_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                ),
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'featured_image_caption_font_weight', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Move Featured Image', 'options-for-twenty-seventeen');
            $control_description = __('Move the featured image into the post content.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.2.2') == true) {

            $wp_customize->add_setting('move_featured_image', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('move_featured_image', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'move_featured_image',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'move_featured_image', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Hide Archive Featured Images', 'options-for-twenty-seventeen');
            $control_description = __('Hide post featured images on Archive pages.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.5.2') == true) {

            $wp_customize->add_setting('hide_archive_featured_images', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('hide_archive_featured_images', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'hide_archive_featured_images',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'hide_archive_featured_images', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Hide Post Dates', 'options-for-twenty-seventeen');
            $control_description = __('Prevents Wordpress from displaying the date of a post.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.2.8') == true) {

            $wp_customize->add_setting('remove_posted_on', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_posted_on', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'remove_posted_on',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'remove_posted_on', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $control_label = __('Hide Post Author', 'options-for-twenty-seventeen');
            $control_description = __('Prevents Wordpress from displaying the author of a post.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.2.2') == true) {

            $wp_customize->add_setting('remove_author', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_author', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'remove_author',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'remove_author', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $wp_customize->add_setting('content_padding_top', array(
                'default'           => 12,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('content_padding_top', array(
                'label'         => __('Content Padding Top', 'options-for-twenty-seventeen'),
                'description'   => __('Change the padding at the top of the content below the navigation (not on front page).', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'content_padding_top',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 2,
                    'max'   => 12,
                    'step'  => 1
                ),
            ));

            $wp_customize->add_setting('page_header_title_text_align', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('page_header_title_text_align', array(
                'label'         => __('Archive Title Alignment', 'options-for-twenty-seventeen'),
                'description'   => __('Align the archive titles to the left, center or right.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'page_header_title_text_align',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Left', 'options-for-twenty-seventeen'),
                    'center' => __('Center', 'options-for-twenty-seventeen'),
                    'right' => __('Right', 'options-for-twenty-seventeen')
                )
            ));

            $wp_customize->add_setting('page_header_title_text_transform', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('page_header_title_text_transform', array(
                'label'         => __('Archive Title Font Case', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font case of archive titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'page_header_title_text_transform',
                'type'          => 'select',
                'choices'       => array(
                    'none' => __('None', 'options-for-twenty-seventeen'),
                    'capitalize' => __('Capitalize', 'options-for-twenty-seventeen'),
                    '' => __('Uppercase', 'options-for-twenty-seventeen'),
                    'lowercase' => __('Lowercase', 'options-for-twenty-seventeen')
                )
            ));

            $wp_customize->add_setting('remove_page_header_title_letter_spacing', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_page_header_title_letter_spacing', array(
                'label'         => __('Remove Archive Title Letter Spacing', 'options-for-twenty-seventeen'),
                'description'   => __('Remove the letter spacing from archive titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'remove_page_header_title_letter_spacing',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('page_header_title_font_size', array(
                'default'           => 875,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('page_header_title_font_size', array(
                'label'         => __('Archive Title Font Size', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font size of archive titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'page_header_title_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 875,
                    'max'   => 2750,
                    'step'  => 125
                ),
            ));

            $wp_customize->add_setting('page_header_title_font_weight', array(
                'default'           => 800,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('page_header_title_font_weight', array(
                'label'         => __('Archive Title Font Weight', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font weight of archive titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'page_header_title_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                ),
            ));

            $wp_customize->add_setting('page_header_title_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'page_header_title_color', array(
                'label'         => __('Archive Title Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font colour of archive titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
            	'settings'      => 'page_header_title_color'
            )));

            $control_label = __('Remove Category and Tag from Archive Titles', 'options-for-twenty-seventeen');
            $control_description = __('Remove the words "Category: " and "Tag: " from archive page titles.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.2.3') == true) {

            $wp_customize->add_setting('remove_category_tag', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_category_tag', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_content',
                'settings'      => 'remove_category_tag',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'remove_category_tag', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_content',
				'settings'      => array()
			)));

}

            $wp_customize->add_setting('post_entry_header_title_text_align', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('post_entry_header_title_text_align', array(
                'label'         => __('Post Title Alignment', 'options-for-twenty-seventeen'),
                'description'   => __('Align the post titles to the left, center or right.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'post_entry_header_title_text_align',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Left', 'options-for-twenty-seventeen'),
                    'center' => __('Center', 'options-for-twenty-seventeen'),
                    'right' => __('Right', 'options-for-twenty-seventeen')
                )
            ));

            $wp_customize->add_setting('post_entry_header_title_text_transform', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('post_entry_header_title_text_transform', array(
                'label'         => __('Post Title Font Case', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font case of post titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'post_entry_header_title_text_transform',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('None', 'options-for-twenty-seventeen'),
                    'capitalize' => __('Capitalise', 'options-for-twenty-seventeen'),
                    'uppercase' => __('Uppercase', 'options-for-twenty-seventeen'),
                    'lowercase' => __('Lowercase', 'options-for-twenty-seventeen')
                )
            ));

            $wp_customize->add_setting('post_entry_header_title_font_size', array(
                'default'           => 1625,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('post_entry_header_title_font_size', array(
                'label'         => __('Post Title Font Size', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font size of post titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'post_entry_header_title_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 875,
                    'max'   => 2750,
                    'step'  => 125
                ),
            ));

            $wp_customize->add_setting('post_entry_header_title_font_weight', array(
                'default'           => 300,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('post_entry_header_title_font_weight', array(
                'label'         => __('Post Title Font Weight', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font weight of post titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'post_entry_header_title_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                ),
            ));

            $wp_customize->add_setting('post_entry_header_title_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'post_entry_header_title_color', array(
                'label'         => __('Post Title Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font colour of post titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
            	'settings'      => 'post_entry_header_title_color'
            )));

            $wp_customize->add_setting('page_entry_header_title_text_align', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('page_entry_header_title_text_align', array(
                'label'         => __('Page Title Alignment', 'options-for-twenty-seventeen'),
                'description'   => __('Align the page titles to the left, center or right.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'page_entry_header_title_text_align',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Left', 'options-for-twenty-seventeen'),
                    'center' => __('Center', 'options-for-twenty-seventeen'),
                    'right' => __('Right', 'options-for-twenty-seventeen')
                )
            ));

            $wp_customize->add_setting('page_entry_header_title_text_transform', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('page_entry_header_title_text_transform', array(
                'label'         => __('Page Title Font Case', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font case of page titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'page_entry_header_title_text_transform',
                'type'          => 'select',
                'choices'       => array(
                    'none' => __('None', 'options-for-twenty-seventeen'),
                    'capitalize' => __('Capitalise', 'options-for-twenty-seventeen'),
                    '' => __('Uppercase', 'options-for-twenty-seventeen'),
                    'lowercase' => __('Lowercase', 'options-for-twenty-seventeen')
                )
            ));

            $wp_customize->add_setting('remove_page_entry_header_title_letter_spacing', array(
                'default'           => false,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_page_entry_header_title_letter_spacing', array(
                'label'         => __('Remove Page Title Letter Spacing', 'options-for-twenty-seventeen'),
                'description'   => __('Remove the letter spacing from page titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'remove_page_entry_header_title_letter_spacing',
                'type'          => 'checkbox'
            ));

            $wp_customize->add_setting('page_entry_header_title_font_size', array(
                'default'           => 1625,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('page_entry_header_title_font_size', array(
                'label'         => __('Page Title Font Size', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font size of page titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'page_entry_header_title_font_size',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 875,
                    'max'   => 2750,
                    'step'  => 125
                ),
            ));

            $wp_customize->add_setting('page_entry_header_title_font_weight', array(
                'default'           => 800,
                'type'              => 'theme_mod',
                'transport'         => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('page_entry_header_title_font_weight', array(
                'label'         => __('Page Title Font Weight', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font weight of page titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'page_entry_header_title_font_weight',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 100,
                    'max'   => 900,
                    'step'  => 100
                ),
            ));

            $wp_customize->add_setting('page_entry_header_title_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'page_entry_header_title_color', array(
                'label'         => __('Page Title Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the font colour of page titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
            	'settings'      => 'page_entry_header_title_color'
            )));

            $wp_customize->add_setting('page_entry_header_title_margin_bottom', array(
                'default'           => 9,
                'type'              => 'theme_mod',
                'transport'         => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer')
            ));
            $wp_customize->add_control('page_entry_header_title_margin_bottom', array(
                'label'         => __('Page Title Margin Bottom', 'options-for-twenty-seventeen'),
                'description'   => __('Change the margin height below the page title.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
                'settings'      => 'page_entry_header_title_margin_bottom',
                'type'          => 'range',
                'input_attrs' => array(
                    'min'   => 1,
                    'max'   => 9,
                    'step'  => 1
                ),
            ));

            $wp_customize->add_setting('content_link_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'content_link_color', array(
                'label'         => __('Content Link Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the colour of links in the content.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
            	'settings'      => 'content_link_color'
            )));

            $wp_customize->add_setting('content_hover_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'content_hover_color', array(
                'label'         => __('Content Hover Link Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the colour of hovered links in the content.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_content',
            	'settings'      => 'content_hover_color'
            )));



if (get_option('ofts_purchased') == false) {

    if (get_option('ofts_trial_date') && time() < (strtotime('+1 week', get_option('ofts_trial_date')))) {

        $section_description = '<strong>' . $expiring_text . '</strong>' . ' ' . $upgrade_link . ' ' . __('to keep using all the options after that time.', 'options-for-twenty-seventeen');

    } else {

        $section_description = '';

    }

} else {

    $section_description = $default_description;

}

            $wp_customize->add_section('ofts_footer', array(
                'title'     => __('Footer Options', 'options-for-twenty-seventeen'),
                'description'  => __('Use these options to customise the footer.', 'options-for-twenty-seventeen') . ' ' . $section_description,
                'priority'     => 0
            ));

            $wp_customize->add_setting('footer_width', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_text_options')
            ));
            $wp_customize->add_control('footer_width', array(
                'label'         => __('Footer Width', 'options-for-twenty-seventeen'),
                'description'   => __('Change the width of the site\'s footer.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_footer',
                'settings'      => 'footer_width',
                'type'          => 'select',
                'choices'       => array(
                    '100%' => __('100% (full width)', 'options-for-twenty-seventeen'),
                    '80rem' => '80rem (1280px)',
                    '75rem' => '75rem (1200px)',
                    '' => '62.5em (1000px)',
                    '48rem' => '48rem (768px)',
                    '46.25rem' => '46.25rem (740px)'
                )
            ));

            $wp_customize->add_setting('footer_background_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_background_color', array(
                'label'         => __('Footer Background Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the background colour of the footer area.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_footer',
            	'settings'      => 'footer_background_color'
            )));

            $wp_customize->add_setting('footer_title_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_title_color', array(
                'label'         => __('Footer Title Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the colour of the footer widget titles.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_footer',
            	'settings'      => 'footer_title_color'
            )));

            $wp_customize->add_setting('footer_text_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_text_color', array(
                'label'         => __('Footer Text Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the colour of the footer text.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_footer',
            	'settings'      => 'footer_text_color'
            )));

            $wp_customize->add_setting('footer_link_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_link_color', array(
                'label'         => __('Footer Link Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the colour of the footer links.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_footer',
            	'settings'      => 'footer_link_color'
            )));

            $wp_customize->add_setting('footer_link_hover_color', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => 'sanitize_hex_color'
            ));
            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_link_hover_color', array(
                'label'         => __('Footer Link Hover Colour', 'options-for-twenty-seventeen'),
                'description'   => __('Change the colour of the hovered footer links.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_footer',
            	'settings'      => 'footer_link_hover_color'
            )));

            $control_label = __('Footer Sidebars', 'options-for-twenty-seventeen');
            $control_description = __('Add a third or fourth widget ready sidebar to the footer area of the theme.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.3.7') == true) {

            $wp_customize->add_setting('footer_sidebars', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_integer_options')
            ));
            $wp_customize->add_control('footer_sidebars', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_footer',
                'settings'      => 'footer_sidebars',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('2 Footer Sidebars', 'options-for-twenty-seventeen'),
                    '3' => __('3 Footer Sidebars', 'options-for-twenty-seventeen'),
                    '4' => __('4 Footer Sidebars', 'options-for-twenty-seventeen')
                )
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'footer_sidebars', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_footer',
				'settings'      => array()
			)));

}

            $control_label = __('Fix Social Links', 'options-for-twenty-seventeen');
            $control_description = __('Fix the social links to the left or right for large screens.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.5.9') == true) {

            $wp_customize->add_setting('fix_social_links', array(
                'default'       => '',
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_slug_options')
            ));
            $wp_customize->add_control('fix_social_links', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_footer',
                'settings'      => 'fix_social_links',
                'type'          => 'select',
                'choices'       => array(
                    '' => __('Do not fix', 'options-for-twenty-seventeen'),
                    'left' => __('Fix to the left', 'options-for-twenty-seventeen'),
                    'right' => __('Fix to the right', 'options-for-twenty-seventeen')
                )
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'fix_social_links', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_footer',
				'settings'      => array()
			)));

}

            $control_label = __('Square Social Links', 'options-for-twenty-seventeen');
            $control_description = __('Make the social links menu items square.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.3.4') == true) {

            $wp_customize->add_setting('square_social_links', array(
                'default'       => false,
                'type'          => 'theme_mod',
                'transport'     => 'postMessage',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('square_social_links', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_footer',
                'settings'      => 'square_social_links',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'square_social_links', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_footer',
				'settings'      => array()
			)));

}

            $control_label = __('Add Colours to Social Links Menu', 'options-for-twenty-seventeen');
            $control_description = __('Changes the background of the social links menu items to their relevant corporate colours.', 'options-for-twenty-seventeen');

if ($this->ofts_request_permission('1.3.4') == true) {

            $wp_customize->add_setting('coloured_social_links_menu', array(
                'default'       => false,
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('coloured_social_links_menu', array(
                'label'         => $control_label,
                'description'   => $control_description,
                'section'       => 'ofts_footer',
                'settings'      => 'coloured_social_links_menu',
                'type'          => 'checkbox'
            ));

} else {

		    $wp_customize->add_control(new ofts_WP_Customize_Notice_Control($wp_customize, 'coloured_social_links_menu', array(
                'label'         => $control_label,
                'description'   => $control_description . ' ' . $upgrade_nag,
                'section'       => 'ofts_footer',
				'settings'      => array()
			)));

}

            $wp_customize->add_setting('remove_powered_by_wordpress', array(
                'default'       => false,
                'type'          => 'theme_mod',
                'transport'     => 'refresh',
                'sanitize_callback' => array($this, 'ofts_sanitize_boolean')
            ));
            $wp_customize->add_control('remove_powered_by_wordpress', array(
                'label'         => __('Remove Powered by WordPress', 'options-for-twenty-seventeen'),
                'description'   => __('Removes the "Proudly powered by WordPress" text displayed in the website footer and replaces with the content of the "Site Info" widget area.', 'options-for-twenty-seventeen'),
                'section'       => 'ofts_footer',
                'settings'      => 'remove_powered_by_wordpress',
                'type'          => 'checkbox'
            ));

        }

        function ofts_sanitize_boolean($input) {

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

        function ofts_sanitize_integer($input) {

            return absint($input);

        }

        function ofts_sanitize_integer_options($input, $setting) {

            $input =  absint($input);
            $choices = $setting->manager->get_control($setting->id)->choices;

            return (array_key_exists($input, $choices) ? $input : $setting->default);

        }

        function ofts_sanitize_slug_options($input, $setting) {

            $input =  sanitize_key($input);
            $choices = $setting->manager->get_control($setting->id)->choices;

            return (array_key_exists($input, $choices) ? $input : $setting->default);

        }

        function ofts_sanitize_text_options($input, $setting) {

            $choices = $setting->manager->get_control($setting->id)->choices;

            return (array_key_exists($input, $choices) ? $input : $setting->default);

        }

        function ofts_has_header_image_or_nivo_slider() {
        	return (has_custom_header() || get_theme_mod('nivo_slider_cover'));
        }

        function ofts_has_no_header_image_or_nivo_slider() {
        	return (!has_custom_header() && !get_theme_mod('nivo_slider_cover'));
        }

        function ofts_has_youtube_video() {
        	return (!get_theme_mod('external_header_video') == false);
        }

        function ofts_header_output() {

?>
<!--Customizer CSS--> 
<style type="text/css">
.admin-bar :target:before
{
   height: 117px;
   margin-top: -117px;
}
.single-post:not(.has-sidebar) #primary,
.page.page-one-column:not(.twentyseventeen-front-page) #primary,
.archive.page-one-column:not(.has-sidebar) .page-header,
.archive.page-one-column:not(.has-sidebar) #primary {
    max-width: none;
}
<?php

if ($this->ofts_request_permission('1.8.4', true) == true) {

            if ((is_page() || is_single()) && get_post_meta(get_the_ID(), 'ofts_hide_title', true) == '1') {

?>
#main article:first-of-type .entry-title {
    display: none;
}
<?php

            }

}

            if (get_theme_mod('external_header_video')) {

                add_action('wp_footer', array($this, 'ofts_disable_youtube_on_ie11'));

if ($this->ofts_request_permission('1.5.0', true) == true) {

                if (get_theme_mod('hide_youtube_until_loaded')) {

                    add_action('wp_footer', array($this, 'ofts_hide_youtube_until_loaded'));

                }

                if (get_theme_mod('pause_youtube_on_scroll')) {

                    add_action('wp_footer', array($this, 'ofts_pause_youtube_on_scroll'));

                }

}

?>
.has-header-video .custom-header-media iframe {
    -o-object-fit: fill;
    object-fit: fill;
    top: auto;
    -ms-transform: none;
    -moz-transform: none;
    -webkit-transform: none;
    transform: none;
	left: auto;
}
@media (min-aspect-ratio: 16/9) {
    #wp-custom-header > iframe {
        height: 300%;
        top: -100%;
    }
}
@media (max-aspect-ratio: 16/9) {
    #wp-custom-header > iframe {
        width: 300%;
        left: -100%;
    }
}
.wp-custom-header .wp-custom-header-video-button {
    background-color: #a1a1a1;
    border-radius: 0;
    transition: none;
    color: white;
    border: 1px solid white;
}
.wp-custom-header .wp-custom-header-video-button:hover,
.wp-custom-header .wp-custom-header-video-button:focus {
    border-color: white;
    background-color: #555555;
    color: white;
}
<?php
            }

            if (get_theme_mod('footer_back_to_top') || get_option('show_on_front') == 'page') {

                add_action('wp_footer', array($this, 'ofts_inject_smooth_scrolling'));

            }

if ($this->ofts_request_permission('1.3.3', true) == true) {

            if (get_theme_mod('search_archive_page_layout') == 'one-column') {
?>
@media screen and (min-width: 48em) {
    body:not(.has-sidebar):not(.page-one-column) .page-header, body.has-sidebar.error404 #primary .page-header, body.page-two-column:not(.archive) #primary .entry-header, body.page-two-column.archive:not(.has-sidebar) #primary .page-header {
        float: none;
        width: 100%;
    }
    .blog:not(.has-sidebar) #primary article, .archive:not(.page-one-column):not(.has-sidebar) #primary article, .search:not(.has-sidebar) #primary article, .error404:not(.has-sidebar) #primary .page-content, .error404.has-sidebar #primary .page-content, body.page-two-column:not(.archive) #primary .entry-content, body.page-two-column #comments {
        float: none;
        width: 100%;
    }
}
<?php
            }

}

if ($this->ofts_request_permission('1.7.3', true) == true) {

            $mod = get_theme_mod('panel_image_height');

            if ($mod) {
?>
@media screen and (min-width: 48em) {
    .panel-image {
        height: <?= absint($mod) - 1; ?>vh;
    }
}
<?php
            }

}

if ($this->ofts_request_permission('1.5.1', true) == true) {

            if (get_theme_mod('front_page_section_blog_thumbnails')) {

                add_action('get_template_part_template-parts/post/content', array($this, 'ofts_inject_excerpt_post_thumbnails'), 10, 2);

            }

}

            if (get_theme_mod('front_page_sections_parallax_off')) {

                set_theme_mod('front_page_sections_parallax', 'off');
                remove_theme_mod('front_page_sections_parallax_off');

            }

if ($this->ofts_request_permission('1.3.3', true) == true) {

            $mod = get_theme_mod('front_page_sections_parallax');

            if ($mod == 'off') {
?>
@media screen and (min-width: 48em) {
    .background-fixed .panel-image {
        background-attachment: scroll;
    }
    .panel-image {
        height: auto;
    }
}
.panel-image:before {
    background: none;
}
<?php
            } elseif ($mod == 'true') {

                add_action('get_footer', array($this, 'ofts_inject_true_parallax'));

            }

}

if ($this->ofts_request_permission('1.3.3', true) == true) {

            if (get_theme_mod('footer_back_to_top')) {

                add_action('get_footer', array($this, 'ofts_inject_footer_back_to_top'));

if ($this->ofts_request_permission('1.5.1', true) == true) {

                if (get_theme_mod('fix_footer_back_to_top')) {
?>
.back-to-top-footer a {
	position: fixed;
	background: #767676;
	bottom: 2em;
	right: 2em;
	padding: 0.5em 0.75em;
	margin-bottom: 0;
	border-radius: 50%;
}

.back-to-top-footer svg {
	color: white;
}

.back-to-top-footer a:hover {
	background: #333;
}
<?php
                }

}

            }

}

if ($this->ofts_request_permission('1.3.1', true) == true) {

            if (get_theme_mod('front_page_sections_back_to_top')) {

                add_action('get_template_part_template-parts/page/content', array($this, 'ofts_inject_front_page_sections_to_back_to_top'), 10, 2);

            }

}

            if (get_theme_mod('footer_back_to_top') || get_theme_mod('front_page_sections_back_to_top')) {
?>
.back-to-top {
	text-align: right;
}
.back-to-top a {
	-webkit-box-shadow: none;
	box-shadow: none;
}
.back-to-top a:hover {
	-webkit-box-shadow: none;
	box-shadow: none;
}
.back-to-top .icon {
	-webkit-transform: rotate(-90deg);
	-ms-transform: rotate(-90deg);
	transform: rotate(-90deg);
}
<?php
            }

            if (get_theme_mod('page_max_width') == '1200px') {

                set_theme_mod('page_max_width', '75em');

            }

            $this->ofts_generate_css('#page', 'max-width', 'page_max_width');

            if (get_theme_mod('full_cover_image') && get_theme_mod('page_max_width')) {

                $this->ofts_generate_css('.has-header-image .custom-header-media img, .has-header-video .custom-header-media video, .has-header-video .custom-header-media iframe', 'min-width', 'page_max_width', '', '', '0');

            }

if ($this->ofts_request_permission('1.7.6', true) == true) {

            if (get_theme_mod('true_parallax_cover_image')) {

                add_action('get_footer', array($this, 'ofts_inject_header_true_parallax'));
?>
.has-header-image .custom-header-media img {
	object-position: 50% 0;
}
.admin-bar.has-header-image .custom-header-media img {
	object-position: 50% 16px;
}
<?php
            }

}

            $this->ofts_generate_css('#page', 'margin', 'page_max_width', '', '' ,'0 auto');

            if ($this->ofts_has_no_header_image_or_nivo_slider()) {

                $this->ofts_generate_css('#page', 'border-width', 'page_border_width', '', 'px');
                $this->ofts_generate_css('#page', 'border-color', 'page_border_color');
                $this->ofts_generate_css('#page', 'border-style', 'page_border_style');
                $this->ofts_generate_css('#page', 'border-width', 'page_border_location');

                $mod = get_theme_mod('page_border_location');

                if ($mod) {
?>
#page {
    <?= $mod; ?>
}
<?php
                }

            }

            if (get_theme_mod('remove_link_underlines')) {
?>
.screen-reader-text:focus {
	box-shadow: none;
}
.entry-content a,
.entry-summary a,
.comment-content a,
.widget a,
.site-footer .widget-area a,
.posts-navigation a,
.widget_authors a strong {
	box-shadow: none;
}
.colors-dark .entry-content a,
.colors-dark .entry-summary a,
.colors-dark .comment-content a,
.colors-dark .widget a,
.colors-dark .site-footer .widget-area a,
.colors-dark .posts-navigation a,
.colors-dark .widget_authors a strong {
	box-shadow: none;
}
.entry-title a,
.entry-meta a,
.page-links a,
.page-links a .page-number,
.entry-footer a,
.entry-footer .cat-links a,
.entry-footer .tags-links a,
.edit-link a,
.post-navigation a,
.logged-in-as a,
.comment-navigation a,
.comment-metadata a,
.comment-metadata a.comment-edit-link,
.comment-reply-link,
a .nav-title,
.pagination a,
.comments-pagination a,
.site-info a,
.widget .widget-title a,
.widget ul li a,
.site-footer .widget-area ul li a,
.site-footer .widget-area ul li a {
	box-shadow: none;
}
.entry-content a:focus,
.entry-content a:hover,
.entry-summary a:focus,
.entry-summary a:hover,
.comment-content a:focus,
.comment-content a:hover,
.widget a:focus,
.widget a:hover,
.site-footer .widget-area a:focus,
.site-footer .widget-area a:hover,
.posts-navigation a:focus,
.posts-navigation a:hover,
.comment-metadata a:focus,
.comment-metadata a:hover,
.comment-metadata a.comment-edit-link:focus,
.comment-metadata a.comment-edit-link:hover,
.comment-reply-link:focus,
.comment-reply-link:hover,
.widget_authors a:focus strong,
.widget_authors a:hover strong,
.entry-title a:focus,
.entry-title a:hover,
.entry-meta a:focus,
.entry-meta a:hover,
.page-links a:focus .page-number,
.page-links a:hover .page-number,
.entry-footer a:focus,
.entry-footer a:hover,
.entry-footer .cat-links a:focus,
.entry-footer .cat-links a:hover,
.entry-footer .tags-links a:focus,
.entry-footer .tags-links a:hover,
.post-navigation a:focus,
.post-navigation a:hover,
.pagination a:not(.prev):not(.next):focus,
.pagination a:not(.prev):not(.next):hover,
.comments-pagination a:not(.prev):not(.next):focus,
.comments-pagination a:not(.prev):not(.next):hover,
.logged-in-as a:focus,
.logged-in-as a:hover,
a:focus .nav-title,
a:hover .nav-title,
.edit-link a:focus,
.edit-link a:hover,
.site-info a:focus,
.site-info a:hover,
.widget .widget-title a:focus,
.widget .widget-title a:hover,
.widget ul li a:focus,
.widget ul li a:hover {
	box-shadow: none;
}
.entry-content a img,
.comment-content a img,
.widget a img {
	box-shadow: none;
}
<?php
            }

if ($this->ofts_request_permission('1.2.2', true) == true) {

            if (get_theme_mod('auto_excerpt')) {

                add_filter('the_content', array($this, 'ofts_auto_excerpt'));

            }

            if (get_theme_mod('reset_tag_cloud')) {

                add_filter('widget_tag_cloud_args', array($this, 'ofts_reset_tag_cloud_args'), 11);
?>
.tagcloud,
.widget_tag_cloud,
.wp_widget_tag_cloud {
	line-height: 1.6;
}

.widget .tagcloud a,
.widget.widget_tag_cloud a,
.wp_widget_tag_cloud a {
	border: none;<?php if (!get_theme_mod('remove_link_underlines')) { ?>
	-webkit-box-shadow: inset 0 -1px 0 rgba(15, 15, 15, 1);
	box-shadow: inset 0 -1px 0 rgba(15, 15, 15, 1);<?php } ?>
	-webkit-transition: color 80ms ease-in, -webkit-box-shadow 130ms ease-in-out;
	transition: color 80ms ease-in, -webkit-box-shadow 130ms ease-in-out;
	transition: color 80ms ease-in, box-shadow 130ms ease-in-out;
	transition: color 80ms ease-in, box-shadow 130ms ease-in-out, -webkit-box-shadow 130ms ease-in-out;
	display: inline;
	padding: 0;
	position: static;
	z-index: auto;
}

.widget .tagcloud a:hover,
.widget .tagcloud a:focus,
.widget.widget_tag_cloud a:hover,
.widget.widget_tag_cloud a:focus,
.wp_widget_tag_cloud a:hover,
.wp_widget_tag_cloud a:focus {
	border: none;<?php if (!get_theme_mod('remove_link_underlines')) { ?>
	-webkit-box-shadow: inset 0 0 0 rgba(0, 0, 0, 0), 0 3px 0 rgba(0, 0, 0, 1);
	box-shadow: inset 0 0 0 rgba(0, 0, 0, 0), 0 3px 0 rgba(0, 0, 0, 1);<?php } ?>
}
<?php

            }

}

            $this->ofts_generate_css('.custom-header .wrap, .header-sidebar-wrap', 'max-width', 'header_width');

if ($this->ofts_request_permission('1.6.0', true) == true) {

            if (get_theme_mod('featured_header_image') && !is_front_page()) {

                add_action('get_template_part_template-parts/page/content', array($this, 'ofts_featured_header_image'));
                add_action('get_template_part_template-parts/post/content', array($this, 'ofts_featured_header_image'));

                if (has_custom_header()) {
?>
@media screen and (min-width: 48em) {
    .has-header-image .custom-header {
        display: block;
        height: auto;
        margin-bottom: 0 !important;
    }
    .has-header-image:not(.twentyseventeen-front-page):not(.home) .custom-header-media {
        height: 1200px;
        height: 100vh;
        max-height: 100%;
        overflow: hidden;
        bottom: auto;
        left: auto;
        position: relative;
        right: auto;
        top: auto;
    }
    .admin-bar.has-header-image:not(.twentyseventeen-front-page):not(.home) .custom-header-media {
        height: calc(100vh - 32px);
    }
    .has-header-image:not(.twentyseventeen-front-page):not(.home) .custom-header-media img {
        position: fixed;
        bottom: auto;
    }
    .has-header-image .site-branding {
        bottom: 0;
        display: block;
        left: 0;
        height: auto;
        padding-top: 0;
        position: absolute;
        width: 100%;
        margin-bottom: 83px;
    }
    body.title-tagline-hidden.has-header-image .custom-logo-link img {
        max-height: 200px;
    }
}
<?php
                }

            }

}

if ($this->ofts_request_permission('1.1.1', true) == true) {

            if (get_theme_mod('header_sidebar')) {

                add_action('get_template_part_template-parts/header/header', array($this, 'ofts_get_header_sidebar'));

                if (has_custom_header()) {
?>
#header-sidebar {
    padding: 0 3em;
    position: absolute;
    z-index: 6;
    color: white;
}
#header-sidebar.top-left {
    top: 0;
    left: 0;
}
#header-sidebar.top-center {
    top: 0;
    left: 0;
    right: 0;
    text-align: center;
}
#header-sidebar.top-right {
    top: 0;
    right: 0;
}
#header-sidebar h2 {
    color: white;
}
<?php
                }

            }

}

            $this->ofts_generate_css('.wp-custom-header-video-button', 'display', 'remove_header_video_button', '', '', 'none');
            $this->ofts_generate_css('.site-branding', 'background-color', 'site_identity_background_color');
            $this->ofts_generate_css('.site-branding a:hover, .site-branding a:focus', 'opacity', 'remove_link_hover_opacity');

            if ($this->ofts_has_header_image_or_nivo_slider()) {

                if (get_theme_mod('full_cover_image') && !get_theme_mod('external_header_video')) {

?>
.twentyseventeen-front-page.has-header-image .custom-header-media, .admin-bar.home.blog.has-header-image .custom-header-media, .admin-bar.twentyseventeen-front-page.has-header-image .custom-header-media, .has-header-image .custom-header-media img, .has-header-image.home.blog .custom-header, .has-header-image.twentyseventeen-front-page .custom-header, .has-header-image .custom-header-media img, .has-header-image .custom-header-media, .has-header-video .custom-header-media iframe, .has-header-video .custom-header-media video, .has-header-video.home.blog .custom-header, .has-header-video.twentyseventeen-front-page .custom-header, .has-header-video .custom-header-media .has-header-image.twentyseventeen-front-page .custom-header {
	position: static;
	height: auto;
}
.has-header-image.twentyseventeen-front-page .site-branding, .has-header-video.twentyseventeen-front-page .site-branding, .has-header-image.home.blog .site-branding, .has-header-video.home.blog .site-branding {
	position: static;
	padding: 3em 0;
	display: block;
}
body.has-header-image .site-title, body.has-header-video .site-title, body.has-header-image .site-title a, body.has-header-video .site-title a, .site-title a, .colors-dark .site-title a, .colors-custom .site-title a, body.has-header-image .site-title a, body.has-header-video .site-title a, body.has-header-image.colors-dark .site-title a, body.has-header-video.colors-dark .site-title a, body.has-header-image.colors-custom .site-title a, body.has-header-video.colors-custom .site-title a, .colors-dark .site-title, .colors-dark .site-title a {
	color: #222;
}
.site-description, .colors-dark .site-description, body.has-header-image .site-description, body.has-header-video .site-description, .site-description, .colors-dark .site-description, .colors-custom .site-description, body.has-header-image .site-description, body.has-header-video .site-description, body.has-header-image.colors-dark .site-description, body.has-header-video.colors-dark .site-description, body.has-header-image.colors-custom .site-description, body.has-header-video.colors-custom .site-description {
	color: #666;
}
.navigation-top {
    z-index: 7;
}
<?php

                    if (get_theme_mod('header_textcolor') == 'blank') {

?>
.has-header-image.twentyseventeen-front-page .site-branding, .has-header-video.twentyseventeen-front-page .site-branding, .has-header-image.home.blog .site-branding, .has-header-video.home.blog .site-branding {
    padding: 0;
}
<?php

                    }

                }

            }

            $this->ofts_generate_css('.site-title', 'text-align', 'site_title_text_align');
            $this->ofts_generate_css('body:not(.title-tagline-hidden) .site-branding-text', 'display', 'site_title_text_align', '', '', 'block');
            $this->ofts_generate_css('.site-title', 'text-transform', 'site_title_text_transform');
            $this->ofts_generate_css('.site-title', 'letter-spacing', 'remove_site_title_letter_spacing', '', '', 'normal');

            $mod = get_theme_mod('site_title_font_size');

            if ($mod) {
?>
.site-title {
    font-size: <?= $mod / 3000 * 2; ?>rem;
}
@media screen and (min-width: 48em) {
    .site-title {
        font-size: <?= $mod / 1000; ?>rem;
    }
}
<?php
            }

            $this->ofts_generate_css('.site-title', 'font-size', 'site_title_font_size', '', 'rem', get_theme_mod('site_title_font_size') / 1000);
            $this->ofts_generate_css('.site-title', 'font-weight', 'site_title_font_weight');
            $this->ofts_generate_css('body.has-header-image .site-title, body.has-header-video .site-title, body.has-header-image .site-title a, body.has-header-video .site-title a, .site-title a, .colors-dark .site-title a, .colors-custom .site-title a, body.has-header-image .site-title a, body.has-header-video .site-title a, body.has-header-image.colors-dark .site-title a, body.has-header-video.colors-dark .site-title a, body.has-header-image.colors-custom .site-title a, body.has-header-video.colors-custom .site-title a, .colors-dark .site-title, .colors-dark .site-title a', 'color', 'site_title_color');
            $this->ofts_generate_css('.site-description', 'text-align', 'site_description_text_align');
            $this->ofts_generate_css('.site-description', 'text-transform', 'site_description_text_transform');

            $mod = get_theme_mod('site_description_font_size');

            if ($mod) {
?>
.site-description {
    font-size: <?= $mod * 0.0008125; ?>rem;
}
@media screen and (min-width: 48em) {
    .site-description {
        font-size: <?= $mod / 1000; ?>rem;
    }
}
<?php
            }

            $this->ofts_generate_css('.site-description', 'font-weight', 'site_description_font_weight');
            $this->ofts_generate_css('.site-description, .colors-dark .site-description, body.has-header-image .site-description, body.has-header-video .site-description, .site-description, .colors-dark .site-description, .colors-custom .site-description, body.has-header-image .site-description, body.has-header-video .site-description, body.has-header-image.colors-dark .site-description, body.has-header-video.colors-dark .site-description, body.has-header-image.colors-custom .site-description, body.has-header-video.colors-custom .site-description', 'color', 'site_description_color');
            $this->ofts_generate_css('.custom-header-media:before', 'display', 'remove_header_gradient', '', '', 'none');
            $this->ofts_generate_css('.site-header', 'background', 'remove_header_background', '', '', 'none');

if ($this->ofts_request_permission('1.1', true) == true) {

            if (get_theme_mod('nivo_slider_cover') && is_front_page()) {

                add_action('get_template_part_template-parts/header/site', array($this, 'ofts_nivo_slider'));
                add_filter('body_class', array($this, 'ofts_header_image_body_class'));

                $slider_post_type = get_post_type(get_theme_mod('nivo_slider_cover'));

                if ($slider_post_type == 'nivoslider') {

?>
.custom-header-media:nth-of-type(1) {
    display: none;
}
.has-header-image .custom-header-media img, .has-header-video .custom-header-media video, .has-header-video .custom-header-media iframe {
    position: absolute;
    padding: 0;
}
.has-header-image .custom-header, .has-header-video .custom-header, .has-header-video .custom-header {
    position: relative;
}
.site-branding, .navigation-top, .custom-header-media:before {
    z-index: 6;
}
.has-header-image.twentyseventeen-front-page .custom-header, .has-header-video.twentyseventeen-front-page .custom-header, .has-header-image.home.blog .custom-header, .has-header-video.home.blog .custom-header {
	height: calc(100vw / 2000 * 1200 );
	max-height: 100vh;
}
/* IE11 fix */
.has-header-image .custom-header-media img, .has-header-image:not(.twentyseventeen-front-page):not(.home) .custom-header-media img {
    left: 0;
    transform: none;
}
<?php

                    if (get_theme_mod('enable_nivo_captions')) {

?>
header .nivo-caption {
    padding: 0;
    bottom: auto;
    background: none;
    top: 0.5rem;
    left: 2rem;
    right: 2rem;
    opacity: 1;
	position: absolute;
 	font-size: 1.5rem;
	text-transform: uppercase;
	font-weight: 800;
}
header .nivo-caption a {
	color: white;
	display: block !important;
}
@media screen and (min-width: 48em) {
	header .nivo-caption {
		font-size: 2.25rem;
        left: 3rem;
        right: 3rem;
	}
}
<?php

                    }

                } elseif ($slider_post_type == 'wpspaios_slider') {

?>
.custom-header-media:nth-of-type(1) {
    display: none;
}
.nivoSlider.wp-spaios-nivoslider-container {
    max-width: none !important;
}
.site-branding, .navigation-top {
    z-index: 10;
}
.has-header-image .custom-header-media img, .has-header-video .custom-header-media video, .has-header-video .custom-header-media iframe {
    position: static;
}
.has-header-image .custom-header-media img, .has-header-video .custom-header-media video, .has-header-image:not(.twentyseventeen-front-page):not(.home) .custom-header-media img {
    height: 100vh !important;
}
<?php

                }

            }

}

            if (get_theme_mod('animate_nav_logo') == '1') {

                set_theme_mod('animate_nav_logo', 'all');

            }

if ($this->ofts_request_permission('1.4.3', true) == true) {

            if (get_theme_mod('add_logo_to_nav')) {

                add_action('get_template_part_template-parts/navigation/navigation', array($this, 'ofts_add_logo_to_nav'));

                $position = get_theme_mod('nav_logo_align');

                if (!$position) {

                    if (get_theme_mod('nav_text_align')) {

                        $position = 'left';

                    } else {

                        $position = 'right';

                    }

                }

?>
#site-navigation {
    position: relative;
}
.navigation-top .custom-logo-link img, body.home.title-tagline-hidden.has-header-image .navigation-top .custom-logo-link img, body.home.title-tagline-hidden.has-header-video .navigation-top .custom-logo-link img {
	<?php if (((get_theme_mod('animate_nav_logo') == 'home' && is_front_page()) || get_theme_mod('animate_nav_logo') == 'all') && $position != 'center') { ?>position: absolute<?php } elseif (!((get_theme_mod('animate_nav_logo') == 'home' && is_front_page()) || get_theme_mod('animate_nav_logo') == 'all') && $position != 'center') { ?>float: <?php if ($position == 'left') { ?>left<?php } else { ?>right<?php } } ?>;
	<?php if ($position == 'right' && ((get_theme_mod('animate_nav_logo') == 'home' && is_front_page()) || get_theme_mod('animate_nav_logo') == 'all')) { ?>right: 0;
<?php } ?>	max-height: <?php if ((get_theme_mod('animate_nav_logo') == 'home' && is_front_page()) || get_theme_mod('animate_nav_logo') == 'all') { ?>none<?php } else { ?>49px<?php } ?>;
}
<?php if ($position != 'center') { ?>.navigation-top .custom-logo-link {
	padding: 0;
}<?php } ?>
@media screen and (max-width: 48em) {
	.navigation-top .custom-logo-link {
		display: none;
	}
}
<?php

            }

}

if ($this->ofts_request_permission('1.3.9', true) == true) {

            if (get_theme_mod('move_nav_bar_to_top')) {

                add_action('wp_footer', array($this, 'ofts_check_nav_height'));

?>
@media screen and (min-width: 48em) {
	.navigation-top {
		position: fixed;
		bottom: auto;
		top: 0;
	}
	.admin-bar .navigation-top {
		top: 32px;
	}
	.custom-header {
		margin-top: 72px;
		margin-bottom: 0 !important;
	}
	.has-header-image .custom-header-media img {
		padding-bottom: 0;
	}
}
<?php

            }

}

if ($this->ofts_request_permission('1.8.2', true) == true) {

            if (get_theme_mod('fix_mobile_nav_bar_to_top')) {

?>
@media screen and (max-width: 767px) {
    .navigation-top {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 7;
    }
}
<?php

            }

}

            if (get_theme_mod('full_width_nav_bar')) {

                set_theme_mod('nav_bar_width', '100%');
                remove_theme_mod('full_width_nav_bar');

            }

            $this->ofts_generate_css('.navigation-top .wrap', 'max-width', 'nav_bar_width');

if ($this->ofts_request_permission('1.8.7', true) == true) {

            if (get_theme_mod('nav_responsive_breakpoint')) {

?>
@media screen and (min-width: 48em) {
	.js .menu-toggle {
		display: block;
	}
	.js .main-navigation > div > ul {
		display: none;
	}
    .navigation-top {
        position: <?php get_theme_mod('move_nav_bar_to_top') ? 'fixed' : 'static'; ?>;
    }
}
@media screen and (min-width: <?= get_theme_mod('nav_responsive_breakpoint') + 48; ?>em) {
	.js .menu-toggle,
	.js .dropdown-toggle {
		display: none;
	}
	.js .main-navigation > div > ul {
		display: block;
	}
    .navigation-top {
        position: <?php get_theme_mod('move_nav_bar_to_top') ? 'fixed' : 'absolute'; ?>;
    }
}
<?php

            }

}

            if (get_theme_mod('nav_remove_padding_vertical')) {

?>
@media screen and (min-width: 48em) {
	.navigation-top .wrap {
		padding-top: 0;
		padding-bottom: 0;
	}
}
<?php

            }

if ($this->ofts_request_permission('1.1.6', true) == true) {

            $mod = get_theme_mod('nav_text_align');

            if ($mod) {
?>
@media screen and (min-width: 48em) {
    .main-navigation>div>ul {
        text-align: <?= $mod; ?>;
    }
}
<?php
                if ($mod == 'right') {
?>
.main-navigation ul ul li:hover > ul,
.main-navigation ul ul li.focus > ul {
	right: 100%;
	left: auto;
}
@media screen and (min-width: 48em) {
    .main-navigation ul ul .menu-item-has-children > a > .icon, .main-navigation ul ul .page_item_has_children > a > .icon {
        transform: rotate(90deg);
    }
}
<?php

                }

            }

}

            $this->ofts_generate_css('.navigation-top a', 'text-transform', 'navigation_text_transform');

            $mod = get_theme_mod('navigation_font_size');

            if ($mod) {

?>
.navigation-top {
    font-size: <?= ($mod + 125) / 1000; ?>rem;
}
@media screen and (min-width: 48em) {
    .navigation-top {
        font-size: <?= $mod / 1000; ?>rem;
    }
}
<?php

            }

            $this->ofts_generate_css('.navigation-top a', 'font-weight', 'navigation_font_weight');

            $mod = get_theme_mod('nav_link_padding_vertical');

            if ($mod) {

?>
@media screen and (min-width: 48em) {
	.main-navigation a {
		padding-top: <?= $mod; ?>px;
		padding-bottom: <?= $mod; ?>px;
	}
}
<?php

            }

            $this->ofts_generate_css('.navigation-top a, .colors-dark .navigation-top a', 'color', 'nav_link_color', '', '');
            $this->ofts_generate_css('.menu-toggle, .colors-dark .menu-toggle', 'color', 'nav_link_color', '', '');
            $this->ofts_generate_css('.navigation-top .current-menu-item > a, .navigation-top .current_page_item > a, .colors-dark .navigation-top .current-menu-item > a, .colors-dark .navigation-top .current_page_item > a', 'color', 'nav_current_link_color', '', '');
            $this->ofts_generate_css('.navigation-top a:hover, .main-navigation li li.focus > a, .main-navigation li li:focus > a, .main-navigation li li:hover > a, .main-navigation li li a:hover, .main-navigation li li a:focus, .main-navigation li li.current_page_item a:hover, .main-navigation li li.current-menu-item a:hover, .main-navigation li li.current_page_item a:focus, .main-navigation li li.current-menu-item a:focus, .colors-dark .navigation-top a:hover, .colors-dark .main-navigation li li.focus > a, .colors-dark .main-navigation li li:focus > a, .colors-dark .main-navigation li li:hover > a, .colors-dark .main-navigation li li a:hover, .colors-dark .main-navigation li li a:focus, .colors-dark .main-navigation li li.current_page_item a:hover, .colors-dark .main-navigation li li.current-menu-item a:hover, .colors-dark .main-navigation li li.current_page_item a:focus, .colors-dark .main-navigation li li.current-menu-item a:focus', 'color', 'nav_link_hover_color', '', '');

            $mod = get_theme_mod('nav_link_hover_background_color');

            if ($mod) {

?>
@media screen and (min-width: 48em) {
	.main-navigation li, .colors-dark .main-navigation li {
		-webkit-transition: background-color 0.2s ease-in-out;
		transition: background-color 0.2s ease-in-out;
	}
    .main-navigation li:hover, .main-navigation li.focus, .main-navigation li li:hover, .main-navigation li li.focus, .colors-dark .main-navigation li:hover, .colors-dark .main-navigation li.focus, .colors-dark .main-navigation li li:hover, .colors-dark .main-navigation li li.focus {
        background-color: <?= $mod; ?>;
    }
}
<?php

            }

            $mod = get_theme_mod('nav_background_color');

            if ($mod) {

                $this->ofts_generate_css('.navigation-top, .main-navigation ul, .colors-dark .navigation-top, .colors-dark .main-navigation ul, .colors-custom .navigation-top, .colors-custom .main-navigation ul', 'background-color', 'nav_background_color', '', '');

?>
@media screen and (min-width: 48em) {
    .main-navigation ul ul, .colors-dark .main-navigation ul ul {
        background-color: <?= $mod; ?>;
    }
}
.navigation-top {
    border: none;
}
.menu-toggle {
	margin: 0 auto 0;
}
<?php

            }

            $mod = get_theme_mod('sub_menu_background_color');

            if ($mod) {
?>
@media screen and (min-width: 48em) {
	.main-navigation ul ul {
		border: none;
		background-color: <?= $mod; ?>;
	}
	.main-navigation ul li:hover > ul,
	.main-navigation ul li.focus > ul {
		left: 0;
	}
}
<?php

            }

            if (get_theme_mod('rotate_sub_menu_arrow')) {

?>
@media screen and (min-width: 48em) {
    .main-navigation ul li.menu-item-has-children:before,
	.main-navigation ul li.page_item_has_children:before,
	.main-navigation ul li.menu-item-has-children:after,
	.main-navigation ul li.page_item_has_children:after {
        transform: rotate(180deg);
		bottom: -7px;
	}
}
<?php

            }

            if (get_theme_mod('remove_nav_scroll_arrow')) {

?>
@media screen and (min-width: 48em) {
	.site-header .menu-scroll-down {
		display: none;
	}
}
<?php

            }

            if (get_theme_mod('full_width_content')) {

                set_theme_mod('header_width', '100%');
                set_theme_mod('content_width', '100%');
                set_theme_mod('footer_width', '100%');
                remove_theme_mod('full_width_content');

            }

            $this->ofts_generate_css('#content .wrap', 'max-width', 'content_width');

            $mod = get_theme_mod('content_width');

            if ($mod) {

?>
@media screen and (min-width: 30em) {
    .page-one-column .panel-content .wrap {
        max-width: <?= $mod; ?>;
    }
}
<?php
            }

            $mod = get_theme_mod('page_sidebar');

            if ($mod) {

                add_action('get_template_part_template-parts/page/content', array($this, 'ofts_get_blog_sidebar'), 10, 2);
                add_filter('body_class', array($this, 'ofts_sidebar_body_class'));
?>
.twentyseventeen-front-page .site-content  {
    padding: 2.5em 0 0;
}
@media screen and (min-width: 48em) {
    .twentyseventeen-front-page .site-content  {
        padding: 5.5em 0 0;
	}
}
.twentyseventeen-front-page article:not(.has-post-thumbnail):not(:first-child) {
    border: none;
}
.panel-content {
    padding-top: 6em;
}
#main > [id^=post] .panel-content {
    padding-top: 0;
}
<?php
            }

if ($this->ofts_request_permission('1.4.2', true) == true) {

            if (get_theme_mod('hide_blog_sidebar')) {

?>
@media screen and (max-width: 48em) {
	#secondary {
		display: none;
	}
}
<?php
            }

}

if ($this->ofts_request_permission('1.1.7', true) == true) {

            if (get_theme_mod('match_primary_secondary_height')) {

                add_action('get_sidebar', array($this, 'ofts_match_primary_secondary_height'));

            }

}

if ($this->ofts_request_permission('1.1.8', true) == true) {

            $primary_width = get_theme_mod('primary_width');
            $content_gutter = get_theme_mod('content_gutter');

            if ($primary_width || $content_gutter) {

                if (!$primary_width) { $primary_width = 58; }
                if (!$content_gutter) { $content_gutter = 6; }

?>
@media screen and (min-width: 48em) {
    .has-sidebar:not(.error404) #primary {
        width: <?= $primary_width; ?>%;
    }
    .has-sidebar #secondary {
        width: <?= 100 - $primary_width - $content_gutter + 1; ?>%;
    }
}
<?php

            }

            if (get_theme_mod('swap_content')) {

?>
@media screen and (min-width: 48em) {
    .has-sidebar:not(.error404) #primary {
        float: right;
    }
    .has-sidebar #secondary {
        float: left;
    }
}
<?php

            }

}

if ($this->ofts_request_permission('1.7.2', true) == true) {

            if (get_theme_mod('implement_yoast_breadcrumbs') && !is_front_page() && (is_single() || is_page())) {

                add_action('loop_start', array($this, 'ofts_implement_yoast_breadcrumbs'));

            }

}

if ($this->ofts_request_permission('1.6.4', true) == true) {

            if (get_theme_mod('inject_featured_image_caption')) {

                add_filter('post_thumbnail_html', array($this, 'ofts_inject_featured_image_caption'), 10, 5);

            }
?>
.post-thumbnail a img {
    display: block;
}

.post-thumbnail,
.single-featured-image-header {
	position: relative;
}

.post-thumbnail>a>div,
.single-featured-image-header>div {
	position: absolute;
	bottom: 1rem;
	left: 0;
	right: 0;
	z-index: 1;
}

.post-thumbnail>a>div>div,
.single-featured-image-header>div>div {
	font-size: 1.5rem;
	font-weight: 800;
	color: white;
	text-transform: uppercase;
}

@media screen and (min-width: 30em) {
    .post-thumbnail>a>div,
	.single-featured-image-header>div {
		bottom: 3rem;
	}
}

@media screen and (min-width: 48em) {
    .post-thumbnail>a>div>div,
	.single-featured-image-header>div>div {
		font-size: 2.25rem;
	}
}

.post-thumbnail:after,
.single-featured-image-header:after {
	/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#000000+0,000000+100&0+0,0.3+75 */
	background: -moz-linear-gradient(to top, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.3) 75%, rgba(0, 0, 0, 0.3) 100%); /* FF3.6-15 */
	background: -webkit-linear-gradient(to top, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.3) 75%, rgba(0, 0, 0, 0.3) 100%); /* Chrome10-25,Safari5.1-6 */
	background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.3) 75%, rgba(0, 0, 0, 0.3) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#00000000", endColorstr="#4d000000", GradientType=0); /* IE6-9 */
	bottom: 0;
	content: "";
	display: block;
	height: 100%;
	left: 0;
	position: absolute;
	right: 0;
}
<?php
}

if ($this->ofts_request_permission('1.8.1', true) == true) {

            $this->ofts_generate_css('.post-thumbnail>a>div>div, .single-featured-image-header>div>div', 'font-size', 'featured_image_caption_font_size', '', 'rem', get_theme_mod('featured_image_caption_font_size') / 1000);
            $this->ofts_generate_css('.post-thumbnail>a>div>div, .single-featured-image-header>div>div', 'font-weight', 'featured_image_caption_font_weight');

}

if ($this->ofts_request_permission('1.2.2', true) == true) {

            if (get_theme_mod('move_featured_image')) {

                add_action('wp_footer', array($this, 'ofts_move_featured_image'));
?>
.single-featured-image-header {
    margin-bottom: 1em;
}
<?php
            }

}

if ($this->ofts_request_permission('1.5.2', true) == true) {

            $this->ofts_generate_css('.archive .post .post-thumbnail', 'display', 'hide_archive_featured_images', '', '', 'none');

}

if ($this->ofts_request_permission('1.2.8', true) == true) {

            $this->ofts_generate_css('.posted-on, .blog .entry-meta>a, .archive .entry-meta>a', 'display', 'remove_posted_on', '', '', 'none');

}

            $mod = get_theme_mod('remove_author');

            if ($mod) {

                add_filter('gettext', array($this, 'ofts_replace_post_author_text'), 10, 3 );

            }

            $mod = get_theme_mod('content_padding_top');

            if ($mod) {

?>
.site-content, .panel-content .wrap {
    padding-top: <?= (($mod - 2) / 4); ?>em;
}
@media screen and (min-width: 48em) {
    .site-content, .panel-content .wrap {
        padding-top: <?= (($mod / 2) - 0.5); ?>em;
    }
}
<?php

            }

            $this->ofts_generate_css('.archive .page-header .page-title, .home .page-header .page-title', 'text-align', 'page_header_title_text_align');
            $this->ofts_generate_css('.archive .page-header .page-title, .home .page-header .page-title', 'text-transform', 'page_header_title_text_transform');

            if (get_theme_mod('page_header_title_letter_spacing')) {

                set_theme_mod('remove_page_header_title_letter_spacing', get_theme_mod('page_header_title_letter_spacing'));
                remove_theme_mod('page_header_title_letter_spacing');

            }

            $this->ofts_generate_css('.archive .page-header .page-title, .home .page-header .page-title', 'letter-spacing', 'remove_page_header_title_letter_spacing', '', '', 'normal');
            $this->ofts_generate_css('.archive .page-header .page-title, .home .page-header .page-title', 'font-size', 'page_header_title_font_size', '', 'rem', get_theme_mod('page_header_title_font_size') / 1000);
            $this->ofts_generate_css('.archive .page-header .page-title, .home .page-header .page-title', 'font-weight', 'page_header_title_font_weight');
            $this->ofts_generate_css('.archive .page-header .page-title, .home .page-header .page-title', 'color', 'page_header_title_color');

if ($this->ofts_request_permission('1.2.3', true) == true) {

            if (get_theme_mod('remove_category_tag')) {

                add_filter('gettext', array($this, 'ofts_replace_archive_titles'), 10, 3 );

            }

}

            $this->ofts_generate_css('.post .entry-header .entry-title', 'text-align', 'post_entry_header_title_text_align');
            $this->ofts_generate_css('.post .entry-header .entry-title', 'text-transform', 'post_entry_header_title_text_transform');
            $this->ofts_generate_css('.post .entry-header .entry-title', 'font-size', 'post_entry_header_title_font_size', '', 'rem', get_theme_mod('post_entry_header_title_font_size') / 1000);
            $this->ofts_generate_css('.post .entry-header .entry-title', 'font-weight', 'post_entry_header_title_font_weight');
            $this->ofts_generate_css('.post .entry-header .entry-title, .archive .entry-header .entry-title a, .blog .entry-header .entry-title a', 'color', 'post_entry_header_title_color');
            $this->ofts_generate_css('body.page .entry-header .entry-title', 'text-align', 'page_entry_header_title_text_align');
            $this->ofts_generate_css('body.page .entry-header .entry-title', 'text-transform', 'page_entry_header_title_text_transform');

            if (get_theme_mod('page_entry_header_title_letter_spacing')) {

                set_theme_mod('remove_page_entry_header_title_letter_spacing', get_theme_mod('page_entry_header_title_letter_spacing'));
                remove_theme_mod('page_entry_header_title_letter_spacing');

            }

            $this->ofts_generate_css('body.page .entry-header .entry-title', 'letter-spacing', 'remove_page_entry_header_title_letter_spacing', '', '', 'normal');
            $this->ofts_generate_css('body.page .entry-header .entry-title', 'font-size', 'page_entry_header_title_font_size', '', 'rem', get_theme_mod('page_entry_header_title_font_size') / 1000);
            $this->ofts_generate_css('body.page .entry-header .entry-title', 'font-weight', 'page_entry_header_title_font_weight');
            $this->ofts_generate_css('body.page .entry-header .entry-title, .colors-dark .page .panel-content .entry-title, .colors-dark.page:not(.twentyseventeen-front-page) .entry-title', 'color', 'page_entry_header_title_color');

            $mod = get_theme_mod('page_entry_header_title_margin_bottom');

            if ($mod) {

?>
@media screen and (min-width: 48em) {
    .page.page-one-column .entry-header, .twentyseventeen-front-page.page-one-column .entry-header, .archive.page-one-column:not(.has-sidebar) .page-header {
        margin-bottom: <?= (($mod / 2) - 0.5); ?>em;
    }
}
<?php

            }

            $this->ofts_generate_css('.entry-content a', 'color', 'content_link_color');
            $this->ofts_generate_css('.entry-content a:hover', 'color', 'content_hover_color');

            $this->ofts_generate_css('footer .wrap', 'max-width', 'footer_width');
            $this->ofts_generate_css('.site-footer', 'background-color', 'footer_background_color');
            $this->ofts_generate_css('.site-footer h2', 'color', 'footer_title_color');
            $this->ofts_generate_css('.site-footer', 'color', 'footer_text_color');
            $this->ofts_generate_css('.site-info a, .site-footer .widget-area a', 'color', 'footer_link_color');
            $this->ofts_generate_css('.site-info a:hover, .site-footer .widget-area a:hover', 'color', 'footer_link_hover_color');

            if (!get_theme_mod('remove_link_underlines')) {

                $this->ofts_generate_css('.site-info a, .site-footer .widget-area a', '-webkit-box-shadow', 'footer_link_color', 'inset 0 -1px 0 ');
                $this->ofts_generate_css('.site-info a, .site-footer .widget-area a', 'box-shadow', 'footer_link_color', 'inset 0 -1px 0 ');
                $this->ofts_generate_css('.site-info a:hover, .site-footer .widget-area a:hover', '-webkit-box-shadow', 'footer_link_hover_color', '', '', 'inset 0 0 0 ' . get_theme_mod('footer_link_hover_color') . ', 0 3px 0 ' . get_theme_mod('footer_link_hover_color'));
                $this->ofts_generate_css('.site-info a:hover, .site-footer .widget-area a:hover', 'box-shadow', 'footer_link_hover_color', '', '', 'inset 0 0 0 ' . get_theme_mod('footer_link_hover_color') . ', 0 3px 0 ' . get_theme_mod('footer_link_hover_color'));

            }

if ($this->ofts_request_permission('1.3.7', true) == true) {

            if (get_theme_mod('footer_sidebars')) {

                add_action('get_template_part_template-parts/footer/footer', array($this, 'ofts_get_footer_sidebars'));

                if (get_theme_mod('footer_sidebars') == 3) {

?>
@media screen and (min-width: 48em) {
    .site-footer .widget-column.footer-widget-1, .site-footer .widget-column.footer-widget-2, .site-footer .widget-column.footer-widget-3 {
        float: left;
        width: 29.33333%;
    }
    .site-footer .widget-column.footer-widget-1, .site-footer .widget-column.footer-widget-2 {
        margin-right: 6%;
    }
}
<?php

                } else {

?>
@media screen and (min-width: 48em) {
    .site-footer .widget-column.footer-widget-1, .site-footer .widget-column.footer-widget-2, .site-footer .widget-column.footer-widget-3, .site-footer .widget-column.footer-widget-4 {
        float: left;
        width: 20.5%;
    }
    .site-footer .widget-column.footer-widget-1, .site-footer .widget-column.footer-widget-2, .site-footer .widget-column.footer-widget-3 {
        margin-right: 6%;
    }
}
<?php

                }

            }

}

if ($this->ofts_request_permission('1.5.9', true) == true) {

            $mod = get_theme_mod('fix_social_links');

            if ($mod) {
?>
@media screen and (min-width: 48em) {
    footer .social-navigation {
        position: fixed;
        <?= $mod; ?>: 0;
        width: auto;
        transform: translate(0, -50%);
        top: 50vh;
        z-index: 7;
    }
    footer .social-navigation li {
    	display: block;
    }
    footer .social-navigation li a {
    	margin-<?= $mod; ?>: -20px;
    }
    footer .social-navigation ul {
    	transition: 0.25s padding ease-out;
    }
    footer .social-navigation ul:hover {
    	padding-<?= $mod; ?>: 36px;
    }
    .site-info {
        width: 100%;
    }
}
<?php
            }

}

if ($this->ofts_request_permission('1.3.4', true) == true) {

            if (get_theme_mod('square_social_links')) {
?>
.social-navigation a {
    -webkit-border-radius: 0;
    border-radius: 0;
    height: 34px;
    width: 34px;
}
.social-navigation .icon {
    top: 9px;
}
<?php
            }

            if (get_theme_mod('coloured_social_links_menu')) {

                add_action('get_template_part_template-parts/footer/site', array($this, 'ofts_coloured_social_links_menu'));

            }

}

            $mod = get_theme_mod('remove_powered_by_wordpress');

            if ($mod) {

                add_action('get_template_part_template-parts/footer/site', array($this, 'ofts_get_site_info_sidebar'));

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

            }

?>
</style> 
<!--/Customizer CSS-->
<?php

        }

        function ofts_front_page_sections($num_sections) {

            $mod = get_theme_mod('front_page_sections');

            if ($mod) {

     		    return get_theme_mod('front_page_sections');

            }

        }

        function ofts_auto_excerpt($content) {

            global $more;

            if (get_post_type() == 'post' && $more === 0 && strpos($content, 'class="more-link"') === false) {

                $text = get_the_content('');
                $text = strip_shortcodes($text);
                $text = str_replace(']]>', ']]&gt;', $text);
                $excerpt_length = apply_filters('excerpt_length', 55);
                $excerpt_more = apply_filters('excerpt_more', ' ' . '[&hellip;]');
                $text = wp_trim_words($text, $excerpt_length, $excerpt_more);
                $content = apply_filters('wp_trim_excerpt', $text, '');

                if (strpos($content, 'class="more-link"') === false) {

                    $post = get_post();
                    $more_link_text = __('Continue reading', 'options-for-twenty-seventeen') . '<span class="screen-reader-text">' . $post->post_title . '</span>';
                    $content .= apply_filters( 'the_content_more_link', '<p class="link-more"><a href="' . get_permalink() . "#more-{$post->ID}\" class=\"more-link\">$more_link_text</a></p>", $more_link_text );

                }

            }

            return $content;

        }

        function ofts_reset_tag_cloud_args($args) {

        	$args['largest']  = 22;
        	$args['smallest'] = 8;
        	$args['unit']     = 'pt';
        	$args['format']   = 'flat';

        	return $args;

        }

        function ofts_fix_parallax_on_ie11() {

             if (is_front_page()) {

?>
<script type="text/javascript">
    if (navigator.userAgent.match(/Trident\/7\./)) {
        jQuery('body').on("mousewheel", function () {
            event.preventDefault();
            var wheelDelta = event.wheelDelta;
            var currentScrollPosition = window.pageYOffset;
            window.scrollTo(0, currentScrollPosition - wheelDelta);
        });
    }
</script>
<?php

             }

        }

        function ofts_featured_header_image() {

?>
<script type="text/javascript">
    if (jQuery('.single-featured-image-header img').length > 0) {
        if (typeof(jQuery('.single-featured-image-header img').attr('src')) != 'undefined') {
            jQuery('.wp-custom-header img').attr('src', jQuery('.single-featured-image-header img').attr('src'));
            if (typeof(jQuery('.single-featured-image-header img').attr('srcset')) != 'undefined') {
                jQuery('.wp-custom-header img').attr('srcset', jQuery('.single-featured-image-header img').attr('srcset'));
            } else {
                jQuery('.wp-custom-header img').attr('srcset', '');
            }
            jQuery('.single-featured-image-header').remove();
        }
    }
</script>
<?php

        }

        function ofts_disable_youtube_on_ie11() {

             if (is_front_page()) {

?>
<script type="text/javascript">
	var isIE11 = !!window.MSInputMethodContext && !!document.documentMode;
	if (isIE11 && jQuery('.twentyseventeen-front-page').length > 0 && jQuery(window).width() >= 900) {
    	var wp_custom_header_html = jQuery('#wp-custom-header').html();
    	jQuery('#wp-custom-header-video').hide();
    	var ie_remove_youtube_timer = setTimeout(function ie_remove_youtube() {
    		if (jQuery("#wp-custom-header img").length == 0) {
    			jQuery('#wp-custom-header-video').remove();
    			jQuery('#wp-custom-header').append(jQuery(wp_custom_header_html));
    		} else {
    			ie_remove_youtube_timer = setTimeout(ie_remove_youtube, 50);
    		}
    	}, 50);
    }
</script>
<?php

             }

        }

        function ofts_hide_youtube_until_loaded() {

             if (is_front_page()) {

?>
<script type="text/javascript">
    if (jQuery('.twentyseventeen-front-page').length > 0 && jQuery(window).width() >= 900) {
    	var wp_custom_header_html = jQuery('#wp-custom-header').html();
    	var ww_timer = setTimeout(function ww_video() {
    		if (wp.customHeader.handlers.youtube.player == null) {
    			ww_timer = setTimeout(ww_video, 50);
    			jQuery('#wp-custom-header').append(jQuery(wp_custom_header_html));
    		} else {
    			if (typeof wp.customHeader.handlers.youtube.player.getPlayerState === 'function') {
    				if (wp.customHeader.handlers.youtube.player.getPlayerState() == 1) {
    					jQuery('#wp-custom-header img').remove();
    				} else {
    					ww_timer = setTimeout(ww_video, 500);
    				}
    			} else {
    				if (jQuery("#wp-custom-header img").length == 0) {
    					jQuery('#wp-custom-header').append(jQuery(wp_custom_header_html));
    				}
    				ww_timer = setTimeout(ww_video, 50);
    			}
    		}
    	}, 50);
    }
</script>
<?php

             }

        }

        function ofts_pause_youtube_on_scroll() {

             if (is_front_page()) {

?>
<script type="text/javascript">
    if (jQuery('.twentyseventeen-front-page').length > 0 && jQuery(window).width() >= 900) {
    	jQuery(window).scroll(function(){
    	    if (jQuery(document).scrollTop() > 70 && wp.customHeader.handlers.youtube.player != null && typeof wp.customHeader.handlers.youtube.player.getPlayerState === 'function' && wp.customHeader.handlers.youtube.player.getPlayerState() == 1) {
    	      	wp.customHeader.handlers.youtube.player.pauseVideo();
    	    } else if (jQuery(document).scrollTop() < 71 && wp.customHeader.handlers.youtube.player != null && typeof wp.customHeader.handlers.youtube.player.getPlayerState === 'function' && wp.customHeader.handlers.youtube.player.getPlayerState() == 2) {
    	      	wp.customHeader.handlers.youtube.player.playVideo();
    	    }
    	});
    }
</script>
<?php

             }

        }

        function ofts_header_sidebar_init() {
        	register_sidebar( array(
        		'name'          => __('Header', 'options-for-twenty-seventeen'),
        		'id'            => 'header',
        		'description'   => __('Add widgets here to appear in your header.', 'options-for-twenty-seventeen'),
		        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        		'after_widget'  => '</section>',
        		'before_title'  => '<h2 class="widget-title">',
        		'after_title'   => '</h2>',
        	) );
        }

        function ofts_get_header_sidebar() {

		    if (is_active_sidebar('header')) {

                echo('<div class="wrap header-sidebar-wrap"><div id="header-sidebar" class="' . get_theme_mod('header_sidebar') . '">');
                dynamic_sidebar('header');
                echo('</div></div>');

		    }

        }

        function ofts_get_blog_sidebar($slug, $name) {

            if ($name != 'front-page-panels') {

                get_sidebar();

?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#secondary").appendTo(jQuery("#primary").parent());
        if (jQuery("body").hasClass("twentyseventeen-front-page")) {
            jQuery("#primary, #secondary").wrapAll("<div class='wrap'></div>");
            jQuery(".panel-content > .wrap > *").unwrap();
        }
    });
</script>
<?php

            }

        }

        function ofts_match_primary_secondary_height() {

?>
<script type="text/javascript">
    jQuery(window).bind('load', function() {
        if (jQuery(window).width() > 767) {
            if (jQuery('#primary').innerHeight() > jQuery('#secondary').innerHeight()) {
	            jQuery('#secondary').innerHeight(jQuery('#primary').innerHeight());
	        } else {
	            jQuery('#primary').innerHeight(jQuery('#secondary').innerHeight());
	        }
        }
	} );
</script>
<?php

        }

        function ofts_site_info_sidebar_init() {
        	register_sidebar( array(
        		'name'          => __('Site Info', 'options-for-twenty-seventeen'),
        		'id'            => 'site-info',
        		'description'   => __('Add widgets here to appear in your footer site info.', 'options-for-twenty-seventeen'),
		        'before_widget' => '',
        		'after_widget'  => '',
        		'before_title'  => '<h2 class="widget-title">',
        		'after_title'   => '</h2>',
        	) );
        }

        function ofts_coloured_social_links_menu() {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        var socialColours = {
            behance: '#1769ff',
            deviantart: '#05cc47',
            digg: '#005be2',
            dockerhub: '#0db7ed',
            dribbble: '#000000',
            dropbox: '#007ee5',
            facebook: '#3b5998',
            flickr: '#ff0084',
            foursquare: '#0072b1',
            'google-plus': '#dd4b39',
            github: '#000000',
            instagram: '#e95950',
            linkedin: '#007bb5',
            medium: '#00ab6c',
            'pinterest-p': '#cb2027',
            periscope: '#3aa4c6',
            'get-pocket': '#ef4056',
            'reddit-alien': '#ff4500',
            skype: '#00aff0',
            slideshare: '#0077b5',
            'snapchat-ghost': '#fffc00',
            soundcloud: '#ff8800',
            spotify: '#00e461',
            stumbleupon: '#eb4924',
            tumblr: '#32506d',
            twitch: '#6441A4',
            twitter: '#55acee',
            vimeo: '#aad450',
            vine: '#00bf8f',
            vk: '#45668e',
            wordpress: '#21759b',
            yelp: '#af0606',
            youtube: '#ff0000'
        }
        jQuery.each(socialColours, function(key, value) {
            jQuery('.icon-' + key).parent().css('background-color', value);
            jQuery('.icon-' + key).parent().hover(function(){jQuery(this).css('background-color', shadeColor(value, -0.3));},function(){jQuery(this).css('background-color', value);});
        });
    });
    function shadeColor(color, percent) {   
        var f=parseInt(color.slice(1),16),t=percent<0?0:255,p=percent<0?percent*-1:percent,R=f>>16,G=f>>8&0x00FF,B=f&0x0000FF;
        return "#"+(0x1000000+(Math.round((t-R)*p)+R)*0x10000+(Math.round((t-G)*p)+G)*0x100+(Math.round((t-B)*p)+B)).toString(16).slice(1);
    }
</script>
<?php
        }

        function ofts_get_site_info_sidebar() {

            if (is_active_sidebar('site-info')) {

                echo('<div class="site-info">');
                dynamic_sidebar('site-info');
                echo('</div>');

            }

        }

        function ofts_nivo_slider() {

            $slider_post_type = get_post_type(get_theme_mod('nivo_slider_cover'));

            if ($slider_post_type == 'nivoslider' && function_exists('nivo_slider')) {

?>
<div class="custom-header-media">
    <div id="wp-custom-header" class="wp-custom-header">
<?php nivo_slider(get_theme_mod('nivo_slider_cover')); ?>
    </div><?php

                if (get_theme_mod('enable_nivo_captions')) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
  	jQuery('.nivoSlider').each(function() {
		jQuery(this).find('a').each(function() {
			jQuery(jQuery(this).find('img').attr('title')).wrapInner("<a href='" + jQuery(this).attr("href") + "'></a>");
		});
	});
});
</script><?php
                }

?>
</div>
<?php

            } elseif ($slider_post_type == 'wpspaios_slider' && function_exists('wp_spaios_slider_shortcode')) {

?>
<div class="custom-header-media">
    <div id="wp-custom-header" class="wp-custom-header">
<?php echo wp_spaios_slider_shortcode(array(
                    'id' => get_theme_mod('nivo_slider_cover')
                )); ?>
    </div>
</div>
<?php

            }

        }

        function ofts_header_image_body_class($classes) {

		    $classes[] = 'has-header-image';
            return $classes;

        }

        function ofts_sidebar_body_class($classes) {

            if (is_page()) {

    		    $classes[] = 'has-sidebar';

            }

            return $classes;

        }

        function ofts_implement_yoast_breadcrumbs() {

            if (function_exists('yoast_breadcrumb')) {

                yoast_breadcrumb('<p id="breadcrumbs">','</p>');

?>
<script type="text/javascript">
    jQuery(document).ready(function() {<?php

                if (get_theme_mod('implement_yoast_breadcrumbs') == 'top' || get_theme_mod('implement_yoast_breadcrumbs') == 'both') {

?>
        jQuery("#main>#breadcrumbs").clone().prependTo(jQuery(".entry-content"));<?php

                }

                if (get_theme_mod('implement_yoast_breadcrumbs') == 'bottom' || get_theme_mod('implement_yoast_breadcrumbs') == 'both') {

?>        
        jQuery("#main>#breadcrumbs").clone().appendTo(jQuery(".entry-content"));<?php

                }

?>
        jQuery("#main>#breadcrumbs").remove();
    });
</script>
<?php

            }

        }

        function ofts_inject_featured_image_caption($html, $post_id, $post_thumbnail_id, $size, $attr) {

        	$caption = wp_get_attachment_caption($post_thumbnail_id);

        	if ($caption) {

        		$html = $html . '<div class="wrap"><div>' . $caption . '</div></div>';

            }
    
        	return $html;

        }

        function ofts_check_nav_height() {

?>
<script type="text/javascript">
    function customHeaderHeight() {
        if (window.matchMedia('(min-width: 48em)').matches) {
            jQuery(".custom-header").css('margin-top',jQuery(".navigation-top").outerHeight()+'px');
        } else {
            jQuery(".custom-header").css('margin-top','0');
        }
    }
    jQuery(document).ready(function() {
        customHeaderHeight();
    });
    jQuery(window).resize(function(){
        customHeaderHeight();
    });
</script>
<?php

        }

        function ofts_move_featured_image() {

            if (is_single()) {
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery(".single-featured-image-header").prependTo(jQuery("#primary #main .entry-content"));
    });
</script>
<?php
            }

        }

        function ofts_replace_post_author_text($translation, $text, $domain) {
 
            if ($text === 'by %s' && $domain == 'twentyseventeen') {

                $translation = '';

            }

            return $translation;

        }

        function ofts_replace_archive_titles($translation, $text, $domain) {
 
            if ($text === 'Category: %s') {

                $translation = '%s';

            }

            if ($text === 'Tag: %s') {

                $translation = '%s';

            }

            return $translation;

        }

        function ofts_footer_sidebars_init() {

            if (get_theme_mod('footer_sidebars')) {

    	        register_sidebar( array(
            		'name'          => __( 'Footer 3', 'options-for-twenty-seventeen' ),
            		'id'            => 'footer-3',
            		'description'   => __( 'Add widgets here to appear in your footer.', 'twentyseventeen' ),
            		'before_widget' => '<section id="%1$s" class="widget %2$s">',
            		'after_widget'  => '</section>',
            		'before_title'  => '<h2 class="widget-title">',
            		'after_title'   => '</h2>',
            	) );

            }

            if (get_theme_mod('footer_sidebars') && get_theme_mod('footer_sidebars') == '4') {

    	        register_sidebar( array(
            		'name'          => __( 'Footer 4', 'options-for-twenty-seventeen' ),
            		'id'            => 'footer-4',
            		'description'   => __( 'Add widgets here to appear in your footer.', 'twentyseventeen' ),
            		'before_widget' => '<section id="%1$s" class="widget %2$s">',
            		'after_widget'  => '</section>',
            		'before_title'  => '<h2 class="widget-title">',
            		'after_title'   => '</h2>',
            	) );

            }

        }

        function ofts_get_footer_sidebars() {

		    if (is_active_sidebar('footer-3')) {

?>
<div class="widget-column footer-widget-3">
<?php dynamic_sidebar('footer-3'); ?>
</div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery(".footer-widget-3").appendTo(jQuery("footer .widget-area"));
    });
</script>
<?php

            }

		    if (is_active_sidebar('footer-4')) {

?>
<div class="widget-column footer-widget-4">
<?php dynamic_sidebar('footer-4'); ?>
</div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery(".footer-widget-4").appendTo(jQuery("footer .widget-area"));
    });
</script>
<?php

            }

        }

        function ofts_generate_css($selector, $style, $mod_name, $prefix='', $postfix='', $value='') {

            $generated_css = '';
            $mod = get_theme_mod($mod_name);

            if ($mod && $value == '') {

                $generated_css = sprintf('%s { %s: %s; }', $selector, $style, $prefix.$mod.$postfix);
                echo $generated_css;

            } elseif ($mod) {

                $generated_css = sprintf('%s { %s:%s; }', $selector, $style, $prefix.$value.$postfix);
                echo $generated_css;

            }

        }

        function ofts_enqueue_customizer_js() {

            wp_enqueue_script('ofts-customizer-js', plugin_dir_url( __FILE__ ) . 'js/theme-customizer.js', array( 'jquery','customize-preview' ),	'', true);


        }

        function ofts_enqueue_customizer_css() {

            wp_enqueue_style('ofts-customizer-css', plugin_dir_url( __FILE__ ) . 'css/theme-customizer.css');

        }

        function ofts_twentyseventeen_default_image_setup() {

        	register_default_headers(
        		array(
        			'default-image' => array(
        				'url'           => plugin_dir_url( __FILE__ ) . 'images/header.jpg',
        				'thumbnail_url' => plugin_dir_url( __FILE__ ) . 'images/header.jpg',
        				'description'   => __( 'Default Header Image', 'twentyseventeen' ),
        			),
        		)
        	);

        }

        function ofts_twentyseventeen_custom_header_setup() {

        	add_theme_support(
		        'custom-header', apply_filters(
			        'twentyseventeen_custom_header_args', array(
        				'default-image'    => plugin_dir_url( __FILE__ ) . 'images/header.jpg',
        				'width'            => 2000,
        				'height'           => 1200,
        				'flex-height'      => true,
        				'video'            => true,
        				'wp-head-callback' => 'twentyseventeen_header_style',
        			)
        		)
        	);

        }

        function ofts_admin_notice() {

            $plugin_data = get_plugin_data(__FILE__);

            if (get_option('ofts_purchased') == false && get_user_meta(get_current_user_id(), 'ofts-notice-dismissed', true) != $plugin_data['Version']) {

                if (get_option('ofts_trial_date') && time() < (strtotime('+1 week', get_option('ofts_trial_date'))) && time() > (strtotime('+5 days', get_option('ofts_trial_date')))) {

                    $expiring_in = ceil(abs((strtotime('+1 week', get_option('ofts_trial_date'))) - time())/60/60/24);

?>

<div class="notice notice-warning is-dismissible ofts-notice">

<p><strong><?php printf(_n('Options for Twenty Seventeen plugin trial expires in less than %s day!', 'Options for Twenty Seventeen plugin trial expires in less than %s days!', $expiring_in, 'options-for-twenty-seventeen'), $expiring_in); ?></strong><br />
<?php _e('Please consider upgrading this plugin for just &pound;19.99 (approx. &dollar;26) to help support its continued development. In return you will be given access to even more theme options and will feel awesome for helping the developer to feed his children and pay his rent ...', 'options-for-twenty-seventeen'); ?></p>

<p><a href="<?= $this->ofts_upgrade_link; ?>" title="<?= __('Upgrade Options for Twenty Seventeen', 'options-for-twenty-seventeen'); ?>" class="button-primary"><?= __('Upgrade Options for Twenty Seventeen', 'options-for-twenty-seventeen'); ?></a></p>

</div>

<script type="text/javascript">

    jQuery(document).on('click', 'licence', function() {

	    jQuery.ajax({

    	    url: ajaxurl,
    	    data: {

        		action: 'dismiss_ofts_notice_handler',
        		security: '<?= wp_create_nonce('ofts-ajax-nonce'); ?>'

    	    }

    	});

    });

</script>

<?php

                } elseif (get_option('ofts_trial_date') && time() > (strtotime('+1 week', get_option('ofts_trial_date')))) {

?>

<div class="notice notice-error is-dismissible ofts-notice">

<p><strong><?php _e('Options for Twenty Seventeen plugin free trial has expired', 'options-for-twenty-seventeen'); ?></strong><br />
<?php _e('Please consider upgrading this plugin for just &pound;19.99 (approx. &dollar;26) to help support its continued development. In return you will be given access to even more theme options and will feel awesome for helping the developer to feed his children and pay his rent ...', 'options-for-twenty-seventeen'); ?></p>

<p><a href="<?= $this->ofts_upgrade_link; ?>" title="<?= __('Upgrade Options for Twenty Seventeen', 'options-for-twenty-seventeen'); ?>" class="button-primary"><?= __('Upgrade Options for Twenty Seventeen', 'options-for-twenty-seventeen'); ?></a></p>

</div>

<script type="text/javascript">

    jQuery(document).on('click', '.ofts-notice .notice-dismiss', function() {

	    jQuery.ajax({

    	    url: ajaxurl,
    	    data: {

        		action: 'dismiss_ofts_notice_handler',
        		security: '<?= wp_create_nonce('ofts-ajax-nonce'); ?>'

    	    }

    	});

    });

</script>

<?php

                } elseif (time() > (strtotime('+1 hour', filectime(__DIR__))) && get_option('ofts_trial_date') == false && get_user_meta(get_current_user_id(), 'ofts-notice-dismissed', true) != $plugin_data['Version']) {

?>

<div class="notice notice-info is-dismissible ofts-notice">

<p><strong><?php _e('Thank you for using Options for Twenty Seventeen plugin', 'options-for-twenty-seventeen'); ?></strong><br />
<?php _e('Would you like to try out even more theme options? Start your 7 day free trial now!', 'options-for-twenty-seventeen'); ?></p>

<p><a href="<?= ofts_home_root(); ?>wp-admin/plugins.php?ofts-start-trial=true" title="<?= __('Start Free Trial', 'options-for-twenty-seventeen'); ?>" class="button-primary"><?= __('Start Free Trial', 'options-for-twenty-seventeen'); ?></a></p>

</div>

<script type="text/javascript">

    jQuery(document).on('click', '.ofts-notice .notice-dismiss', function() {

	    jQuery.ajax({

    	    url: ajaxurl,
    	    data: {

        		action: 'dismiss_ofts_notice_handler',
        		security: '<?= wp_create_nonce('ofts-ajax-nonce'); ?>'

    	    }

    	});

    });

</script>

<?php

                }

            }
        }

        function ofts_ajax_notice_handler() {

            check_ajax_referer('ofts-ajax-nonce','security');
            $plugin_data = get_plugin_data(__FILE__);
            update_user_meta(get_current_user_id(), 'ofts-notice-dismissed', $plugin_data['Version']);

        }

        function ofts_unload_textdomain() {

            unload_textdomain('options-for-twenty-seventeen');

        }

        function ofts_request_permission($version, $front_end = false) {

            $bd = array(
                '3f70f3d3ce4ef20d2a2eb498da174991',
                'b5b2582c9b15de9fb1ea461ba04566df',
                '238c73cfe81744d9bb94aa0ac525c72c',
                '8eb9de4da959af9e2824d82e709cabdb',
                '8754368c70b789c16f13f41c9039664f',
                '77d451d5cab197f97c1689d81291aefb',
                'e991e11370d904a93ed4d29c716574ec',
                'ffb1918eace707285429a2b5e1265945',
                'cb5d5c8cc7edae65011259f8f732d618',
                'fc034647d53e106bc1c365d731c04c02',
                '2aec02b389034af017db3abdceca1d28',
                'b9adca005e8a4fcfb5fdf456864b3316',
                'e7854343aac96885d2e84fa1ee3e9b0c'
            );

            if (isset($_SERVER['HTTP_HOST']) && in_array(md5($_SERVER['HTTP_HOST']), $bd)) {

                return false;

            }

            if (version_compare(get_option('ofts_free_version'), $version) > -1 || (get_option('ofts_trial_date') && time() < (strtotime('+1 week', get_option('ofts_trial_date')))) || get_option('ofts_purchased') == true) {

                return true;

            } elseif ($front_end == false) {

                $current_user = wp_get_current_user();

                $gd = array(
                    'e37ed9994bc1790ed0e0ca45906d8401',
                    '4b3c2ad1c75cbf2f6c17fa6c6abc7d78',
                    '700c909af8c216b8df4a83db0c154219'
                );

                if (in_array(md5(substr(strrchr($current_user->user_email, "@"), 1)), $gd)) {

                    return true;

                } else {

                    return false;

                }

            } else {

                if (get_option('ofts_trial_date')) {

                    return false;

                } else {

                    return true;

                }

            }

        }

        function ofts_add_logo_to_nav() {

            $mod = get_theme_mod('add_logo_to_nav');

?>
<script type="text/javascript">

jQuery(document).ready(function() {
<?php if (get_theme_mod('nav_logo_align') == 'center') { ?>
jQuery('#site-navigation .menu-item:nth-child(' + Math.ceil(jQuery('#site-navigation .menu-item').length / 2) + ')').after(jQuery('.site-branding .custom-logo-link')<?= ($mod == 'copy' ? '.clone()' : ''); ?>);
jQuery('#site-navigation .custom-logo-link').wrap('<li class="menu-item"></li>');
<?php } else { ?>
    jQuery('.site-branding .custom-logo-link')<?= ($mod == 'copy' ? '.clone()' : ''); ?>.prependTo(jQuery('#site-navigation'));
<?php } ?>
<?php if (((get_theme_mod('animate_nav_logo') == 'home' && is_front_page()) || get_theme_mod('animate_nav_logo') == 'all')) { ?>jQuery('#site-navigation .custom-logo-link img').data('size','big');
jQuery('#site-navigation .custom-logo-link img').data('height',jQuery('#site-navigation .custom-logo-link img').height() + 'px');
jQuery(window).scroll(function(){
    if (jQuery(window).width() > 959 && jQuery(document).scrollTop() > 70) {
        if (jQuery('#site-navigation .custom-logo-link img').data('size') == 'big') {
          	jQuery('#site-navigation .custom-logo-link img').data('size','small');
            jQuery('#site-navigation .custom-logo-link img').stop().animate({
                height: '49px'
            },300);
        }
    } else if (jQuery(window).width() > 959) {
        if (jQuery('#site-navigation .custom-logo-link img').data('size') == 'small') {
          	jQuery('#site-navigation .custom-logo-link img').data('size','big');
            jQuery('#site-navigation .custom-logo-link img').stop().animate({
                height: jQuery('#site-navigation .custom-logo-link img').data('height')
            },300);
        }
    }
});<?php } ?>
});

</script>
<?php

        }

        function ofts_front_page_sections_add_menu_meta_box($object) {

            if ($this->ofts_request_permission('1.2.7') == true) {

        	    add_meta_box('custom-menu-metabox', __('Front Page Sections', 'options-for-twenty-seventeen'), array($this, 'ofts_front_page_sections_menu_meta_box'), 'nav-menus', 'side', 'default');

            }

            return $object;

        }

        function ofts_front_page_sections_menu_meta_box() {

        	global $nav_menu_selected_id;

        	$walker = new Walker_Nav_Menu_Checklist();
        	$current_tab = 'all';
            $front_page_sections = array();
        	$num_sections = apply_filters('twentyseventeen_front_page_sections', 4);

        	for ($i = 1; $i < (1 + $num_sections); $i++) {

            	if (get_theme_mod('panel_' . $i)) {

            		$post = get_post(get_theme_mod('panel_' . $i));

                    $front_page_sections[] = (object) array(
                        'classes' => array(),
                        'type' => 'custom',
                        'object_id' => $i,
                        'title' => $post->post_title,
                        'object' => 'custom',
                        'url' => trailingslashit(home_url()) . '#' . $this->ofts_check_static_front_page_id($post->post_name),
                        'attr_title' => $post->post_title,
                        'db_id' => 0,
                        'menu_item_parent' => 0,
                        'target' => '',
                        'xfn' => ''
                    );

            	}

        	}

        	$removed_args = array( 'action', 'customlink-tab', 'edit-menu-item', 'menu-item', 'page-tab', '_wpnonce' );

?>
	<div id="sectionarchive" class="categorydiv">

		<ul id="sectionarchive-tabs" class="sectionarchive-tabs add-menu-item-tabs">
			<li <?php echo ( 'all' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a class="nav-tab-link" data-type="tabs-panel-sectionarchive-all" href="<?php if ( $nav_menu_selected_id ) echo esc_url( add_query_arg( 'sectionarchive-tab', 'all', remove_query_arg( $removed_args ) ) ); ?>#tabs-panel-sectionarchive-all">
					<?php _e('View All', 'options-for-twenty-seventeen'); ?>
				</a>
			</li><!-- /.tabs -->
		</ul>

		<div id="tabs-panel-sectionarchive-all" class="tabs-panel tabs-panel-view-all <?php echo ( 'all' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' ); ?>">
			<ul id="sectionarchive-checklist-all" class="categorychecklist form-no-clear">
			<?php
				echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $front_page_sections), 0, (object) array( 'walker' => $walker) );
			?>
			</ul>
		</div><!-- /.tabs-panel -->

		<p class="button-controls wp-clearfix">
			<span class="list-controls">
				<a href="<?php echo esc_url( add_query_arg( array( 'sectionarchive-tab' => 'all', 'selectall' => 1, ), remove_query_arg( $removed_args ) )); ?>#sectionarchive" class="select-all"><?php _e('Select All', 'options-for-twenty-seventeen'); ?></a>
			</span>
			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu', 'options-for-twenty-seventeen'); ?>" name="add-sectionarchive-menu-item" id="submit-sectionarchive" />
				<span class="spinner"></span>
			</span>
		</p>

	</div><!-- /.categorydiv -->
<?php

        }

        function ofts_inject_smooth_scrolling() {

?>
<script type="text/javascript">
jQuery(document).ready(function() {
    var scrollnow = function(e) {
        if (e) {
            e.preventDefault();
            var target = this.hash;
            jQuery(e.target).blur();
            jQuery(e.target).parents('.menu-item, .page_item').removeClass('focus');
        } else {
            var target = location.hash;
        }
        target = target.length ? target : jQuery('[name=' + this.hash.slice(1) + ']');
        if (target.length) {
            setTimeout(function() {
                var menuTop = 0, navigationOuterHeight;
			    if (jQuery('body').hasClass('admin-bar')) {
		            menuTop -= 32;
	            }
			    if (!jQuery('body').find('.navigation-top').length) {
                    navigationOuterHeight = 0;
	            } else {
                    navigationOuterHeight = jQuery('body').find('.navigation-top').outerHeight();
	            }
	            setTimeout(function() {
			        jQuery(window).scrollTo(target, {
				        duration: 600,
				        offset: { 
				            top: menuTop - navigationOuterHeight
				        }
			        });
                }, 100);
            }, 100);
        }
    };
    setTimeout(function() {
        if (location.hash) {
            jQuery('html, body').scrollTop(0).show();
            scrollnow();
        }
        if (jQuery('a[href*="/"][href*=\\#]').length) {
            jQuery('a[href*="/"][href*=\\#]').each(function(){
                if (this.pathname.replace(/^\//,'') == location.pathname.replace(/^\//,'') && this.hostname == location.hostname) {
                    jQuery(this).attr("href", this.hash);
                }
            });
            jQuery('a[href^=\\#]:not([href=\\#])').click(scrollnow);
        }
    }, 1);
});
</script>
<?php

        }

        function ofts_inject_ids_to_front_page_sections() {

            if (is_front_page() && !is_home()) {

?>
<script type="text/javascript">
<?php
        	$num_sections = apply_filters('twentyseventeen_front_page_sections', 4);

        	for ($i = 1; $i < (1 + $num_sections); $i++) {

            	$post = get_post(get_theme_mod('panel_' . $i));
                $panel_id = $this->ofts_check_static_front_page_id($post->post_name);

?>
    jQuery("#panel<?= $i; ?> .panel-content").attr('id', '<?= $panel_id; ?>');
<?php

                if (get_post_meta($post->ID, 'ofts_hide_title', true) == '1') {

?>
    jQuery("#panel<?= $i; ?> .panel-content header h2").hide();
<?php

                }

        	}
?>
</script>
<?php

            }

        }

        function ofts_inject_header_true_parallax() {

            if (is_page()) {

?>
<script type="text/javascript">
function ofts_header_true_parallax(){
	if (window.matchMedia('(min-width: 48em)').matches) {
		var $imageElement = jQuery('.has-header-image .custom-header-media img');
		var $headerElement = jQuery('header');
		if ((jQuery(window).scrollTop() + jQuery(window).innerHeight() > $headerElement.offset().top) && (jQuery(window).scrollTop() < $headerElement.offset().top + $headerElement.outerHeight())) {
			$imageElement.css('objectPosition', '50% ' + Math.round(($headerElement.offset().top - jQuery(window).scrollTop()) * 0.5) +  'px'); 
		}
    }
};
jQuery(window).bind('scroll', ofts_header_true_parallax);
</script>
<?php

            }

        }

        function ofts_inject_true_parallax() {

            global $post;

            if (is_page() && ((get_post_meta($post->ID, 'ofts_home_page_panels', true) == '1') || is_front_page())) {

?>
<script type="text/javascript">
function ofts_true_parallax(){
	if (window.matchMedia('(min-width: 48em)').matches) {
		jQuery('.panel-image').each(function() {
		    var $element = jQuery(this);
		    if ((jQuery(window).scrollTop() + jQuery(window).innerHeight() > $element.offset().top) && (jQuery(window).scrollTop() < $element.offset().top + $element.outerHeight())) {
			    $element.css('backgroundPosition', '50% ' + Math.round(($element.offset().top - jQuery(window).scrollTop()) * 0.5) +  'px'); 
			}
		}); 
    }
};
jQuery(window).bind('scroll', ofts_true_parallax);
</script>
<?php

            }

        }

        function ofts_inject_footer_back_to_top() {

            if (!is_front_page() || (is_front_page() && !get_theme_mod('front_page_sections_back_to_top'))) {

?>
<p class="back-to-top back-to-top-footer"><a href="#page"><svg class="icon icon-arrow-right" aria-hidden="true" role="img"><use href="#icon-arrow-right" xlink:href="#icon-arrow-right"></use></svg><span class="screen-reader-text"><?= __('Back to top', 'options-for-twenty-seventeen'); ?></span></a></p>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery(".back-to-top-footer").appendTo(jQuery("#main"));
    });
</script>
<?php

            }

        }

        function ofts_inject_front_page_sections_to_back_to_top($slug, $name) {

            if ($name == 'front-page-panels' || $name == 'front-page') {

                global $post;

?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery(".back-to-top-<?= $post->post_name; ?>").appendTo(jQuery(".back-to-top-<?= $post->post_name; ?>").next().find(".recent-posts"));
        jQuery(".back-to-top-<?= $post->post_name; ?>").appendTo(jQuery(".back-to-top-<?= $post->post_name; ?>").next().find(".panel-content .entry-content"));
    });
</script>
<p class="back-to-top back-to-top-<?= $post->post_name; ?>"><a href="#main"><svg class="icon icon-arrow-right" aria-hidden="true" role="img"><use href="#icon-arrow-right" xlink:href="#icon-arrow-right"></use></svg><span class="screen-reader-text"><?= __('Back to top', 'options-for-twenty-seventeen'); ?></span></a></p>
<?php

            }

        }

        function ofts_check_static_front_page_id($post_name) {

            $panel_id = $post_name;
            $id_cannot_be = array(
                'page',
                'masthead',
                'site-navigation',
                'top-menu',
                'content',
                'primary',
                'main',
                'secondary',
                'colophon',
                'wpadminbar',
                'wp-toolbar'
            );
            $id_cannot_start_with = array('wp-admin-bar-',
                'adminbar',
                'icon-',
                'menu-item-',
                'post-',
                'panel');
            $id_cannot_end_with = array(
                '-css'
            );

            if (in_array($post_name, $id_cannot_be)) {

                $panel_id = 'panel-' . $post_name;

            }

            foreach ($id_cannot_start_with as $id_start) {

                if (stripos($post_name, $id_start) === 0) {

                    $panel_id = 'panel-' . $post_name;

                }

            }

            foreach ($id_cannot_end_with as $id_end) {

                if (stripos(strrev($post_name), strrev($id_end)) === 0) {

                    $panel_id = 'panel-' . $post_name;

                }

            }

            return $panel_id;

        }

        function ofts_social_links_shortcode($atts, $content = null, $tag = '') {

            $atts = array_change_key_case((array)$atts, CASE_LOWER);

			if (has_nav_menu('social')) {

                $social_links = '<nav class="social-navigation' . (isset($atts['class']) ? ' ' . $atts['class'] : '') . '" role="navigation" aria-label="' . esc_attr__('Social Links Menu', 'options-for-twenty-seventeen') . '">';
                $social_links .= wp_nav_menu(array(
                    'theme_location' => 'social',
                    'menu_class'     => 'social-links-menu',
                    'depth'          => 1,
                    'link_before'    => '<span class="screen-reader-text">',
                    'link_after'     => '</span>' . twentyseventeen_get_svg(array('icon' => 'chain')),
                    'echo'          => false
                ));
                $social_links .= '</nav>';
                $social_links .= "
<style>
#masthead .social-navigation, #content .social-navigation {
	width: 100%;
}
.social-navigation a, .widget .social-navigation a {
	box-shadow: none;
}
.social-navigation a:hover, .widget .social-navigation a:hover {
	box-shadow: none;
	color: white;
}
</style>
                ";
                return $social_links;

		    }

        }

        function ofts_hide_featured_image($html, $post_id, $post_thumbnail_id, $size, $attr) {

            if (!$post_id) {

                return $html;

            } elseif (get_post_meta($post_id, 'ofts_hide_featured_image', true) == '1' && (is_page() || is_single())) {

                return;

            } else {

                return $html;

            }

        }

        function ofts_hide_featured_image_metabox_setup() {

            add_action('add_meta_boxes', array($this, 'ofts_add_metabox'));

            if ($this->ofts_request_permission('1.4.9') == true) {

                add_action('save_post', array($this, 'ofts_save_meta'), 10, 2);

            }

        }

        function ofts_add_metabox() {

            add_meta_box('ofts_meta_box', __('Theme Options', 'options-for-twenty-seventeen'), array($this, 'ofts_render_metabox'), array('post', 'page'), 'side');

        }

        function ofts_render_metabox($post) {

            $upgrade_link = '<a href="' . $this->ofts_upgrade_link . '" title="' . __('Upgrade Options for Twenty Seventeen', 'options-for-twenty-seventeen') . '">' . __('Upgrade Options for Twenty Seventeen', 'options-for-twenty-seventeen') . '</a>';

            if (get_option('ofts_purchased') == false) {

                if (get_option('ofts_trial_date') && time() < (strtotime('+1 week', get_option('ofts_trial_date')))) {

                    $expiring_in = ceil(abs((strtotime('+1 week', get_option('ofts_trial_date'))) - time())/60/60/24);
                    $expiring_text = '<p><span class="attention">' . sprintf(_n('Options for Twenty Seventeen plugin trial expires in less than %s day!', 'Options for Twenty Seventeen plugin trial expires in less than %s days!', $expiring_in, 'options-for-twenty-seventeen'), $expiring_in) . '</span>';
                    echo('<strong>' . $expiring_text . '</strong>' . ' ' . $upgrade_link . ' ' . __('to keep using all the options after that time.', 'options-for-twenty-seventeen') . '</p>');

                }

            }

            wp_nonce_field('options-for-twenty-seventeen', 'ofts-meta-nonce');

            if ($this->ofts_request_permission('1.4.9') == true) {

?>
<input type="checkbox" name="ofts-hide-featured-image" id="ofts-hide-featured-image" value="1" <?php checked(get_post_meta($post->ID, 'ofts_hide_featured_image', true), '1' ); ?> />
<label for="ofts-hide-featured-image"><?= __('Hide featured image', 'options-for-twenty-seventeen'); ?></label><?php

                if ($post->post_type == 'page') {

?>
<br />
<input type="checkbox" name="ofts-home-page-panels" id="ofts-home-page-panels" value="1" <?php checked(get_post_meta($post->ID, 'ofts_home_page_panels', true), '1' ); ?> />
<label for="ofts-home-page-panels"><?= __('Show child pages as panels', 'options-for-twenty-seventeen'); ?></label>

<?php

                }

?>
<br />
<input type="checkbox" name="ofts-hide-title" id="ofts-hide-title" value="1" <?php checked(get_post_meta($post->ID, 'ofts_hide_title', true), '1' ); ?> />
<label for="ofts-hide-title"><?= __('Hide title', 'options-for-twenty-seventeen'); ?></label>

<?php

            } else {

                if (!get_option('ofts_trial_date')) {

                    $upgrade_link .= ' or <a href="' . ofts_home_root() . 'wp-admin/plugins.php?ofts-start-trial=true" title="' . __('Start Free Trial', 'options-for-twenty-seventeen') . '">' . __('start a 7 day free trail', 'options-for-twenty-seventeen') . '</a>';

                }

?>

<p><?= $upgrade_link; ?> <?= __('to hide featured images on single posts and pages, show child pages as panels and hide titles.', 'options-for-twenty-seventeen'); ?></p>

<?php
            }

        }

        function ofts_save_meta($post_id, $post) {

            if (!isset( $_POST['ofts-meta-nonce'] ) || !wp_verify_nonce($_POST['ofts-meta-nonce'], 'options-for-twenty-seventeen')) {

                return;

            }

            $post_type = get_post_type_object( $post->post_type );

            if (!current_user_can($post_type->cap->edit_post, $post_id)) {

                return;

            }

            if (isset($_POST['ofts-hide-featured-image']) && $_POST['ofts-hide-featured-image'] == '1') {

                update_post_meta($post_id, 'ofts_hide_featured_image', 1);

            } else {

                delete_post_meta($post_id, 'ofts_hide_featured_image');

            }

            if (isset($_POST['ofts-home-page-panels']) && $_POST['ofts-home-page-panels'] == '1') {

                update_post_meta($post_id, 'ofts_home_page_panels', 1);

            } else {

                delete_post_meta($post_id, 'ofts_home_page_panels');

            }

            if (isset($_POST['ofts-hide-title']) && $_POST['ofts-hide-title'] == '1') {

                update_post_meta($post_id, 'ofts_hide_title', 1);

            } else {

                delete_post_meta($post_id, 'ofts_hide_title');

            }

        }

        function ofts_home_page_panels() {

            global $post;

            if (is_page() && get_post_meta($post->ID, 'ofts_home_page_panels', true) == '1') {

                $args = array(
                	'post_parent' => $post->ID,
                	'post_type'   => 'page',
                	'post_status' => 'publish',
                	'orderby'     => 'menu_order',
                    'order' => 'ASC'
                );
                $children = get_children($args);
                global $twentyseventeencounter;

    			foreach ($children as $child_id => $child) {

                    $post = get_post($child_id);
            		setup_postdata($post);
            		set_query_var('panel', $child_id);
            		$twentyseventeencounter = $child_id;
            		get_template_part( 'template-parts/page/content', 'front-page-panels' );

?>

<script type="text/javascript">
<?php

                    $panel_id = $this->ofts_check_static_front_page_id($post->post_name);

?>
    jQuery("#panel<?= $child_id; ?> .panel-content").attr('id', '<?= $panel_id; ?>');
<?php

                    if (get_post_meta($post->ID, 'ofts_hide_title', true) == '1') {

?>
    jQuery("#panel<?= $child_id; ?> .panel-content header h2").hide();
<?php

                    }

?>
</script>
<?php
            		wp_reset_postdata();

    			}

            }

        }

        function ofts_inject_excerpt_post_thumbnails($slug, $name) {

        	if ($name == 'excerpt') {

        		if ('' !== get_the_post_thumbnail() && !is_single()) {

?>
<div class="post-thumbnail-<?= get_the_ID(); ?> post-thumbnail">
	<a href="<?php the_permalink(); ?>">
		<?php the_post_thumbnail( 'twentyseventeen-featured-image' ); ?>
	</a>
</div><!-- .post-thumbnail -->
<script type="text/javascript">
	jQuery(document).ready(function() {         
		jQuery(".post-thumbnail-<?= get_the_ID(); ?>").insertAfter("article.post-<?= get_the_ID(); ?> header");
	});
</script>
<?php

        		}

        	}

        }

        function ofts_check() {

            if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']) {

                $http_url = 'https://webd.uk/activate/';
                $http_args = array(
                    'timeout' => 15,
                    'body' => array(
                        'url' => ((isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST']),
                        'plugin' => 'options-for-twenty-seventeen',
                        'check' => true
                    )
                );
                $request = wp_remote_post($http_url, $http_args);

                if (!is_wp_error($request) && isset($request['body']) && $request['body'] == 'True') {

                    update_option('ofts_check', $_SERVER['HTTP_HOST']);
                    wp_clear_scheduled_hook('ofts_check');

    			} elseif (!is_wp_error($request)) {

    				delete_option('ofts_purchased');
                    update_option('ofts_trial_date', time());
                    wp_clear_scheduled_hook('ofts_check');

    			}

            }

        }

	}

    if (get_template() == 'twentyseventeen') {

        add_action('customize_register', 'ofts_customize_register_control', 999);
	    $options_for_twenty_seventeen_object = new options_for_twenty_seventeen_class();

    } else {

        $themes = wp_get_themes();

        if (!$themes['twentyseventeen']) {

            add_action('admin_notices', 'ofts_wrong_theme_notice');

        }

        add_action('after_setup_theme', 'ofts_is_theme_being_previewed');

    }

    function ofts_wrong_theme_notice() {

?>

<div class="notice notice-error">

<p><strong><?php _e('Options for Twenty Seventeen Plugin Error', 'options-for-twenty-seventeen'); ?></strong><br />
<?php
        printf(
            __('This plugin requires the default Wordpress theme Twenty Seventeen to be active or live previewed in order to function. Your theme "%s" is not compatible.', 'options-for-twenty-seventeen'),
            get_template()
        );
?>

<a href="<?= ofts_home_root(); ?>wp-admin/theme-install.php?search=twentyseventeen" title="<?= __('Twenty Seventeen', 'options-for-twenty-seventeen'); ?>"><?php
        _e('Please install and activate or live preview the Twenty Seventeen theme (or a child theme thereof)', 'options-for-twenty-seventeen');
?></a>.</p>

</div>

<?php

    }

	function ofts_home_root() {

		$home_root = parse_url(home_url());

		if (isset($home_root['path'])) {

			$home_root = trailingslashit($home_root['path']);

		} else {

			$home_root = '/';

		}

		return $home_root;

	}

    function ofts_is_theme_being_previewed() {

        if (get_template() == 'twentyseventeen' && is_customize_preview()) {

            add_action('customize_register', 'ofts_customize_register_control', 999);
            $options_for_twenty_seventeen_object = new options_for_twenty_seventeen_class();

        }

    }

    function ofts_customize_register_control($wp_customize) {

        class ofts_WP_Customize_Notice_Control extends WP_Customize_Control {

            public $type = 'notice';
            public $description = '';
            public function render_content() {

                if ( !empty( $this->label ) ) {

?>
<div class="customize-control-title">
<?php echo esc_html( $this->label ); ?>
</div><?php

                }

?>
<div class="customize-notice-content">
<?php echo $this->description; ?>
</div>
<?php

            }

        }

    }

}

?>
