<?php
class StudentModuleProgress {
    private $db;
    private $table = 'student_module_progress';

    public function __construct($pdo_connection) {
        $this->db = $pdo_connection;
    }
    public function setProgress($student_id, $module_id, $is_completed, $completion_date = null) {
        $sql = "INSERT INTO " . $this->table . " (student_id, module_id, is_completed, completion_date)
                VALUES (:student_id, :module_id, :is_completed, :completion_date)
                ON CONFLICT (student_id, module_id) DO UPDATE SET
                    is_completed = EXCLUDED.is_completed,
                    completion_date = EXCLUDED.completion_date
                    -- updated_at is handled by trigger
                RETURNING progress_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindParam(':module_id', $module_id, PDO::PARAM_INT);
            $stmt->bindParam(':is_completed', $is_completed, PDO::PARAM_BOOL);
            $stmt->bindParam(':completion_date', $completion_date);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Student module progress setting error: " . $e->getMessage());
            return false;
        }
    }

    public function getProgress($student_id, $module_id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE student_id = :student_id AND module_id = :module_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindParam(':module_id', $module_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getStudentProgressForCourse($student_id) {
        $sql = "SELECT smp.*, m.module_name, m.course_id
                FROM " . $this->table . " smp
                JOIN modules m ON smp.module_id = m.module_id
                JOIN students s ON smp.student_id = s.student_id AND m.course_id = s.course_id
                WHERE smp.student_id = :student_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function areAllModulesCompleted($student_id) {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM modules m_total WHERE m_total.course_id = s.course_id) AS total_course_modules,
                    COUNT(smp.module_id) AS completed_student_modules
                FROM students s
                LEFT JOIN student_module_progress smp ON s.student_id = smp.student_id AND smp.is_completed = TRUE
                LEFT JOIN modules m_check ON smp.module_id = m_check.module_id AND m_check.course_id = s.course_id
                WHERE s.student_id = :student_id
                GROUP BY s.course_id"; 
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['total_course_modules'] > 0) {
            return $result['total_course_modules'] == $result['completed_student_modules'];
        }
        return false; 
    }

    public function getStudentsForModuleStatus($course_id, $module_id) {
        $sql = "SELECT s.student_id, s.first_name, s.last_name, c.course_name,
                       COALESCE(smp.is_completed, FALSE) as is_completed, smp.completion_date
                FROM students s
                JOIN courses c ON s.course_id = c.course_id
                JOIN modules m_target ON m_target.course_id = :course_id AND m_target.module_id = :module_id
                LEFT JOIN " . $this->table . " smp ON s.student_id = smp.student_id AND smp.module_id = :module_id_for_smp
                WHERE s.course_id = :course_id_for_student
                ORDER BY s.last_name, s.first_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT); 
        $stmt->bindParam(':module_id', $module_id, PDO::PARAM_INT); 
        $stmt->bindParam(':module_id_for_smp', $module_id, PDO::PARAM_INT); 
        $stmt->bindParam(':course_id_for_student', $course_id, PDO::PARAM_INT); 
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>