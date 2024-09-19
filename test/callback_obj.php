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

$data = ['ToUserName'   => 'gh_2a191e24f4d4',
         'FromUserName' => 'oR2ef6Pr2cmqAvp6zXGXM0C-c7RE',
         'CreateTime'   => '1726740548',
         'MsgType'      => 'event',
         'Event'        => 'SCAN',
         'EventKey'     => 'b5afa47d5a8e891c780061e3dd712611',
         'Ticket'       => 'gQFt8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQzM3RmtiN0RldkUxWk9VSDFDY1gAAgQx_OtmAwRBAAAA',
];

$ins = MessageBase::getMessageInstance($data);

\LFPhp\Func\dump(json_encode($ins), 1);

