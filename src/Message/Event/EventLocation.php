<?php

namespace LFPhp\WechatSdk\Message\Event;

class EventLocation extends EventBase {
	/** @var float 纬度 */
	public $Latitude;

	/** @var float 经度 */
	public $Longitude;

	/** @var float 精度 */
	public $Precision;

	/** @var string 事件类型 */
	public $Event = self::EVENT_LOCATION;
}
