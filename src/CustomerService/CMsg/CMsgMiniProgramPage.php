<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

class CMsgMiniProgramPage extends CMsgAbstract {

    /** @var string 小程序消息标题 */
    public $title;

    /** @var string 小程序AppID */
    public $app_id;

    /** @var string 小程序页面路径 */
    public $page_path;

    /** @var string 小程序消息缩略图媒体ID */
    public $thumb_media_id;

    public function getType() {
        return 'miniprogrampage';
    }

    public function getData() {
        return [
            'title' => $this->title,
            'appid' => $this->app_id,
            'pagepath' => $this->page_path,
            'thumb_media_id' => $this->thumb_media_id,
        ];
    }
}
