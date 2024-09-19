<?php

namespace LFPhp\WechatSdk\Base;
use Exception;

/**
 * 包含access token信息的基础服务
 */
abstract class AuthorizedService extends BaseService {
	private static $access_token;

	/**
	 * @return mixed
	 */
	protected static function getAccessToken(){
		return self::$access_token;
	}

	/**
	 * @param mixed $access_token
	 */
	public static function setAccessToken($access_token){
		self::$access_token = $access_token;
	}

	protected static function sendJsonRequest($url, array $param = [], $request_method = 'post', array $file_map = []){
		$access_token = static::getAccessToken();
		if(!$access_token){
			throw new Exception('access token required for call:'.static::class);
		}
		$param['access_token'] = $access_token;

		//post请求，url加上一个access_token确保安全
		if(strtolower($request_method) == 'post'){
			$url .= (strpos($url, '?') !== false ? '&' : '?').'access_token='.$access_token;
		}
		return parent::sendJsonRequest($url, $param, $request_method, $file_map);
	}
}
