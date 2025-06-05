<?php
session_start();

class Database {
    private $conn;
    private $host = "localhost";
    private $dbname = "FINAL_IM"; 
    private $username = "postgres"; 
    private $password = "almartinez"; 

    public function connect() {
        try {
            $this->conn = new PDO("pgsql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return null;
        }
    }
}

class Auth {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database->connect();
    }

    public function logout() {
        try {
            // Clear all session data
            $_SESSION = array();
            
            // Destroy the session
            session_destroy();
            
            return true;
        } catch (Exception $e) {
            echo "Logout error: " . $e->getMessage();
            return false;
        }
    }
}

// Handle logout request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'logout') {
    $db = new Database();
    $auth = new Auth($db);
    
    if ($auth->logout()) {
        header("Location: admin.php"); // Redirect to login page
        exit();
    } else {
        echo "Failed to logout. Please try again.";
    }
}
?>