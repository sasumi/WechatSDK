<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

class CMsgMPNewsArticle extends CMsgAbstract {
    /** @var string 图文消息文章ID */
    public $article_id;

    public function getType() {
        return 'mpnewsarticle';
    }

    public function getData() {
        return [
            'article_id' => $this->article_id,
        ];
    }
}
