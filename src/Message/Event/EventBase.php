<?php

namespace LFPhp\WechatSdk\Message\Event;

use LFPhp\WechatSdk\Message\MessageBase;

class EventBase extends MessageBase {
	const EVENT_SUBSCRIBE = 'subscribe';
	const EVENT_UNSUBSCRIBE = 'unsubscribe';
	const EVENT_SCAN = 'SCAN';
	const EVENT_LOCATION = 'LOCATION';
	const EVENT_TMPL_MSG_SENT = 'TEMPLATESENDJOBFINISH';

	const EVENT_MAP = [
		self::EVENT_SUBSCRIBE   => '订阅',
		self::EVENT_UNSUBSCRIBE => '取消订阅',
		self::EVENT_SCAN        => '扫描公众号二维码',
		self::EVENT_LOCATION    => '上报地理位置',
		self::EVENT_TMPL_MSG_SENT => '模板消息发送完成',
	];

	public $MsgType = self::MSG_TYPE_EVENT;

	/** @var string 事件类型 */
	public $Event;
}
