<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

class CMsgWxCard extends CMsgAbstract {
    /** @var string 卡券ID */
    public $card_id;

    public function getType() {
        return 'wxcard';
    }

    public function getData() {
        return [
            'card_id' => $this->card_id,
        ];
    }
}
