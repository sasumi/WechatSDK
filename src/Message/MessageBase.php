<?php

namespace LFPhp\WechatSdk\Message;

use Exception;
use JsonSerializable;
use LFPhp\WechatSdk\Message\Event\EventBase;
use LFPhp\WechatSdk\Message\Event\EventLocation;
use LFPhp\WechatSdk\Message\Event\EventScan;
use LFPhp\WechatSdk\Message\Event\EventSubscribe;
use LFPhp\WechatSdk\Message\Event\EventUnSubscribe;
use LFPhp\WechatSdk\Message\Message\MessageStandard;
use ReflectionObject;
use function LFPhp\WechatSdk\Util\array_to_xml;

class MessageBase implements JsonSerializable {
	const MSG_TYPE_EVENT = 'event';
	const MSG_TYPE_TEXT = 'text';

	//开发者微信号
	public $ToUserName;

	//发送方账号（一个OpenID）
	public $FromUserName;

	//消息创建时间 （整型）
	public $CreateTime;

	//消息类型，文本为text
	public $MsgType;

	//文本消息内容
	public $Content;

	final public static function getMessageInstance($raw_arr){
		$class = self::resolveMessageClass($raw_arr);
		$instance = new $class();
		$ri = new ReflectionObject($instance);
		$properties = $ri->getProperties();

		foreach($properties as $pro){
			$name = $pro->name;
			if(isset($raw_arr[$name])){
				$instance->{$name} = $raw_arr[$name];
			}
		}
		return $instance;
	}

	final public static function resolveMessageClass($raw_arr){
		if($raw_arr['MsgType'] === self::MSG_TYPE_TEXT){
			return MessageStandard::class;
		}
		if($raw_arr['MsgType'] === self::MSG_TYPE_EVENT){
			switch($raw_arr['Event']){
				case EventBase::EVENT_SUBSCRIBE:
					return EventSubscribe::class;
				case EventBase::EVENT_UNSUBSCRIBE:
					return EventUnSubscribe::class;
				case EventBase::EVENT_SCAN:
					return EventScan::class;
				case EventBase::EVENT_LOCATION:
					return EventLocation::class;
			}
		}
		throw new Exception('message type no support');
	}

	public function toXml(){
		return array_to_xml($this->toArray());
	}

	public function toArray(){
		return (array)$this;
	}

	public function jsonSerialize(){
		return $this->toArray();
	}
}
