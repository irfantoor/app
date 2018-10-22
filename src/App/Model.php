<?php

namespace IrfanTOOR\App;

use Exception;
use IrfanTOOR\Database\Model as DatabaseModel;

class Model extends DatabaseModel
{
    function __construct($connection = [])
    {
        $class = explode('\\', get_called_class());
        $table = strtolower(array_pop($class));

        $defaults = [
            'table' => $table,
            'file'  => ROOT . 'storage/db/' . $table . '.sqlite',
        ];

        foreach ($defaults as $k => $v) {
        	if (!isset($connection[$k])) {
        		$connection[$k] = $v;
        	}
        }

        try {
            parent::__construct($connection);
        } catch(Exception $e) {
            $err = $e->getMessage();
            preg_match('/file \[(.*)\] does not exist/', $err, $m);
            if (isset($m[1])) {
                $file = $m[1];
                file_put_contents($file, '');
                parent::__construct($connection);
                $this->create();
            } else {
                throw new Exception($err);
                exit;
            }
        }
    }
}
