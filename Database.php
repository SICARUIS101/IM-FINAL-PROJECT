<?php
class Database {
    private $host = "localhost";
    private $db_name = "Dashboard";
    private $username = "postgres";
    private $password = "Alburo";
    public $conn;

    public function connect() {
        try {
            $this->conn = new PDO("pgsql:host={$this->host};dbname={$this->db_name}",
                                  $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit;
        }
    }
}
?>
