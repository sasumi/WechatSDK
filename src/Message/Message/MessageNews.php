<?php

namespace LFPhp\WechatSdk\Message\Message;

use LFPhp\WechatSdk\Message\MessageBase;

class MessageNews extends MessageBase {
	public $MsgType = self::MSG_TYPE_NEWS;

	public $ArticleCount = 0;

	//文本消息内容
	public $Articles = [];

	/**
	 * 添加图文消息
	 */
	public function addArticle($title, $description, $url, $pic_url = '') {
		$this->Articles[] = [
			'Item' => [
				'Title' => $title,
				'Description' => $description,
				'Url' => $url,
				'PicUrl' => $pic_url,
			]
		];
		$this->ArticleCount = count($this->Articles);
	}

	public function addArticles(array $articles) {
		foreach ($articles as $article) {
			$this->addArticle($article['title'], $article['description'], $article['url'], $article['pic_url']);
		}
	}
}
