<?php 

if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class XH_Social_Widget_Login_Wide extends WP_Widget{
    function __construct() {
        parent::__construct(
            'xh_social_login_wide',
            '登录按钮(侧边栏)',
            array(
                'customize_selective_refresh'=>true,
                'description'=>"短码：[xh_social_login_wide] php代码:<?php xh_social_login_wide() ?>"
            )
        );
    }
   
    function widget( $args, $instance ) {
       xh_social_login_wide();
    }
}
?>