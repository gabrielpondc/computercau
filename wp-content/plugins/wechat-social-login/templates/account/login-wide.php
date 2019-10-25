<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!is_user_logged_in()){
	$loginActive = XH_Social::instance()->get_available_addon('add_ons_login');
	?>
	<div class="xh-widget-title">用户您好！请先登录！</div>
    <div class="xh-wechat-social-widget">
          <a <?php echo $loginActive? 'onclick="window.wsocial_dialog_login_show();"':'';?> href="<?php echo $loginActive?'javascript:void(0);':wp_login_url(XH_Social_Helper_Uri::get_location_uri())?>" class="xh-btn xh-btn-default xh-sm">登录</a> <a href="<?php echo wp_registration_url()?>" class="xh-btn xh-btn-default xh-sm">注册</a>
	</div>
	<?php 
}else{
	global $current_user;
	?>
	 <div align="center" class="xh-wechat-social-widget"> 
	 	<a href="<?php echo esc_url(get_edit_profile_url());/*用户中心链接*/ ?>" title="<?php echo esc_attr($current_user->display_name)?>" ><?php echo get_avatar(get_current_user_id(),35,'','',array(
           	    'class'=>'xh-Avatar'
           	));?></a>
	 </div>
     <div class="xh-widget-title"><?php echo esc_html($current_user->display_name)?> 您好！</div>
     <div class="xh-wechat-social-widget">
		<a href="<?php echo esc_url(get_edit_profile_url()); ?>" class="xh-btn xh-btn-default xh-sm">个人中心</a> <a href="<?php echo wp_logout_url(XH_Social_Helper_Uri::get_location_uri())?>" class="xh-btn xh-btn-default xh-sm">退出登录</a>
	</div>
	<?php 
}
?>