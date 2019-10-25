<?php





class DWQR_Admin
{

    public static $name = 'dwqr_pack';
    public static $optionName = 'dwqr_option';


    public static function cnf($key,$default=null){
        static $_option = array();
        if(!$_option){

            $_option = get_option(self::$optionName,array());
        }
        if(isset($_option[$key])){
            return $_option[$key];
        }

        return $default;

    }

	public function __construct(){

        if(is_admin()){

            //注册相关动作
            add_action( 'admin_menu', array($this,'admin_menu') );

            add_action( 'admin_init', array($this,'admin_init') );
            //插件设置连接
            add_filter( 'plugin_action_links', array($this,'actionLinks'), 10, 2 );

            add_action('admin_enqueue_scripts',array($this,'admin_enqueue_scripts'),1);

            add_filter('plugin_row_meta', array(__CLASS__, 'plugin_row_meta'), 10, 2);
        }


	}

	public function admin_enqueue_scripts($hook){
        global $wb_settings_page_hook_dwqr;
        if($wb_settings_page_hook_dwqr != $hook) return;

        wp_enqueue_style('wbs-style-dwqr', plugin_dir_url(DWQR_BASE_FILE) . 'assets/wbp_setting.css', array(), DWQR_VERSION);
    }

    public static function plugin_row_meta($links,$file){

        $base = plugin_basename(DWQR_BASE_FILE);
        if($file == $base) {
            $links[] = '<a href="https://www.wbolt.com/plugins/dwq" target="_blank">插件主页</a>';
            $links[] = '<a href="https://www.wbolt.com/dwq-plugin-documentation.html" target="_blank">FAQ</a>';
            $links[] = '<a href="https://wordpress.org/support/plugin/donate-with-qrcode/" target="_blank">反馈</a>';
        }
        return $links;
    }
	
	function actionLinks( $links, $file ) {
		
		if ( $file != plugin_basename(DWQR_BASE_FILE) )
			return $links;
	
		$settings_link = '<a href="'.menu_page_url( self::$name, false ).'">设置</a>';
	
		array_unshift( $links, $settings_link );
	
		return $links;
	}
	
	function admin_menu(){
		global $wb_settings_page_hook_dwqr;
		$wb_settings_page_hook_dwqr = add_options_page(
			'打赏/点赞/分享组件设置',
			'打赏/点赞/分享组件',
			'manage_options',
			self::$name,
			array($this,'admin_settings')
		);
	}


	public static function compat(){

        $opt = get_option(self::$optionName,array());

        if(!$opt){
            return;
        }

        if(isset($opt['items'])){
            return;
        }

        $opt['items'] = array();

        $items = array();
        if(isset($opt['paypal_url'])){
            $items['paypal'] = array(
                'name'=>'Paypal',
                'type'=>0,
                'url'=>$opt['paypal_url'],
                'img'=>'',
            );
        }
        if(isset($opt['wechat_qrcode'])){
            $items['weixin'] = array(
                'name'=>'微信',
                'type'=>1,
                'url'=>'',
                'img'=>$opt['wechat_qrcode'],
            );
        }

        if(isset($opt['alipay_qrcode'])){
            $items['alipay'] = array(
                'name'=>'支付宝',
                'type'=>1,
                'url'=>'',
                'img'=>$opt['alipay_qrcode'],
            );
        }

        if(isset($opt['wechat']) || isset($opt['alipay']) || isset($opt['paypal'])){
            $opt['switch'] = 1;
        }

        $opt['items'] = $items;

        update_option(self::$optionName,$opt);

    }

	function admin_settings(){

        self::compat();


		$setting_field = self::$optionName;
		$option_name = self::$optionName;
        $opt = get_option( $option_name ,array());




		
		include_once( DWQR_PATH.'/settings.php' );
	}

	function admin_init(){
		register_setting(  self::$optionName,self::$optionName );
	}
	
	
}