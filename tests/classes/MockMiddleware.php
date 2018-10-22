<?php

namespace Tests;

use IrfanTOOR\App\Middleware;

class MockMiddleware extends Middleware
{
    public function preProcess($request, $response, $args)
    {
        $response->write('.pre.');
        return $response;
    }

    public function postProcess($request, $response, $args)
    {
        $response->write('.post.');
        return $response;
    }
}
