<?php

namespace LFPhp\WechatSdk\Message\Event;

class EventLocation extends EventBase {
	public $Latitude;
	public $Longitude;
	public $Precision;

	public $Event = self::EVENT_LOCATION;
}
