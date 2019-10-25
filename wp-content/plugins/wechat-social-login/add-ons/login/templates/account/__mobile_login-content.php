<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$attdata = XH_Social_Temp_Helper::clear('atts','templete');

$api = XH_Social_Add_On_Social_Mobile::instance();
$log_on_callback_uri=XH_Social::instance()->WP->get_log_on_backurl($attdata['atts'],true,true,true);
$wp_user_id=0;
if(isset($_GET['uid'])&&isset($_GET['hash'])){
    $params = array(
        'uid'=>$_GET['uid'],
        'notice_str'=>isset($_GET['notice_str'])?$_GET['notice_str']:''
    );

    $hash =XH_Social_Helper::generate_hash($params, XH_Social::instance()->get_hash_key());
    if($hash==$_GET['hash']){
        $wp_user_id = $_GET['uid'];
    }
}

if(
    //wp_user_id>0 且登录用户id不等于wp_user_id
    ($wp_user_id>0&&is_user_logged_in()&&$wp_user_id!=get_current_user_id())
    ||
    //已登录的情况
    $wp_user_id<=0&&is_user_logged_in()
    ){
    
    if(method_exists(XH_Social::instance()->WP, 'wp_loggout_html')) {
        echo XH_Social::instance()->WP->wp_loggout_html($log_on_callback_uri);
    }else{
        wp_logout();
    }
    return;
}
$fields = null;
try {
    $fields = $api->page_mobile_login_fields(false);
} catch (Exception $e) {
    XH_Social::instance()->WP->wp_die($e->getMessage(),false,false);
    return;
}
?>
<div class="xh-title"><?php echo __('Login',XH_SOCIAL)?></div>
	<form class="xh-form">
		<div id="fields-error"></div>
			<?php 
               echo XH_Social_Helper_Html_Form::generate_html('login',$fields);
               do_action('xh_social_page_mobile_login_form');
            ?>
            <div class="xh-form-group mt10">
                <button type="button" id="btn-login" onclick="window.xh_social_view.login();" class="xh-btn xh-btn-primary xh-btn-block xh-btn-lg">
                <?php if(is_user_logged_in()){
                    echo __('Bind',XH_SOCIAL);
                }else{
                    echo __('Log On',XH_SOCIAL);
                }?>
                </button>
            </div>
            <?php 
        	$channels = XH_Social::instance()->channel->get_social_channels(array('login'));
        	if(count($channels)>0){
        	    ?>
        	    <div class="xh-form-group xh-mT20">
                    <label><?php echo __('Quick Login',XH_SOCIAL)?></label>
                   <div class="xh-social">
                       <?php foreach ($channels as $channel){
                           if($channel->id=='social_mobile'){continue;}
                           ?><a title="<?php echo esc_attr($channel->title)?>" href="<?php echo XH_Social::instance()->channel->get_authorization_redirect_uri($channel->id,$log_on_callback_uri);?>" class="xh-social-item" style="background:url(<?php echo $channel->icon?>) no-repeat transparent;"></a><?php 
                       }?>
                   </div>
                </div>
        	    <?php 
        	}
        	?>
	</form>
<script type="text/javascript">
	(function($){
		$(document).keypress(function(e) {
			if (e.which == 13){
			　　window.xh_social_view.login();
			}
		});
		window.xh_social_view={
			loading:false,
			reset:function(){
				$('.xh-alert').empty().css('display','none');
			},
			error:function(msg){
				$('#fields-error').html('<div class="xh-alert xh-alert-danger" role="alert">'+msg+' </div>').css('display','block');
			},
			success:function(msg){
				$('#fields-error').html('<div class="xh-alert xh-alert-success" role="alert">'+msg+' </div>').css('display','block');
			},
			login:function(){
				this.reset();
				<?php 
				$data = array(
				    'notice_str'=>str_shuffle(time()),
				    'action'=>"xh_social_{$api->id}",
				    'uid'=>$wp_user_id,
				    "xh_social_{$api->id}"=>wp_create_nonce("xh_social_{$api->id}"),
				    'tab'=>'login'
				);
				
				$data['hash']= XH_Social_Helper::generate_hash($data,XH_Social::instance()->get_hash_key());
				?>
				var data=<?php echo json_encode($data);?>;
				<?php XH_Social_Helper_Html_Form::generate_submit_data('login', 'data');?>
				if(this.loading){
					return;
				}
				
				$('#btn-login').attr('disabled','disabled').text('<?php print __('loading...',XH_SOCIAL)?>');
				this.loading=true;

				jQuery.ajax({
		            url: '<?php echo XH_Social::instance()->ajax_url()?>',
		            type: 'post',
		            timeout: 60 * 1000,
		            async: true,
		            cache: false,
		            data: data,
		            dataType: 'json',
		            complete: function() {
		            	$('#btn-login').removeAttr('disabled').text('<?php print __('Log On',XH_SOCIAL)?>');
		            	window.xh_social_view.loading=false;
		            },
		            success: function(m) {
		            	if(m.errcode==405||m.errcode==0){
			            	<?php if(is_user_logged_in()){
			            	    ?>window.xh_social_view.success('<?php print __('Binded successfully!',XH_SOCIAL);?>');<?php 
			            	}else{
			            	    ?>window.xh_social_view.success('<?php print __('Log on successfully!',XH_SOCIAL);?>');<?php 
			            	}?>
		            		
		            		location.href=m.data;
							return;
						}

		            	console.error('errcode:'+m.errcode+',errmsg:'+m.errmsg);
		            	window.xh_social_view.error(m.errmsg);
		            },
		            error:function(e){
		            	window.xh_social_view.error('<?php print __('Internal Server Error!',XH_SOCIAL);?>');
		            	console.error(e.responseText);
		            }
		         });
			}
		};
	})(jQuery);
</script>