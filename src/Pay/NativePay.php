<?php

namespace LFPhp\WechatSdk\Pay;

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
     * @return string 支付二维码地址
     */
    public static function makeOrder($param) {
        $param = array_merge([
            'out_trade_no' => '',
            'notify_url' => '',
            'product_name' => '',
            'amount' => 0,
            'currency' => CURRENCY_CNY
        ], $param);
        $rsp = self::postJsonSuccess(
            '/v3/pay/transactions/native',
            [
                'appid' => self::getAppId(),
                'mchid' => self::getMerchantId(),
                'description' => $param['product_name'],
                'out_trade_no' => $param['out_trade_no'],
                'notify_url' => $param['notify_url'],
                'amount' => [
                    'total' => $param['amount'],
                    'currency' => $param['currency'],
                ]
            ]
        );
        return $rsp['code_url'];
    }
}
