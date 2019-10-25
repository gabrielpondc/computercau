<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$attdata = XH_Social_Temp_Helper::clear('atts','templete');
$atts = $attdata['atts'];
$api = XH_Social_Add_On_Login::instance();

if(is_user_logged_in()){
    if(method_exists(XH_Social::instance()->WP, 'wp_loggout_html')){
        echo XH_Social::instance()->WP->wp_loggout_html();
        return;
    }else{
        wp_logout();
    }
}
$findpassword_email_mode = $api->get_option('findpassword_email_mode','');
?>
<div class="xh-regbox">
	<div class="xh-title" id="form-title"><?php echo __('Find password',XH_SOCIAL)?></div>
	<form class="xh-form">
		<div class="findpassword fields-error"></div>
            <?php 
               $methods = $api->page_login_findpassword_methods(); 
               
               $method_options = array();
               foreach ($methods as $method=>$setting){
                   $method_options[$method]= $setting['title'];
               }
               
               if(count($method_options)==1){
                   foreach ($method_options as $key=>$op){
                       echo XH_Social_Helper_Html_Form::generate_hidden_html('resetpassword', 'method',array('default'=>$key));
                   }
               }else{
                   echo XH_Social_Helper_Html_Form::generate_select_html('resetpassword', 'method', array(
                       'title'=>__('method',XH_SOCIAL),
                       'type'=>'select',
                       'options'=>$method_options
                   ));
               }
               
               foreach ($methods as $method=>$setting){
                   ?>
                   <div id="wsocial-findpassword-method-<?php echo $method?>" class="wsocial-findpassword-method" data-submit="<?php echo isset($setting['submit'])?esc_attr($setting['submit']):''; ?>" style="display:none;">
                   		<?php echo XH_Social_Helper_Html_Form::generate_html('resetpassword',$setting['fields']);?>
                   </div>
                   <?php 
               }
            ?>
            <div class="xh-form-group mt10">
                <button type="button" id="btn-resetpassword" onclick="window.xh_social_view.resetpassword();" class="xh-btn xh-btn-primary xh-btn-block xh-btn-lg"><?php echo __('Reset password',XH_SOCIAL)?></button>
            </div>
	</form>
</div>
<?php echo XH_Social::instance()->WP->requires(XH_SOCIAL_DIR, '___.php');?>
<script type="text/javascript">
	(function($){
	    $(document).keypress(function(e) {
			if (e.which == 13){
			　　window.xh_social_view.resetpassword();
			}
		});

	    window.xh_social_view.resetpassword=function(){
	    	window.xh_social_view.reset();
			var data={};
			
			<?php XH_Social_Helper_Html_Form::generate_submit_data('resetpassword', 'data');?>		
			var pre_txt = $('#btn-resetpassword').text();
			$('#btn-resetpassword').attr('disabled','disabled').text('<?php print __('loading...',XH_SOCIAL)?>');		

			var callback = {
	            type:'resetpassword',
				done:false,
				data:data
    		};
    		$(document).trigger('wsocial_action_before',callback);
			if(callback.done){return;}

			if(window.xh_social_view.loading){
				return;
			}
			window.xh_social_view.loading=true;
			
			jQuery.ajax({
	            url: '<?php echo XH_Social::instance()->ajax_url(array('action'=>"xh_social_{$api->id}",'tab'=>'resetpassword'),true,true)?>',
	            type: 'post',
	            timeout: 60 * 1000,
	            async: true,
	            cache: false,
	            data: data,
	            dataType: 'json',
	            complete: function() {
	            	window.xh_social_view.loading=false;
	            	$('#btn-resetpassword').removeAttr('disabled').text(pre_txt);
	            },
	            success: function(m) {
	            	var callback = {
        	            type:'resetpassword',
        				done:false,
        				retry:window.xh_social_view.resetpassword,
        				data:m
            		};
            		$(document).trigger('wsocial_action_after',callback);
        			if(callback.done){return;}
	        			
	            	if(m.errcode==0){
		            	if($('#resetpassword_method').val()==='email'){
			            	<?php if($findpassword_email_mode=='code'){
			            	    ?>window.xh_social_view.success('登录密码已成功重置，请登录！','.findpassword');<?php
			            	}else{
			            	    ?> window.xh_social_view.success('重置密码的链接已发送到您的邮箱，请注意查收！','.findpassword');<?php
			            	}?>
		            	}else if($('#resetpassword_method').val()==='mobile'){
		            		 window.xh_social_view.success('登录密码已成功重置，请登录！','.findpassword');
			            }
			            
	            		setTimeout(function(){
	            			location.href='<?php echo wp_login_url()?>';
		            	},2500);
						return;
					}
	            	
	            	window.xh_social_view.error(m.errmsg,'.findpassword');
	            },
	            error:function(e){
	            	window.xh_social_view.error('<?php echo __('Internal Server Error!',XH_SOCIAL);?>','.findpassword');
	            	console.error(e.responseText);
	            }
	         });
		};
		
		window.xh_social_view.on_change_resetpassword_method_change=function(){
			$('.wsocial-findpassword-method').hide();
			var $selector =$('#wsocial-findpassword-method-'+$('#resetpassword_method').val());
			$selector.show();

			var txt_submit = $selector.attr('data-submit');
			if(txt_submit!=null&&txt_submit.length>0){
				$('#btn-resetpassword').text(txt_submit);
			}
		};
		
		window.xh_social_view.on_change_resetpassword_method_change();
		$('#resetpassword_method').change(function(){window.xh_social_view.on_change_resetpassword_method_change();});
	})(jQuery);
</script>