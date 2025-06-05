<?php
class Student {
    private $db;
    private $table = 'students';

    public function __construct($pdo_connection) {
        $this->db = $pdo_connection;
    }

    public function create($data) {
        $sql = "INSERT INTO " . $this->table . " (first_name, last_name, gender, birthdate, contact_number, course_id)
                VALUES (:first_name, :last_name, :gender, :birthdate, :contact_number, :course_id) RETURNING student_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':gender', $data['gender']);
            $stmt->bindParam(':birthdate', $data['birthdate']);
            $stmt->bindParam(':contact_number', $data['contact_number']);
            $stmt->bindParam(':course_id', $data['course_id'], PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Student creation error: " . $e->getMessage());
            return false;
        }
    }

    public function getById($student_id) {
        $sql = "SELECT s.*, c.course_name 
                FROM " . $this->table . " s
                LEFT JOIN courses c ON s.course_id = c.course_id
                WHERE s.student_id = :student_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll($filters = [], $orderBy = 's.last_name ASC, s.first_name ASC') {
        $sql = "SELECT s.*, c.course_name 
                FROM " . $this->table . " s
                LEFT JOIN courses c ON s.course_id = c.course_id"; 
        
        $whereClauses = [];
        $params = [];

        if (!empty($filters['course_id'])) {
            $whereClauses[] = "s.course_id = :filter_course_id";
            $params[':filter_course_id'] = $filters['course_id'];
        }
        if (!empty($filters['search_term'])) {
            $whereClauses[] = "(s.first_name ILIKE :search_term OR s.last_name ILIKE :search_term)";
            $params[':search_term'] = '%' . $filters['search_term'] . '%';
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
        
        $allowedOrderBy = ['s.last_name ASC, s.first_name ASC', 's.student_id DESC', 's.created_at DESC'];
        if (!in_array($orderBy, $allowedOrderBy) && !empty($orderBy)) {
            $orderBy = 's.last_name ASC, s.first_name ASC'; 
        }
        $sql .= " ORDER BY " . $orderBy;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function update($student_id, $data) {
        $fields = [];
        $params = [':student_id' => $student_id];
        $allowed_keys = ['first_name', 'last_name', 'gender', 'birthdate', 'contact_number', 'course_id'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_keys)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        if (empty($fields)) {
            return false; 
        }

        $sql = "UPDATE " . $this->table . " SET " . implode(", ", $fields) . " WHERE student_id = :student_id";
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => &$val) {
                if ($key === ':course_id' || $key === ':student_id') {
                    $stmt->bindParam($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindParam($key, $val);
                }
            }
            unset($val); 
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Student update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($student_id) {
        
        $sql = "DELETE FROM " . $this->table . " WHERE student_id = :student_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Student deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function getStudentsByCourse($course_id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE course_id = :course_id ORDER BY last_name, first_name";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>