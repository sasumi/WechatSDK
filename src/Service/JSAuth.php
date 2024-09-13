<?php

namespace LFPhp\WechatSdk\Service;

use LFPhp\WechatSdk\Base\BaseService;
use function LFPhp\Func\dump;
use function LFPhp\Func\rand_string;

/**
 * JS-SDK 鉴权类
 */
class JSAuth extends BaseService{
	/**
	 * 获取js ticket
	 * jsapi_ticket的有效期为7200秒
	 * @param string $access_token 网页单用户授权token
	 * @return array [ticket, expires second]
	 */
	public static function getJsTicket($access_token){
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket";
		$data = self::getJsonSuccess($url, [
			'type'         => 'jsapi',
			'access_token' => $access_token,
		]);
		return [
			$data['ticket'],
			$data['expires_in'],
		];
	}


	public static function getJsSignatureSimple($jsapi_ticket, $url){
		$nonce_str = rand_string(12);
		$timestamp = time();
		$signature = self::getJsSignature($jsapi_ticket, $url, $nonce_str, $timestamp);
		return [
			'noncestr'  => $nonce_str,
			'timestamp' => $timestamp,
			'signature' => $signature,
		];
	}

	public static function getJsSignature($jsapi_ticket, $url, $nonce_str, $timestamp){
		$data = [
			'noncestr'     => $nonce_str,
			'jsapi_ticket' => $jsapi_ticket,
			'timestamp'    => $timestamp,
			'url'          => $url,
		];
		ksort($data, SORT_ASC);

		//注意，这里微信没有采用 url encode，而是普通的字符串连接。
		$query_str = [];
		foreach($data as $k=>$val){
			$query_str[] = $k.'='.$val;
		}
		$query_str = join('&',$query_str);
		return sha1($query_str);
	}
}
