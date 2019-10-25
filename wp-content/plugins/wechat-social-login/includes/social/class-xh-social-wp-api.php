<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wordpress apis
 * 
 * @author rain
 * @since 1.0.0
 */
class XH_Social_WP_Api{
    /**
     * The single instance of the class.
     *
     * @since 1.0.0
     * @var XH_Social_WP_Api
     */
    private static $_instance = null;
    /**
     * Main Social Instance.
     *
     * Ensures only one instance of Social is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return XH_Social - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    
    private function __construct(){}
    
    public function convert_remoteimage_to_local($wp_user_id,$remote_image_url){
        if(!$remote_image_url){
            throw new Exception("读取远程文件失败：文件地址不能为空");
        }
       
        if(stripos($remote_image_url, '//')===0){
            $remote_image_url="https:{$remote_image_url}";
        }
        
        if(stripos($remote_image_url, 'http://')===0&&stripos($remote_image_url, 'https://')===0){
            throw new Exception("读取远程文件失败：文件地址类型异常，{$remote_image_url}");
        }
     
        $info = pathinfo($remote_image_url);
        $basename = $info&&is_array($info)&&isset($info['basename'])&&$info['basename']?$info['basename']:null;
        if(!$basename){
            throw new Exception("读取远程文件失败：文件名读取失败，{$remote_image_url}");
        }
        $basenames = explode('?', $basename);
        $basename = $basenames[0];
        if(empty($basename)){
            throw new Exception("读取远程文件失败：文件名信息异常，{$remote_image_url}");
        }
        
        $p = strpos($basename, '.');
        if($p!==false){
            $filekey ='/'. md5($remote_image_url).substr($basename, $p);
        }else{
            $filekey ='/'. md5($remote_image_url);
        }
        
        $social_img = get_user_meta($wp_user_id, '_social_img',true);
        if($social_img&&strpos($social_img, $filekey)){
            return $social_img;
        }
        
        $config = wp_get_upload_dir();
        $localdir = $config['path'];
         
        $localpath = $localdir.$filekey;
        
        $img = @file_get_contents($remote_image_url,false,stream_context_create( array(   
          'http'=>array(   
                'method'=>"GET",   
                'timeout'=>3,//单位秒  
           )   
        )));
        if(!$img){
            throw new Exception("读取远程文件失败：{$remote_image_url}");
        }
         
        if(!@file_put_contents($localpath, $img)){
            throw new Exception("文件写入失败：{$localpath}");
        }
        
        update_user_meta($wp_user_id, '_social_img',$config['url'].$filekey);
        return $config['url'].$filekey;
    }

    /**
     * @return $captchas[]
     */
    public function get_captchas() {
        return apply_filters('wsocil_captcha', array(
            new WSocial_Captcha()
        ));
    }

    /**
     * 
     * @return Abstract_WSocial_Captcha
     */
    public function get_captcha() {
        $captcha_id = XH_Social_Settings_Default_Other_Default::instance()->get_option('captcha');
        $captchas = $this->get_captchas();
        foreach ($captchas as $captcha){
            if($captcha->id==$captcha_id){
                return $captcha;
            }
        }
        return count($captchas)>0? $captchas[0]:null;
    }
    
    public function filter_display_name($nickname_or_loginname_or_displayname){
        $_return = $nickname_or_loginname_or_displayname;
        //如果是手机号，那么
        if(preg_match('/^\d{11}$/',$nickname_or_loginname_or_displayname)){
            //139****4325
            $_return= substr($nickname_or_loginname_or_displayname, 0,3)."****".substr($nickname_or_loginname_or_displayname, -4);
        }else if(is_email($nickname_or_loginname_or_displayname)&&strlen($nickname_or_loginname_or_displayname)>4){
            $index_of_at = strpos($nickname_or_loginname_or_displayname, '@');
            if($index_of_at!==false&&$index_of_at>1){
                //12@qq.com
                $length =$index_of_at-4;
                if($length<=0){$length=1;}
                if($length>3){$length=3;}
    
                $_return = substr( $nickname_or_loginname_or_displayname, 0,$length)."****".substr( $nickname_or_loginname_or_displayname, $index_of_at>7?7:$index_of_at);
            }
        }
    
        return apply_filters('wsocial_filter_display_name', $_return,$nickname_or_loginname_or_displayname);
    }
    /**
     * 判断当前用户是否允许操作
     * @param array $roles
     * @deprecated 1.0.0 Use get_user_by()       
     * @since 1.0.0
     */
    public function capability($roles=array('administrator')){
        global $current_user;
        if(!is_user_logged_in()){
        }
         
        if(!$current_user->roles||!is_array($current_user->roles)){
            $current_user->roles=array();
        }
         
        foreach ($roles as $role){
            if(in_array($role, $current_user->roles)){
                return true;
            }
        }
        return false;
    }
    
