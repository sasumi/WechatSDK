<?php

use LFPhp\WechatSdk\Cryptic\MsgCrypt;
use function LFPhp\Func\dump;

include __DIR__.'/test.inc.php';

$xml = '<xml>
    <ToUserName><![CDATA[gh_2a191e24f4d4]]></ToUserName>
    <Encrypt><![CDATA[gDToQJTq2oxFTBsyfcg9/0xjnsq8Jp55l49RRbWpRer6stYgGKNI0OFY2cX4nWFVbjuz0WMJSJxO4te1foKiZRqlYpRk/bJU6Z/3FDAAvWWQAMAZJDeoezNPhybeA2AbekcL4tJbS8PIxUo0VQwi7a26BHO7WdAvXP9TC8Q8ow/YonQQiIJ0p7P3Bls36Xgr5qfAk+pBIGLAGAwHY6Hn1qc2NZ+sA2Zw6qjRUcjnIzAAXo1HN8CEpmxi81NiwS8GUXkLNQb4ZkPOMbrRhoRbfSS0hwYZdbdHkavZnjPCMtprQ6K8U7fZuS0kLISX+M04EzbY1lZHDxR9v4AMjkXMT1XCRFtFvY03BCU+fxWvGrfMO7HdRIpyVYz98BpuvLlo6WJKeWoWEerU++5o7kW+KyQlzCMpepIywbP7Wf72LrA=]]></Encrypt>
</xml>';

$a = \LFPhp\WechatSdk\Util\xml_to_array($xml);
dump($a, 1);
