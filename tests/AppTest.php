<?php

use Tests\MockApp;
use Tests\MockController;
use Tests\MockControllerWithMiddleware;
use IrfanTOOR\Test;

class AppTest extends Test
{
    function app($config = [])
    {
        return new MockApp($config);
    }

    function testAppInstance()
    {
        $app = $this->app();
        $this->assertInstanceOf(IrfanTOOR\App::class, $app);
        $this->assertInstanceOf(IrfanTOOR\Engine::class, $app);
    }

    function testInit()
    {
        $config = [
            'a' => [
                'b' => 'c',
            ],
        ];

        $app = $this->app();
        $this->assertNull($app->config('a'));

        $app = $this->app($config);
        $this->assertEquals('c', $app->config('a.b'));
    }

    function testAddRoute() {
        # 404 - no route defined!
        $app = $this->app();
        $app->run();
        $r = $app->getResult();
        $this->assertEquals(404, $r[1]->getStatusCode());
        $this->assertEquals('no route defined!', $r[1]->getBody()->__toString());

        # Defined Route
        $app = $this->app();
        $app->addRoute('GET', '/', function () {
            echo 'Home';
        });

        $app->run();
        $r = $app->getResult();
        $this->assertEquals('Home', $r[1]->getBody()->__toString());

        # Defined Route
        $app = $this->app([
            'default' => [
                'ServerRequest' => [
                    'uri' => 'http://example.com/hello',
                ]
            ]
        ]);

        $app->addRoute('GET', 'hello(/.*)?', function () {
            echo 'Hello';
        });

        $app->addRoute('GET', '.*', function () {
            echo 'Default Route';
        });

        $app->run();
        $r = $app->getResult();
        $this->assertEquals('Hello', $r[1]->getBody()->__toString());

        # Default Route
        $app = $this->app([
            'default' => [
                'ServerRequest' => [
                    'uri' => 'http://example.com/helloworld',
                ]
            ]
        ]);

        $app->addRoute('GET', 'hello(/.*)?', function () {
            echo 'Hello';
        });

        $app->addRoute('GET', '.*', function () {
            echo 'Default Route';
        });

        $app->run();
        $r = $app->getResult();
        $this->assertEquals('Default Route', $r[1]->getBody()->__toString());        
    }

    /* These tests will be removed from the version 0.2 */
    /* {START_TESTS_TO_BE_REMOVED} */
    function testGetCookie()
    {
        $app = $this->app();
        $this->assertInstanceOf(IrfanTOOR\Engine\Http\Cookie::class, $app->GetCookie());
    }

    function testGetEnvironment()
    {
        $app = $this->app();
        $this->assertInstanceOf(IrfanTOOR\Engine\Http\Environment::class, $app->GetEnvironment());
    }

    function testGetRequest()
    {
        $app = $this->app();
        $this->assertInstanceOf(IrfanTOOR\Engine\Http\Request::class, $app->GetRequest());
    }

    function testGetResponse()
    {
        $app = $this->app();
        $this->assertInstanceOf(IrfanTOOR\Engine\Http\Response::class, $app->GetResponse());
    }

    function testGetServerRequest()
    {
        $app = $this->app();
        $this->assertInstanceOf(
            IrfanTOOR\Engine\Http\ServerRequest::class, 
            $app->GetServerRequest()
        );
    }

    function testGetUploadedFile()
    {
        $app = $this->app();
        $this->assertInstanceOf(
            IrfanTOOR\Engine\Http\UploadedFile::class, 
            $app->GetUploadedFile()
        );
    }

    function testGetUri()
    {
        $app = $this->app();
        $this->assertInstanceOf(
            IrfanTOOR\Engine\Http\Uri::class, 
            $app->GetUri()
        );
    }
    /* {END_TESTS_TO_BE_REMOVED} */

    function testProcessClosure()
    {
        # test echo
        $app = $this->app();

        $app->addRoute('GET', '.*', function($request, $response, $args){
            echo '.ECHO.';
        });

        $app->run();
        $r = $app->getResult();
        $this->assertEquals('.ECHO.', (string) $r[1]->getBody());


        # test return
        $app = $this->app();

        $app->addRoute('GET', '.*', function($request, $response, $args){
            return '.RETURN.';
        });

        $app->run();
        $r = $app->getResult();
        $this->assertEquals('.RETURN.', (string) $r[1]->getBody());

        # test echo and return
        $app = $this->app();

        $app->addRoute('GET', '.*', function($request, $response, $args){
            echo '.ECHO.';
            return '.RETURN.';
        });

        $app->run();
        $r = $app->getResult();
        $this->assertEquals('.ECHO..RETURN.', (string) $r[1]->getBody());

        # test echo and return not a string type
        $app = $this->app();

        $app->addRoute('GET', '.*', function($request, $response, $args){
            echo '.ECHO.';
            return ['a' => 'b'];
        });

        $app->run();
        $r = $app->getResult();
        $this->assertEquals('.ECHO.' . print_r(['a' => 'b'], 1), (string) $r[1]->getBody());

        # test echo and returns a response
        $app = $this->app();

        $app->addRoute('GET', '.*', function($request, $response, $args){
            echo '.ECHO.';
            $response->write('.RESPONSE.');
            return $response;
        });

        $app->run();
        $r = $app->getResult();
        $this->assertEquals('.RESPONSE.', (string) $r[1]->getBody());
    }

    function testProcessClassName()
    {
        # defaultMethod
        $app = $this->app();
        $app->addRoute('GET', '.*', 'Tests\MockController');
        $app->run();
        $r = $app->getResult();
        $this->assertEquals('defaultMethod', (string) $r[1]->getBody());

        # definedMethod
        $app = $this->app();
        $app->addRoute('GET', '.*', 'definedMethod@Tests\MockController');
        $app->run();
        $r = $app->getResult();
        $this->assertEquals('definedMethod', (string) $r[1]->getBody());

        # definedMethod
        $app = $this->app();
        $app->addRoute('GET', '.*', 'undefinedMethod@Tests\MockController');
        $app->run();        
        $r = $app->getResult();
        $this->assertEquals('defaultMethod', (string) $r[1]->getBody());

        ###############################################################################
        # with middleware
        #
        # defaultMethod
        $app = $this->app();
        $app->addRoute('GET', '.*', 'Tests\MockControllerWithMiddleware');
        $app->run();
        $r = $app->getResult();
        $this->assertEquals('.pre.defaultMethod.post.', (string) $r[1]->getBody());

        # definedMethod
        $app = $this->app();
        $app->addRoute('GET', '.*', 'definedMethod@Tests\MockControllerWithMiddleware');
        $app->run();
        $r = $app->getResult();
        $this->assertEquals('.pre.definedMethod.post.', (string) $r[1]->getBody());

        # definedMethod
        $app = $this->app();
        $app->addRoute('GET', '.*', 'undefinedMethod@Tests\MockControllerWithMiddleware');
        $app->run();        
        $r = $app->getResult();
        $this->assertEquals('.pre.defaultMethod.post.', (string) $r[1]->getBody());
    }
}
