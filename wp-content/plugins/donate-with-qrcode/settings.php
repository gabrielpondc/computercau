<?php
/**
 * This was contained in an addon until version 1.0.0 when it was rolled into
 * core.
 *
 * @package    WBOLT
 * @author     WBOLT
 * @since      1.1.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2019, WBOLT
 */

$pd_title = '打赏/点赞/分享组件';
$pd_version = DWQR_VERSION;
$pd_code = 'dwq-setting';
$pd_index_url = 'https://www.wbolt.com/plugins/dwq';
$pd_doc_url = 'https://www.wbolt.com/dwq-plugin-documentation.html';


wp_enqueue_script('wbp-js', plugin_dir_url(DWQR_BASE_FILE) . 'assets/wbp_setting.js', array(), DLIPP_VERSION, true);
wp_enqueue_media();

echo "<link rel='stylesheet' id='wbs-style-dwqr-css'  href='".plugin_dir_url(DWQR_BASE_FILE) . "assets/wbp_donate.css' type='text/css' media='all' />";
?>

<div style=" display:none;">
    <svg aria-hidden="true" style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <defs>
            <symbol id="sico-upload" viewBox="0 0 16 13">
                <path d="M9 8v3H7V8H4l4-4 4 4H9zm4-2.9V5a5 5 0 0 0-5-5 4.9 4.9 0 0 0-4.9 4.3A4.4 4.4 0 0 0 0 8.5C0 11 2 13 4.5 13H12a4 4 0 0 0 1-7.9z" fill="#666" fill-rule="evenodd"/>
            </symbol>
            <symbol id="sico-download" viewBox="0 0 16 16">
                <path d="M9 9V0H7v9H4l4 4 4-4z"/><path d="M15 16H1a1 1 0 0 1-1-1.1l1-8c0-.5.5-.9 1-.9h3v2H2.9L2 14H14L13 8H11V6h3c.5 0 1 .4 1 .9l1 8a1 1 0 0 1-1 1.1"/>
            </symbol>
            <symbol id="sico-wb-logo" viewBox="0 0 18 18">
                <title>sico-wb-logo</title>
                <path d="M7.264 10.8l-2.764-0.964c-0.101-0.036-0.172-0.131-0.172-0.243 0-0.053 0.016-0.103 0.044-0.144l-0.001 0.001 6.686-8.55c0.129-0.129 0-0.321-0.129-0.386-0.631-0.163-1.355-0.256-2.102-0.256-2.451 0-4.666 1.009-6.254 2.633l-0.002 0.002c-0.791 0.774-1.439 1.691-1.905 2.708l-0.023 0.057c-0.407 0.95-0.644 2.056-0.644 3.217 0 0.044 0 0.089 0.001 0.133l-0-0.007c0 1.221 0.257 2.314 0.643 3.407 0.872 1.906 2.324 3.42 4.128 4.348l0.051 0.024c0.129 0.064 0.257 0 0.321-0.129l2.25-5.593c0.064-0.129 0-0.257-0.129-0.321z"></path>
                <path d="M16.714 5.914c-0.841-1.851-2.249-3.322-4.001-4.22l-0.049-0.023c-0.040-0.027-0.090-0.043-0.143-0.043-0.112 0-0.206 0.071-0.242 0.17l-0.001 0.002-2.507 5.914c0 0.129 0 0.257 0.129 0.321l2.571 1.286c0.129 0.064 0.129 0.257 0 0.386l-5.979 7.264c-0.129 0.129 0 0.321 0.129 0.386 0.618 0.15 1.327 0.236 2.056 0.236 2.418 0 4.615-0.947 6.24-2.49l-0.004 0.004c0.771-0.771 1.414-1.671 1.929-2.7 0.45-1.029 0.643-2.121 0.643-3.279s-0.193-2.314-0.643-3.279z"></path>
            </symbol>
            <symbol id="sico-more" viewBox="0 0 16 16">
                <path d="M6 0H1C.4 0 0 .4 0 1v5c0 .6.4 1 1 1h5c.6 0 1-.4 1-1V1c0-.6-.4-1-1-1M15 0h-5c-.6 0-1 .4-1 1v5c0 .6.4 1 1 1h5c.6 0 1-.4 1-1V1c0-.6-.4-1-1-1M6 9H1c-.6 0-1 .4-1 1v5c0 .6.4 1 1 1h5c.6 0 1-.4 1-1v-5c0-.6-.4-1-1-1M15 9h-5c-.6 0-1 .4-1 1v5c0 .6.4 1 1 1h5c.6 0 1-.4 1-1v-5c0-.6-.4-1-1-1"/>
            </symbol>
            <symbol id="sico-plugins" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M16 3h-2V0h-2v3H8V0H6v3H4v2h1v2a5 5 0 0 0 4 4.9V14H2v-4H0v5c0 .6.4 1 1 1h9c.6 0 1-.4 1-1v-3.1A5 5 0 0 0 15 7V5h1V3z"/>
            </symbol>
            <symbol id="sico-doc" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 0H1C.4 0 0 .4 0 1v14c0 .6.4 1 1 1h14c.6 0 1-.4 1-1V1c0-.6-.4-1-1-1zm-1 2v9h-3c-.6 0-1 .4-1 1v1H6v-1c0-.6-.4-1-1-1H2V2h12z"/><path d="M4 4h8v2H4zM4 7h8v2H4z"/>
            </symbol>
            
            <symbol id="wbsico-donate" viewBox="0 0 9 18">
                <path fill-rule="evenodd" d="M5.63 8.1V4.61c.67.23 1.12.9 1.12 1.58S7.2 7.3 7.88 7.3 9 6.86 9 6.2a3.8 3.8 0 0 0-3.38-3.83V1.12C5.63.45 5.17 0 4.5 0S3.37.45 3.37 1.12v1.24A3.8 3.8 0 0 0 0 6.2C0 8.55 1.8 9.45 3.38 9.9v3.49c-.68-.23-1.13-.9-1.13-1.58S1.8 10.7 1.12 10.7 0 11.14 0 11.8a3.8 3.8 0 0 0 3.38 3.83v1.24c0 .67.45 1.12 1.12 1.12s1.13-.45 1.13-1.12v-1.24A3.88 3.88 0 0 0 9 11.8c0-2.36-1.8-3.26-3.38-3.7zM2.25 6.19c0-.79.45-1.35 1.13-1.58v2.93c-.8-.34-1.13-.68-1.13-1.35zm3.38 7.2v-2.93c.78.34 1.12.68 1.12 1.35 0 .79-.45 1.35-1.13 1.58z"></path>
            </symbol>
            <symbol id="wbsico-like" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M13.3 6H9V2c0-1.5-.8-2-2-2-.3 0-.6.2-.6.5L4 8v8h8.6c1.3 0 2.4-1 2.6-2.3l.8-4.6c.1-.8-.1-1.6-.6-2.1-.5-.7-1.3-1-2.1-1M0 8h2v8H0z"/>
            </symbol>
            <symbol id="wbsico-share" viewBox="0 0 14 16">
                <path fill-rule="evenodd" d="M11 6a3 3 0 1 0-3-2.4L5 5.6A3 3 0 0 0 3 5a3 3 0 0 0 0 6 3 3 0 0 0 1.9-.7l3.2 2-.1.7a3 3 0 1 0 3-3 3 3 0 0 0-1.9.7L6 8.7a3 3 0 0 0 0-1.3l3.2-2A3 3 0 0 0 11 6"/>
            </symbol>
        </defs>
    </svg>
