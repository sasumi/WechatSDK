<?php

namespace LFPhp\WechatSdk\Message;

use Exception;
use JsonSerializable;
use LFPhp\WechatSdk\Message\Event\EventBase;
use LFPhp\WechatSdk\Message\Event\EventLocation;
use LFPhp\WechatSdk\Message\Event\EventScan;
use LFPhp\WechatSdk\Message\Event\EventSubscribe;
use LFPhp\WechatSdk\Message\Event\EventTmplMsgSent;
use LFPhp\WechatSdk\Message\Event\EventUnSubscribe;
use LFPhp\WechatSdk\Message\Message\MessageText;
use ReflectionObject;

use function LFPhp\Func\array_to_xml;

class MessageBase implements JsonSerializable {
	const MSG_TYPE_EVENT = 'event';
	const MSG_TYPE_TEXT = 'text';
	const MSG_TYPE_IMAGE = 'image';
	const MSG_TYPE_NEWS = 'news';
	const MSG_TYPE_MUSIC = 'music';
	const MSG_TYPE_VOICE = 'voice';
	const MSG_TYPE_VIDEO = 'video';

	const MSG_TYPE_MAP = [
		self::MSG_TYPE_EVENT => '事件推送',
		self::MSG_TYPE_TEXT  => '文本消息',
		self::MSG_TYPE_IMAGE => '图片消息',
		self::MSG_TYPE_NEWS  => '图文消息',
		self::MSG_TYPE_MUSIC => '音乐消息',
		self::MSG_TYPE_VOICE => '语音消息',
		self::MSG_TYPE_VIDEO => '视频消息',
	];

	/** @var string 开发者微信号 */
	public $ToUserName;

	/** @var string 发送方账号（一个OpenID） */
	public $FromUserName;

	/** @var int 消息创建时间 （整型） */
	public $CreateTime;

	/** @var string 消息类型，文本为text */
	public $MsgType;

	/**
	 * 根据原始数组获取消息实例
	 * @param array $raw_arr
	 * @return MessageBase
	 */
	final public static function getMessageInstance($raw_arr) {
		$class = self::resolveMessageClass($raw_arr);
		$instance = new $class();
		$ri = new ReflectionObject($instance);
		$properties = $ri->getProperties();

		foreach ($properties as $pro) {
			$name = $pro->name;
			if (isset($raw_arr[$name])) {
				$instance->{$name} = $raw_arr[$name];
			}
		}
		return $instance;
	}

	/**
	 * @param array $raw_arr
	 * @return string
	 * @throws Exception
	 */
	final public static function resolveMessageClass($raw_arr) {
		if ($raw_arr['MsgType'] === self::MSG_TYPE_TEXT) {
			return MessageText::class;
		}
		if ($raw_arr['MsgType'] === self::MSG_TYPE_EVENT) {
			switch ($raw_arr['Event']) {
				case EventBase::EVENT_SUBSCRIBE:
					return EventSubscribe::class;
				case EventBase::EVENT_UNSUBSCRIBE:
					return EventUnSubscribe::class;
				case EventBase::EVENT_SCAN:
					return EventScan::class;
				case EventBase::EVENT_LOCATION:
					return EventLocation::class;
				case EventBase::EVENT_TMPL_MSG_SENT:
					return EventTmplMsgSent::class;
				default:
					throw new Exception('event type no support:' . $raw_arr['Event']);
			}
		}
		throw new Exception('message type no support:' . $raw_arr['MsgType']);
	}

	public function toXml() {
		return array_to_xml($this->toArray());
	}

	public function toArray() {
		return (array)$this;
	}

	public function jsonSerialize(): array {
		return $this->toArray();
	}
}
