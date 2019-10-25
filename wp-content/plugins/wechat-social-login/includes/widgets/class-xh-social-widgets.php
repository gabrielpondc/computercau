<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class XH_Social_widgets{
    public static function init(){
        require_once 'class-xh-social-widget-login-wide.php';
        require_once 'class-xh-social-widget-login-short.php';
        require_once 'class-xh-social-widget-login-bar.php';
        require_once 'class-xh-social-widget-share-bar.php';
        register_widget('XH_Social_Widget_Login_Wide');
        register_widget('XH_Social_Widget_Login_Short');
        register_widget('XH_Social_Widget_Share_Bar');
        register_widget('XH_Social_Widget_Login_Bar');
        
    }
}