<?php

use IrfanTOOR\Database\Model;
use Tests\MockModel;

use IrfanTOOR\Test;

class ModelTest extends Test
{
    protected $dbfile;

    function setup()
    {
        $this->dbfile = dirname(__FILE__) . '/tmp/test.sqlite';
        if (file_exists($this->dbfile)) {
            unlink($this->dbfile);
        }
    }

    function model()
    {
        return new MockModel([
            'table' => 'test',
            'file'  => $this->dbfile,
        ]);
    }

    function testModelInstance()
    {
        $m = $this->model();
        $this->assertInstanceOf(IrfanTOOR\App\Model::class, $m);
        $this->assertInstanceOf(IrfanTOOR\Database\Model::class, $m);
    }
}
