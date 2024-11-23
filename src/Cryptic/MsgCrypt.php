<?php

namespace LFPhp\WechatSdk\Cryptic;

use Exception;
use function LFPhp\Func\array_get;
use function LFPhp\WechatSdk\Util\array_to_xml;
use function LFPhp\WechatSdk\Util\wechat_sha1;
use function LFPhp\WechatSdk\Util\xml_to_array;

/**
 * 对公众平台发送给公众账号的消息加解密示例代码.
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */

/**
 * 1.第三方回复加密消息给公众平台；
 * 2.第三方收到公众平台发送的消息，验证消息的安全性，并对消息进行解密。
 */
class MsgCrypt {
	private $app_id;
	private $token;
	private $encoding_aes_key;

	/**
	 * 明文签名校验
	 * @param string $app_token 应用token
	 * @param string $signature 签名
	 * @param int $timestamp 时间戳
	 * @param string $nonce 混淆字符
	 * @return bool
	 */
	public static function checkPlainTextSignature($signature, $app_token, $timestamp, $nonce){
		return wechat_sha1($app_token, $timestamp, $nonce) === $signature;
	}

	/**
	 * 构造函数
	 * @param $app_id string 公众平台的appId
	 * @param $token string 公众平台上，开发者设置的token
	 * @param $encoding_aes_key string 公众平台上，开发者设置的EncodingAESKey
	 */
	public function __construct($app_id, $token, $encoding_aes_key){
		$this->app_id = $app_id;
		$this->token = $token;
		$this->encoding_aes_key = $encoding_aes_key;
	}

	/**
	 * 将公众平台回复用户的消息加密打包.
	 * <ol>
	 *    <li>对要发送的消息进行AES-CBC加密</li>
	 *    <li>生成安全签名</li>
	 *    <li>将消息密文和安全签名打包成xml格式</li>
	 * </ol>
	 * @param string $replyMsg 公众平台待回复用户的消息，xml格式的字符串
	 * @param int $timeStamp 时间戳，可以自己生成，也可以用URL参数的timestamp
	 * @param string $nonce 随机串，可以自己生成，也可以用URL参数的nonce
	 * @param string $rand_str 随机串，缺省为内部生成
	 * @return string 加密后的可以直接回复用户的密文，包括msg_signature, timestamp, nonce, encrypt的xml格式的字符串,
	 *                      当return返回0时有效
	 */
	public function encryptMsg($replyMsg, $timeStamp, $nonce, $rand_str = ''){
		$pc = new PRPCrypt($this->encoding_aes_key);

		//加密
		$encrypt = $pc->encrypt($this->app_id, $replyMsg, $rand_str);

		//生成安全签名
		$signature = wechat_sha1($this->token, $timeStamp, $nonce, $encrypt);

		//生成发送的xml
		return array_to_xml([
			'Encrypt'      => $encrypt,
			'MsgSignature' => $signature,
			'TimeStamp'    => (int)$timeStamp,
			'Nonce'        => $encrypt,
		]);
	}

	/**
	 * 检验消息的真实性，并且获取解密后的明文.
	 * <ol>
	 *    <li>利用收到的密文生成安全签名，进行签名验证</li>
	 *    <li>若验证通过，则提取xml中的加密消息</li>
	 *    <li>对消息进行解密</li>
	 * </ol>
	 * @param $msg_signature string 签名串，对应URL参数的msg_signature
	 * @param $timestamp string 时间戳 对应URL参数的timestamp
	 * @param $nonce string 随机串，对应URL参数的nonce
	 * @param $post_data string 密文，对应POST请求的数据
	 * @return string 解密后的原文
	 */
	public function decryptMsg($msg_signature, $timestamp, $nonce, $post_data){
		if(strlen($this->encoding_aes_key) != 43){
			throw new Exception('Illegal AES Key('.strlen($this->encoding_aes_key).')');
		}

		$timestamp = $timestamp ?: time();
		$pc = new PRPCrypt($this->encoding_aes_key);

		//提取密文
		$encrypt_str = array_get(xml_to_array($post_data), 'Encrypt');

		//验证安全签名
		$signature = wechat_sha1($this->token, $timestamp, $nonce, $encrypt_str);

		if($signature != $msg_signature){
			throw new Exception('Validate signature');
		}

		return $pc->decrypt($encrypt_str, $this->app_id);
	}
}

