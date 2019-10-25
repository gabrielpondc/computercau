<?php
/*
Plugin Name: 打赏/点赞/分享组件
Plugin URI: http://wordpress.org/plugins/donate-with-qrcode/
Description: 打赏/点赞/分享组件是一款整合了网站打赏，文章点赞和文章社交分享功能插件。插件为读者提供点赞和分享功能，激励网站访客互动，提升WordPress博客文章传播；同时方便访客通过二维码打赏（捐赠）站长以鼓励站长继续创作贡献。
Author: wbolt team
Version: 1.1.0
Author URI: http://www.wbolt.com/
*/
define('DWQR_PATH',dirname(__FILE__));
define('DWQR_BASE_FILE',__FILE__);
define('DWQR_VERSION','1.1.0');

require_once DWQR_PATH.'/classes/admin.class.php';
require_once DWQR_PATH.'/classes/front.class.php';
new DWQR_Admin();
new DWQR_Front();
