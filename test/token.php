<?php

use LFPhp\WechatSdk\Service\Auth;
use function LFPhp\Func\dump;

include __DIR__.'/test.inc.php';

$app_id = '';
$app_secret = 'wxf8617dd16e0de28d';
$token = Auth::getAccessToken($app_id, $app_secret);
dump($token, 1);
