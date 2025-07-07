<?php

namespace LFPhp\WechatSdk\Pay;

use LFPhp\WechatSdk\Base\BaseService;
use LFPhp\WechatSdk\Exception\PayException;

use function LFPhp\Func\assert_via_exception;
use function LFPhp\Func\is_url;
use const LFPhp\Func\HTTP_METHOD_GET;
use const LFPhp\Func\HTTP_METHOD_POST;

/**
 * @see https://github.com/wechatpay-apiv3/wechatpay-php
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012791874
 */
abstract class PayService extends BaseService {
    private static $merchant_info = [
        'app_id' => '', //「应用ID」
        'merchant_id' => '', //「商户号」

        'merchant_certificate_serial' => '', //「商户API证书」的「证书序列号」
        'merchant_private_key_file' => '', //「商户API证书」的「证书序列号」

        'platform_certificate_serial' => '', //「微信支付平台证书」的「平台证书序列号」, 可以从「微信支付平台证书」文件解析，也可以在 商户平台 -> 账户中心 -> API安全 查询到
        'platform_certificate_file' => '', //从本地文件中加载「微信支付平台证书」，可由内置CLI工具下载到，用来验证微信支付应答的签名

        'platform_public_key_id' => '', //「微信支付公钥」的「微信支付公钥ID」，需要在 商户平台 -> 账户中心 -> API安全 查询
        'platform_public_key_file' => '', //从本地文件中加载「微信支付公钥」，用来验证微信支付应答的签名
    ];

    final private function __construct() {
    }

    public static function setMerchantInfo($merchant_info) {
        $keys = array_diff(array_keys(self::$merchant_info), array_keys($merchant_info));
        if ($keys) {
            throw new PayException('keys required:' . join(',', $keys));
        }
        self::$merchant_info = $merchant_info;
    }

    protected function getMerchantInfo() {
        return self::$merchant_info;
    }

    protected function getAppId() {
        return self::$merchant_info['app_id'];
    }

    protected function getMerchantId() {
        return self::$merchant_info['merchant_id'];
    }

    /**
     * 微信支付签名
     */
    private static function getAuthorization($url, $request_method, $param) {
        $timestamp = time();
        $nonce_str = bin2hex(random_bytes(16));
        $url_path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        // GET请求需要把param拼到query里
        if ($request_method === HTTP_METHOD_GET && $param) {
            $query_arr = [];
            if ($query) {
                parse_str($query, $query_arr);
            }
            $query_arr = array_merge($query_arr, $param);
            ksort($query_arr); // 微信建议参数按字典序排序
            $query = http_build_query($query_arr);
        }
        $canonical_url = $url_path . ($query ? "?$query" : '');
        $body = $request_method === HTTP_METHOD_GET ? '' : json_encode($param, JSON_UNESCAPED_UNICODE);
        $message = "{$request_method}\n{$canonical_url}\n{$timestamp}\n{$nonce_str}\n{$body}\n";

        $privateKey = file_get_contents(self::$merchant_info['merchant_private_key_file']);
        openssl_sign($message, $signature, $privateKey, 'sha256WithRSAEncryption');
        $signature = base64_encode($signature);

        $serial_no = self::$merchant_info['merchant_certificate_serial'];
        $merchant_id = self::getMerchantId();
        $auth_str = "mchid=\"{$merchant_id}\",nonce_str=\"{$nonce_str}\",signature=\"{$signature}\",timestamp=\"{$timestamp}\",serial_no=\"{$serial_no}\"";
        return $auth_str;
    }

    /**
     * 补全API URL
     */
    private static function patchApiUrl($url) {
        $url = ltrim($url, '/');
        return "https://api.mch.weixin.qq.com/{$url}";
    }

    /**
     * 发送JSON请求
     * @param string $url
     * @param array $param
     * @param string $request_method
     * @param array $file_map
     * @param array $headers
     */
    protected static function sendJsonRequest($url, array $param = [], $request_method = HTTP_METHOD_POST, array $file_map = [], $headers = []) {
        if (!is_url($url)) {
            $url = self::patchApiUrl($url);
        }
        $headers = array_merge([
            'Accept' => 'application/json',
            'User-Agent' => 'PHP-WechatSdk',
            'Authorization' => 'WECHATPAY2-SHA256-RSA2048 ' . self::getAuthorization($url, $request_method, $param),
        ], $headers);
        return parent::sendJsonRequest($url, $param, $request_method, $file_map, $headers);
    }

