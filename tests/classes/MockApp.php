<?php

namespace Tests;

use IrfanTOOR\App;

class MockApp extends App
{
    protected $result = null;


    function definedInApp()
    {
        return md5('DefinedInApp');
    }

    function finalize($request, $response, $args)
    {
        $this->result = [$request, $response, $args];
    }

    function getResult()
    {
        return $this->result;
    }
}
