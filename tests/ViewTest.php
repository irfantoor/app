<?php

use IrfanTOOR\App\Exception;
use IrfanTOOR\App\View;

use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    protected $path     = __DIR__ . '/views/';
    protected $tmp_path = __DIR__ . '/tmp/';

    function view()
    {
        return new View([
            'path'     => $this->path,
            'tmp_path' => $this->tmp_path,
        ]);
    }

    function testViewInstance()
    {
        $v = $this->view();
        $this->assertInstanceOf(IrfanTOOR\App\View::class, $v);
    }

    function testRender()
    {
        $v = $this->view();

        ob_start();
        $v->render('hello.tplt', [
            'hello' => 'Hello World!',
            'name' => 'someone',
        ]);
        $r = ob_get_clean();

        $this->assertEquals('Hello World!, I\'m someone.', $r);
    }

    function testRenderToString()
    {
        $v = $this->view();

        $r = $v->renderToString('hello.tplt', [
            'hello' => 'Hello World!',
            'name' => 'someone',
        ]);

        $this->assertEquals('Hello World!, I\'m someone.', $r);
    }

    function testTemplateValidity()
    {
        $v = $this->view();

        # prameter tplt must be a string
        $e = null;
        $tplt = 123;
        try {
            $r = $v->renderToString($tplt, [
                'hello' => 'Hello World!',
                'name' => 'someone',
            ]);            
        } catch (Exception $e) {}

        $this->assertInstanceOf(Exception::class, $e);
        $this->assertEquals('prameter tplt must be a string', $e->getMessage());

        # provided data must be an associative array
        $e = null;
        $tplt = 'hello.tplt';
        try {
            $r = $v->renderToString($tplt, 'world');            
        } catch (Exception $e) {}

        $this->assertInstanceOf(Exception::class, $e);
        $this->assertEquals('provided data must be an associative array', $e->getMessage());

        # view file: $file, does not exist
        $e = null;
        $tplt = 'home.tplt';
        try {
            $r = $v->renderToString($tplt, [
                'hello' => 'Hello World!',
                'name' => 'someone',
            ]);            
        } catch (Exception $e) {}

        $this->assertInstanceOf(Exception::class, $e);
        $this->assertEquals(
            'view file: ' . 
            __DIR__ . '/views/' . $tplt . 
            ', does not exist', $e->getMessage()
        );
    }

    function testSameResultOfRenderAndRenderToString()
    {
        $v = $this->view();
        $tplt = 'test.tplt';
        $file = $this->path . $tplt;
        $contents = file_get_contents($this->path . 'hello.tplt');

        file_put_contents($file, $contents);

        ob_start();
        $v->render('hello.tplt', [
            'hello' => 'Hello World!',
            'name' => 'someone',
        ]);
        $r1 = ob_get_clean();

        $r2 = $v->renderToString('test.tplt', [
            'hello' => 'Hello World!',
            'name' => 'someone',
        ]);

        $this->assertEquals($r1, $r2);
    }
}
