<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!is_user_logged_in()){
	$loginActive = XH_Social::instance()->get_available_addon('add_ons_login');
	?>
	 <span class="xh-wechat-social-nav-login"><a <?php echo $loginActive? 'onclick="window.wsocial_dialog_login_show();"':'';?> href="<?php echo $loginActive?'javascript:void(0);':wp_login_url(XH_Social_Helper_Uri::get_location_uri())?>">登录</a></span>	
	<?php 
}else{
	global $current_user;
	?>
	 <span class="xh-wechat-social-nav-login"> <a href="<?php echo esc_url(get_edit_profile_url());/*用户中心链接*/ ?>" title="<?php echo esc_attr($current_user->display_name)?>">
	 <?php  echo get_avatar(get_current_user_id(),35,'','',array(
           	    'class'=>'xh-Avatar'
           	));?>
	 </a> <a href="<?php echo wp_logout_url(XH_Social_Helper_Uri::get_location_uri())?>">退出</a>  </span>
	<?php 
}
?>