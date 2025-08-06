<?php

namespace LFPhp\WechatSdk\Message\Message;

use LFPhp\WechatSdk\Message\MessageBase;

class MessageVoice extends MessageBase {
	public $MsgType = self::MSG_TYPE_VOICE;

	public $Voice;

	public function setMediaId($media_id) {
		$this->Voice = ['MediaId' => $media_id];
	}
}
