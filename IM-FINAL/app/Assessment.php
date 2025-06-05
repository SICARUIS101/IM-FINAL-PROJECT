<?php
class Assessment {
    private $db;
    private $table = 'assessments';

    public function __construct($pdo_connection) {
        $this->db = $pdo_connection;
    }

    public function create($data) {
        $sql = "INSERT INTO " . $this->table . " 
                    (student_id, course_id, assessment_title, date_conducted, status, result, score, assessor, tries, remarks, certification_status)
                VALUES 
                    (:student_id, :course_id, :assessment_title, :date_conducted, :status, :result, :score, :assessor, :tries, :remarks, :certification_status)
                RETURNING assessment_id";
        try {
            $stmt = $this->db->prepare($sql);
            $allowed_keys = ['student_id', 'course_id', 'assessment_title', 'date_conducted', 'status', 'result', 'score', 'assessor', 'tries', 'remarks', 'certification_status'];
            $bind_params = [];
            foreach($allowed_keys as $key){
                $bind_params[":$key"] = isset($data[$key]) ? $data[$key] : null;
            }

            $stmt->execute($bind_params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Assessment creation error: " . $e->getMessage());
            return false;
        }
    }

    public function getById($assessment_id) {
        $sql = "SELECT a.*, s.first_name, s.last_name, c.course_name
                FROM " . $this->table . " a
                LEFT JOIN students s ON a.student_id = s.student_id
                LEFT JOIN courses c ON a.course_id = c.course_id
                WHERE a.assessment_id = :assessment_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':assessment_id', $assessment_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getByStudentAndCourse($student_id, $course_id, $latestOnly = false) {
        $sql = "SELECT * FROM " . $this->table . " 
                WHERE student_id = :student_id AND course_id = :course_id 
                ORDER BY date_conducted DESC, created_at DESC";
        if ($latestOnly) {
            $sql .= " LIMIT 1";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        return $latestOnly ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAll($filters = [], $orderBy = 'a.date_conducted DESC') {
        $sql = "SELECT a.*, s.first_name, s.last_name, c.course_name
                FROM " . $this->table . " a
                LEFT JOIN students s ON a.student_id = s.student_id
                LEFT JOIN courses c ON a.course_id = c.course_id";
        
        $whereClauses = [];
        $params = [];

        if (!empty($filters['course_id'])) {
            $whereClauses[] = "a.course_id = :filter_course_id";
            $params[':filter_course_id'] = $filters['course_id'];
        }
        if (!empty($filters['result']) && $filters['result'] !== 'N/A') {
            $whereClauses[] = "a.result = :filter_result";
            $params[':filter_result'] = $filters['result'];
        } elseif (isset($filters['result']) && $filters['result'] === 'N/A') {
            $whereClauses[] = "(a.result = 'N/A' OR a.result = 'To be assessed' OR a.result IS NULL)";
        }
        if (!empty($filters['status'])) {
            $whereClauses[] = "a.status = :filter_status";
            $params[':filter_status'] = $filters['status'];
        }
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $whereClauses[] = "a.date_conducted BETWEEN :date_from AND :date_to";
            $params[':date_from'] = $filters['date_from'];
            $params[':date_to'] = $filters['date_to'];
        }
         if (!empty($filters['search_term'])) { 
            $whereClauses[] = "(s.first_name ILIKE :search_term OR s.last_name ILIKE :search_term)";
            $params[':search_term'] = '%' . $filters['search_term'] . '%';
        }


        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }
        $sql .= " ORDER BY " . $orderBy; 

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($assessment_id, $data) {
        $fields = [];
        $params = [':assessment_id' => $assessment_id];
        $allowed_keys = ['student_id', 'course_id', 'assessment_title', 'date_conducted', 'status', 'result', 'score', 'assessor', 'tries', 'remarks', 'certification_status'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_keys)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE " . $this->table . " SET " . implode(", ", $fields) . " WHERE assessment_id = :assessment_id"; 
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => &$val) {
                if ($key === ':assessment_id' || $key === ':student_id' || $key === ':course_id') {
                    $stmt->bindParam($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindParam($key, $val);
                }
            }
            unset($val);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Assessment update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($assessment_id) {
        $sql = "DELETE FROM " . $this->table . " WHERE assessment_id = :assessment_id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':assessment_id', $assessment_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Assessment deletion error: " . $e->getMessage());
            return false;
        }
    }
    public function getEligibleStudentsForAssessment($filters = []) {
        $baseSql = "
            WITH StudentCourseModules AS (
                -- Count total modules for each student's enrolled course
                SELECT 
                    s.student_id,
                    s.course_id,
                    COUNT(m.module_id) AS total_course_modules
                FROM students s
                JOIN modules m ON s.course_id = m.course_id
                GROUP BY s.student_id, s.course_id
            ),
            StudentCompletedCourseModules AS (
                -- Count completed modules for each student within their enrolled course
                SELECT 
                    smp.student_id,
                    m.course_id,
                    COUNT(smp.module_id) AS completed_modules_count
                FROM student_module_progress smp
                JOIN modules m ON smp.module_id = m.module_id
                WHERE smp.is_completed = TRUE
                GROUP BY smp.student_id, m.course_id
            ),
            EligibleStudentsBase AS (
                -- Identify students who have completed all modules for their course
                SELECT 
                    s.student_id,
                    s.first_name,
                    s.last_name,
                    s.course_id,
                    crs.course_name
                FROM students s
                JOIN courses crs ON s.course_id = crs.course_id
                JOIN StudentCourseModules scm_total ON s.student_id = scm_total.student_id AND s.course_id = scm_total.course_id
                LEFT JOIN StudentCompletedCourseModules scm_completed ON s.student_id = scm_completed.student_id AND s.course_id = scm_completed.course_id
                WHERE scm_total.total_course_modules > 0 AND scm_total.total_course_modules = COALESCE(scm_completed.completed_modules_count, 0)
            ),
            LatestStudentAssessments AS (
                -- Get the latest assessment record for each student per course
                SELECT 
                    a.*,
                    ROW_NUMBER() OVER(PARTITION BY a.student_id, a.course_id ORDER BY a.date_conducted DESC, a.created_at DESC) as rn
                FROM assessments a
            )
            SELECT 
                esb.student_id,
                esb.first_name,
                esb.last_name,
                esb.course_id,
                esb.course_name,
                lsa.assessment_id,
                lsa.assessment_title,
                lsa.date_conducted,
                COALESCE(lsa.status, 'To be assessed') AS assessment_status, -- Default if no assessment yet
                COALESCE(lsa.result, 'N/A') AS assessment_result,          -- Default if no assessment yet
                lsa.score AS assessment_score,
                lsa.certification_status
            FROM EligibleStudentsBase esb
            LEFT JOIN LatestStudentAssessments lsa ON esb.student_id = lsa.student_id AND esb.course_id = lsa.course_id AND lsa.rn = 1
        ";

        $whereClauses = [];
        $queryParams = [];

        if (!empty($filters['course_id'])) {
            $whereClauses[] = "esb.course_id = :filter_course_id";
            $queryParams[':filter_course_id'] = $filters['course_id'];
        }
        if (!empty($filters['search_term'])) {
            $whereClauses[] = "(esb.first_name ILIKE :search_term OR esb.last_name ILIKE :search_term)";
            $queryParams[':search_term'] = '%' . $filters['search_term'] . '%';
        }
        if (isset($filters['assessment_result'])) {
            if ($filters['assessment_result'] === 'N/A') { 
                $whereClauses[] = "(lsa.assessment_id IS NULL OR lsa.result = 'N/A' OR lsa.result = 'To be assessed')";
            } elseif (!empty($filters['assessment_result'])) {
                $whereClauses[] = "lsa.result = :filter_assessment_result";
                $queryParams[':filter_assessment_result'] = $filters['assessment_result'];
            }
        }
         if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $whereClauses[] = "(lsa.date_conducted BETWEEN :date_from AND :date_to)";
            $queryParams[':date_from'] = $filters['date_from'];
            $queryParams[':date_to'] = $filters['date_to'];
        }


        if (!empty($whereClauses)) {
            $baseSql .= " WHERE " . implode(" AND ", $whereClauses);
        }
        
        $baseSql .= " ORDER BY esb.last_name, esb.first_name";
        
        $stmt = $this->db->prepare($baseSql);
        $stmt->execute($queryParams);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>