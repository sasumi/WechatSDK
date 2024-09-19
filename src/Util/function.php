<?php
namespace LFPhp\WechatSdk\Util;

use Exception;
use function LFPhp\Func\dump;

/**
 * 微信JS API列表
 * @return mixed
 */
function wechat_js_api_list(){
	static $api_list;
	if(!isset($api_list)){
		$api_list = include __DIR__.'/js_api_list.php';
	}
	return $api_list;
}

/**
 * 判断js api是否在微信js api列表中
 * @param string $api
 * @return bool
 */
function wechat_in_js_api($api){
	$list = wechat_js_api_list();
	return in_array($api, $list);
}

/**
 * 微信SHA1算法
 * @param ...$args
 * @return string
 */
function wechat_sha1(...$args){
	sort($args, SORT_STRING);
	$str = implode($args);
	return sha1($str);
}

/**
 * 判断浏览器在微信中
 * @return bool
 */
function in_wechat(){
	return !!preg_match('/MicroMessenger/i', $_SERVER['HTTP_USER_AGENT']) && !in_wework();
}

/**
 * 判断浏览器在企业微信中
 * @return bool
 */
function in_wework(){
	return !!preg_match('/wxwork/i', $_SERVER['HTTP_USER_AGENT']);
}

/**
 * xml转换成数组（忽略属性等）
 * @param string $xml_str
 * @return array
 */
function xml_to_array($xml_str){
	$xml = simplexml_load_string($xml_str, "SimpleXMLElement", LIBXML_NOCDATA);
	$json = json_encode($xml);
	return json_decode($json,true);
}

/**
 * 简单数组转换成xml（不支持属性）
 * @param array $array
 * @param bool $content_only 是否返回xml头
 * @return string
 */
function array_to_xml($array, $content_only = false){
	$xml = !$content_only ? '<xml>' : '';
	foreach($array as $key => $value){
		if(is_array($value)){
			$xml .= array_to_xml($value, true);
		}else{
			$val_str = '';
			switch(gettype($value)){
				case 'integer':
				case 'double':
					$val_str = $value;
					break;
				case 'string':
					$val_str = "<![CDATA[".htmlspecialchars($value, ENT_XML1)."]]>";
					break;
				default:
					throw new Exception('no support type'.gettype($value));
			}
			$xml .= "<$key>".$val_str."</$key>";
		}
	}
	$xml .= !$content_only ? '</xml>' : '';
	return $xml;
}
