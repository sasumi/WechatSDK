<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

class CMsgVideo extends CMsgAbstract {
    /** @var string 视频媒体ID */
    public $media_id;

    /** @var string 视频消息缩略图的媒体ID */
    public $thumb_media_id;

    /** @var string 视频消息标题 */
    public $title;

    /** @var string 视频消息描述 */
    public $description;

    public function getType() {
        return 'video';
    }

    public function getData() {
        return [
            'media_id' => $this->media_id,
            'thumb_media_id' => $this->thumb_media_id,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
