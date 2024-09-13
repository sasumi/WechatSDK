<?php

namespace LFPhp\WechatSdk\Exception;

use Exception;
use Throwable;

class WechatException extends Exception {
	public $data;
	public function __construct($message = "", $code = 0, Throwable $previous = null){
		parent::__construct($message, $code, $previous);
	}

	public static function throw($message, $code, $data = null){
		$ex = new self($message, $code);
		$ex->data = $data;
		throw $ex;
	}
}
