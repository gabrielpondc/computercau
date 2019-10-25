<?php 

if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class XH_Social_Widget_Share_Bar extends WP_Widget{
    function __construct() {
        parent::__construct(
            'xh_social_share_bar',
            '第三方分享条',
            array(
                'customize_selective_refresh'=>true,
                'description'=>'短码：[xh_social_share] php代码:<?php xh_social_share() ?>'
            )
        );
    }
    
    function widget( $args, $instance ) {
       xh_social_share(true);
    }
}
?>