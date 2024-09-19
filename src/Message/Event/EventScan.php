<?php

namespace LFPhp\WechatSdk\Message\Event;

class EventScan extends EventBase {

	//事件KEY值，是一个32位无符号整数，即创建二维码时的二维码scene_id
	public $EventKey;

	public $Event = self::EVENT_SCAN;

	//二维码的ticket，可用来换取二维码图片
	public $Ticket;
}
