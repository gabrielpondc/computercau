<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$channels = XH_Social::instance()->channel->get_social_channels(array('login'));
global $current_user;
if(!is_user_logged_in()){
    return;
}
if(count($channels)==0){
    return;
}
?>
<div class="xh-regbox" style="width: 100%;">
  <h4 class="xh-title" style="margin-bottom:40px"><?php echo __('Account Binding/Unbundling',XH_SOCIAL)?></h4> 
  <div class="xh-form ">
  <?php if($channels){
            $disable_wechat =XH_Social_Helper_Uri::is_app_client()&& !XH_Social_Helper_Uri::is_wechat_app();
		    foreach ($channels as $channel){
		        if($disable_wechat&&$channel->id==='add_ons_social_wechat'){
		            continue;
		        }
		        ?>
                <div class="xh-form-group xh-mT20  xh-social xh-clearfix" style="display:flex;flex-direction:row;align-items:center;">
                     <span class="xh-text" style="width:20%;display:flex;flex-direction:row;justify-content:flex-start;"><a href="javascript:void(0);" rel="noflow"  class="xh-social-item <?php echo $channel->svg?>" title="<?php echo esc_attr($channel->title);?>"></a> <?php echo $channel->title?></span>
                     <span style="display:flex;flex-direction:row;justify-content:flex-end;width:80%;"><?php echo $channel->bindinfo($current_user->ID)?></span>
                </div>
                <hr/>
      <?php }
  }?>    
  </div>
</div>
<?php 