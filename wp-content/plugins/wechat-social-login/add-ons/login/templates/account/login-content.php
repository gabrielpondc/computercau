<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$attdata = XH_Social_Temp_Helper::clear('atts','templete');
$atts = $attdata['atts'];
$api = XH_Social_Add_On_Login::instance();

$log_on_callback_uri=XH_Social::instance()->WP->get_log_on_backurl($attdata['atts'],true,true,true);

if(is_user_logged_in()){
    if(method_exists(XH_Social::instance()->WP, 'wp_loggout_html')){
        echo XH_Social::instance()->WP->wp_loggout_html($log_on_callback_uri);
        return;
    }else{
        wp_logout();
    }
}

?>
<script type="text/javascript">
window.__wsocial_enable_entrl_submit=true;
</script>
<div class="xh-regbox">
	<?php 
	 echo XH_Social::instance()->WP->requires($api->dir, 'account/__login.php',array(
	     'log_on_callback_uri'=>$log_on_callback_uri
	 ));
	?>
</div>