    public function get_log_on_backurl($atts = array(),$_get=true,$get_session=true,$set_session = false,$default = null){
        $log_on_callback_uri=$atts&&is_array($atts)&&isset($atts['redirect_to'])?esc_url_raw($atts['redirect_to']):null;
        if(empty($log_on_callback_uri)){
            if(!is_null($_get)&&$_get===true){
                $_get=$_GET;
            }
            if($_get&&is_array($_get)&&isset($_get['redirect_to'])){
                $log_on_callback_uri =esc_url_raw(urldecode($_get['redirect_to']));
            }
        }
        
        if($get_session&&empty($log_on_callback_uri)){
            $log_on_callback_uri = XH_Social::instance()->session->get('social_login_location_uri');
        }
        
        if(empty($default)){
            $default = home_url('/');
        }
        
        if(empty($log_on_callback_uri)){
            $log_on_callback_uri=$default;
        }
        
        if(strcasecmp(XH_Social_Helper_Uri::get_location_uri(), $log_on_callback_uri)===0){
            $log_on_callback_uri = $default;
        }
        
        $log_on_callback_uri =  apply_filters('wsocial_log_on_backurl', $log_on_callback_uri);
        if($set_session){
            XH_Social::instance()->session->set('social_login_location_uri',$log_on_callback_uri);
        }
        
        return $log_on_callback_uri;
    }
    
    /**
     * 根据昵称，创建user_login
     * @param string $nickname
     * @return string
     * @since 1.0.1
     */
    public function generate_user_login($nickname){
        $_nickname = $nickname;
        
        $nickname =  apply_filters('wsocial_user_login_pre',null, $_nickname);
        if(!empty($nickname)){
            return $nickname;
        }
        
        $nickname = sanitize_user(XH_Social_Helper_String::remove_emoji($nickname,false));
        if(empty($nickname)){
            $nickname = mb_substr(str_shuffle("abcdefghigklmnopqrstuvwxyz123456") ,0,4,'utf-8');
        }
        
        $nickname =  apply_filters('wsocial_user_login_sanitize', $nickname,$_nickname);
        if(mb_strlen($nickname)>32){
            $nickname = mb_substr($nickname, 0,32,'utf-8');
        }
        
        $pre_nickname =$nickname;
    
        $index=0;
        while (username_exists($nickname)){
            $index++;
            if($index==1){
                $nickname=$pre_nickname.'_'.time();//年+一年中的第N天
                continue;
            }
            
            //加随机数
            $nickname.=mt_rand(1000, 9999);
            if(strlen($nickname)>60){
                $nickname = $pre_nickname.'_'.time();
            }
            
            //尝试次数过多
            if($index>5){
                $nickname = XH_Social_Helper_String::guid();
                break;
            }
        }
    
        return apply_filters('wsocial_user_login', $nickname,$_nickname);  
    }

    public function get_plugin_settings_url(){
        return admin_url('admin.php?page=social_page_add_ons');
    }
    
    /**
     * @since 1.0.9
     * @param array $request
     * @param bool $validate_notice
     * @return bool
     */
    public function ajax_validate(array $request,$hash,$validate_notice = true){
        if(XH_Social_Helper::generate_hash($request, XH_Social::instance()->get_hash_key())!=$hash){
            return false;
        }
       
        return true;
    }
    
