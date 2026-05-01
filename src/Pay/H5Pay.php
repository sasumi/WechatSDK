<?php

namespace LFPhp\WechatSdk\Pay;

use function LFPhp\Func\array_clean_empty;
use function LFPhp\WechatSdk\Util\assert_attrs_no_empty;
use function LFPhp\WechatSdk\Util\in_android;
use function LFPhp\WechatSdk\Util\in_ios;

/**
 * H5页面
 */
class H5Pay extends PayService {
    const SCENE_TYPE_WAP = 'Wap';
    const SCENE_TYPE_IOS = 'IOS';
    const SCENE_TYPE_ANDROID = 'Android';

    public static function detectedSceneType() {
        if (in_ios()) {
            return self::SCENE_TYPE_IOS;
        }
        if (in_android()) {
            return self::SCENE_TYPE_ANDROID;
        }
        return self::SCENE_TYPE_WAP;
    }

    /**
     * H5下单
     * @return string h5 支付页面链接
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
            'payer_client_ip' => null,
            'scene_type' => '',
        ], $param);

        $param = array_clean_empty($param);
        assert_attrs_no_empty($param, ['out_trade_no', 'product_name', 'amount', 'notify_url']);

        $rsp = self::postJson(
            'v3/pay/transactions/h5',
            [
                'appid' => self::getAppId(),
                'mchid' => self::getMerchantId(),
                'description' => $param['product_name'],
                'out_trade_no' => $param['out_trade_no'],
                'notify_url' => $param['notify_url'],
                'time_expire' => $param['expire_timestamp'] ? date('Y-m-d\TH:i:sP', $param['expire_timestamp']) : null,
                'attach' => $param['attach'],
                'amount' => [
                    'total' => $param['amount'],
                    'currency' => $param['currency'] ?: CURRENCY_CNY,
                ],
                'scene_info' => [
                    'payer_client_ip' => $param['payer_client_ip'],
                    'h5_info' => [
                        'type' => $param['scene_type'] ?: self::detectedSceneType(),
                    ],
                ],
            ]
        );
        self::assertResultSuccess($rsp);
        return $rsp['h5_url'];
    }
}
