<?php
require_once 'Database.php';

class Teacher {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function addTeacher($firstName, $lastName, $email, $subject) {
        $sql = "INSERT INTO teachers (first_name, last_name, email, subject)
                VALUES (:first_name, :last_name, :email, :subject)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':subject' => $subject
        ]);
    }

    public function getAllTeachers() {
        $stmt = $this->conn->query("SELECT * FROM teachers ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM teachers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateTeacher($id, $first, $last, $email, $subject) {
        $sql = "UPDATE teachers SET first_name = :first, last_name = :last,
                email = :email, subject = :subject WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':first' => $first,
            ':last' => $last,
            ':email' => $email,
            ':subject' => $subject,
            ':id' => $id
        ]);
    }
    
    public function deleteTeacher($id) {
        $stmt = $this->conn->prepare("DELETE FROM teachers WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
    
}
?>
