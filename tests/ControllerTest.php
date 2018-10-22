<?php

use IrfanTOOR\App\Exception;
use IrfanTOOR\App\Controller;
use Tests\MockApp;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    function app($config = [])
    {
        return new MockApp($config);
    }

    function controller()
    {
        $app = $this->app();
        return new Controller($app);
    }

    function testControllerInstance()
    {
        $c = $this->controller();
        
        $this->assertInstanceOf(IrfanTOOR\App\Controller::class, $c);
    }

    function test__call()
    {
        $app = new MockApp;
        $c = new Controller($app);
        $e = null;

        # Funcltion neither present in Controller nor in App
        try {
            $c->hello();
        } catch (Exception $e1) {}

        try {
            $app->hello();
        } catch (Exception $e2) {}

        $this->assertEquals($e1, $e2);
        $this->assertEquals('Method: hello, not defined', $e1->getMessage());

        # function present in app but not in Controller
        $r1 = $c->definedInApp();
        $r2 = $app->definedInApp();

        $this->assertEquals($r1, $r2);
    }

    function testAddAndGetMiddlewares()
    {
        $c = $this->controller();
        // $this->assertNull($c->getMiddlewares());
        $this->assertTrue(is_array($c->getMiddlewares()));
        $this->assertEquals(0, count($c->getMiddlewares()));

        $c->addMiddleware('Tests\MockMiddleware');
        $c->addMiddleware('Tests\MockMiddleware');

        $this->assertTrue(is_array($c->getMiddlewares()));
        $this->assertEquals(1, count($c->getMiddlewares()));
        
        foreach ($c->getMiddlewares() as $k => $mw) {
            $this->assertEquals('Tests\MockMiddleware', $k);
            $this->assertInstanceOf(Tests\MockMiddleware::class, $mw);
        }
    }

    function testShow()
    {
        $app = $this->app();
        $c = new Controller($app);

        $res = $app->Response();
        $tplt = dirname(__FILE__) . '/views/hello.tplt';

        $data = [
            'hello' => 'Hello World!',
            'name'  => 'Romeo',
        ];

        $r = $c->show($res, $tplt, $data);
        $this->assertEquals("Hello World!, I'm Romeo.", (string) $r->getBody());
    }
}
