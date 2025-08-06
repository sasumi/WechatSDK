<?php

namespace LFPhp\WechatSdk\Message\Message;

use LFPhp\WechatSdk\Message\MessageBase;

class MessageVideo extends MessageBase {
	public $MsgType = self::MSG_TYPE_VIDEO;

	public $Video;

	public function setVideo($media_id, $title, $description = '') {
		$this->Video = ['MediaId' => $media_id, 'Title' => $title, 'Description' => $description];
	}
}
