<?php
namespace App\Core;

class Model
{
    protected \PDO $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }
}
