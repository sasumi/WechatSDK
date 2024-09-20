<?php

use LFPhp\WechatSdk\Message\MessageBase;

include __DIR__.'/test.inc.php';

$xml = '<xml><ToUserName><![CDATA[123132132]]></ToUserName>
<FromUserName><![CDATA[oR2ef6FdYZA652asfasfvG_Yk_SOyXE6AU]]></FromUserName>
<CreateTime>1726723576</CreateTime>
<MsgType><![CDATA[event]]></MsgType>
<Event><![CDATA[debug_demo]]></Event>
<debug_str><![CDATA[]]></debug_str>
</xml>
';

$ins = MessageBase::getMessageInstance(\LFPhp\WechatSdk\Util\xml_to_array($xml));

\LFPhp\Func\dump(json_encode($ins), 1);

