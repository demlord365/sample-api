<?php

namespace App\utilities\Db;

final class Database
{

    private \PDO $pdo;

    public function __construct() {
        $config = require __DIR__.'/../../configs/database.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

        $username = $config['user'];
        $password = $config['password'];

        try {
            $this->pdo = new \PDO($dsn, $username, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {

            die("Connection failed: " . $e->getMessage());
        }
    }

    public function query($query, $params = [])
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function getPdoInstance(): \PDO
    {
        return $this->pdo;
    }
}