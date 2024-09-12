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
}
