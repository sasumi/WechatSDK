<?php

namespace LFPhp\WechatSdk\Pay;

/**
 * H5页面
 */
class H5Pay extends PayService {
    /**
     * H5下单
     * @param string $out_trade_no 商户订单号
     * @param string $notify_url 回调地址
     * @param string $product_name 商品描述
     * @param int $amount 订单金额
     * @param string $currency 货币类型，默认为人民币
     * @return string h5 支付页面
     */
    public static function makeOrder($out_trade_no, $notify_url, $product_name, $amount, $currency = CURRENCY_CNY) {
        $rsp = self::postJson(
            'v3/pay/transactions/h5',
            [
                'appid' => self::getAppId(),
                'mchid' => self::getMerchantId(),
                'description' => $product_name,
                'out_trade_no' => $out_trade_no,
                'notify_url' => $notify_url,
                'amount' => [
                    'total' => $amount,
                    'currency' => $currency,
                ]
            ]
        );
        self::assertResultSuccess($rsp);
        return $rsp['h5_url'];  
    }

}
