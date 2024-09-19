<?php

namespace LFPhp\WechatSdk\Message\Message;
use LFPhp\WechatSdk\Message\MessageBase;

class MessageStandard extends MessageBase {
	//消息id，64位整型
	public $MsgId;

	//消息的数据ID（消息如果来自文章时才有）
	public $MsgDataId;

	//多图文时第几篇文章，从1开始（消息如果来自文章时才有）
	public $Idx;
}
