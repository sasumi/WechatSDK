<?php

namespace LFPhp\WechatSdk\Base;

use Exception;
use LFPhp\Logger\Logger;
use LFPhp\WechatSdk\Exception\WechatException;

use function LFPhp\Func\array_clean_null;
use function LFPhp\Func\array_merge_assoc;
use function LFPhp\Func\curl_data2str;
use function LFPhp\Func\curl_get;
use function LFPhp\Func\curl_post_file;
use function LFPhp\Func\curl_post_json;
use function LFPhp\Func\curl_query;
use const LFPhp\Func\HTTP_METHOD_GET;
use const LFPhp\Func\HTTP_METHOD_POST;

abstract class BaseService {
	const DEFAULT_TIMEOUT = 20;

	/**
	 * 发送请求
	 */
	protected static function sendJsonRequest($url, array $param = [], $request_method = HTTP_METHOD_POST, array $file_map = [], $headers = []) {
		$curl_opt = [CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT, CURLOPT_HTTPHEADER => $headers];
		$logger = Logger::instance(__CLASS__);
		$logger->info("[$request_method]", $url, $param, $file_map);

		$param = array_clean_null($param);
		switch ($request_method) {
			case HTTP_METHOD_GET:
				$ret = curl_get($url, $param, $curl_opt);
				break;
			case HTTP_METHOD_POST:
				if ($file_map) {
					$ret = curl_post_file($url, $file_map, $param, $curl_opt);
				} else {
					$ret = curl_post_json($url, $param, $curl_opt);
				}
				break;
			default:
				$ret = curl_query($url, array_merge_assoc([
					CURLOPT_POSTFIELDS    => curl_data2str($param),
					CURLOPT_CUSTOMREQUEST => $request_method,
				], $curl_opt));
		}
		$logger->info("[Response code:{$ret['info']['http_code']}]", 'Body Size: ' . strlen($ret['body']) . ' Bytes');
		$logger->debug('[Response Body]', $ret['body']);

		//return as json
		$body_str = $ret['body'];

		//204 状态码不需要内容
		if (!$body_str && $ret['info']['http_code'] === 204) {
			return null;
		}
		if (!$body_str) {
			throw new Exception('http request body empty');
		}
		$obj = json_decode($body_str, true);
		if (json_last_error()) {
			throw new Exception('json decode fail:' . json_last_error_msg());
		}
		return $obj;
	}

	protected static function getJsonSuccess($url, $param = []) {
		$data = static::getJson($url, $param);
		self::assertResultSuccess($data);
		return $data;
	}

	protected static function getJson($url, $param = []) {
		return static::sendJsonRequest($url, $param, HTTP_METHOD_GET);
	}

	protected static function postJsonSuccess($url, $param = []) {
		$data = static::postJson($url, $param);
		self::assertResultSuccess($data);
		return $data;
	}

	protected static function postJson($url, $param = []) {
		return static::sendJsonRequest($url, $param, HTTP_METHOD_POST);
	}

	/**
	 * {"errcode":40013,"errmsg":"invalid appid"}
	 * @param $rsp_data
	 * @return void
	 */
	protected static function assertResultSuccess($rsp_data) {
		if (isset($rsp_data['errcode']) && $rsp_data['errcode']) {
			WechatException::throw($rsp_data['errmsg'], $rsp_data['errcode'], $rsp_data);
		}
	}
}
