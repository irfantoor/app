<?php

use IrfanTOOR\App\Session;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    function setup()
    {
    }

    function session()
    {
        return new Session([
            'path' => __DIR__ . '/tmp/'
        ]);
    }

    function testSessionInstance()
    {
        $s = $this->session();
        $this->assertInstanceOf(IrfanTOOR\App\Session::class, $s);
        $this->assertInstanceOf(IrfanTOOR\Collection::class, $s);

        $s->start();
        $s->destroy();
    }

    function testStart()
    {
        $s = $this->session();
        $this->assertEquals([], $s->toArray());

        $s->set('tokens.hello', 'world');
        $this->assertNull($s->get('tokens.hello'));

        $s->start();
        $this->assertNotEquals([], $s->toArray());
        $this->assertEquals([], $s->get('tokens'));

        $s->set('tokens.hello', 'world');
        $this->assertEquals('world', $s->get('tokens.hello'));

        $s->save();
        $s->close();
        $this->assertNull($s->get('tokens.hello'));

        $s->start();
        $this->assertEquals('world', $s->get('tokens.hello'));
        $s->remove('tokens.hello');
        $s->save();
        $s->close();
    }

    function testDefaults()
    {
        $s = $this->session();

        $s->start();
        $d = $s->toArray();

        $this->assertTrue(isset($d['created_at']));
        $this->assertTrue(isset($d['updated_at']));
        $this->assertTrue(isset($d['tokens']));

        $this->assertTrue($d['created_at'] > 0);
        $this->assertTrue($d['updated_at'] > 0);
        $this->assertEquals([], $d['tokens']);

        $this->assertEquals($d['created_at'], $s->get('created_at'));
        $this->assertEquals($d['updated_at'], $s->get('updated_at'));
        $this->assertEquals($d['tokens'], $s->get('tokens'));
    }

    function testSave()
    {
        $s = $this->session();

        # if the sessions is not saved the values are temporary
        # so it works fine in incognito mode
        $s->start();
        $s->set('tokens.hello', 'save my world');

        $s->close();

        $s->start();
        $this->assertEquals([], $s->get('tokens'));

        # saving makes the values persistant - add a value
        $s->set('tokens.hello', 'save my world');

        $s->save();
        $s->close();

        $s->start();
        $this->assertEquals(['hello' => 'save my world'], $s->get('tokens'));

        # saving makes the values persistant - remove a value
        $s->remove('tokens.hello');
        $this->assertEquals([], $s->get('tokens'));
        $s->save();
        $s->close();
        $s->start();
        $this->assertEquals([], $s->get('tokens'));
    }

    function testClose()
    {
        $s = $this->session();
        $this->assertNull($s->get('created_at'));

        $s->start();
        $this->assertNotNull($s->get('created_at'));

        $s->close();
        $this->assertNull($s->get('created_at'));
    }

    function testDestroy()
    {
        $s = $this->session();

        $s->start();
        $this->assertEquals([], $s->get('tokens'));
        $s->set('tokens.hello', 'dd my world!');
        $s->save();
        $s->close();

        $s->start();
        $this->assertEquals('dd my world!', $s->get('tokens.hello'));

        $s->destroy();
        $s->start();
        $this->assertNull($s->get('tokens.hello'));
        $s->destroy();
    }
}
