<?php

use IrfanTOOR\App;
use IrfanTOOR\App\Response;
use IrfanTOOR\Test;

# todo -- must be used rather: use IrfanTOOR\Engine\Tests\ResponseTest;
require dirname(__DIR__) . "/vendor/irfantoor/engine/tests/ResponseTest.php";

class AppResponseTest extends ResponseTest
{
    function getResponse(
        $status  = 200,
        $headers = [],
        $body    = ''
    ){
        return new Response([
            'status'  => $status,
            'headers' => $headers,
            'body'    => $body,
        ]);
    }

    function testAppResponseInstance()
    {
        $response = $this->getResponse();
        $this->assertInstanceOf(IrfanTOOR\App\Response::class, $response);
    }

    function testHeadersCount()
    {
        $response = $this->getResponse();
        $response = $response->withHeader('alfa', 'beta');
        $this->assertEquals(['beta'], $response->getHeader('ALFA'));
        $this->assertEquals(3, count($response->getHeaders()));        
    }

    function testAppDefaults()
    {
        $response = $this->getResponse();
        
        $this->assertEquals('1.1', $response->getProtocolVersion());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(App::NAME . ' ' . App::VERSION, $response->getHeaders()['App'][0]);
        $this->assertEquals('', $response->getBody());
    }
}
