<?php

namespace LFPhp\WechatSdk\Message\Message;

use LFPhp\WechatSdk\Message\MessageBase;

class MessageText extends MessageBase {
	public $MsgType = self::MSG_TYPE_TEXT;

	//文本消息内容
	public $Content;
}
