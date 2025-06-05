<?php
class Course {
    private $db;
    private $table = 'courses';

    public function __construct($pdo_connection) {
        $this->db = $pdo_connection;
    }

    public function create($course_name, $course_description = null) { 
        $sql = "INSERT INTO " . $this->table . " (course_name, course_description) 
                VALUES (:course_name, :course_description) RETURNING course_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':course_name', $course_name);
            $stmt->bindParam(':course_description', $course_description); 
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Course creation error: " . $e->getMessage());
            return false;
        }
    }
    public function getById($course_id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE course_id = :course_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll($searchTerm = null) { 
        $sql = "SELECT * FROM " . $this->table;
        $params = [];

        if ($searchTerm !== null && $searchTerm !== '') {
           
            $sql .= " WHERE course_name ILIKE :searchTerm OR course_description ILIKE :searchTerm";
            $params[':searchTerm'] = '%' . $searchTerm . '%';
        }
        
        $sql .= " ORDER BY course_name ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching courses: " . $e->getMessage());
            return false;
        }
    }

    public function update($course_id, $course_name, $course_description = null) { 
        $sql = "UPDATE " . $this->table . " 
                SET course_name = :course_name, course_description = :course_description 
                WHERE course_id = :course_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':course_name', $course_name);
            $stmt->bindParam(':course_description', $course_description); 
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Course update error: " . $e->getMessage());
            return false;
        }
    }
    public function delete($course_id) {
        $sql = "DELETE FROM " . $this->table . " WHERE course_id = :course_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23503') {
                error_log("Cannot delete course: It is referenced by other records.");
            } else {
                error_log("Course deletion error: " . $e->getMessage());
            }
            return false;
        }
    }
}
?>
