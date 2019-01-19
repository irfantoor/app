<?php

use IrfanTOOR\App\Router;
use IrfanTOOR\Test;

class RouterTest extends Test
{
    function methods()
    {
        return [
            'HEAD',
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
            'PURGE',
            'OPTIONS',
            'TRACE',
            'CONNECT',
        ];
    }

    function testRouterInstance()
    {
        $r = new Router;
        $this->assertInstanceOf(IrfanTOOR\App\Router::class, $r);
        $this->assertInstanceOf(IrfanTOOR\Collection::class, $r);
    }

    function testDefaultAllowedMethod()
    {
        $r = new Router;
        $this->assertEquals($this->methods(), $r->getAllowedMethods());

        foreach ($this->methods() as $method) {
            $this->assertEquals([], $r->get($method));
        }
    }

    function testInitAllowedMethod()
    {
        $init_methods = ['GET', 'POST'];

        $r = new Router([
            'methods' => $init_methods,
        ]);

        $allowed_methods = $r->getAllowedMethods();
        foreach ($this->methods() as $method) {
            if (in_array($method, $init_methods))
                $this->assertTrue(in_array($method, $allowed_methods));
            else
                $this->assertFalse(in_array($method, $allowed_methods));
        }

        $init_methods = [];

        $r = new Router([
            'methods' => $init_methods,
        ]);

        $allowed_methods = $r->getAllowedMethods();
        $this->assertEquals([], $allowed_methods);
    }

    function testSetAllowedMethod()
    {
        $init_methods = [];

        $r = new Router([
            'methods' => $init_methods,
        ]);

        $r->setAllowedMethod('GET');
        $allowed_methods = $r->getAllowedMethods();
        $this->assertEquals(['GET'], $r->getAllowedMethods());

        $r->setAllowedMethod('POST');
        $this->assertEquals(['GET', 'POST'], $r->getAllowedMethods());

        $r->setAllowedMethod('NEW');
        $this->assertEquals(['GET', 'POST', 'NEW'], $r->getAllowedMethods());

        $r->setAllowedMethod(0);
        $r->setAllowedMethod(NULL);
        $r->setAllowedMethod(['DELETE']);
        $r->setAllowedMethod('Invalid');
        $r->setAllowedMethod('invalid');
        $r->setAllowedMethod('INVALId');
        $this->assertEquals(['GET', 'POST', 'NEW'], $r->getAllowedMethods());

    }

    function testSetAllowedMethods()
    {
        $init_methods = [];

        $r = new Router([
            'methods' => $init_methods,
        ]);

        $r->setAllowedMethods(['GET', 'POST', 0, NULL, ['DELETE'], 'invalid', 'NEW']);
        $this->assertEquals(['GET', 'POST', 'NEW'], $r->getAllowedMethods());
    }

    function testAddRoute()
    {
        $r = new Router;

        $r->addRoute('ANY', '/', function(){return true;});
        foreach ($this->methods() as $method) {
            $defs = $r->get($method);
            foreach ($defs as $def) {
                $this->assertEquals('/', $def['patern']);
                $this->assertTrue(is_callable($def['handler']));
                $this->assertTrue($def['handler']());
            }
        }

        $r->addRoute(['GET', 'POST'], '.*', function(){return false;});
        $defs = $r->get('GET');
        $this->assertEquals(2, count($defs));
        
        $def = $defs[1];
        $this->assertEquals('.*', $def['patern']);
        $this->assertTrue(is_callable($def['handler']));
        $this->assertFalse($def['handler']());

        $defs = $r->get('POST');
        $this->assertEquals(2, count($defs));
        
        $def = $defs[1];
        $this->assertEquals('.*', $def['patern']);
        $this->assertTrue(is_callable($def['handler']));
        $this->assertFalse($def['handler']());

        $defs = $r->get('HEAD');
        $this->assertEquals(1, count($defs));

        $r->addRoute('GET', 'hello', 'hello@MainController');
        $defs = $r->get('GET');
        $this->assertEquals(3, count($defs));
        
        $def = $defs[2];
        $this->assertEquals('hello', $def['patern']);
        $this->assertFalse(is_callable($def['handler']));
        $this->assertTrue(is_string($def['handler']));
        $this->assertEquals('hello@MainController', $def['handler']);

        # check exception cases
        $e = null;
        try {
            $r->addRoute('UNKNOWN', 'hello', 'hello@MainController');
        } catch (Exception $e) {}

        $this->assertInstanceOf(Exception::class, $e);
        $this->assertEquals('UNKNOWN, is not an allowed method', $e->getMessage());

        $e = null;
        try {
            $r->addRoute('GET', 123, 'hello@MainController');
        } catch (Exception $e) {}

        $this->assertInstanceOf(Exception::class, $e);
        $this->assertEquals('patern must be a string', $e->getMessage());

        $e = null;
        try {
            $r->addRoute('GET', 'hello', null);
        } catch (Exception $e) {}

        $this->assertInstanceOf(Exception::class, $e);
        $this->assertEquals('handler can either be a closure or a string', $e->getMessage());
    }

    function testProcess()
    {
        $r = new Router;
        $def = $r->process('GET', '/');
        $this->assertNull($def['type']);

        $r->addRoute('GET', '/', function(){ return 'home'; });
        $r->addRoute('GET', 'hello(/.*)?', function(){ return 'world'; });

        $def = $r->process('GET', '/');
        $this->assertEquals('callable', $def['type']);
        $this->assertEquals('home', $def['handler']());

        $def = $r->process('GET', '');
        $this->assertEquals('callable', $def['type']);
        $this->assertEquals('home', $def['handler']());

        $def = $r->process('GET', 'hello');
        $this->assertEquals('callable', $def['type']);
        $this->assertEquals('world', $def['handler']());

        // $def = $r->process('GET', 'hello?name=someone');
        // $this->assertEquals('callable', $def['type']);
        // $this->assertEquals('world', $def['handler']());

        $def = $r->process('GET', 'hello/?name=someone');
        $this->assertEquals('callable', $def['type']);
        $this->assertEquals('world', $def['handler']());

        $def = $r->process('GET', 'hello/world');
        $this->assertEquals('callable', $def['type']);
        $this->assertEquals('world', $def['handler']());

        $def = $r->process('GET', 'hello/world/i/am/here/');
        $this->assertEquals('callable', $def['type']);
        $this->assertEquals('world', $def['handler']());

        $def = $r->process('GET', 'admin');
        $this->assertEquals(null, $def['type']);

        $r->addRoute('GET', '.*', function(){ return 'default'; });

        $def = $r->process('GET', 'admin');
        $this->assertEquals('callable', $def['type']);
        $this->assertEquals('default', $def['handler']());
    }
}
