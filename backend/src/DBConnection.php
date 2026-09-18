<?php

namespace Vendor\Schoolarsystem;

class DBConnection
{
    private static ?DBConnection $instance = null;

    private \mysqli $connection;

    public function __construct()
    {
        loadEnv::cargar();

        $host = $_ENV['DB_HOST'];
        $user = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'];
        $db = $_ENV['DB_NAME'];

        $this->connection = new \mysqli($host, $user, $password, $db);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

        if (!$this->connection->set_charset("utf8")) {
            die("Error al cargar el conjunto de caracteres utf8: " . $this->connection->error);
        }
    }

    public static function getInstance(): DBConnection
    {
        if (self::$instance === null) {
            self::$instance = new DBConnection();
        }

        return self::$instance;
    }

    public function getConnection(): \mysqli
    {
        return $this->connection;
    }
}