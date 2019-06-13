<?php

namespace IrfanTOOR\App;

use IrfanTOOR\Engine\Http\Response as IeResponse;
use IrfanTOOR\App;

class Response extends IeResponse
{
    function __construct($init = [])
    {
        $init['headers']['App'] = App::NAME . ' ' . App::VERSION;
        parent::__construct($init);
    }
}
