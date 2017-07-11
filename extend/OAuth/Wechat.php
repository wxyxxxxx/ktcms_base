<?php
namespace OAuth;
/**
 * 微信授权相关接口
 *
 * @link http://www.phpddt.com
 */
class Wechat {
	//高级功能-》开发者模式-》获取
	private $app_id = ''; //公众号appid
	private $app_secret = ''; //公众号app_secret
	private $redirect_uri = ''; //授权之后跳转地址
		
	public function __construct($appid='',$appsecret='',$redirect_uri=''){
		$this->app_id=$appid;
		$this->app_secret=$appsecret;
		$this->redirect_uri=$redirect_uri;
	}
	/**
	 * 获取微信授权链接
	 *
	 * @param string $redirect_uri 跳转地址
	 * @param mixed $state 参数
	 */
	public function get_authorize_url($state='') {
		$redirect_uri = urlencode($this->redirect_uri);
		
		$url="https://open.weixin.qq.com/connect/qrconnect?appid={$this->app_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_login&state={$state}#wechat_redirect";

		header("location:$url");exit;
	}
	/**
	 * 获取授权token
	 *
	 * @param string $code 通过get_authorize_url获取到的code
	 */
	public function get_access_token($code='') {
		$code=input("get.code",'');
		// $open_access_token=cache('open_access_token');
		// if ($open_access_token&&($open_access_token['expire_time']-time())>1000) {
		// 	$access_token=$open_access_token['access_token'];
		// 	return $access_token;
		// }else{
			$token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secret}&code={$code}&grant_type=authorization_code";
			$token_data = $this->http($token_url);
			if ($token_data[0] == 200) {
				$arr=json_decode($token_data[1], TRUE);
				// $open_access_token['access_token']=$arr['access_token'];
				// $open_access_token['expire_time']=time()+7000;
				// cache('open_access_token',$open_access_token);
				
				return $arr;
			}
		// }

		return FALSE;
	}
	/**
	 * 获取授权后的微信用户信息
	 *
	 * @param string $access_token
	 * @param string $open_id
	 */
	public function get_user_info($access_token='', $openid='') {
		$res=$this->get_access_token();
		$access_token=$res['access_token'];
		$openid=$res['openid'];
		if ($access_token && $openid) {
			$info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
			$info_data = $this->http($info_url);
			if ($info_data[0] == 200) {
				
				return json_decode($info_data[1], TRUE);
			}
		}
		return FALSE;
	}
	public function http($url, $method='', $postfields = null, $headers = array(), $debug = false) {
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ci, CURLOPT_TIMEOUT, 30);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, true);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
					$this->postdata = $postfields;
				}
			break;
		}
		curl_setopt($ci, CURLOPT_URL, $url);
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ci, CURLINFO_HEADER_OUT, true);
		$response = curl_exec($ci);
		$http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		if ($debug) {
			echo "=====post data======\r\n";
			var_dump($postfields);
			echo '=====info=====' . "\r\n";
			print_r(curl_getinfo($ci));
			echo '=====$response=====' . "\r\n";
			print_r($response);
		}
		curl_close($ci);
		return array($http_code, $response);
	}
}


