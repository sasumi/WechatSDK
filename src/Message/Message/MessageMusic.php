<?php

namespace LFPhp\WechatSdk\Message\Message;

use LFPhp\WechatSdk\Message\MessageBase;

class MessageMusic extends MessageBase {
	public $MsgType = self::MSG_TYPE_MUSIC;

	public $Music;

	public function setMusic($title, $description, $music_url, $hq_music_url, $thumb_media_id) {
		$this->Music = [
			'Title' => $title,
			'Description' => $description,
			'MusicUrl' => $music_url,
			'HQMusicUrl' => $hq_music_url,
			'ThumbMediaId' => $thumb_media_id,
		];
	}
}
