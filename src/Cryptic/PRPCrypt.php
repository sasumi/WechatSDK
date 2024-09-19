<?php
namespace LFPhp\WechatSdk\Cryptic;

use Exception;
use function LFPhp\Func\rand_string;

/**
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class PRPCrypt {
	public $aes_key;

	function __construct($encoding_aes_key){
		$this->aes_key = base64_decode($encoding_aes_key."=");
	}

	/**
	 * 对明文进行加密
	 * @param string $text 需要加密的明文
	 * @param string $app_id 需要加密的明文
	 * @param string $rand_str 随机串，缺省为内部生成
	 * @return string 加密后的密文
	 */
	public function encrypt($app_id, $text, $rand_str = ''){
		//获得16位随机字符串，填充到明文之前
		$rand_str = $rand_str ?: rand_string(16);
		$text = $rand_str.pack("N", strlen($text)).$text.$app_id;

		// 网络字节序
		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

		$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		$iv = substr($this->aes_key, 0, 16);

		//使用自定义的填充方式对明文进行补位填充
		$pkc_encoder = new PKCS7Encoder;
		$text = PKCS7Encoder::encode($text);
		mcrypt_generic_init($module, $this->aes_key, $iv);

		//加密
		$encrypted = mcrypt_generic($module, $text);
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);

		//使用BASE64对加密后的字符串进行编码
		return base64_encode($encrypted);
	}

	/**
	 * 对密文进行解密
	 * @param string $encrypted_str 需要解密的密文
	 * @return string 解密得到的明文
	 */
	public function decrypt($encrypted_str, $app_id){
		//使用BASE64对需要解密的字符串进行解码
		$ciphertext_dec = base64_decode($encrypted_str);
		$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		$iv = substr($this->aes_key, 0, 16);
		mcrypt_generic_init($module, $this->aes_key, $iv);

		//解密
		$decrypted = mdecrypt_generic($module, $ciphertext_dec);
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);

		//去除补位字符
		$result = PKCS7Encoder::decode($decrypted);

		//去除16位随机字符串,网络字节序和AppId
		if(strlen($result) < 16){
			return "";
		}

		$content = substr($result, 16, strlen($result));
		$len_list = unpack("N", substr($content, 0, 4));
		$xml_len = $len_list[1];
		$xml_content = substr($content, 4, $xml_len);
		$from_appid = substr($content, $xml_len + 4);
		if($from_appid != $app_id){
			throw new Exception('Validate AppID, from:'.$from_appid.' config:'.$app_id);
		}
		return $xml_content;
	}
}
