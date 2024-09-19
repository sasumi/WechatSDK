<?php

namespace LFPhp\WechatSdk\Message\Event;

class EventSubscribe extends EventBase {
	public $Event = self::EVENT_SUBSCRIBE;

	//事件KEY值，qrscene_为前缀，后面为二维码的参数值
	public $EventKey;

	//二维码的ticket，可用来换取二维码图片
	public $Ticket;
}
