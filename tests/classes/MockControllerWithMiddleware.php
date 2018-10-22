<?php

namespace Tests;

use Tests\MockController;

class MockControllerWithMiddleware extends MockController
{
    function __construct($app)
    {
        $this->addMiddleware('Tests\MockMiddleware');
        parent::__construct($app);
    }
}
