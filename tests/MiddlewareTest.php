<?php

use IrfanTOOR\Test;
use Tests\MockApp;
use Tests\MockController;
use Tests\MockControllerWithMiddleware;
use Tests\MockMiddleware;

class MiddlewareTest extends Test
{
    function app($config = [])
    {
        return new MockApp($config);
    }

    function controller()
    {
        $app = $this->app();
        return new MockController($app);
    }

    function middleware()
    {
        $c = $this->controller();
        return new MockMiddleware($c);
    }

    function testMiddlewareInstance()
    {
        $m = $this->middleware();
        $this->assertInstanceOf(IrfanTOOR\App\Middleware::class, $m);
    }

    function test__call()
    {
        $app = new MockApp();
        $c   = new MockController($app);
        $m   = new MockMiddleware($c);
        $req = $app->getServerRequest();
        $res = $app->getResponse();

        # __call calls the controller's defaultMethod
        $r = $m->defaultMethod($req, $res, []);
        $this->assertEquals($res, $r);
        $this->assertSame($res, $r);

        # __call calls the app's Response
        $r1 = $m->definedMethod($req, $res, []);
        $r2 = $m->getResponse();
        $this->assertNotEquals($res, $r1);
        $this->assertEquals($res, $r2);
        $this->assertSame($res, $r2);
    }

    function testPreProcess()
    {
        $m = $this->middleware();
        $res = $m->preProcess($m->getServerRequest(), $m->getResponse(), []);
        $this->assertEquals('.pre.', (string) $res->getBody());
    }

    function testPostProcess()
    {
        $m = $this->middleware();
        $res = $m->postProcess($m->getServerRequest(), $m->getResponse(), []);
        $this->assertEquals('.post.', (string) $res->getBody());
    }

    function testProcessingOrder()
    {
        $app = new MockApp();
        $app->addRoute('ANY', '.*', 'Tests\MockController');
        $app->run();
        $r = $app->getResult();
        $this->assertEquals('defaultMethod', (string) $r[1]->getBody());

        $app = new MockApp();
        $app->addRoute('ANY', '.*', 'Tests\MockControllerWithMiddleware');
        $app->run();
        $r = $app->getResult();
        $this->assertEquals('.pre.defaultMethod.post.', (string) $r[1]->getBody());
    }
}
