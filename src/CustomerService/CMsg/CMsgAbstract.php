<?php

namespace LFPhp\WechatSdk\CustomerService\CMsg;

abstract class CMsgAbstract {
    final public function __construct($data = []) {
        foreach($data as $k => $v) {
            $this->$k = $v;
        }
    }

    abstract public function getType();
    abstract public function getData();
}
