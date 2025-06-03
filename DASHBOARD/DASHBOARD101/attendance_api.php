<?php
header('Content-Type: application/json');
include 'config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Database {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
}

class Student extends Database {
    public function getStudents() {
        try {
            $stmt = $this->pdo->query("SELECT s.student_id, s.first_name, s.last_name, s.birthdate, s.contact_number, s.course_id, c.course_name 
                                       FROM students s 
                                       JOIN courses c ON s.course_id = c.course_id 
                                       ORDER BY s.last_name");
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $students];
        } catch (Exception $e) {
            error_log("Error in getStudents: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function addStudent($first_name, $last_name, $birthdate, $contact_number, $course_id) {
        try {
            if (empty($first_name) || empty($last_name) || empty($birthdate) || empty($course_id)) {
                throw new Exception('Missing required fields: first_name, last_name, birthdate, or course_id');
            }
            $stmt = $this->pdo->prepare("INSERT INTO students (first_name, last_name, birthdate, contact_number, course_id) 
                                        VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $birthdate, $contact_number, $course_id]);
            return ['success' => true, 'message' => 'Student added successfully'];
        } catch (Exception $e) {
            error_log("Error in addStudent: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function deleteStudent($student_id) {
        try {
            if (empty($student_id)) {
                throw new Exception('Missing required field: student_id');
            }
            $stmt = $this->pdo->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            if ($stmt->rowCount() == 0) {
                throw new Exception('No student found with ID: ' . $student_id);
            }
            return ['success' => true, 'message' => 'Student and their attendance records deleted successfully'];
        } catch (Exception $e) {
            error_log("Error in deleteStudent: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}

class Attendance extends Database {
    public function getAttendance($course_id) {
        try {
            if (empty($course_id)) {
                throw new Exception('Missing required field: course_id');
            }
            $stmt = $this->pdo->prepare("SELECT a.attendance_id, a.student_id, s.first_name, s.last_name, c.course_name, a.attendance_date, a.status, a.notes 
                                        FROM attendance a 
                                        JOIN students s ON a.student_id = s.student_id 
                                        JOIN courses c ON a.course_id = c.course_id 
                                        WHERE a.course_id = ? 
                                        ORDER BY a.attendance_date DESC");
            $stmt->execute([$course_id]);
            $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $attendance];
        } catch (Exception $e) {
            error_log("Error in getAttendance: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function addAttendance($student_id, $course_id, $attendance_date, $status, $notes) {
        try {
            if (empty($student_id) || empty($course_id) || empty($attendance_date) || empty($status)) {
                throw new Exception('Missing required fields: student_id, course_id, attendance_date, or status');
            }
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ? AND course_id = ?");
            $stmt->execute([$student_id, $course_id]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception('Student ID ' . $student_id . ' does not belong to course ID ' . $course_id);
            }
            $stmt = $this->pdo->prepare("INSERT INTO attendance (student_id, course_id, attendance_date, status, notes) 
                                        VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$student_id, $course_id, $attendance_date, $status, $notes]);
            $this->pdo->commit();
            return ['success' => true, 'message' => 'Attendance record added successfully'];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error in addAttendance: " . $e->getMessage());
            if ($e->getCode() == '23503') {
                return ['success' => false, 'message' => 'Foreign key error: Invalid student ID or course ID'];
            }
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function updateAttendance($attendance_id, $student_id, $course_id, $attendance_date, $status, $notes) {
        try {
            if (empty($attendance_id) || empty($student_id) || empty($course_id) || empty($attendance_date) || empty($status)) {
                throw new Exception('Missing required fields: attendance_id, student_id, course_id, attendance_date, or status');
            }
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ? AND course_id = ?");
            $stmt->execute([$student_id, $course_id]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception('Student ID ' . $student_id . ' does not belong to course ID ' . $course_id);
            }
            $stmt = $this->pdo->prepare("UPDATE attendance SET student_id = ?, course_id = ?, attendance_date = ?, status = ?, notes = ? 
                                        WHERE attendance_id = ?");
            $stmt->execute([$student_id, $course_id, $attendance_date, $status, $notes, $attendance_id]);
            if ($stmt->rowCount() == 0) {
                throw new Exception('No attendance record found with ID: ' . $attendance_id);
            }
            $this->pdo->commit();
            return ['success' => true, 'message' => 'Attendance record updated successfully'];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error in updateAttendance: " . $e->getMessage());
            if ($e->getCode() == '23503') {
                return ['success' => false, 'message' => 'Foreign key error: Invalid student ID or course ID'];
            }
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function deleteAttendance($attendance_id) {
        try {
            if (empty($attendance_id)) {
                throw new Exception('Missing required field: attendance_id');
            }
            $stmt = $this->pdo->prepare("DELETE FROM attendance WHERE attendance_id = ?");
            $stmt->execute([$attendance_id]);
            if ($stmt->rowCount() == 0) {
                throw new Exception('No attendance record found with ID: ' . $attendance_id);
            }
            return ['success' => true, 'message' => 'Attendance record deleted successfully'];
        } catch (Exception $e) {
            error_log("Error in deleteAttendance: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}

try {
    if (!$pdo) {
        throw new Exception('Database connection is not established');
    }

    // Test database connection
    $pdo->query("SELECT 1");

    $action = $_POST['action'] ?? '';
    $student = new Student($pdo);
    $attendance = new Attendance($pdo);

    switch ($action) {
        case 'get_students':
            echo json_encode($student->getStudents());
            break;
        case 'add_student':
            echo json_encode($student->addStudent(
                $_POST['first_name'] ?? '',
                $_POST['last_name'] ?? '',
                $_POST['birthdate'] ?? '',
                $_POST['contact_number'] ?? null,
                $_POST['course_id'] ?? ''
            ));
            break;
        case 'delete_student':
            echo json_encode($student->deleteStudent($_POST['student_id'] ?? ''));
            break;
        case 'get_attendance':
            echo json_encode($attendance->getAttendance($_POST['course_id'] ?? ''));
            break;
        case 'add_attendance':
            error_log("add_attendance data: " . print_r($_POST, true));
            echo json_encode($attendance->addAttendance(
                $_POST['student_id'] ?? '',
                $_POST['course_id'] ?? '',
                $_POST['attendance_date'] ?? '',
                $_POST['status'] ?? '',
                $_POST['notes'] ?? ''
            ));
            break;
        case 'update_attendance':
            echo json_encode($attendance->updateAttendance(
                $_POST['attendance_id'] ?? '',
                $_POST['student_id'] ?? '',
                $_POST['course_id'] ?? '',
                $_POST['attendance_date'] ?? '',
                $_POST['status'] ?? '',
                $_POST['notes'] ?? ''
            ));
            break;
        case 'delete_attendance':
            echo json_encode($attendance->deleteAttendance($_POST['attendance_id'] ?? ''));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log("Error in attendance_api.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
