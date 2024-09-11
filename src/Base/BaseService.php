<?php

namespace LFPhp\WechatSdk\Base;

use Exception;
use LFPhp\WechatSdk\Exception\WechatException;
use function LFPhp\Func\curl_post_json;
use const LFPhp\Func\HTTP_METHOD_GET;
use const LFPhp\Func\HTTP_METHOD_POST;

abstract class BaseService {
	const DEFAULT_TIMEOUT = 20;

	protected static function sendJsonRequest($url, array $param = [], $request_method = HTTP_METHOD_POST, $files = []){
		$ret = curl_post_json($url, $param, [CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT]);
		$json_str = $ret['body'];
		if(!$json_str){
			throw new Exception('http request body empty');
		}
		$obj = json_decode($json_str, true);
		if(json_last_error()){
			throw new Exception('json decode fail:'.json_last_error_msg());
		}
		return $obj;
	}

	protected static function getJson($url, $param = []){
		return static::sendJsonRequest($url, $param, HTTP_METHOD_GET);
	}

	protected static function postJson($url, $param = []){
		return static::sendJsonRequest($url, $param, HTTP_METHOD_POST);
	}

	/**
	 * {"errcode":40013,"errmsg":"invalid appid"}
	 * @param $rsp_data
	 * @return void
	 */
	protected static function assertResultSuccess($rsp_data){
		if(isset($rsp_data['errcode']) && $rsp_data['errcode']){
			throw new WechatException($rsp_data['errcode'], $rsp_data['errmsg']);
		}
	}
}
