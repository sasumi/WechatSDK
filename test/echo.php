<?php

use LFPhp\WechatSdk\Cryptic\MsgCrypt;
use function LFPhp\Func\dump;

include __DIR__.'/test.inc.php';

$cfg = include __DIR__.'/../tmp/config.inc.php';
$token = $cfg['token'];
$app_id = $cfg['app_id'];
$encoding_aes_key = $cfg['encoding_aes_key'];

$timestamp = 12313123;
$nonce = 123132123;
$body = 'hello world';

// 第三方发送消息给公众平台
$body = "hello";

$pc = new MsgCrypt($app_id, $token, $encoding_aes_key);
$encrypted = $pc->encryptMsg($body, $timestamp, $nonce, 'f836b1031dee31ab');
dump($encrypted, 1);
