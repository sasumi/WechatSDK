<?php

use LFPhp\Logger\Logger;
use LFPhp\Logger\LoggerLevel;
use LFPhp\Logger\Output\ConsoleOutput;
use function LFPhp\Func\curl_set_default_option;

include __DIR__.'/../vendor/autoload.php';

curl_set_default_option([CURLOPT_VERBOSE => true], true);
Logger::registerGlobal(new ConsoleOutput(), LoggerLevel::DEBUG);;
