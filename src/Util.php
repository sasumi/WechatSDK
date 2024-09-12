<?php

namespace LFPhp\WechatSdk;
class Util {
	/**
	 * 判断浏览器在微信中
	 * @return bool
	 */
	public static function inWechat(){
		return preg_match('/MicroMessenger/i', $_SERVER['HTTP_USER_AGENT']) && !self::inWework();
	}

	/**
	 * 判断浏览器在企业微信中
	 * @return false|int
	 */
	public static function inWework(){
		return preg_match('/wxwork/i', $_SERVER['HTTP_USER_AGENT']);
	}
}
