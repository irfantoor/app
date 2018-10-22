<?php

namespace IrfanTOOR\App;

use IrfanTOOR\Engine\Exception as EngineException;

class Exception extends EngineException
{
	function __construct($init)
	{
		parent::__construct($init);
	}
}
