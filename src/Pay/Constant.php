<?php

namespace LFPhp\WechatSdk\Pay;

//货币类型
const CURRENCY_CNY = 'CNY';
const CURRENCY_USD = 'USD';
const CURRENCY_HKD = 'HKD';
const CURRENCY_JPY = 'JPY';
const CURRENCY_AUD = 'AUD';
const CURRENCY_NZD = 'NZD';
const CURRENCY_KRW = 'KRW';
const CURRENCY_THB = 'THB';
const CURRENCY_SGD = 'SGD';
const CURRENCY_EUR = 'EUR';
const CURRENCY_GBP = 'GBP';
const CURRENCY_TWD = 'TWD';
const CURRENCY_PHP = 'PHP';
const CURRENCY_IDR = 'IDR';
const CURRENCY_VND = 'VND';
const CURRENCY_MYR = 'MYR';

//交易状态
const TRADE_STATE_NOTPAY = 'NOTPAY';
const TRADE_STATE_SUCCESS = 'SUCCESS';
const TRADE_STATE_REFUND = 'REFUND';
const TRADE_STATE_CLOSED = 'CLOSED';
const TRADE_STATE_REVOKED = 'REVOKED'; //（仅付款码支付会返回）
const TRADE_STATE_USERPAYING = 'USERPAYING'; //（仅付款码支付会返回）
const TRADE_STATE_PAYERROR = 'PAYERROR'; //（仅付款码支付会返回）
const TRADE_STATE_MAP = [
    TRADE_STATE_NOTPAY => '未支付',
    TRADE_STATE_SUCCESS => '支付成功',
    TRADE_STATE_REFUND => '转入退款',
    TRADE_STATE_CLOSED => '已关闭',
    TRADE_STATE_REVOKED => '已撤销（刷卡支付）',
    TRADE_STATE_USERPAYING => '用户支付中',
    TRADE_STATE_PAYERROR => '支付失败'
];

//交易类型
const TRADE_TYPE_JSAPI = 'JSAPI';
const TRADE_TYPE_NATIVE = 'NATIVE';
const TRADE_TYPE_APP = 'APP';
const TRADE_TYPE_MICROPAY = 'MICROPAY';
const TRADE_TYPE_MINIPROG = 'MINIPROG';
const TRADE_TYPE_WALLET = 'WALLET';
const TRADE_TYPE_MAP = [
    TRADE_TYPE_JSAPI => '公众号支付',
    TRADE_TYPE_NATIVE => '扫码支付',
    TRADE_TYPE_APP => 'APP支付',
    TRADE_TYPE_MICROPAY => '刷卡支付',
    TRADE_TYPE_MINIPROG => '小程序支付',
    TRADE_TYPE_WALLET => '微信钱包'
];