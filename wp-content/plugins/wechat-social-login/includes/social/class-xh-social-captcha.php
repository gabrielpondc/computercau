<?php

class WSocial_Captcha extends Abstract_WSocial_Captcha{
    public $title="图形验证码";
    public $id = 'default';

    public function clear_captcha(){
        XH_Social::instance()->session->__unset('social_captcha');
    }

    //生成表单验证码字段
    public function create_captcha_form($form_id, $data_name, $settings){
        $html_name = $data_name;
        $html_id = isset($settings['id']) ? $settings['id'] : ($form_id . "_" . $data_name);

        ob_start();
        ?>
        <div class="xh-input-group" style="width:100%;">
            <input name="<?php echo esc_attr($html_name); ?>" type="text" id="<?php echo esc_attr($html_id); ?>"
                   maxlength="6" class="form-control"
                   placeholder="<?php echo __('image captcha', XH_SOCIAL) ?>">
            <span class="xh-input-group-btn" style="width:96px;"><img
                    style="width:96px;height:35px;border:1px solid #ddd;background:url('<?php echo XH_SOCIAL_URL ?>/assets/image/loading.gif') no-repeat center;"
                    id="img-captcha-<?php echo esc_attr($html_id); ?>"/></span>
        </div>

        <script type="text/javascript">
            (function ($) {
                if (!$) {
                    return;
                }

                window.captcha_<?php echo esc_attr($html_id);?>_load = function () {
                    $('#img-captcha-<?php echo esc_attr($html_id);?>').attr('src', '<?php echo XH_SOCIAL_URL?>/assets/image/empty.png');
                    $.ajax({
                        url: '<?php echo XH_Social::instance()->ajax_url(array(
                            'action' => 'xh_social_captcha',
                            'social_key' => $settings['social_key']
                        ), true, true)?>',
                        type: 'post',
                        timeout: 60 * 1000,
                        async: true,
                        cache: false,
                        data: {},
                        dataType: 'json',
                        success: function (m) {
                            if (m.errcode == 0) {
                                $('#img-captcha-<?php echo esc_attr($html_id);?>').attr('src', m.data);
                            }
                        }
                    });
                };

                $('#img-captcha-<?php echo esc_attr($html_id);?>').click(function () {
                    window.captcha_<?php echo esc_attr($html_id);?>_load();
                });

                window.captcha_<?php echo esc_attr($html_id);?>_load();
            })(jQuery);
        </script>
        <?php
        XH_Social_Helper_Html_Form::generate_field_scripts($form_id, $html_name, $html_id);
        return ob_get_clean();
    }

    //验证
    public function validate_captcha($name, $datas, $settings){
        //插件未启用，那么不验证图形验证码
        $code_post = isset($_REQUEST[$name]) ? trim($_REQUEST[$name]) : '';
        if (empty($code_post)) {
            return XH_Social_Error::error_custom(__('image captcha is required!', XH_SOCIAL));
        }

        $captcha = XH_Social::instance()->session->get($settings['social_key']);
        if (empty($captcha)) {
            return XH_Social_Error::error_custom(__('Please refresh the image captcha!', XH_SOCIAL));
        }

        if (strcasecmp($captcha, $code_post) !== 0) {
            return XH_Social_Error::error_custom(__('image captcha is invalid!', XH_SOCIAL));
        }

        XH_Social::instance()->session->__unset($settings['social_key']);

        return $datas;
    }
}