<?php

use IrfanTOOR\App\Exception;
use IrfanTOOR\App\Controller;
use Tests\MockApp;
use IrfanTOOR\Test;

class ControllerTest extends Test
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
        $this->assertException(
            function() use($c){
                $c->hello();
            },
            Exception::class,
            "Unknown method: 'hello'"
        );


        $this->assertException(
            function() use($app){
                $app->hello();
            },
            Exception::class,
            "Unknown method: 'hello'"
        );

        # function present in app but not in Controller
        $r1 = $c->definedInApp();
        $r2 = $app->definedInApp();

        $this->assertEquals($r1, $r2);
    }

    function testGetApp()
    {
        $c = $this->Controller();
        $app = $c->getApp();
        $this->assertInstanceOf(IrfanTOOR\App::class, $app);
    }

    function testAddAndGetMiddlewares()
    {
        $c = $this->controller();
        // $this->assertNull($c->getMiddlewares());
        $this->assertArray($c->getMiddlewares());
        $this->assertEquals(0, count($c->getMiddlewares()));

        $c->addMiddleware('Tests\MockMiddleware');
        $c->addMiddleware('Tests\MockMiddleware');

        $this->assertArray($c->getMiddlewares());
        $this->assertEquals(1, count($c->getMiddlewares()));
        
        foreach ($c->getMiddlewares() as $k => $mw) {
            $this->assertEquals('Tests\MockMiddleware', $k);
            $this->assertInstanceOf(Tests\MockMiddleware::class, $mw);
        }
    }

    function testDefaultMethod()
    {
        $c = $this->controller();
        $this->assertTrue(method_exists($c, 'defaultMethod'));

        $app = $c->getApp();
        $res = $app->Response();
        $r = $c->defaultMethod(null, $res, []);
        $this->assertInstanceOf(IrfanTOOR\Engine\Http\Response::class, $r);
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
