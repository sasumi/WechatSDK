<?php

namespace LFPhp\WechatSdk\Pay;

use function LFPhp\Func\array_clean_empty;
use function LFPhp\WechatSdk\Util\assert_attrs_no_empty;

/**
 * 原生微信支付、扫码支付
 */
class NativePay extends PayService {
    /**
     * H5下单
     * @param array $param
     * - out_trade_no 商户订单号
     * - notify_url 回调地址
     * - product_name 商品描述
     * - amount 订单金额
     * - currency 货币类型，默认为人民币
     * - expire_timestamp 订单过期时间，单位为时间戳
     * @return string 支付二维码地址
     */
    public static function makeOrder($param) {
        $param = array_merge([
            'out_trade_no' => '',
            'notify_url' => '',
            'product_name' => '',
            'amount' => null,
            'currency' => '',
            'expire_timestamp' => null,
            'attach' => null
        ], $param);

        $param = array_clean_empty($param);
        assert_attrs_no_empty($param, ['out_trade_no', 'product_name', 'amount', 'notify_url']);

        $rsp = self::postJsonSuccess(
            '/v3/pay/transactions/native',
            [
                'appid' => self::getAppId(),
                'mchid' => self::getMerchantId(),
                'description' => $param['product_name'],
                'out_trade_no' => $param['out_trade_no'],
                'notify_url' => $param['notify_url'],
                'attach' => $param['attach'],
                'time_expire' => $param['expire_timestamp'] ? date(DATE_ATOM, $param['expire_timestamp']) : null,
                'amount' => [
                    'total' => (int)$param['amount'],
                    'currency' => $param['currency'] ?: CURRENCY_CNY,
                ]
            ]
        );
        return $rsp['code_url'];
    }
}
