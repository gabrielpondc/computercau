<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$data = XH_Social_Temp_Helper::clear('atts','templates');
$redirect=$data['redirect'];
$channels =XH_Social::instance()->channel->get_social_channels(array('login'));    
?>
<div class="xh-social" style="clear:both;">
   <?php 
    foreach ($channels as $channel){
        if(!apply_filters('wsocial_channel_login_enabled', true,$channel)){
            continue;
        }
        ?>
        <a title="<?php echo esc_attr($channel->title)?>" href="<?php echo XH_Social::instance()->channel->get_authorization_redirect_uri($channel->id);?>" class="xh-social-item <?php echo $channel->svg?>" rel="noflow"></a>
        <?php 
    }?>
</div><?php 