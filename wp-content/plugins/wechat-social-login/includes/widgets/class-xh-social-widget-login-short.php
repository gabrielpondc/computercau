<?php 

if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class XH_Social_Widget_Login_Short extends WP_Widget{
    function __construct() {
        parent::__construct(
            'xh_social_login_short',
            '登录按钮(导航条)',
            array(
                'customize_selective_refresh'=>true,
                'description'=>'短码：[xh_social_login_short] php代码:<?php xh_social_login_short() ?>'
            )
        );
    }
   
    function widget( $args, $instance ) {
       xh_social_login_short();
    }
}
?>