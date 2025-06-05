<?php
session_start();

class Database {
    private $conn;
    private $host = "localhost";
    private $dbname = "EletechTrack"; 
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

class Registrar {
    private $db;

    public function __construct(Database $database) {
        $this->db = $database->connect();
    }

    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM REGISTRAR WHERE USERNAME = :username AND PASSWORD = :password");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['registrar_id'] = $user['REGISTRAR_ID'];
                $_SESSION['username'] = $user['USERNAME'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}

// Handle form submission
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $db = new Database();
    $registrar = new Registrar($db);

    if ($registrar->login($username, $password)) {
        header("Location: dashboard.php"); // Redirect to a dashboard page
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>