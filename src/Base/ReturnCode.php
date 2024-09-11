<?php

namespace LFPhp\WechatSdk\Base;

abstract class ReturnCode {
	/**
	 * 根据错误码返回提醒信息
	 * @param $code
	 * @return array [技术消息,友好消息] 友好消息可能为空
	 */
	public static function getMessages($code){
		if(isset(self::RETURN_CODE_MAP[$code])){
			return self::RETURN_CODE_MAP[$code];
		}
		return ["", ""];
	}

	/**
	 * 全局错误码
	 */
	const RETURN_CODE_MAP = [
		'-1'    => ['系统繁忙，此时请开发者稍候再试'],
		'0'     => ['请求成功'],
		'40001' => ['AppSecret错误或者AppSecret不属于这个公众号，请开发者确认AppSecret的正确性'],
		'40002' => ['请确保grant_type字段值为client_credential'],
		'40164' => ['调用接口的IP地址不在白名单中，请在接口IP白名单中进行设置。'],
		'40243' => ['AppSecret已被冻结，请登录MP解冻后再次调用。'],
		'89503' => ['此IP调用需要管理员确认,请联系管理员'],
		'89501' => ['此IP正在等待管理员确认,请联系管理员'],
		'89506' => ['24小时内该IP被管理员拒绝调用两次，24小时内不可再使用该IP调用'],
		'89507' => ['1小时内该IP被管理员拒绝调用一次，1小时内不可再使用该IP调用'],
	];
}
