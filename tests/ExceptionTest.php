<?php

use IrfanTOOR\App\Exception;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    function testExceptionInstance()
    {
        $e = null;
        try {
            throw new Exception("Error Processing Request", 1);
        } catch (Exception $e) {}

        $this->assertInstanceOf(IrfanTOOR\App\Exception::class, $e);
        $this->assertInstanceOf(IrfanTOOR\Engine\Exception::class, $e);
        $this->assertEquals("Error Processing Request", $e->getMessage());
    }
}
