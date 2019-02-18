<?php

use Exception;
use IrfanTOOR\App\View;
use IrfanTOOR\Test;

class ViewTest extends Test
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
        $this->assertException(
            function() use($v){
                $tplt = 123;
                $r = $v->renderToString($tplt, [
                    'hello' => 'Hello World!',
                    'name' => 'someone',
                ]);
            },
            Exception::class,
            'prameter tplt must be a string'
        );

        # provided data must be an associative array
        $this->assertException(
            function() use($v){
                $tplt = 'hello.tplt';
                $r = $v->renderToString($tplt, 'world');
            },
            Exception::class,
            'provided data must be an associative array'
        );

        # view file: $file, does not exist
        $this->assertException(
            function() use($v){
                $tplt = 'home.tplt';
                $r = $v->renderToString($tplt, [
                    'hello' => 'Hello World!',
                    'name' => 'someone',
                ]);
            },
            Exception::class,
            'view file: ' . __DIR__ . '/views/home.tplt, does not exist'
        );
    }

    function testSameResultOfRenderAndRenderToString()
    {
        $v = $this->view();

        ob_start();
        $v->render('hello.tplt', [
            'hello' => 'Hello World!',
            'name' => 'someone',
        ]);
        $r1 = ob_get_clean();

        $r2 = $v->renderToString('hello.tplt', [
            'hello' => 'Hello World!',
            'name' => 'someone',
        ]);

        $this->assertEquals($r1, $r2);
    }
}
