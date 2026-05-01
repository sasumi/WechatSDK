<?php

namespace LFPhp\WechatSdk\Pay;

use function LFPhp\Func\array_clean_empty;
use function LFPhp\Func\get_client_ip;
use function LFPhp\WechatSdk\Util\assert_attrs_no_empty;

/**
 * JSAPI支付
 */
class JsapiPay extends PayService {
    /**
     * 生成微信JSAPI支付参数
     * @param string $prepay_id 预支付交易会话标识
     * @return array 生成微信JSAPI支付参数
     */
    public static function generateWechatJsapiPayParam($prepay_id) {
        $appId = self::getAppId();
        $timestamp = strval(time());
        $nonceStr = self::generateNonceStr();
        $message = "$appId\n$timestamp\n$nonceStr\nprepay_id=$prepay_id\n";
        
        $privateKey = file_get_contents(self::$merchant_info['merchant_api_key_file']);
        openssl_sign($message, $signature, $privateKey, 'sha256WithRSAEncryption');
        $signature = base64_encode($signature);

        $param = [
            'appId' => $appId,
            'timeStamp' => $timestamp,
            'nonceStr' => $nonceStr,
            'package' => "prepay_id=$prepay_id",
            'signType' => 'RSA',
            'paySign' => $signature,
        ];
        return $param;
    }

    /**
     * JSAPI下单
     * @return string prepay_id 预支付交易会话标识
     */
    public static function makeOrder(array $param) {
        $param = array_merge([
            'out_trade_no' => '',
            'notify_url' => '',
            'product_name' => '',
            'amount' => null,
            'currency' => '',
            'expire_timestamp' => null,
            'attach' => null,
            'payer_open_id'=>null,
            'payer_client_ip' => null,
        ], $param);

        $param = array_clean_empty($param);
        assert_attrs_no_empty($param, ['out_trade_no', 'product_name', 'amount', 'notify_url', 'payer_open_id']);

        $rsp = self::postJson(
            'v3/pay/transactions/jsapi',
            [
                'appid' => self::getAppId(),
                'mchid' => self::getMerchantId(),
                'description' => $param['product_name'],
                'out_trade_no' => $param['out_trade_no'],
                'notify_url' => $param['notify_url'],
                'time_expire' => $param['expire_timestamp'] ? date('Y-m-d\TH:i:sP', $param['expire_timestamp']) : null,
                'attach' => $param['attach'],
                'payer' => [
                    'openid' => $param['payer_open_id'],
                ],
                'amount' => [
                    'total' => $param['amount'],
                    'currency' => $param['currency'] ?: CURRENCY_CNY,
                ],
                'scene_info' => [
                    'payer_client_ip' => $param['payer_client_ip'] ?: get_client_ip(),
                ],
            ]
        );
        self::assertResultSuccess($rsp);
        return $rsp['prepay_id'];
    }
}
