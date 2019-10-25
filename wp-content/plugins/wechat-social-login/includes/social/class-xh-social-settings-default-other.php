<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

/**
 * Social Admin
 *
 * @since 1.0.0
 * @author ranj
 */
class XH_Social_Settings_Default_Other_Default extends Abstract_XH_Social_Settings{
    /**
     * Instance
     * @since  1.0.0
     */
    private static $_instance;

    /**
     * Instance
     * @since  1.0.0
     */
    public static function instance() {
        if ( is_null( self::$_instance ) )
            self::$_instance = new self();
        return self::$_instance;
    }

    private function __construct(){
        $this->id='settings_default_other_default';
        $this->title=__('Basic Settings',XH_SOCIAL);

        $this->init_form_fields();
    }

    public function init_form_fields(){
        $this->form_fields =array(
            'logo'=>array(
                'title'=>__('Website Logo',XH_SOCIAL),
                'type'=>'image',
                'default'=>XH_SOCIAL_URL.'/assets/image/wordpress-logo.png'
            ),
            'bingbg'=>array(
                'title'=>'调用Bing背景作为登录页背景',
                'type'=>'checkbox'
            ),
            'enable_emoji_filter'=>array(
                'title'=>'昵称表情符(emoji)',
                'type'=>'checkbox',
                'label'=>'过滤',
                'default'=>'yes',
                'description'=>'<b style="color:red;">注意</b>: 如需保留表情符，条件：mysql(5.5.3+)utf8mb4'
            ),
            'captcha'=>array(
                'title'=>'验证码模式',
                'type'=>'select',
                'func'=>true,
                'default'=>'default',
                'options'=>function(){
                    $captchats =  XH_Social::instance()->WP->get_captchas();
                    $options = array();
                    foreach ($captchats as $captcha){
                        $options[$captcha->id] = $captcha->title;
                    };
                    return $options;
                },
            )
        );
    }
}


?>