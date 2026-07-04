<?php

namespace LFPhp\WechatSdk\Message\Event;

class EventScan extends EventBase {

	/** @var int 事件KEY值，是一个32位无符号整数，即创建二维码时的二维码scene_id */
	public $EventKey;

	/** @var string 事件类型 */
	public $Event = self::EVENT_SCAN;

	/** @var string 二维码的ticket，可用来换取二维码图片 */
	public $Ticket;
}