    public static function getCertificates() {
        $rsp = self::getJson('v3/certificates');
        echo (string) $rsp->getBody(), PHP_EOL;
    }

    /**
     * 获取回调数据
     */
    public static function getJsonFromCallback() {
        return json_decode(self::validateCallback(), true);
    }

    /**
     * 验证回调数据是否来自微信支付
     */
    public static function validateCallback() {
        $headers = getallheaders();
        $wechatpay_timestamp = $headers['Wechatpay-Timestamp'] ?? '';
        $wechatpay_nonce = $headers['Wechatpay-Nonce'] ?? '';
        $wechatpay_signature = $headers['Wechatpay-Signature'] ?? '';
        $wechatpay_serial = $headers['Wechatpay-Serial'] ?? '';

        //获取请求体
        $body = file_get_contents('php://input');

        //拼接待签名字符串
        $message = "{$wechatpay_timestamp}\n{$wechatpay_nonce}\n{$body}\n";

        //读取微信支付平台证书
        $platform_cert = file_get_contents(self::$merchant_info['platform_certificate_file']);
        $public_key = openssl_pkey_get_public($platform_cert);

        //验证签名
        $result = openssl_verify($message, base64_decode($wechatpay_signature), $public_key, OPENSSL_ALGO_SHA256);

        //释放资源
        openssl_free_key($public_key);
        if ($result !== 1) {
            throw new PayException('签名验证失败');
        }
        return $body;
    }

    /**
     * 关闭订单
     */
    public static function closeOrder($out_trade_no) {
        $rsp = self::postJson("/v3/pay/transactions/out-trade-no/$out_trade_no/close", [
            'mchid' => self::getMerchantId()
        ]);
        assert_via_exception($rsp === null, '关闭订单失败');
    }

    /**
     * 申请退款
     * @param array $param
     * @param string $param['transaction_id']  微信支付订单号
     * @param string $param['out_trade_no']  商户订单号
     * @param string $param['refund_no']  商户退款单号
     * @param string $param['reason']  退款原因
     * @param string $param['notify_url']  退款结果通知URL
     * @param int $param['amount']  退款金额
     * @param int $param['total']  订单金额
     * @param string $param['funds_account']  退款资金来源
     * 可选值：
     * UNSETTLED：未结算资金
     * AVAILABLE：可用余额
     * UNAVAILABLE：不可用余额
     * @return array
     * {
     *     "refund_id": "2006001091201407033233368018",
     *     "out_refund_no": "1217752501201407033233368018",
     *     "transaction_id": "1217752501201407033233368018",
     *    "out_trade_no": "1217752501201407033233368018",
     *     "channel": "ORIGINAL",
     *     "status": "SUCCESS",
     *     "create_time": "2019-08-26T10:39:04+08:00",
     }
     */
    public static function refund($param) {
        $transaction_id = $param['transaction_id'] ?? '';
        $out_trade_no = $param['out_trade_no'] ?? '';
        $refund_no = $param['refund_no'] ?? '';
        $reason = $param['reason'] ?? '';
        $notify_url = $param['notify_url'] ?? '';
        $amount = $param['amount'] ?? 0;
        $total = $param['total'] ?? 0;
        if (!$transaction_id && !$out_trade_no) {
            throw new PayException('transaction_id和out_trade_no不能同时为空');
        }

        $rsp = self::postJson("/v3/refund/domestic/refunds", [
            'transaction_id' => $transaction_id,
            'out_trade_no' => $out_trade_no,
            'out_refund_no' => $refund_no,
            'reason' => $reason,
            'notify_url' => $notify_url,
            'funds_account' => '',
            'amount' => [
                'refund' => $amount,
                'total' => $total,
                'currency' => CURRENCY_CNY,
            ]
        ]);
        self::assertResultSuccess($rsp);
        return $rsp;
    }

    /**
     * 查询订单
     * @param string $transaction_id
     * @return array
     */
    public static function queryOrderByTransactionId($transaction_id) {
        $rsp = self::getJson("/v3/pay/transactions/id/$transaction_id", [
            'mchid' => self::getMerchantId()
        ]);
        self::assertResultSuccess($rsp);
        return $rsp;
    }
    /**
     * 查询订单
     * @param string $out_trade_no
     * @return array
     */
    public static function queryOrderByOutTradeNo($out_trade_no) {
        $rsp = self::getJson("/v3/pay/transactions/out-trade-no/$out_trade_no", [
            'mchid' => self::getMerchantId()
        ]);
        self::assertResultSuccess($rsp);
        return $rsp;
    }
}