    /**
     * 设置错误
     * @param string $key
     * @param string $error
     * @since 1.0.5
     */
    public function set_wp_error($key,$error){
        XH_Social::instance()->session->set("error_{$key}", $error);
    }
    
    /**
     * 清除错误
     * @param string $key
     * @param string $error
     * @since 1.0.5
     */
    public function unset_wp_error($key){
        XH_Social::instance()->session->__unset("error_{$key}");
    }
    
    /**
     * 获取错误
     * @param string $key
     * @param string $error
     * @since 1.0.5
     */
    public function get_wp_error($key,$clear=true){
        $cache_key ="error_{$key}";
        $session =XH_Social::instance()->session;
        $error = $session->get($cache_key);
        if($clear){
            $this->unset_wp_error($key);
        }
        return $error;
    }
    
    /**
     * @since 1.0.7
     * @param string $log_on_callback_uri
     * @return string
     */
    public function wp_loggout_html($log_on_callback_uri=null,$include_css=false,$include_header_footer=false,$include_html=false){
        XH_Social_Temp_Helper::set('atts', array(
            'log_on_callback_uri'=>$log_on_callback_uri,
            'include_css'=>$include_css,
            'include_header_footer'=>$include_header_footer,
            'include_html'=>$include_html
        ),'templete');
        
        ob_start();
        require XH_Social::instance()->WP->get_template(XH_SOCIAL_DIR, 'account/logout-content.php');
     
        return ob_get_clean();
    }
    
    /**
     * wp die
     * @param Exception|XH_Social_Error|WP_Error|string|object $err
     * @since 1.0.0
     */
    public function wp_die($err=null,$include_header_footer=true,$exit=true){
        XH_Social_Temp_Helper::set('atts', array(
            'err'=>$err,
            'include_header_footer'=>$include_header_footer
        ),'templete');
        
        ob_start();
        require XH_Social::instance()->WP->get_template(XH_SOCIAL_DIR, 'wp-die.php');
        echo ob_get_clean();
        if($exit){
        exit;
        }
    }
    
    /**
     * 返回登录/注册/找回密码/完善资料等页面
     * 特点：1.不需要登录检查
     *      2.登录成功后的跳转不能回到这些页面
     * 
     * @since 1.2.4
     * @return int[]
     */
    public function get_unsafety_pages(){
        return apply_filters('wsocial_unsafety_pages', array());
    }
    
    public function get_safety_authorize_redirect_page(){
        //在template_redirect之前调用的当前方法
        if(!function_exists('get_the_ID')){
            return home_url('/');
        }
        
        //当前不是页面
        $current_post_id = get_the_ID();
        if(!$current_post_id){
            return home_url('/');
        }
        
        $unsafety_pages = $this->get_unsafety_pages();
        if(in_array($current_post_id, $unsafety_pages)){
            return home_url('/');
        }
        
        return XH_Social_Helper_Uri::get_location_uri();
    }
    
    /**
     * 执行登录操作
     * @param WP_User $wp_user
     * @return XH_Social_Error
     * @since 1.0.0
     */
    public function do_wp_login($wp_user,$remember=true){
        XH_Social::instance()->session->__unset('social_login_location_uri');
        
        $user = apply_filters( 'authenticate', $wp_user, $wp_user->user_login, null );
        if(is_wp_error($user)){
            return XH_Social_Error::wp_error($user);    
        }
        
        $secure_cookie='';
        if ( get_user_option('use_ssl', $wp_user->ID) ) {
            $secure_cookie = true;
            force_ssl_admin(true);
        }
    
        wp_set_auth_cookie($wp_user->ID, $remember, $secure_cookie);
        /**
         * Fires after the user has successfully logged in.
         *
         * @since 1.5.0
         *
         * @param string  $user_login Username.
         * @param WP_User $user       WP_User object of the logged-in user.
         */
        do_action( 'wp_login', $wp_user->user_login, $wp_user );
        
        return XH_Social_Error::success();
    }
    
    public function clear_captcha(){
        $captcha = $this->get_captcha();
        if($captcha){
            $captcha->clear_captcha();
        }
    }

