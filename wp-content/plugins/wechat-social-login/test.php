<?php
if(!is_user_logged_in()){
     //用户未登录：
    
     //获取当前页面url，方便用户登录后，会跳到当前页面
     $location_url =  ((! empty ( $_SERVER ['HTTPS'] ) && $_SERVER ['HTTPS'] !== 'off' || $_SERVER ['SERVER_PORT'] == 443) ? "https://" : "http://").$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
     /*
                     调用登陆插件弹窗，使用此代码
      <a href="javascript:void(0);" onclick="window.wsocial_dialog_login_show();" class="button  greenbtn">登录</a>
      */
     
    ?><a href="<?php echo esc_url(wp_login_url($location_url))?>">登录</a> | <a href="<?php echo esc_url(wp_registration_url())?>">注册</a><?php 
}else{
    //用户已登录：
    global $current_user;
    
    ?><a href="<?php echo esc_url(get_edit_profile_url());/*用户中心链接*/ ?>">
           <?php 
           //用户头像
            echo get_avatar(get_current_user_id(),50,'','',array(
           	    'class'=>'avatar'
           	));?> 
    
            <?php 
            //用户昵称
            echo esc_html($current_user->display_name);
            ?>
      </a><?php
}


?>
