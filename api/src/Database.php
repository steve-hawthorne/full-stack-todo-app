<?php

namespace Yohten\Api;

class Database {
    private $dbConnection = null;

    public function __construct() {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $db = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];

        try {
            $this->dbConnection = new \PDO(
                "mysql:host=$host;port=$port;dbname=$db", 
                $user, 
                $password
            );
        } catch(\PDOException $error) {
            exit($error->getMessage());
        }
    }

    public function connect() {
        return $this->dbConnection;
    }
}