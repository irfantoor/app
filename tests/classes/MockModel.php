<?php

namespace Tests;

use IrfanTOOR\App\Model;

class MockModel extends Model
{
    function __construct($connection = [])
    {
        $this->schema = [
            'id NTEGER PRIMARY KEY',
            'name NOT NULL',
            'email NOT NULL',
            'created_on DATETIME DEFAULT CURRENT_TIMESTAMP',
        ];

        $this->indecies = [
            ['index'  => 'name'],
            ['unique' => 'email'],
        ];

        parent::__construct($connection);
    }
}
