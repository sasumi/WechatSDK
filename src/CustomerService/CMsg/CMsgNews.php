<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

class CMsgNews extends CMsgAbstract {
    /** @var string 图文消息标题 */
    public $title;

    /** @var string 图文消息描述 */
    public $description;

    /** @var string 图文消息链接 */
    public $url;

    /** @var string 图文消息图片链接 */
    public $picurl;

    public function getType() {
        return 'news';
    }

    public function getData() {
        return [
            'articles' => [
                [
                    'title' => $this->title,
                    'description' => $this->description,
                    'url' => $this->url,
                    'picurl' => $this->picurl,
                ],
            ],
        ];
    }
}
