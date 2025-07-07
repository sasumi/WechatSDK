<?php

namespace LFPhp\WechatSdk\Exception;

const CODE_MAP = [
    '' => '请求成功',
    "PARAM_ERROR" => "参数错误",
    "INVALID_REQUEST" => "无效请求",
    "MCH_NOT_EXISTS" => "商户号不存在",
    "SIGN_ERROR" => "签名错误",
    "RULE_LIMIT" => "业务规则限制",
    "TRADE_ERROR" => "交易错误",
    "FREQUENCY_LIMITED" => "频率超限",
    "SYSTEM_ERROR" => "系统错误",
];
class PayException extends \Exception {
}
