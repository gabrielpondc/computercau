<?php 

if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class XH_Social_Widget_Login_Bar extends WP_Widget{
    function __construct() {
        parent::__construct(
            'xh_social_login_bar',
            '第三方登录条',
            array(
                'customize_selective_refresh'=>true,
                'description'=>'短码：[xh_social_loginbar] php代码:<?php xh_social_loginbar() ?>'
            )
        );
    }
    
    function widget( $args, $instance ) {
       xh_social_loginbar(XH_Social_Helper_Uri::get_location_uri(),true);
    }
}
?>