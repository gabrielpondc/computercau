<?php
abstract class Abstract_WSocial_Captcha{
    public $title;
    public $id;

    abstract function clear_captcha();
    abstract function create_captcha_form($form_id, $data_name, $settings);
    abstract function validate_captcha($name, $datas, $settings);
}
