<?php

use IrfanTOOR\App\Config;
use IrfanTOOR\Test;

class ConfigTest extends Test
{
    function config($init = [])
    {
        return new Config($init);
    }

    function testConfigInstance()
    {
        $config = $this->config();
        $this->assertInstanceOf(IrfanTOOR\App\Config::class, $config);
    }

    function testConfigInit()
    {
        # data is assigned while initialisation
        $config = $this->config();
        $this->assertEquals([], $config->toArray());

        $init = [
            'debug' => [
                'level' => 1,
            ],
            'hello' => 'world!',
        ];

        $config = $this->config($init);
        $this->assertEquals($init, $config->toArray());
    }

    function testSet()
    {
        # nothing can be set after initialisation
        $init = [
            'debug' => [
                'level' => 1,
            ],
            'hello' => 'world!',
        ];

        $config = $this->config($init);


        $config->set('hello', 'WORLD!');
        $this->assertEquals('world!', $config->get('hello'));

        $config->set('something', 'somevalue');
        $this->assertFalse($config->has('something'));
        $this->assertNull($config->get('something'));

        $this->assertEquals($init, $config->toArray());
    }

    function testRemove()
    {
        # nothing can be removed after initialisation
        $init = [
            'debug' => [
                'level' => 1,
            ],
            'hello' => 'world!',
        ];

        $config = $this->config($init);

        $config->remove('debug.level');
        $this->assertEquals($init['debug']['level'], $config->get('debug.level'));
        
        $config->remove('debug');
        $config->remove('hello');
        $config->remove('something');

        $this->assertEquals($init, $config->toArray());
    }
}
