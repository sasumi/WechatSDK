<?php

use LFPhp\WechatSdk\Service\Auth;
use function LFPhp\Func\dump;

include __DIR__.'/test.inc.php';

$app_id = '';
$app_secret = '';
$token = Auth::getAccessToken($app_id, $app_secret);
dump($token, 1);
