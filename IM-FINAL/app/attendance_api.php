<?php
// Set the content type to JSON for the response
header('Content-Type: application/json');

// Include the database configuration (PDO connection is expected here)
include 'config.php';

// Enable error reporting (for debugging during development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === Base Database Class ===
class Database {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
}

// === Student Class Handles Student Records ===
class Student extends Database {

    // Fetch all students and their course names
    public function getStudents() {
        try {
            $stmt = $this->pdo->query("
                SELECT s.student_id, s.first_name, s.last_name, s.birthdate, 
                       s.contact_number, s.course_id, c.course_name
                FROM students s
                JOIN courses c ON s.course_id = c.course_id
                ORDER BY s.last_name
            ");
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'data' => $students];

        } catch (Exception $e) {
            error_log("Error in getStudents: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Add a new student to the database
    public function addStudent($first_name, $last_name, $birthdate, $contact_number, $course_id) {
        try {
            if (empty($first_name) || empty($last_name) || empty($birthdate) || empty($course_id)) {
                throw new Exception('Required fields missing: first_name, last_name, birthdate, or course_id');
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO students (first_name, last_name, birthdate, contact_number, course_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$first_name, $last_name, $birthdate, $contact_number, $course_id]);

            return ['success' => true, 'message' => 'Student added successfully'];

        } catch (Exception $e) {
            error_log("Error in addStudent: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Delete a student by their ID
    public function deleteStudent($student_id) {
        try {
            if (empty($student_id)) {
                throw new Exception('Missing student_id');
            }

            $stmt = $this->pdo->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);

            if ($stmt->rowCount() == 0) {
                throw new Exception("No student found with ID: $student_id");
            }

            return ['success' => true, 'message' => 'Student deleted successfully'];

        } catch (Exception $e) {
            error_log("Error in deleteStudent: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}

// === Attendance Class Handles Attendance Records ===
class Attendance extends Database {

    // Fetch attendance for a specific course
    public function getAttendance($course_id) {
        try {
            if (empty($course_id)) {
                throw new Exception('Missing course_id');
            }

            $stmt = $this->pdo->prepare("
                SELECT a.attendance_id, a.student_id, s.first_name, s.last_name,
                       c.course_name, a.attendance_date, a.status, a.notes
                FROM attendance a
                JOIN students s ON a.student_id = s.student_id
                JOIN courses c ON a.course_id = c.course_id
                WHERE a.course_id = ?
                ORDER BY a.attendance_date DESC
            ");
            $stmt->execute([$course_id]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $records];

        } catch (Exception $e) {
            error_log("Error in getAttendance: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Add a new attendance record
    public function addAttendance($student_id, $course_id, $attendance_date, $status, $notes) {
        try {
            if (empty($student_id) || empty($course_id) || empty($attendance_date) || empty($status)) {
                throw new Exception('Required fields missing: student_id, course_id, attendance_date, or status');
            }

            $this->pdo->beginTransaction();

            // Validate student-course relationship
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM students WHERE student_id = ? AND course_id = ?
            ");
            $stmt->execute([$student_id, $course_id]);

            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Student ID $student_id is not enrolled in course ID $course_id");
            }

            // Insert attendance
            $stmt = $this->pdo->prepare("
                INSERT INTO attendance (student_id, course_id, attendance_date, status, notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$student_id, $course_id, $attendance_date, $status, $notes]);

            $this->pdo->commit();

            return ['success' => true, 'message' => 'Attendance record added successfully'];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error in addAttendance: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Update an existing attendance record
    public function updateAttendance($attendance_id, $student_id, $course_id, $attendance_date, $status, $notes) {
        try {
            if (empty($attendance_id) || empty($student_id) || empty($course_id) || empty($attendance_date) || empty($status)) {
                throw new Exception('Missing required fields for update');
            }

            $this->pdo->beginTransaction();

            // Validate student-course relationship again
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM students WHERE student_id = ? AND course_id = ?
            ");
            $stmt->execute([$student_id, $course_id]);

            if ($stmt->fetchColumn() == 0) {
                throw new Exception("Student ID $student_id is not in course ID $course_id");
            }

            $stmt = $this->pdo->prepare("
                UPDATE attendance
                SET student_id = ?, course_id = ?, attendance_date = ?, status = ?, notes = ?
                WHERE attendance_id = ?
            ");
            $stmt->execute([$student_id, $course_id, $attendance_date, $status, $notes, $attendance_id]);

            if ($stmt->rowCount() == 0) {
                throw new Exception("No attendance found with ID: $attendance_id");
            }

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Attendance updated successfully'];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error in updateAttendance: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Delete an attendance record by ID
    public function deleteAttendance($attendance_id) {
        try {
            if (empty($attendance_id)) {
                throw new Exception('Missing attendance_id');
            }

            $stmt = $this->pdo->prepare("DELETE FROM attendance WHERE attendance_id = ?");
            $stmt->execute([$attendance_id]);

            if ($stmt->rowCount() == 0) {
                throw new Exception("No attendance found with ID: $attendance_id");
            }

            return ['success' => true, 'message' => 'Attendance deleted successfully'];

        } catch (Exception $e) {
            error_log("Error in deleteAttendance: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}

// === Request Handling Starts Here ===
try {
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    // Make sure connection is working
    $pdo->query("SELECT 1");

    // Instantiate class objects
    $student = new Student($pdo); //object
    $attendance = new Attendance($pdo); //object

    // Basahon niya ang request action
    $action = $_POST['action'] ?? '';

    // Handle each action accordingly
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
            error_log("Incoming add_attendance: " . print_r($_POST, true));
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
    error_log("Fatal error in script: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
