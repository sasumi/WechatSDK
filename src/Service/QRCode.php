<?php

namespace LFPhp\WechatSdk\Service;

use Exception;
use LFPhp\WechatSdk\Base\AuthorizedService;
use function LFPhp\Func\array_clean_null;
use function LFPhp\Func\encodeURI;

class QRCode extends AuthorizedService {
	const QR_ACTION_SCENE = 'QR_SCENE';
	const QR_ACTION_STR_SCENE = 'QR_STR_SCENE';
	const QR_ACTION_LIMIT_SCENE = 'QR_LIMIT_SCENE';

	/**
	 * 创建临时二维码
	 * @param string|int $scene 场景值，区分变量类型
	 * @param int $expires
	 * @return array [ticket, expire_seconds, url, qrcode_url]
	 */
	public static function createTemporaryQRCode($scene, $expires = 60){
		$act = is_numeric($scene) ? self::QR_ACTION_SCENE : self::QR_ACTION_STR_SCENE;
		return self::createQRCode($act, $scene, $expires);
	}

	/**
	 * 创建永久二维码
	 * @param string|int $scene
	 * @return array [ticket, expire_seconds, url, qrcode_url]
	 */
	public static function createPermanentQRCode($scene){
		if(is_numeric($scene) && $scene > 100000){
			throw new Exception('permanent qrcode scene id exceed 100000 ('.$scene.')');
		}
		return self::createQRCode(self::QR_ACTION_LIMIT_SCENE, $scene);
	}

	/**
	 * 创建二维码
	 * @param string $action 二维码类型，QR_SCENE为临时的整型参数值，QR_STR_SCENE为临时的字符串参数值，QR_LIMIT_SCENE为永久的整型参数值，QR_LIMIT_STR_SCENE为永久的字符串参数值
	 * @param string|int $scene 场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）或 场景值ID（字符串形式的ID），字符串类型，长度限制为1到64
	 * @param int $expires 该二维码有效时间，以秒为单位。 最大不超过2592000（即30天）,永久二维码不需要这个字段
	 * @return array
	 */
	public static function createQRCode($action, $scene = '', $expires = null){
		$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create';
		$action_info = ['scene' => []];
		if(is_numeric($scene)){
			$action_info['scene']['scene_id'] = $scene;
		}else{
			$action_info['scene']['scene_str'] = $scene;
		}

		$ret = self::postJsonSuccess($url, array_clean_null([
			'expire_seconds' => $expires,
			'action_name'    => $action,
			'action_info'    => $action_info,
		]));

		return [
			//二维码ticket 凭借此ticket可以在有效时间内换取二维码,等效于 qrcode_url
			'ticket'         => $ret['ticket'],

			//过期时间
			'expire_seconds' => $ret['expire_seconds'],

			//二维码内容URL
			'url'            => $ret['url'],

			//二维码图像地址
			'qrcode_url'     => 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.encodeURI($ret['ticket']),
		];
	}
}
