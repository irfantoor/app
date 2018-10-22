<?php

namespace Tests;

use IrfanTOOR\App\Controller;

class MockController extends Controller
{
    function defaultMethod($request, $response, $args)
    {
        $response->write('defaultMethod');
        return $response;
    }

    function definedMethod($request, $response, $args)
    {
        $response->write('definedMethod');
        $response = $response->withHeader('hello', 'world');
        return $response;
    }
}
