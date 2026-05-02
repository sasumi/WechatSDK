<?php

namespace LFPhp\WechatSdk\Pay;

use function LFPhp\Func\assert_via_exception;

class Order extends PayService {
    /**
     * 关闭订单
     * @param string $out_trade_no 商户订单号
     */
    public static function closeOrderByOutTradeNo($out_trade_no) {
        $rsp = self::postJson("/v3/pay/transactions/out-trade-no/$out_trade_no/close", [
            'mchid' => self::getMerchantId()
        ]);
        assert_via_exception($rsp === null, '关闭订单失败');
        return true;
    }

    /**
     * 查询订单
     * @param string $transaction_id
     * @return array
     */
    public static function queryOrderByTransactionId($transaction_id) {
        $rsp = self::getJsonSuccess("/v3/pay/transactions/id/$transaction_id", [
            'mchid' => self::getMerchantId()
        ]);
        return $rsp;
    }

    /**
     * 查询订单
     * @param string $out_trade_no
     * @return array
     */
    public static function queryOrderByOutTradeNo($out_trade_no) {
        $rsp = self::getJsonSuccess("/v3/pay/transactions/out-trade-no/$out_trade_no", [
            'mchid' => self::getMerchantId()
        ]);
        return $rsp;
    }
}
