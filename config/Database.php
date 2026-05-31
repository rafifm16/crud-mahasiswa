<?php
/**
 * Database.php - Kelas koneksi database menggunakan MySQLi OOP
 */
class Database {
    private string $host     = 'localhost';
    private string $username = 'root';
    private string $password = '';
    private string $dbname   = 'crud_mahasiswa';
    private int    $port     = 3306;

    public mysqli $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect(): void {
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->dbname,
            $this->port
        );

        if ($this->conn->connect_error) {
            die(json_encode([
                'status'  => 'error',
                'message' => 'Koneksi database gagal: ' . $this->conn->connect_error
            ]));
        }

        $this->conn->set_charset('utf8mb4');
    }

    public function getConnection(): mysqli {
        return $this->conn;
    }

    public function close(): void {
        $this->conn->close();
    }
}
