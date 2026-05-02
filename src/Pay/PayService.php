<?php

namespace LFPhp\WechatSdk\Pay;

use Exception;
use LFPhp\Logger\Logger;
use LFPhp\WechatSdk\Base\BaseService;
use LFPhp\WechatSdk\Exception\PayException;

use function LFPhp\Func\array_clean_null;
use function LFPhp\Func\dump;
use function LFPhp\Func\is_url;
use const LFPhp\Func\HTTP_METHOD_GET;
use const LFPhp\Func\HTTP_METHOD_POST;

/**
 * @see https://github.com/wechatpay-apiv3/wechatpay-php
 * @see https://pay.weixin.qq.com/doc/v3/merchant/4012791874
 */
abstract class PayService extends BaseService {
    protected static $merchant_info = [
        'app_id' => '', //「应用ID」
        'merchant_id' => '', //「商户号」
        'apiv3_key' => '', //「APIv3密钥」用于解密回调通知

        'merchant_api_serial' => '', //「商户API证书」的「证书序列号」
        'merchant_api_key_file' => '', //「商户API证书」的「证书文件路径」

        'platform_serial' => '', //「微信支付平台证书」的「平台证书序列号」, 可以从「微信支付平台证书」文件解析，也可以在 商户平台 -> 账户中心 -> API安全 查询到
        'platform_cert_file' => '', //从本地文件中加载「微信支付平台证书」，可由内置CLI工具下载到，用来验证微信支付应答的签名
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

    protected static function getAppId() {
        return self::$merchant_info['app_id'];
    }

    protected static function getMerchantId() {
        return self::$merchant_info['merchant_id'];
    }

    /**
     * 生成随机字符串
      * @return string 生成的随机字符串
     */
    protected static function generateNonceStr(){
        return bin2hex(random_bytes(16));
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
        $nonce_str = self::generateNonceStr();
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

        $privateKey = file_get_contents(self::$merchant_info['merchant_api_key_file']);
        openssl_sign($message, $signature, $privateKey, 'sha256WithRSAEncryption');
        $signature = base64_encode($signature);
        $serial_no = self::$merchant_info['merchant_api_serial'];
        $merchant_id = self::getMerchantId();

        Logger::debug("merchant_info:", self::$merchant_info);
        Logger::debug("signature message:", $message);
        Logger::debug("signature:", $signature);

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
        if (is_array($param)) {
            $param = array_clean_null($param);
        }
        $headers = array_merge([
            'Accept' => 'application/json',
            'User-Agent' => 'PHP-WechatSdk',
            'Authorization' => 'WECHATPAY2-SHA256-RSA2048 ' . self::getAuthorization($url, $request_method, $param),
        ], $headers);
        Logger::info("Request Headers:", $headers);
        return parent::sendJsonRequest($url, $param, $request_method, $file_map, $headers);
    }

    public static function getCertificates() {
        $rsp = self::getJsonSuccess('v3/certificates');
        //todo
        dump($rsp, 1);
    }

    /**
     * 获取回调数据（已解密）
     */
    public static function getJsonFromCallback() {
        $data = self::validateCallback();
        $callback_data = json_decode($data, true);

        // 解密 resource 数据
        if (isset($callback_data['resource'])) {
            $resource = $callback_data['resource'];
            if ($resource['algorithm'] === 'AEAD_AES_256_GCM') {
                $decrypted = self::decryptAES256GCM(
                    $resource['ciphertext'],
                    $resource['nonce'],
                    $resource['associated_data']
                );
                $info = json_decode($decrypted, true);
                $callback_data['order_info'] = [
                    'payer_openid' => $info['payer']['openid'] ?? '',
                    'out_trade_no' => $info['out_trade_no'] ?? '',
                    'transaction_id' => $info['transaction_id'] ?? '',
                    'trade_type' => $info['trade_type'] ?? '', //交易类型，见TRADE_TYPE_MAP
                    'trade_state' => $info['trade_state'] ?? '', //交易状态，见TRADE_STATE_MAP
                    'amount_value' => $info['amount']['total'] ?? 0, //订单金额，单位为分
                    'amount_currency' => $info['amount']['currency'] ?? '', //订单金额币种，符合ISO 4217标准的三位字母代码，默认人民币：CNY
                    'payer_total' => $info['amount']['payer_total'] ?? 0, //用户支付金额，单位为分
                    'payer_currency' => $info['amount']['payer_currency'] ?? '', //用户支付金额币种，符合ISO 4217标准的三位字母代码，默认人民币：CNY
                    'bank_type' => $info['bank_type'] ?? '', //银行类型，采用字符串类型的银行标识，银行类型见银行类型列表
                    'success_time' => $info['success_time'] ? date('Y-m-d H:i:s', strtotime($info['success_time'])) : '', //转本地时间，原格式为UTC格式，如 "2020-12-31T23:59:59+00:00"
                ];
            }
        }

        return $callback_data;
    }

    public static function responseSuccessToWechat() {
        header('Content-Type: text/xml');
        echo '<xml>
  <return_code><![CDATA[SUCCESS]]></return_code>
  <return_msg><![CDATA[OK]]></return_msg>
</xml>';
    }

    /**
     * AEAD_AES_256_GCM 解密
     * @param string $ciphertext 加密文本（Base64编码）
     * @param string $nonce 随机串
     * @param string $associated_data 附加数据
     * @return string 解密后的明文
     */
    private static function decryptAES256GCM($ciphertext, $nonce, $associated_data) {
        $apiv3_key = self::$merchant_info['apiv3_key'];
        if (!$apiv3_key) {
            throw new PayException('缺少 apiv3_key 配置，请前往https://pay.weixin.qq.com/index.php/core/cert/api_cert#/ 配置');
        }

        // Base64解码密文
        $ciphertext = base64_decode($ciphertext);

        // 提取tag（最后16字节）
        $tag = substr($ciphertext, -16);
        $encrypted_data = substr($ciphertext, 0, -16);

        // 使用 openssl_decrypt 解密
        $decrypted = openssl_decrypt(
            $encrypted_data,
            'aes-256-gcm',
            $apiv3_key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            $associated_data
        );

        if ($decrypted === false) {
            throw new PayException('解密失败');
        }

        return $decrypted;
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
        $platform_cert = file_get_contents(self::$merchant_info['platform_cert_file']);
        $public_key = openssl_pkey_get_public($platform_cert);

        //验证签名
        $result = openssl_verify($message, base64_decode($wechatpay_signature), $public_key, OPENSSL_ALGO_SHA256);

        //释放资源
        openssl_free_key($public_key);
        if ($result !== 1) {
            throw new PayException('回调数据签名验证失败');
        }
        return $body;
    }
}
