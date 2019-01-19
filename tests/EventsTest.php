<?php

use IrfanTOOR\App\Events;
use IrfanTOOR\Test;

class EventsTest extends Test
{
    function testEventsInstance()
    {
        $e = new Events;

        $this->assertInstanceOf(IrfanTOOR\App\Events::class, $e);
        $this->assertInstanceOf(IrfanTOOR\Collection::class, $e);
    }

    function testRegisterAndTrigger()
    {
        $e = new Events;

        for ($i=10; $i>=0; $i--) {
            $e->register('counter' . $i, function() use($i) {
                echo $i;
            }, $i);
        }

        ob_start();
            $e->trigger('counter1');
            $e->trigger('counter2');
            $e->trigger('counter9');
            $e->trigger('counter0');
        $contents = ob_get_clean();
        $this->assertEquals('1290', $contents);
    }

    function testRegisterLevel()
    {
        $e = new Events;

        for ($i=10; $i>=0; $i--) {
            $e->register('counter', function() use($i) {
                echo $i;
            }, $i);
        }

        ob_start();
            $e->trigger('counter');
        $contents = ob_get_clean();
        $this->assertEquals('012345678910', $contents);
    }

    function testTriggerLevel()
    {
        $e = new Events;

        $e->register('world', function(){
            echo 'World!';
        }, 10);

        $e->register('world', function(){
            echo 'world';
        }, 1);

        $e->register('hello', function(){
            echo 'hello';
        }, 1);

        $e->register('hello', function(){
            echo 'Hello ';
        });

        ob_start();
            $e->trigger('hello');
            $e->trigger('world');
        $contents = ob_get_clean();
        $this->assertEquals('helloHello worldWorld!', $contents);
    }
}
