<?php

namespace LFPhp\WechatSdk\Exception;

use Exception;
use Throwable;

class WechatException extends Exception {
	public function __construct($message = "", $code = 0, Throwable $previous = null){
		parent::__construct($message, $code, $previous);
	}
}
