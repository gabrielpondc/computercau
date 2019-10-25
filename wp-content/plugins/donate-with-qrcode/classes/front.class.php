<?php




class DWQR_Front {

	public function __construct() {

	    //是否开启
	    $switch = DWQR_Admin::cnf('switch',0);
	    if($switch && !is_admin()){
            add_filter( 'the_content', array( $this, 'the_content' ), 100 );
            add_action( 'wp_head', array( $this, 'wp_head' ), 50 );
        }

        add_action('wp_ajax_dwqr_ajax',array($this,'dwqr_ajax'));
        add_action('wp_ajax_nopriv_dwqr_ajax',array($this,'dwqr_ajax'));
	}

	public function dwqr_ajax(){

	    //print_r($_GET);exit();
	    if($_REQUEST['do']=='like'){

	        do{
                $post_id = intval($_REQUEST['post_id']);

                if(!$post_id){
                    break;
                }
                $like = get_post_meta($post_id,'dwqr_like',true);
                if($like){
                    $like = intval($like);
                }else{
                    $like = 0;
                }
                $like++;

                update_post_meta($post_id,'dwqr_like',$like);
                echo $like;
                exit();

            }while(false);


        }


    }

    function wp_head(){
        if ( is_single() ) {
            echo "<link rel='stylesheet' id='wbs-style-dwqr-css'  href='".plugin_dir_url(DWQR_BASE_FILE) . "assets/wbp_donate.css' type='text/css' media='all' />";
            wp_enqueue_script('wbs-font-dwqr', plugin_dir_url(DWQR_BASE_FILE) . 'assets/wbp_front.js', array('jquery'), DWQR_VERSION,true);
        }
    }



	public function the_content( $content ) {
		if ( is_single() ) {
			$content .= $this->donateHtml();
		}

		return $content;
	}

	private function donateHtml(){

	    $ajax_url = admin_url('admin-ajax.php');

			$svg_html = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" overflow="hidden" style="position:absolute;width:0;height:0">
			  <defs>
			    <symbol id="wbsico-close" viewBox="0 0 18 18">
			      <path fill-rule="evenodd" d="M17.61.39a1.24 1.24 0 0 0-1.8 0L9 7.2 2.19.39C1.67-.13.9-.13.39.39s-.52 1.28 0 1.8L7.2 9 .39 15.81a1.24 1.24 0 0 0 0 1.8c.25.26.51.39.9.39s.64-.13.9-.39L9 10.8l6.81 6.81c.26.26.65.39.9.39s.65-.13.9-.39c.52-.51.52-1.28 0-1.8L10.8 9l6.81-6.81c.52-.52.52-1.29 0-1.8z"></path>
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
			</svg>';
			//echo $svg_html;

			$items = DWQR_Admin::cnf('items',array());
			$share_switch = DWQR_Admin::cnf('display_group');


			$tab_html ='';
			$cont_html ='';

			$index = 0;
			foreach ($items as $k => $v){
			    if(empty($v['img']))continue;

				$tab_html .= '<div class="tab-nav-item item-'.$k. ($index==0 ? ' current':'') .'"><span>'.$v['name'].'</span></div>';
				$cont_html .= '<div class="tab-cont'.($index==0 ? ' current':'') .'"><div class="pic"><img src="'.$v['img'].'" alt="'.$v['name'].'二维码图片"></div><p>用<span class="hl">'.$v['name'].'</span>扫描二维码打赏</p></div>';
				$index ++;
			}

			//只有一个item时不显示
			if($index == 1) $tab_html ='';

			$post_id = get_the_ID();

			$like = get_post_meta($post_id,'dwqr_like',true);
			if($like){
			    $like = intval($like);
            }else{
			    $like = 0;
            }



			$tpl = '
			<div class="wbp-cbm">
				<div class="ctrl-item">
						<a class="wb-btn wb-btn-ctrl wb-btn-outlined" id="J_ppoDonateBtn" data-ppo-name="#J_ppoDonate"><svg class="wb-icon wbsico-donate"><use xlink:href="#wbsico-donate"></use></svg><span>打赏</span></a>
						<a class="wb-btn wb-btn-ctrl wb-btn-outlined wb-btn-like" data-api="'.$ajax_url.'" data-post_id="'.$post_id.'"><svg class="wb-icon wbsico-like"><use xlink:href="#wbsico-like"></use></svg><span>赞('.$like.')</span></a>';

			if(isset($share_switch) && $share_switch){
				$tpl.= '<div class="wb-btn-share">
							<div class="bdsharebuttonbox" data-tag="share_1"><a class="popup_more" data-cmd="more"></a></div>
							<a class="wb-btn"><svg class="wb-icon wbsico-share"><use xlink:href="#wbsico-share"></use></svg><span>分享</span></a>
						</div>
				        <script>
				            window._bd_share_config = {
				                share : [{
				                    "bdSize" : 32
				                }],
				                "url" :"'.plugin_dir_url(DWQR_BASE_FILE) . 'assets/"
				            };
				            
				            with(document)0[(getElementsByTagName("head")[0]||body).appendChild(createElement("script")).src=" '.plugin_dir_url(DWQR_BASE_FILE) . '/assets/static/api/js/share.js?cdnversion="+~(-new Date()/36e5)];
				        </script>';
			}

			$tpl .= '<div class="com-popover pst-c pst-fixed wb-dialog-df wb-ppo-donate" id="J_ppoDonate">
			    <div class="bd" id="J_tabBoxDWQ">
			    	<div class="tab-navs">
			    	
			    		'.$tab_html.'
					</div>
					<div class="tab-conts">
						'.$cont_html.'
					</div>
			    </div>
			    <a class="wb-ppo-close"><svg class="wb-icon wbsico-close"><use xlink:href="#wbsico-close"></use></svg></a>
			    </div>';

			$tpl .= '</div>'; //ctrl-item
			$tpl .= '</div>'; //ctrl-area-cbm

			return  $svg_html . $tpl;
	}

}