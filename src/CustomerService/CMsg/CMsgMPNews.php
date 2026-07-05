<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

class CMsgMPNews extends CMsgAbstract {
    /** @var string 图文消息媒体ID */
    public $media_id;

    public function getType() {
        return 'mpnews';
    }

    public function getData() {
        return [
            'media_id' => $this->media_id,
        ];
    }
}
