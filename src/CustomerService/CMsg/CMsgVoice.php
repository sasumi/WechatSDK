<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

class CMsgVoice extends CMsgAbstract {
    /** @var string 语音媒体ID */
    public $media_id;

    public function getType() {
        return 'voice';
    }

    public function getData() {
        return [
            'media_id' => $this->media_id,
        ];
    }
}