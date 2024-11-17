<?php

namespace LFPhp\WechatSdk\Service;

use Exception;
use LFPhp\WechatSdk\Base\AuthorizedService;

/**
 * 素材管理
 * https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/New_temporary_materials.html
 */
class Media extends AuthorizedService {
	const TYPE_IMAGE = 'image';
	const TYPE_VOICE = 'voice';
	const TYPE_VIDEO = 'video';
	const TYPE_THUMB = 'thumb';

	/**
	 * 上传临时素材
	 * 媒体文件在微信后台保存时间为3天，即3天后media_id失效。
	 * 图片（image）: 10M，支持PNG\JPEG\JPG\GIF格式
	 * 语音（voice）：2M，播放长度不超过60s，支持AMR\MP3格式
	 * 视频（video）：10MB，支持MP4格式
	 * 缩略图（thumb）：64KB，支持JPG格式
	 * @param string $file
	 * @param string $type 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
	 * @return string 媒体ID
	 * @throws \Exception
	 */
	public static function uploadTemporary($file, $type){
		$access_token = self::getAccessToken();
		$ret = self::sendJsonRequest("https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$access_token&type=$type", [], 'post', ['media' => $file]);
		self::assertResultSuccess($ret);

		return $ret['media_id'];
	}

	/**
	 * 新增永久素材
	 * 1、最近更新：永久图片素材新增后，将带有URL返回给开发者，开发者可以在腾讯系域名内使用（腾讯系域名外使用，图片将被屏蔽）。
	 * 2、公众号的素材库保存总数量有上限：图文消息素材、图片素材上限为100000，其他类型为1000。
	 * 3、素材的格式大小等要求与公众平台官网一致：
	 * 图片（image）: 10M，支持bmp/png/jpeg/jpg/gif格式
	 * 语音（voice）：2M，播放长度不超过60s，mp3/wma/wav/amr格式
	 * 视频（video）：10MB，支持MP4格式
	 * 缩略图（thumb）：64KB，支持JPG格式
	 * 4、图文消息的具体内容中，微信后台将过滤外部的图片链接，图片url需通过"上传图文消息内的图片获取URL"接口上传图片获取。
	 * 5、"上传图文消息内的图片获取URL"接口所上传的图片，不占用公众号的素材库中图片数量的100000个的限制，图片仅支持jpg/png格式，大小必须在1MB以下。
	 * 6、图文消息支持正文中插入自己账号和其他公众号已群发文章链接的能力。
	 * @param string $file
	 * @param string $type 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
	 * @param array $ext_param 更多参数（如视频必须提供标题）
	 * @return array 媒体ID，媒体URL
	 * @throws \Exception
	 */
	public static function uploadPermanent($file, $type, $ext_param = []){
		$access_token = self::getAccessToken();
		$param = [];
		if($type === self::TYPE_VIDEO){
			if(!$ext_param['title']){
				throw new Exception('视频标题必填');
			}
			$param = json_encode([
				'title'        => $ext_param['title'], //必填
				'introduction' => $ext_param['introduction'],
			]);
		}
		$ret = self::sendJsonRequest("https https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=$access_token&type=$type", $param, 'post', ['media' => $file]);
		self::assertResultSuccess($ret);
		return [$ret['media_id'], $ret['url']];
	}

	/**
	 * 新增图片（仅用于图文消息）
	 * @param string $image_file
	 * @return string 图片URL
	 * @throws \Exception
	 */
	public static function uploadPermanentArticleImage($image_file){
		$access_token = self::getAccessToken();
		$ret = self::sendJsonRequest("https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=$access_token", [], 'post', ['media' => $image_file]);
		self::assertResultSuccess($ret);
		return $ret['url'];
	}

	/**
	 * 获取媒体信息
	 * @param string $media_id
	 * @return array
	 */
	public static function getMaterial($media_id){
		$access_token = self::getAccessToken();
		$ret = self::postJsonSuccess("https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=$access_token", ['media_id' => $media_id]);
		self::assertResultSuccess($ret);
		return $ret;
	}

	/**
	 * 删除永久素材
	 * @param $media_id
	 * @return void
	 */
	public static function delMaterial($media_id){
		$access_token = self::getAccessToken();
		return self::postJsonSuccess("https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=$access_token", ['media_id' => $media_id]);
	}

	/**
	 * 获取素材列表
	 * @see https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/Get_materials_list.html
	 * @param string $type 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
	 * @param int $offset 从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
	 * @param int $count 返回素材的数量，取值在1到20之间
	 * @return void
	 * @throws \Exception
	 */
	public static function getMaterialList($type, $offset, $count = 20){
		$access_token = self::getAccessToken();
		if($count > 20){
			throw new Exception('count 需要小于等于20');
		}
		$ret = self::postJsonSuccess("https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=$access_token");
		self::assertResultSuccess($ret);
		return $ret;
	}
}
