<?php

namespace LFPhp\WechatSdk\Service;

use LFPhp\WechatSdk\Base\BaseService;
use function LFPhp\Func\rand_string;

/**
 * 页面鉴权相关（OAuth2.0）
 * @see https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html
 */
class PageAuth extends BaseService {
	const SCOPE_BASE = 'snsapi_base'; //基本openid，静默授权
	const SCOPE_USERINFO = 'snsapi_userinfo';//用户头像、昵称信息，弹窗确认

	/**
	 * 获取网页认证链接（个人订阅号无权限）
	 * @param string $app_id
	 * @param string $redirect_url
	 * @param string $scope
	 * @param string $state
	 * @return string
	 */
	public static function getAuthUrl($app_id, $redirect_url, $scope, $state = ''){
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".http_build_query([
				'appid'         => $app_id,
				'redirect_uri'  => $redirect_url,
				'response_type' => 'code',
				'scope'         => $scope,
				'state'         => $state,
			]).'#wechat_redirect';
	}

	/**
	 * 5 万/分钟
	 * @param string $app_id
	 * @param string $app_secret
	 * @param string $code
	 * @return array
	 * @throws \LFPhp\WechatSdk\Exception\WechatException
	 */
	public static function getAccessToken($app_id, $app_secret, $code){
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?grant_type=authorization_code";
		$data = self::getJsonSuccess($url, [
			'appid'  => $app_id,
			'secret' => $app_secret,
			'code'   => $code,
		]);
		return [
			'access_token'    => $data['access_token'], //网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
			'expires_in'      => $data['expires_in'], //access_token接口调用凭证超时时间，单位（秒）
			'refresh_token'   => $data['refresh_token'], //用户刷新access_token
			'openid'          => $data['openid'], //用户唯一标识，请注意，在未关注公众号时，用户访问公众号的网页，也会产生一个用户和公众号唯一的OpenID
			'scope'           => explode(',',$data['scope']), //用户授权的作用域，使用逗号（,）分隔
			'is_snapshotuser' => $data['is_snapshotuser'], //是否为快照页模式虚拟账号，只有当用户是快照页模式虚拟账号时返回，值为1
			'unionid'         => $data['unionid'], //用户统一标识（针对一个微信开放平台账号下的应用，同一用户的 unionid 是唯一的），只有当scope为"snsapi_userinfo"时返回
		];
	}

	/**
	 * 由于access_token拥有较短的有效期，当access_token超时后，可以使用refresh_token进行刷新，refresh_token有效期为30天，当refresh_token失效之后，需要用户重新授权。
	 * 	5 万/分钟
	 * @param string $app_id
	 * @param string $refresh_token
	 * @return array
	 * @throws \LFPhp\WechatSdk\Exception\WechatException
	 */
	public static function refreshAccessToken($app_id, $refresh_token){
		$url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?grant_type=refresh_token";
		$data = self::getJsonSuccess($url, [
			'appid'         => $app_id,
			'refresh_token' => $refresh_token,
		]);
		return [
			'access_token'  => $data['access_token'],
			'expires_in'    => $data['expires_in'],
			'refresh_token' => $data['refresh_token'],
			'openid'        => $data['openid'],
			'scope'         => $data['scope'],
		];
	}

	/**
	 * 检验授权凭证（access_token）是否有效
	 * @param $access_token
	 * @param $open_id
	 * @return bool
	 * @throws \LFPhp\WechatSdk\Exception\WechatException
	 */
	public static function validateAccessToken($access_token, $open_id){
		$url = "https://api.weixin.qq.com/sns/auth";
		$data = self::getJsonSuccess($url, [
			'access_token' => $access_token,
			'openid'       => $open_id,
		]);
		return true;
	}

	/**
	 * 拉取用户信息(需scope为 snsapi_userinfo)
	 * 	5 万/分钟
	 * @param string $access_token 网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
	 * @param string $open_id
	 * @param string $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
	 * @return array
	 */
	public static function getSnsUserInfo($access_token, $open_id, $lang = 'zh_CN'){
		$url = 'https://api.weixin.qq.com/sns/userinfo';
		$data = self::getJsonSuccess($url, [
			'access_token' => $access_token,
			'openid'       => $open_id,
			'lang'         => $lang,
		]);
		return [
			'openid' => $data['openid'], //示例："openid": "OPENID",
			'nickname' => $data['nickname'], //示例："nickname": NICKNAME,
			'sex' => $data['sex'], //示例："sex": 1,
			'province' => $data['province'], //示例："province":"PROVINCE",
			'city' => $data['city'], //示例："city":"CITY",
			'country' => $data['country'], //示例："country":"COUNTRY",
			'headimgurl' => $data['headimgurl'], //示例："headimgurl":"https://thirdwx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
			'privilege' => $data['privilege'], //示例："privilege":[ "PRIVILEGE1" "PRIVILEGE2"     ],
			'unionid' => $data['unionid'], //示例："unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
		];
	}

	/**
	 * 获取jsticket
	 * jsapi_ticket的有效期为7200秒
	 * @param string $page_access_token 网页单用户授权token
	 * @return array [ticket, expires second]
	 * @throws \LFPhp\WechatSdk\Exception\WechatException
	 */
	public static function getJsTicket($page_access_token){
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi";
		$data = self::getJsonSuccess($url, [
			'type'         => 'jsapi',
			'access_token' => $page_access_token,
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
		$query_str = http_build_query($data);
		return sha1($query_str);
	}
}
