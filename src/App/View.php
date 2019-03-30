<?php

namespace IrfanTOOR\App;

use Exception;
use IrfanTOOR\TemplateEngine;

class View
{
    protected $path;
    protected $tmp_path;

    function __construct($init = [])
    {
        $defaults = [
            'path'     => defined('APP') ? APP . 'View/' : '',
            'tmp_path' => defined('STORAGE') ? STORAGE . '/tmp/' : '/tmp/',
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

        $te = new TemplateEngine([
            'base_path' => $this->path
        ]);

        echo $te->processFile($tplt, $data);
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
