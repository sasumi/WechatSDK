<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

class CMsgMusic extends CMsgAbstract {
    /** @var string 音乐标题 */
    public $title;

    /** @var string 音乐描述 */
    public $description;

    /** @var string 音乐链接 */
    public $musicurl;

    /** @var string 高质量音乐链接 */
    public $hqmusicurl;

    /** @var string 缩略图媒体ID */
    public $thumb_media_id;

    public function getType() {
        return 'music';
    }

    public function getData() {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'musicurl' => $this->musicurl,
            'hqmusicurl' => $this->hqmusicurl,
            'thumb_media_id' => $this->thumb_media_id,
        ];
    }
}
