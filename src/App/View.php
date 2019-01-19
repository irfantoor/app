<?php

namespace IrfanTOOR\App;

use Exception;
use Latte\Engine as LatteEngine;

class View
{
    protected $path;
    protected $tmp_path;

    function __construct($init = [])
    {
        $defaults = [
            'path'     => '',
            'tmp_path' => '/tmp/',
        ];

        foreach ($defaults as $k => $v) {
            if (!isset($init[$k])) {
                $init[$k] = $v;
            }
        }

        $this->path     = $init['path'];
        $this->tmp_path = $init['tmp_path'];
    }

    function render($tplt, $data = [])
    {
        if (!is_string($tplt))
            throw new Exception("prameter tplt must be a string", 1);

        if (!is_array($data))
            throw new Exception("provided data must be an associative array", 1);

        $file = $this->path . $tplt;

        if (!file_exists($file))
            throw new Exception("view file: $file, does not exist", 1);

        $latte = new LatteEngine();
        $latte->setTempDirectory($this->tmp_path);
        $latte->render($file, $data);
    }

    function renderToString($tplt, $data = [])
    {
        $e = null;

        ob_start();
            try {
                $this->render($tplt, $data);
            } catch (Exception $e) {
            }
        $contents = ob_get_clean();
        
        if ($e)
            throw new Exception($e->getMessage(), 1);
            
        return $contents;
    }
}
