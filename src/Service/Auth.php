<?php

namespace LFPhp\WechatSdk\Service;

use LFPhp\WechatSdk\Base\BaseService;

class Auth extends BaseService {
	public static function getAccessToken($app_id, $app_secret){
		$url = sprintf("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s", $app_id, $app_secret);
		$rsp = self::getJsonSuccess($url);
		return [$rsp['access_token'], $rsp['expires_in']];
	}
}
