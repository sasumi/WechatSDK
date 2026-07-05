<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

class CMsgImage extends CMsgAbstract {
    /** @var string 图片媒体ID */
    public $media_id;

    public function getType() {
        return 'image';
    }

    public function getData() {
        return [
            'media_id' => $this->media_id,
        ];
    }
}