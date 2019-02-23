<?php

namespace Tests;

use Tests\MockApp;

class MockProcessApp extends MockApp
{
    function process($request, $response, $args)
    {   
        $response->write('Hello World!');
        return $response->withHeader('Engine', 'MyEngine 0.1 (test)');
    }
}
