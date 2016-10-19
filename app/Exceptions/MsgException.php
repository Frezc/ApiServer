<?php

namespace App\Exceptions;

use Exception;

/**
*
*/
class MsgException extends Exception
{
	
	function __construct($msg, $code = 500)
	{
		parent::__construct($msg, $code);
	}
}
