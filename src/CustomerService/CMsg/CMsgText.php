<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

class CMsgText extends CMsgAbstract {
    /** @var string 文本消息内容
     * 文本内容，支持插入跳小程序的文字链，
     * 例如「文本内容点击跳小程序」
     * 说明： 
     * 1.data-miniprogram-appid 项，填写小程序appid，则表示该链接跳小程序； 
     * 2.data-miniprogram-path项，填写小程序路径，路径与app.json中保持一致，可带参数； 3.对于不支持data-miniprogram-appid 项的客户端版本，如果有herf项，则仍然保持跳href中的网页链接； 4.data-miniprogram-appid对应的小程序必须与公众号有绑定关系。
     */
    public $content;

    public function getType() {
        return 'text';
    }

    public function getData() {
        return [
            'content' => $this->content,
        ];
    }
}
