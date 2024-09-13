<?php

namespace LFPhp\WechatSdk\Service;

use LFPhp\WechatSdk\Base\BaseService;

class Auth extends BaseService {
	public static function getAccessToken($app_id, $app_secret){
		$url = "https://api.weixin.qq.com/cgi-bin/token";
		$rsp = self::getJsonSuccess($url, [
			'grant_type' => 'client_credential',
			'appid'      => $app_id,
			'secret'     => $app_secret,
		]);
		return [$rsp['access_token'], $rsp['expires_in']];
	}

	/**
	 * @see https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-access-token/getStableAccessToken.html#%E6%8E%A5%E5%8F%A3%E8%AF%B4%E6%98%8E
	 * @param $app_id
	 * @param $app_secret
	 * @param $force_refresh
	 * @return array
	 */
	public static function getStableAccessToken($app_id, $app_secret, $force_refresh = false){
		$url = "https://api.weixin.qq.com/cgi-bin/stable_token";
		$rsp = self::postJsonSuccess($url, [
			'grant_type'    => 'client_credential',
			'appid'         => $app_id,
			'secret'        => $app_secret,
			//默认使用 false。
			//1. force_refresh = false 时为普通调用模式，access_token 有效期内重复调用该接口不会更新 access_token；
			//2. 当force_refresh = true 时为强制刷新模式，会导致上次获取的 access_token 失效，并返回新的 access_token
			'force_refresh' => $force_refresh,
		]);

		return [$rsp['access_token'], $rsp['expires_in']];
	}
}