</div>

<div id="optionsframework-wrap" class="wbs-wrap wbps-wrap" data-wba-source="<?php echo $pd_code; ?>">
    <div class="wbs-header">
        <svg class="wb-icon sico-wb-logo"><use xlink:href="#sico-wb-logo"></use></svg>
        <span>WBOLT</span>
        <strong><?php echo $pd_title; ?></strong>

        <div class="links">
            <a class="wb-btn" href="<?php echo $pd_index_url; ?>" data-wba-campaign="title-bar" target="_blank">
                <svg class="wb-icon sico-plugins"><use xlink:href="#sico-plugins"></use></svg>
                <span>插件主页</span>
            </a>
            <a class="wb-btn" href="<?php echo $pd_doc_url; ?>" data-wba-campaign="title-bar" target="_blank">
                <svg class="wb-icon sico-doc"><use xlink:href="#sico-doc"></use></svg>
                <span>说明文档</span>
            </a>
        </div>
    </div>

    <div class="wbs-main">

        <form class="wbs-content option-form" id="optionsframework" action="options.php" method="post">
	        <?php
	        settings_fields($setting_field);

	        $switch = isset($opt['switch']) && $opt['switch']?1:0;
	        ?>
            <h3 class="sc-header">
                <strong>打赏/点赞/分享组件设置</strong>
            </h3>
            <div class="sc-body">


                <table class="wbs-form-table">
                    <tbody>
                    <tr>
                        <th class="row w8em">
                            功能开关
                        </th>
                        <td>
                            <input class="wb-switch" type="checkbox" name="<?php echo $setting_field; ?>[switch]" value="1" data-target="#J_donateSettingDetail" <?php echo $switch ? ' checked="checked"':''; ?> data-value="<?php echo $switch ?>"> <span class="description">开启后，将在文章详情底部显示打赏按钮</span>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <div class="default-hidden-box <?php echo $switch ? 'active':''; ?>" id="J_donateSettingDetail">
                    <table class="wbs-form-table">
                        <tbody>
                        <?php
                        $display_group_field = $setting_field;
                        $display_group_val = isset($opt['display_group'])?$opt['display_group']:'';
                        ?>
                        <tr>
                            <th class="row w8em">
                                选择样式
                            </th>
                            <td>
                                <div class="selector-bar" id="J_typeItems">
                                    <label><input class="wbs-radio" type="radio" name="<?php echo $display_group_field;?>[display_group]" value="0" <?php echo !$display_group_val ?  'checked' : ''; ?>> 打赏+点赞样式</label>
                                    <label><input class="wbs-radio" type="radio" name="<?php echo $display_group_field;?>[display_group]" value="1" <?php echo $display_group_val==1?  'checked' : ''; ?>> 打赏+点赞+分享样式</label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                样式预览
                            </th>
                            <td>
                                <div class="wbp-cbm preview-box" id="J_typePreview">
                                    <div class="ctrl-item">
                                        <a class="wb-btn wb-btn-ctrl wb-btn-outlined" ><svg class="wb-icon wbsico-donate"><use xlink:href="#wbsico-donate"></use></svg><span>打赏</span></a>
                                        <a class="wb-btn wb-btn-ctrl wb-btn-outlined wb-btn-like"><svg class="wb-icon wbsico-like"><use xlink:href="#wbsico-like"></use></svg><span>赞(0)</span></a>
                                        <div class="wb-btn-share" style="display:<?php echo $display_group_val==1 ? 'inline-block': 'none'; ?>;">
                                            <a class="wb-btn"><svg class="wb-icon wbsico-share"><use xlink:href="#wbsico-share"></use></svg><span>分享</span></a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

				        <?php
				        /*
						 * type: 0为普通url, 1为图片
						 *
						 * **/
				        //初始化数据结构
				        $donateData = array(
					        'weixin' => array(
						        'name' => '微信',
						        'desc' => '请上传1:1尺寸规格的微信收款二维码图片，<a href="https://www.wbolt.com/how-to-get-wechat-and-alipay-qr-code.html" data-wba-campaign="Setting-Des-txt" target="_blank">如何获取微信收款二维码</a>？',
						        'type' => 1,
						        'img'=>'',
						        'url'=>''
					        ),
					        'alipay' => array(
						        'name' => '支付宝',
						        'desc' => '请上传1:1尺寸规格的支付宝收款二维码图片，<a href="https://www.wbolt.com/how-to-get-wechat-and-alipay-qr-code.html" data-wba-campaign="Setting-Des-txt" target="_blank">如何获取支付宝收款二维码</a>？',
						        'type' => 1,
						        'img'=>'',
						        'url'=>''
					        )
				        );
				        ?>

				        <?php

                        $items = isset($opt['items'])?$opt['items']:array();
                        foreach ($donateData as $k => $v){

					        $item_obj = isset($items[$k]) ? $items[$k] : array('','','');
					        $item_name = $setting_field . '[items][' . $k . ']';

					        ?>

                            <tr>
                                <th class="row w8em"><?php echo $v['name']; ?>收款二维码</th>
						        <?php if($v['type'] == 1): ?>
                                    <td>
                                        <div class="section-upload">
                                            <div class="wbs-upload-box">
                                                <input class="wbs-input upload-input" id="<?php echo 'donate-'.$k; ?>"  type="text" placeholder="" name="<?php echo $item_name;?>[img]" value="<?php echo $item_obj['img'];?>"/>
                                                <button type="button" class="wbs-btn wbs-upload-btn">
                                                    <svg class="wb-icon sico-upload"><use xlink:href="#sico-upload"></use></svg>
                                                    <span>上传</span>
                                                </button>
                                            </div>
                                        </div>
                                        <p class="description"><?php echo $v['desc']; ?></p>
                                        <input type="hidden" name="<?php echo $item_name;?>[type]" value="1">
                                        <input type="hidden" name="<?php echo $item_name;?>[name]" value="<?php echo $v['name']; ?>">
                                        <input type="hidden" name="<?php echo $item_name;?>[url]" value="<?php echo $item_obj['url'];?>">
                                    </td>
						        <?php else: ?>
                                    <td>
                                        <input class="wbs-input" id="<?php echo 'donate-'.$k; ?>" type="text" placeholder="" name="<?php echo $item_name;?>[url]" value="<?php echo $item_obj['url'];?>">
                                        <input type="hidden" name="<?php echo $item_name;?>[type]" value="0">
                                        <input type="hidden" name="<?php echo $item_name;?>[name]" value="<?php echo $v['name']; ?>">
                                        <input type="hidden" name="<?php echo $item_name;?>[img]" value="<?php echo $item_obj['img'];?>">
                                        <p class="description"><?php echo $v['desc']; ?></p>
                                    </td>
						        <?php endif; ?>
                            </tr>
					        <?php
				        } ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <script type="text/javascript" src="https://www.wbolt.com/wb-api/v1/news/lastest"></script>

            <div class="wb-copyright-bar">
                <div class="wbcb-inner">
                    <a class="wb-logo" href="https://www.wbolt.com" data-wba-campaign="footer" title="WBOLT" target="_blank"><svg class="wb-icon sico-wb-logo"><use xlink:href="#sico-wb-logo"></use></svg></a>
                    <div class="wb-desc">
                        Made By <a href="https://www.wbolt.com" data-wba-campaign="footer" target="_blank">闪电博</a>
                        <span class="wb-version">版本：<?php echo $pd_version;?></span>
                    </div>
                    <div class="ft-links">
                        <a href="https://www.wbolt.com/plugins" data-wba-campaign="footer" target="_blank">免费插件</a>
                        <a href="https://www.wbolt.com/knowledgebase" data-wba-campaign="footer" target="_blank">插件支持</a>
                        <a href="<?php echo $pd_doc_url; ?>" data-wba-campaign="footer" target="_blank">说明文档</a>
                        <a href="https://www.wbolt.com/terms-conditions" data-wba-campaign="footer" target="_blank">服务协议</a>
                        <a href="https://www.wbolt.com/privacy-policy" data-wba-campaign="footer" target="_blank">隐私条例</a>
                    </div>
                </div>
            </div>

            <div class="wbs-footer" id="optionsframework-submit">
                <div class="wbsf-inner">
                    <button class="wbs-btn-primary" type="submit" name="update">保存设置</button>
                </div>
            </div>
        </form>
    </div>
</div>
