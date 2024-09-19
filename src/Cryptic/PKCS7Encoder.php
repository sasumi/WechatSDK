<?php
namespace LFPhp\WechatSdk\Cryptic;

/**
 * PKCS7Encoder class
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder {
	public static $BLOCK_SIZE = 32;

	/**
	 * 对需要加密的明文进行填充补位
	 * @param string $text 需要进行填充补位操作的明文
	 * @return string 补齐明文字符串
	 */
	public static function encode($text){
		$text_length = strlen($text);
		//计算需要填充的位数
		$amount_to_pad = PKCS7Encoder::$BLOCK_SIZE - ($text_length%PKCS7Encoder::$BLOCK_SIZE);
		if($amount_to_pad == 0){
			$amount_to_pad = PKCS7Encoder::$BLOCK_SIZE;
		}
		//获得补位所用的字符
		$pad_chr = chr($amount_to_pad);
		$pad_str = str_repeat($pad_chr, $amount_to_pad);
		return $text.$pad_str;
	}

	/**
	 * 对解密后的明文进行补位删除
	 * @param string decrypted 解密后的明文
	 */
	public static function decode($text){
		$pad = ord(substr($text, -1));
		if($pad < 1 || $pad > 32){
			$pad = 0;
		}
		return substr($text, 0, (strlen($text) - $pad));
	}
}
