<?php
if(defined('WSOCIAL_LOGIN_COMMON_SCRIPT')){
   return;
}
 define('WSOCIAL_LOGIN_COMMON_SCRIPT', true);
?>
<script type="text/javascript">
	(function($){
		if(!window.xh_social_view){
			window.xh_social_view={};
		}

		window.xh_social_view.reset=function(){
			$('.xh-alert').empty().css('display','none');
		};

		window.xh_social_view.error=function(msg,parent){
			var s = parent?(parent+'.fields-error'):'.fields-error';
			$(s).html('<div class="xh-alert xh-alert-danger" role="alert">'+msg+'</div>').css('display','block');
		};

		window.xh_social_view.warning=function(msg,parent){
			var s = parent?(parent+'.fields-error'):'.fields-error';
			$(s).html('<div class="xh-alert xh-alert-warning" role="alert">'+msg+'</div>').css('display','block');
		};

		window.xh_social_view.success=function(msg,parent){
			var s = parent?(parent+'.fields-error'):'.fields-error';
			$(s).html('<div class="xh-alert xh-alert-success" role="alert">'+msg+'</div>').css('display','block');
		};
	})(jQuery);
</script>