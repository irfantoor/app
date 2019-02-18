<?php

namespace IrfanTOOR\App;

use IrfanTOOR\Engine\Http\Response as IeResponse;
use IrfanTOOR\App\Constants;

class Response extends IeResponse
{
    function __construct($init = [])
    {
        $init['headers']['App'] = [Constants::NAME . ' ' . Constants::VERSION];
        parent::__construct($init);
    }
}
