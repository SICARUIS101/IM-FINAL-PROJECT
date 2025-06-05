<?php
session_start();

class Database {
    private $pdo;

    public function __construct() {
        $host = 'localhost';
        $dbname = 'EletechTrack';
        $username = 'postgres';
        $password = 'almartinez';
        try {
            $this->pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }

    public function getPdo() {
        return $this->pdo;
    }
}

class Program {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getPrograms() {
        try {
            $stmt = $this->db->getPdo()->query('SELECT c.course_id, c.course_name, c.course_description, COUNT(s.student_id) as enrolled_students 
                FROM courses c 
                LEFT JOIN students s ON c.course_id = s.course_id 
                GROUP BY c.course_id, c.course_name, c.course_description');
            $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $programs];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getProgram($course_id) {
        try {
            $stmt = $this->db->getPdo()->prepare('SELECT * FROM courses WHERE course_id = :course_id');
            $stmt->execute(['course_id' => $course_id]);
            $program = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $this->db->getPdo()->prepare('SELECT student_id FROM students WHERE course_id = :course_id');
            $stmt->execute(['course_id' => $course_id]);
            $students = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $program['student_ids'] = $students;
            return ['success' => true, 'data' => $program];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function addProgram($course_name, $course_description, $student_ids) {
    try {
        $this->db->getPdo()->beginTransaction();
        $stmt = $this->db->getPdo()->prepare('INSERT INTO courses (course_name, course_description) VALUES (:course_name, :course_description) RETURNING course_id');
        $stmt->execute(['course_name' => $course_name, 'course_description' => $course_description]);
        $course_id = $stmt->fetchColumn();

        // Only update students with the new course_id if student_ids are provided
        if (!empty($student_ids)) {
            // First, identify students currently enrolled in this course (if any)
            $stmt = $this->db->getPdo()->prepare('SELECT student_id FROM students WHERE course_id = :course_id');
            $stmt->execute(['course_id' => $course_id]);
            $current_students = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Unenroll students not in the new student_ids list
            foreach ($current_students as $student_id) {
                if (!in_array($student_id, $student_ids)) {
                    $stmt = $this->db->getPdo()->prepare('UPDATE students SET course_id = NULL WHERE student_id = :student_id AND course_id = :course_id');
                    $stmt->execute(['student_id' => $student_id, 'course_id' => $course_id]);
                }
            }

            // Enroll selected students
            foreach ($student_ids as $student_id) {
                $stmt = $this->db->getPdo()->prepare('UPDATE students SET course_id = :course_id WHERE student_id = :student_id');
                $stmt->execute(['course_id' => $course_id, 'student_id' => $student_id]);
            }
        }

        $this->db->getPdo()->commit();
        return ['success' => true];
    } catch (PDOException $e) {
        $this->db->getPdo()->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

    public function updateProgram($course_id, $course_name, $course_description, $student_ids) {
    try {
        $this->db->getPdo()->beginTransaction();
        $stmt = $this->db->getPdo()->prepare('UPDATE courses SET course_name = :course_name, course_description = :course_description WHERE course_id = :course_id');
        $stmt->execute(['course_name' => $course_name, 'course_description' => $course_description, 'course_id' => $course_id]);

        // Only update students if student_ids are provided
        if (!empty($student_ids)) {
            // Identify current enrolled students
            $stmt = $this->db->getPdo()->prepare('SELECT student_id FROM students WHERE course_id = :course_id');
            $stmt->execute(['course_id' => $course_id]);
            $current_students = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Unenroll students not in the new student_ids list
            foreach ($current_students as $student_id) {
                if (!in_array($student_id, $student_ids)) {
                    $stmt = $this->db->getPdo()->prepare('UPDATE students SET course_id = NULL WHERE student_id = :student_id AND course_id = :course_id');
                    $stmt->execute(['student_id' => $student_id, 'course_id' => $course_id]);
                }
            }

            // Enroll selected students
            foreach ($student_ids as $student_id) {
                $stmt = $this->db->getPdo()->prepare('UPDATE students SET course_id = :course_id WHERE student_id = :student_id');
                $stmt->execute(['course_id' => $course_id, 'student_id' => $student_id]);
            }
        } else {
            // If no students are selected, clear all enrollments for this course
            $stmt = $this->db->getPdo()->prepare('UPDATE students SET course_id = NULL WHERE course_id = :course_id');
            $stmt->execute(['course_id' => $course_id]);
        }

        $this->db->getPdo()->commit();
        return ['success' => true];
    } catch (PDOException $e) {
        $this->db->getPdo()->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

    public function deleteProgram($course_id) {
        try {
            $stmt = $this->db->getPdo()->prepare('DELETE FROM courses WHERE course_id = :course_id');
            $stmt->execute(['course_id' => $course_id]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

class Module {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getModules($course_id) {
        try {
            $stmt = $this->db->getPdo()->prepare('SELECT * FROM modules WHERE course_id = :course_id');
            $stmt->execute(['course_id' => $course_id]);
            $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $modules];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getModule($module_id) {
        try {
            $stmt = $this->db->getPdo()->prepare('SELECT * FROM modules WHERE module_id = :module_id');
            $stmt->execute(['module_id' => $module_id]);
            return ['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function addModule($course_id, $module_name, $module_description) {
        try {
            $stmt = $this->db->getPdo()->prepare('INSERT INTO modules (course_id, module_name, module_description) VALUES (:course_id, :module_name, :module_description)');
            $stmt->execute(['course_id' => $course_id, 'module_name' => $module_name, 'module_description' => $module_description]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateModule($module_id, $module_name, $module_description, $course_id) {
        try {
            $stmt = $this->db->getPdo()->prepare('UPDATE modules SET module_name = :module_name, module_description = :module_description, course_id = :course_id WHERE module_id = :module_id');
            $stmt->execute(['module_name' => $module_name, 'module_description' => $module_description, 'course_id' => $course_id, 'module_id' => $module_id]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteModule($module_id) {
        try {
            $stmt = $this->db->getPdo()->prepare('DELETE FROM modules WHERE module_id = :module_id');
            $stmt->execute(['module_id' => $module_id]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

class Student {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getStudents() {
        try {
            $stmt = $this->db->getPdo()->query('SELECT student_id, first_name, last_name FROM students');
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $students];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Main controller logic
$db = new Database();
$program = new Program($db);
$module = new Module($db);
$student = new Student($db);

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'getPrograms':
        echo json_encode($program->getPrograms());
        break;
    case 'getProgram':
        echo json_encode($program->getProgram($_POST['course_id']));
        break;
    case 'addProgram':
        echo json_encode($program->addProgram($_POST['course_name'], $_POST['course_description'], $_POST['student_ids'] ?? []));
        break;
    case 'updateProgram':
        echo json_encode($program->updateProgram($_POST['course_id'], $_POST['course_name'], $_POST['course_description'], $_POST['student_ids'] ?? []));
        break;
    case 'deleteProgram':
        echo json_encode($program->deleteProgram($_POST['course_id']));
        break;
    case 'getModules':
        echo json_encode($module->getModules($_POST['course_id']));
        break;
    case 'getModule':
        echo json_encode($module->getModule($_POST['module_id']));
        break;
    case 'addModule':
        echo json_encode($module->addModule($_POST['course_id'], $_POST['module_name'], $_POST['module_description']));
        break;
    case 'updateModule':
        echo json_encode($module->updateModule($_POST['module_id'], $_POST['module_name'], $_POST['module_description'], $_POST['course_id']));
        break;
    case 'deleteModule':
        echo json_encode($module->deleteModule($_POST['module_id']));
        break;
    case 'getStudents':
        echo json_encode($student->getStudents());
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>