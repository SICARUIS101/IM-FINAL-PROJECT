<?php
header('Content-Type: application/json');
include 'config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    if (!$pdo) {
        throw new Exception('Database connection is not established');
    }

    // Test database connection
    $pdo->query("SELECT 1");

    switch ($action) {
        case 'get_students':
            $stmt = $pdo->query("SELECT s.student_id, s.first_name, s.last_name, s.gender, s.birthdate, s.contact_number, s.course_id, c.course_name FROM students s JOIN courses c ON s.course_id = c.course_id ORDER BY s.last_name");
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $students]);
            break;

        case 'add_student':
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $birthdate = $_POST['birthdate'] ?? '';
            $contact_number = $_POST['contact_number'] ?? null;
            $course_id = $_POST['course_id'] ?? '';

            if (empty($first_name) || empty($last_name) || empty($birthdate) || empty($course_id)) {
                throw new Exception('Missing required fields: first_name, last_name, birthdate, or course_id');
            }
            $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, gender, birthdate, contact_number, course_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, 'Other', $birthdate, $contact_number, $course_id]);
            echo json_encode(['success' => true, 'message' => 'Student added successfully']);
            break;

        case 'delete_student':
            $student_id = $_POST['student_id'] ?? '';
            if (empty($student_id)) {
                throw new Exception('Missing required field: student_id');
            }
            
            $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            if ($stmt->rowCount() == 0) {
                throw new Exception('No student found with ID: ' . $student_id);
            }
            echo json_encode(['success' => true, 'message' => 'Student and their attendance records deleted successfully']);
            break;

        case 'get_attendance':
            $course_id = $_POST['course_id'] ?? '';
            if (empty($course_id)) {
                throw new Exception('Missing required field: course_id');
            }
            $stmt = $pdo->prepare("SELECT a.attendance_id, a.student_id, s.first_name, s.last_name, c.course_name, a.attendance_date, a.status, a.notes 
                                 FROM attendance a 
                                 JOIN students s ON a.student_id = s.student_id 
                                 JOIN courses c ON a.course_id = c.course_id 
                                 WHERE a.course_id = ? 
                                 ORDER BY a.attendance_date DESC");
            $stmt->execute([$course_id]);
            $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $attendance]);
            break;

        case 'add_attendance':
            $student_id = $_POST['student_id'] ?? '';
            $course_id = $_POST['course_id'] ?? '';
            $attendance_date = $_POST['attendance_date'] ?? '';
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';

            error_log("add_attendance data: " . print_r($_POST, true));

            if (empty($student_id) || empty($course_id) || empty($attendance_date) || empty($status)) {
                throw new Exception('Missing required fields: student_id, course_id, attendance_date, or status');
            }
            
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ? AND course_id = ?");
                $stmt->execute([$student_id, $course_id]);
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception('Student ID ' . $student_id . ' does not belong to course ID ' . $course_id);
                }

                $stmt = $pdo->prepare("INSERT INTO attendance (student_id, course_id, attendance_date, status, notes) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$student_id, $course_id, $attendance_date, $status, $notes]);
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Attendance record added successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Error in add_attendance: " . $e->getMessage());
                if ($e->getCode() == '23503') {
                    echo json_encode(['success' => false, 'message' => 'Foreign key error: Invalid student ID or course ID']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
            }
            break;

        case 'update_attendance':
            $attendance_id = $_POST['attendance_id'] ?? '';
            $student_id = $_POST['student_id'] ?? '';
            $course_id = $_POST['course_id'] ?? '';
            $attendance_date = $_POST['attendance_date'] ?? '';
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (empty($attendance_id) || empty($student_id) || empty($course_id) || empty($attendance_date) || empty($status)) {
                throw new Exception('Missing required fields: attendance_id, student_id, course_id, attendance_date, or status');
            }
            
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ? AND course_id = ?");
                $stmt->execute([$student_id, $course_id]);
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception('Student ID ' . $student_id . ' does not belong to course ID ' . $course_id);
                }
                
                $stmt = $pdo->prepare("UPDATE attendance SET student_id = ?, course_id = ?, attendance_date = ?, status = ?, notes = ? WHERE attendance_id = ?");
                $stmt->execute([$student_id, $course_id, $attendance_date, $status, $notes, $attendance_id]);
                if ($stmt->rowCount() == 0) {
                    throw new Exception('No attendance record found with ID: ' . $attendance_id);
                }
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Attendance record updated successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                if ($e->getCode() == '23503') {
                    echo json_encode(['success' => false, 'message' => 'Foreign key error: Invalid student ID or course ID']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
            }
            break;

        case 'delete_attendance':
            $attendance_id = $_POST['attendance_id'] ?? '';
            if (empty($attendance_id)) {
                throw new Exception('Missing required field: attendance_id');
            }
            $stmt = $pdo->prepare("DELETE FROM attendance WHERE attendance_id = ?");
            $stmt->execute([$attendance_id]);
            if ($stmt->rowCount() == 0) {
                throw new Exception('No attendance record found with ID: ' . $attendance_id);
            }
            echo json_encode(['success' => true, 'message' => 'Attendance record deleted successfully']);
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