    const FIELD_CAPTCHA_NAME ='captcha';
    /**
     * 获取图片验证字段
     * @return array
     * @since 1.0.0
     */
    public function get_captcha_fields($social_key = 'social_captcha') {
        $captcha = $this->get_captcha();
        if(!$captcha) return [];
        $fields[self::FIELD_CAPTCHA_NAME] = array(
            'social_key' => $social_key,
            'type' => array($captcha,'create_captcha_form'),
            'validate'=>array($captcha,'validate_captcha')
        );

        return apply_filters('xh_social_captcha_fields', $fields);
    }

    /**
     * 获取插件列表
     * @return NULL|Abstract_XH_Social_Add_Ons[]
     */
    public function get_plugin_list_from_system(){
        $base_dirs = XH_Social::instance()->plugins_dir;
        
        $plugins = array();
        $include_files = array();
        
        foreach ($base_dirs as $base_dir){
            try {
                if(!is_dir($base_dir)){
                    continue;
                }
        
                $handle = opendir($base_dir);
                if(!$handle){
                    continue;
                }
                
                try {
                    while(($file = readdir($handle)) !== false){
                        if(empty($file)||$file=='.'||$file=='..'||$file=='index.php'){
                            continue;
                        }
        
                        if(in_array($file, $include_files)){
                            continue;
                        }
                        //排除多个插件目录相同插件重复includ的错误
                        $include_files[]=$file;
                        
                        try {
                            if(strpos($file, '.')!==false){
                                if(stripos($file, '.php')===strlen($file)-4){
                                    $file=str_replace("\\", "/",$base_dir.$file);
                                }
                            }else{
                                $file=str_replace("\\", "/",$base_dir.$file."/init.php");
                            }
        
                            
                            if(file_exists($file)){
                                $add_on=null;
                                if(isset(XH_Social::instance()->plugins[$file])){
                                    //已安装
                                    $add_on=XH_Social::instance()->plugins[$file];
                                }else{
                                    //未安装
                                    $add_on = require_once $file;
                                   
                                    if($add_on&&$add_on instanceof Abstract_XH_Social_Add_Ons){
                                        $add_on->is_active=false;
                                        XH_Social::instance()->plugins[$file]=$add_on;
                                    }else{
                        	            $add_on=null;
                        	        }
                                } 
                               
                                if($add_on){
                                    $plugins[$file]=$add_on;
                                }
                            }
        
                        } catch (Exception $e) {
                        }
                    }
                } catch (Exception $e) {
                }
        
                closedir($handle);
            } catch (Exception $e) {
                
            }
        }
  
        $results = array();
        $plugin_ids=array();
        foreach ($plugins as $file=>$plugin){
            if(in_array($plugin->id, $plugin_ids)){
                continue;
            }
            
            $results[$file]=$plugin;
        }
        
        return $results;
    }

    /**
     *
     * @param string $page_templete_dir
     * @param string $page_templete
     * @return string
     * @since 1.0.8
     */
    public function get_template($page_templete_dir,$page_templete){
        if(strpos($page_templete, 'social/')===0){
            $ltemplete=substr($page_templete, 7);
        }
        
        if(file_exists(STYLESHEETPATH.'/social/'.$page_templete)){
            return STYLESHEETPATH.'/social/'.$page_templete;
        }
        
        if(file_exists(STYLESHEETPATH.'/wechat-social-login/'.$page_templete)){
            return STYLESHEETPATH.'/wechat-social-login/'.$page_templete;
        }
    
        return apply_filters('wsocial_get_template', $page_templete_dir . '/templates/' . $page_templete,$page_templete_dir, $page_templete);
    }
    
    /**
     *
     * @param string $dir
     * @param string $templete_name
     * @param mixed $params
     * @return string
     */
    public function requires($dir, $templete_name, $params = null,$require=false)
    {
        if (! is_null($params)) {
            XH_Social_Temp_Helper::set('atts', $params, 'templates');
        }
        $dir =apply_filters('wsocial_require_dir', $dir,$templete_name);
        
        if($require){
            return require $this->get_template($dir, $templete_name);
        }else{
            ob_start();
            require $this->get_template($dir, $templete_name);
            return ob_get_clean();
        }
    }
}