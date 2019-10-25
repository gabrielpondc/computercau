<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

require_once 'class-wechat-error.php'; 

class XH_Social_Wechat_Token{
    private $__temp=array();
    public $appid,$appsecret,$crossdomain_url;
    
    public function __construct($appid,$appsecret,$crossdomain_url=null){
       $this->appid =$appid;
       $this->appsecret = $appsecret;
       $this->crossdomain_url =$crossdomain_url;
       
       do_action('xh_wechat_token');
    }
    
    public function get($id){
        $data = apply_filters('xh_wechat_token_get',null, $id);
        if($data){
            return $data;
        }
        
        $token=null;
        if(isset($this->__temp[$id])){
            $token  =$this->__temp[$id];
        }else{
            $token = get_option('wechat_token',array());
            if(!$token||!is_array($token)){
                $token=array();
            }
            
            $token = isset($token[$id])?$token[$id]:null;
        }
        
        
        if(!$token
            ||!is_array($token)
            ||!isset($token['expire'])
            ||$token['expire']<time()){
            return null;
        }
        
        $this->__temp[$id]=$token;
        
        return isset($token['data'])?$token['data']:null;
    }
    
    public function set($id,$data){
        do_action('xh_wechat_token_set', $id,$data);
        
        $token = get_option('wechat_token',array());
        if(!$token||!is_array($token)){
            $token=array();
        }
        
        $token[$id]=array(
               'expire'=>time()+6000,
               'data'=>$data
        );
        
        update_option('wechat_token', $token,true);
        $this->__temp[$id]=$data;
    }
    
    /**
     *
     * @param number $retry
     * @param string $refresh
     * @return NULL
     * @since 1.0.2
     */
    public function jsapi_ticket(&$retry = 2,$refresh=false){
        if(empty($this->appid)||empty($this->appid)){
            return XH_Social_Error::error_custom('unknow APPID');
        }
        
        if(!$refresh){
            $cached_jsapi_ticket = $this->get('jsapi_ticket');
            if($cached_jsapi_ticket){
                return $cached_jsapi_ticket;
            }
        }
    
        try {
            $access_token_call = apply_filters('xh_wechat_get_jsapi_ticket', function($api){
                $accessToken = $api->access_token();
                if($accessToken instanceof XH_Social_Error){
                    return $accessToken;
                }
                
                $response =XH_Social_Helper_Http::http_get ( "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token={$accessToken}" );
                $error = new XH_Social_Wechat_Error($api->appid,$api ->appsecret);
                
                $obj = $error->validate($response);
                $api->set('jsapi_ticket', $obj['ticket']);
                return $obj['ticket'];
            },$this);
            
            return call_user_func($access_token_call,$this);
            
            
        } catch (Exception $e) {
            XH_Social_Log::error($e);
            if($e->getCode()==500){
                return new XH_Social_Error($e->getCode(),$e->getMessage());
            }
            if($retry-->0){
                return $this->jsapi_ticket($retry);
            }
        }
    
        return XH_Social_Error::error_unknow();
    }
    
    public function access_token(&$retry = 2,$refresh=false){
        if(empty($this->appid)||empty($this->appid)){
            return XH_Social_Error::error_custom('unknow APPID');
        }
        
        if(!$refresh){
            $cached_access_token = $this->get('access_token');
            if(!empty($cached_access_token)){
                return $cached_access_token;
            }
        }
        
        try {
            $call = function($api){
                $response = XH_Social_Helper_Http::http_get("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$api->appid}&secret={$api->appsecret}");
                $error = new XH_Social_Wechat_Error($api->appid,$api->appsecret);
                $c=0;
                $obj = $error->validate($response,$c);
                $api->set('access_token', $obj['access_token']);
                return $obj['access_token'];
            };
            
            if(!empty($this->crossdomain_url)){
               $call =  function($api){
                    $request = array(
                        'get_access_token'=>1,
                        't'=>time()
                    );
                    $request['hash'] = XH_Social_Helper::generate_hash($request, $api->appsecret);
                    $url =XH_Social_Helper_Uri::get_new_uri($api->crossdomain_url,$request);
                
                    $response_txt = XH_Social_Helper_Http::http_get($url);
                    $response = json_decode($response_txt,true);
                    if(!$response){
                        throw new Exception($response_txt);
                    }
                
                    if(!$response['success']){
                        throw new Exception($response['data']);
                    }
                
                    $api->set('access_token', $response['data']);
                    return $response['data'];
                };
            }
            
            $access_token_call = apply_filters('xh_wechat_get_access_token', $call,$this);
            return call_user_func($access_token_call,$this);
           
        } catch (Exception $e) {
            XH_Social_Log::error($e);
            if($e->getCode()==500){
                return new XH_Social_Error($e->getCode(),$e->getMessage());
            }
            
            if($retry-->0){
                return $this->access_token($retry);
            }
        }
        
        return XH_Social_Error::error_unknow();
    }
}