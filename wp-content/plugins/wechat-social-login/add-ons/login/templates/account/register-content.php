<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$attdata = XH_Social_Temp_Helper::clear('atts','templete');
$api = XH_Social_Add_On_Login::instance();

$log_on_callback_uri=XH_Social::instance()->WP->get_log_on_backurl($attdata['atts'],true,true,true);

do_action('xh_social_page_register_before');

$action = apply_filters('xh_social_page_register_before', null);
if(!empty($action)){
    echo $action;
    return;
}

if(!get_option('users_can_register')){
    ?>
    <div class="xh-regbox">
    <div class="xh-title" id="form-title"><?php echo __('Register',XH_SOCIAL)?></div>
    	<div class="xh-form">  
    		<div class="xh-alert xh-alert-danger">对不起，管理员已关闭了网站注册！</div>     
    		 <div class="xh-form-group mt10">
                   <a href="<?php echo home_url('/')?>" class="xh-btn xh-btn-primary xh-btn-block xh-btn-lg">返回首页</a>
               </div>
    	</div>
    </div>
    <?php 
    return;
}

if(is_user_logged_in()){
    if(method_exists(XH_Social::instance()->WP, 'wp_loggout_html')){
        echo XH_Social::instance()->WP->wp_loggout_html($log_on_callback_uri);
        return;
    }else{
        wp_logout();
    }
}
?>
<div class="xh-regbox">
	<div class="xh-title" id="form-title"><?php echo __('Register',XH_SOCIAL)?></div>
	<form class="xh-form">
		<div class="register fields-error"></div>
       <?php 
           $fields = $api->page_login_register_fields();
           echo XH_Social_Helper_Html_Form::generate_html('register',$fields);
           do_action('xh_social_page_login_register_form');
       ?>
       <div class="xh-form-group mt10">
           <button type="button" id="btn-register" onclick="window.xh_social_view.register();" class="xh-btn xh-btn-primary xh-btn-block xh-btn-lg"><?php echo __('Log In',XH_SOCIAL)?></button>
       </div>
       <?php 
    	$channels = XH_Social::instance()->channel->get_social_channels(array('login'));
    	$news = array();
    	foreach ($channels as $channel){
    	    if(!apply_filters('wsocial_channel_login_enabled', true,$channel)){
    	        continue;
    	    }
    	    $news[]=$channel;
    	}
    	if(count($news)>0){
    	    ?>
    	    <div class="xh-form-group xh-mT20">
                <label><?php echo __('Quick Login',XH_SOCIAL)?></label>
               <div class="xh-social">
                   <?php foreach ($news as $channel){
                       ?><a title="<?php echo esc_attr($channel->title)?>" href="<?php echo XH_Social::instance()->channel->get_authorization_redirect_uri($channel->id,$log_on_callback_uri);?>" class="xh-social-item <?php echo $channel->svg?>"></a><?php 
                   }?>
               </div>
            </div>
    	    <?php 
    	}
    	?>
	</form>
</div>
<?php echo XH_Social::instance()->WP->requires(XH_SOCIAL_DIR, '___.php');?>
<script type="text/javascript">
	(function($){
		$(document).keypress(function(e) {
			if (e.which == 13){
			　　window.xh_social_view.register();
			}
		});

		window.xh_social_view.register=function(){
			window.xh_social_view.reset();
			
			var data={};
			<?php XH_Social_Helper_Html_Form::generate_submit_data('register', 'data');?>

			var validate = {
				data:data,
				success:true,
				message:null
			};
			
			$(document).trigger('wsocial_pre_register',validate);
			if(!validate.success){
				window.xh_social_view.warning(validate.message,'.register');
				return false;
			}

			var callback = {
	            type:'register',
				done:false,
				data:data
    		};
    		$(document).trigger('wsocial_action_before',callback);
			if(callback.done){return;}

			if(window.xh_social_view.loading){
				return;
			}
			window.xh_social_view.loading=true;
			
			$('#btn-register').attr('disabled','disabled').text('<?php print __('loading...',XH_SOCIAL)?>');
		
			jQuery.ajax({
	            url: '<?php echo XH_Social::instance()->ajax_url(array('action'=>"xh_social_{$api->id}",'tab'=>'register'),true,true)?>',
	            type: 'post',
	            timeout: 60 * 1000,
	            async: true,
	            cache: false,
	            data: data,
	            dataType: 'json',
	            complete: function() {
	            	window.xh_social_view.loading=false;
	            	$('#btn-register').removeAttr('disabled').text('<?php print __('Log In',XH_SOCIAL)?>');
	            },
	            success: function(m) {
	            	var callback = {
	    	            type:'register',
        				done:false,
        				retry:window.xh_social_view.register,
        				data:m
	        		};
	        		$(document).trigger('wsocial_action_after',callback);
					if(callback.done){return;}
	        			
	            	if(m.errcode==0){
	            		window.xh_social_view.success('<?php print __('Registered successfully!',XH_SOCIAL);?>','.register');
						location.href='<?php echo $log_on_callback_uri?>';
						return;
					}

					if(m.errcode==1001){
						window.xh_social_view.warning(m.errmsg,'.register');
						location.href='<?php echo admin_url('/')?>';
						return;
					}
	            	
	            	window.xh_social_view.error(m.errmsg);
	            },
	            error:function(e){
	            	window.xh_social_view.error('<?php print __('Internal Server Error!',XH_SOCIAL);?>','.register');
	            	console.error(e.responseText);
	            }
	         });
		};
		
	})(jQuery);
</script>