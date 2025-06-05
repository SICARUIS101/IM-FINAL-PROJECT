<?php
class Module {
    private $db;
    private $table = 'modules';

    public function __construct($pdo_connection) {
        $this->db = $pdo_connection;
    }

    public function create($course_id, $module_name, $module_description = null) {
        $sql = "INSERT INTO " . $this->table . " (course_id, module_name, module_description) 
                VALUES (:course_id, :module_name, :module_description) RETURNING module_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->bindParam(':module_name', $module_name);
            $stmt->bindParam(':module_description', $module_description); 
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Module creation error: " . $e->getMessage());
            return false;
        }
    }

    public function getById($module_id) {
        $sql = "SELECT m.*, c.course_name 
                FROM " . $this->table . " m
                LEFT JOIN courses c ON m.course_id = c.course_id
                WHERE m.module_id = :module_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':module_id', $module_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCourseId($course_id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE course_id = :course_id ORDER BY module_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAll() {
        $sql = "SELECT m.*, c.course_name 
                FROM " . $this->table . " m
                LEFT JOIN courses c ON m.course_id = c.course_id
                ORDER BY c.course_name, m.module_name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($module_id, $course_id, $module_name, $module_description = null) {
        $sql = "UPDATE " . $this->table . " 
                SET course_id = :course_id, module_name = :module_name, module_description = :module_description
                WHERE module_id = :module_id"; 
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->bindParam(':module_name', $module_name);
            $stmt->bindParam(':module_description', $module_description);
            $stmt->bindParam(':module_id', $module_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Module update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($module_id) {
        $sql = "DELETE FROM " . $this->table . " WHERE module_id = :module_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':module_id', $module_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Module deletion error: " . $e->getMessage());
            return false;
        }
    }
    
    public function countByCourse($course_id) {
        $sql = "SELECT COUNT(*) FROM " . $this->table . " WHERE course_id = :course_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
?>