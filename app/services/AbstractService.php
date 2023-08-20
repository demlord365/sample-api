<?php

namespace App\services;

use App\utilities\Db\Database;

abstract class AbstractService
{
    protected Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }
}