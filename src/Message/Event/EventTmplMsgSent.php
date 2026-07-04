<?php

namespace LFPhp\WechatSdk\Message\Event;

class EventTmplMsgSent extends EventBase {
	public $Status;
	public $Event = self::EVENT_TMPL_MSG_SENT;
}
