<?php

namespace IrfanTOOR\App;

use IrfanTOOR\Collection;

class Config extends Collection
{
    protected $is_locked = false;

    function __construct($init = [])
    {
        parent::__construct($init);
        $this->is_locked = true;
    }

    function setItem($id, $value) {
        # can not set any value after __construct
        if ($this->is_locked)
            return;

        parent::setItem($id, $value);        
    }

    function remove($id) 
    {
        # can not remove any id from config
    }
}
