<?php

namespace LFPhp\WechatSdk\Pay;

use Exception;
use LFPhp\WechatSdk\Base\BaseService;
use LFPhp\WechatSdk\Exception\PayException;

use function LFPhp\Func\array_clean_null;
use function LFPhp\Func\assert_via_exception;
use function LFPhp\Func\dump;
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

    protected static function assertResultSuccess($rsp) {
        if ($rsp['code'] && $rsp['code'] !== 'SUCCESS') {
            throw new PayException($rsp['message'], -1);
        }
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
     * @param string $url
     * @param string $request_method
     * @param array|string $param
     * @see https://pay.weixin.qq.com/wiki/doc/apiv3/wechatpay/wechatpay4_0.shtml
     */
    private static function getAuthorization($url, $request_method, $param = '') {
        $timestamp = time();
        $nonce_str = bin2hex(random_bytes(16));
        $body = '';
        $url_path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        // GET请求需要把param拼到query里
        if ($request_method === HTTP_METHOD_GET && $param) {
            $query_arr = [];
            if ($query) {
                parse_str($query, $query_arr);
            }
            if (is_string($param)) {
                parse_str($param, $tmp);
                $param = $tmp;
            } else if (!is_array($param)) {
                throw new Exception('param must be array or string');
            }
            $query_arr = array_merge($query_arr, $param);
            ksort($query_arr); // 微信建议参数按字典序排序
            $query = http_build_query($query_arr);
        }
        $canonical_url = $url_path . ($query ? "?$query" : '');

        //POST请求需要把param转为JSON
        if ($request_method === HTTP_METHOD_POST && $param) {
            if (is_string($param)) {
                $body = $param;
            } else if (is_array($param)) {
                $body = json_encode($param, JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('param must be array or string');
            }
        }
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
     * @param mixed $param
     * @param string $request_method
     * @param array $file_map
     * @param array $headers
     */
    protected static function sendJsonRequest($url, $param = null, $request_method = HTTP_METHOD_POST, array $file_map = [], $headers = []) {
        if (!is_url($url)) {
            $url = self::patchApiUrl($url);
        }
        //POST方法，直接转换成JSON，避免后面计算签名时出错
        if ($request_method === HTTP_METHOD_POST && is_array($param)) {
            $param = array_clean_null($param);
            $param = json_encode($param, JSON_UNESCAPED_UNICODE);
        }
        $headers = array_merge([
            'Accept' => 'application/json',
            'User-Agent' => 'PHP-WechatSdk',
            'Authorization' => 'WECHATPAY2-SHA256-RSA2048 ' . self::getAuthorization($url, $request_method, $param),
        ], $headers);
        return parent::sendJsonRequest($url, $param, $request_method, $file_map, $headers);
    }

    public static function getCertificates() {
        $rsp = self::getJsonSuccess('v3/certificates');
        //todo
        dump($rsp, 1);
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
        $wechatpay_timestamp = $headers['Wechatpay-Timestamp'] ?? null;
        $wechatpay_nonce = $headers['Wechatpay-Nonce'] ?? null;
        $wechatpay_signature = $headers['Wechatpay-Signature'] ?? null;
        $wechatpay_serial = $headers['Wechatpay-Serial'] ?? null;

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
    public static function closeOrderByOutTradeNo($out_trade_no) {
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
     * @param string $param['out_refund_no']  商户退款单号
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
     *     "out_trade_no": "1217752501201407033233368018",
     *     "channel": "ORIGINAL",
     *     "status": "SUCCESS",
     *     "create_time": "2019-08-26T10:39:04+08:00",
     }
     */
    public static function applyRefund($param) {
        $transaction_id = $param['transaction_id'] ?? null;
        $out_trade_no = $param['out_trade_no'] ?? null;
        $out_refund_no = $param['out_refund_no'];
        $reason = $param['reason'] ?? null;
        $notify_url = $param['notify_url'] ?? null;
        $amount = $param['amount'];
        $total = $param['total'];
        $currency = $param['currency'] ?? CURRENCY_CNY;
        $funds_account = $param['funds_account'] ?? null;

        if (!$amount) {
            throw new PayException('退款金额amount不能为空');
        }
        if (!$total) {
            throw new PayException('订单金额total不能为空');
        }
        if (!$out_refund_no) {
            throw new PayException('out_refund_no不能为空');
        }
        if (!$transaction_id && !$out_trade_no) {
            throw new PayException('transaction_id和out_trade_no不能同时为空');
        }

        $rsp = self::postJsonSuccess("/v3/refund/domestic/refunds", [
            'transaction_id' => $transaction_id,
            'out_trade_no' => $out_trade_no,
            'out_refund_no' => $out_refund_no,
            'reason' => $reason,
            'notify_url' => $notify_url,
            'funds_account' => $funds_account,
            'amount' => [
                'refund' => $amount,
                'total' => $total,
                'currency' => $currency,
            ]
        ]);
        return $rsp;
    }

    /**
     * 发起异常退款
     * 提交退款申请后，退款结果通知或查询退款确认状态为退款异常，可调用此接口发起异常退款处理。支持退款至用户、退款至交易商户银行账户两种处理方式。
     */
    public static function applyAbnormalRefund($param) {
        if (!$param['refund_id']) {
            throw new PayException('refund_id不能为空');
        }
        if (!$param['out_refund_no']) {
            throw new PayException('out_refund_no不能为空');
        }
        if (!$param['type']) {
            throw new PayException('异常退款处理方式type不能为空');
        }

        $rsp = self::postJsonSuccess("/v3/refund/domestic/refunds/{$param['refund_id']}/apply-abnormal-refund", [
            'out_refund_no' => $param['out_refund_no'], //【商户退款单号】商户申请退款时传入的商户系统内部退款单号。
            'type' => $param['type'],
            'bank_type' => $param['bank_type'] ?? null, //银行类型，值列表详见银行类型。仅支持招行、交通银行、农行、建行、工商、中行、平安、浦发、中信、光大、民生、兴业、广发、邮储、宁波银行的借记卡。若退款至用户此字段必填。
            'bank_account' => $param['bank_account'] ?? null, //收款银行卡号
            'real_name' => $param['real_name'] ?? null, //收款用户姓名
        ]);
        return $rsp;
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
