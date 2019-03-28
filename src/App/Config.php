<?php

namespace IrfanTOOR\App;

use IrfanTOOR\Collection;

class Config extends Collection
{
    function __construct($init)
    {
        parent::__construct($init);
        $this->lock();
    }
}
