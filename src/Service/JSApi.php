<?php

namespace LFPhp\WechatSdk\Service;

use LFPhp\WechatSdk\Base\AuthorizedBaseService;
use function LFPhp\Func\rand_string;

class JSApi extends AuthorizedBaseService {
	/**
	 * 获取jsticket
	 * jsapi_ticket的有效期为7200秒
	 * @return array [ticket, expires second]
	 * @throws \LFPhp\WechatSdk\Exception\WechatException
	 */
	public static function getJsTicket(){
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi";
		$data = self::getJson($url);
		self::assertResultSuccess($data);

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
		$query_str = http_build_query($data);
		return sha1($query_str);
	}
}
