<?php

namespace LFPhp\WechatSdk\Message\Message;

use LFPhp\WechatSdk\Message\MessageBase;

class MessageImage extends MessageBase {
	public $MsgType = self::MSG_TYPE_IMAGE;

	//图片，结构为：['MediaId'=>'abc']
	public $Image;
}
