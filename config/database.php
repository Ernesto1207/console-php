<?php
require_once 'config.php';

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct()
    {
        global $current;
        $this->host = $current['host'];
        $this->db_name = $current['db_name'];
        $this->username = $current['username'];
        $this->password = $current['password'];
    }

    public function conectar()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");

            // $this->conn->exec("SET time_zone = 'America/Lima'");
        } catch (PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
