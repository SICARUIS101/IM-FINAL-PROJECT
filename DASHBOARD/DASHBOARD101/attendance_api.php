<?php
header('Content-Type: application/json');
include 'config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Map courses to their respective table names
$course_tables = [
    'Computer System Servicing NC II' => 'computer_system_servicing_nc_ii_attendance',
    'Dressmaking NC II' => 'dressmaking_nc_ii_attendance',
    'Electronic Products Assembly Servicing NC II' => 'electronic_products_assembly_servicing_nc_ii_attendance',
    'Shielded Metal Arc Welding (SMAW) NC I' => 'shielded_metal_arc_welding_nc_i_attendance',
    'Shielded Metal Arc Welding (SMAW) NC II' => 'shielded_metal_arc_welding_nc_ii_attendance'
];

$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    if (!$pdo) {
        throw new Exception('Database connection is not established');
    }

    // Test database connection before proceeding
    $pdo->query("SELECT 1");

    switch ($action) {
        case 'get_students':
            $stmt = $pdo->query("SELECT student_id, first_name, last_name, course FROM students ORDER BY last_name");
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($students)) {
                echo json_encode(['success' => false, 'message' => 'No students found in the database']);
            } else {
                echo json_encode(['success' => true, 'data' => $students]);
            }
            break;

        case 'add_student':
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $course = $_POST['course'] ?? '';
            if (empty($first_name) || empty($last_name) || empty($course)) {
                throw new Exception('Missing required fields: first_name, last_name, or course');
            }
            if (!in_array($course, array_keys($course_tables))) {
                throw new Exception('Invalid course: ' . $course);
            }
            $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, course) VALUES (?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $course]);
            echo json_encode(['success' => true, 'message' => 'Student added successfully']);
            break;

        case 'delete_student':
            $student_id = $_POST['student_id'] ?? '';
            $delete_attendance = isset($_POST['delete_attendance']) && $_POST['delete_attendance'] === 'true';
            if (empty($student_id)) {
                throw new Exception('Missing required field: student_id');
            }
            
            // Start a transaction to ensure data integrity
            $pdo->beginTransaction();
            
            try {
                if ($delete_attendance) {
                    // Delete attendance records for this student from all course tables
                    foreach ($course_tables as $table) {
                        $stmt = $pdo->prepare("DELETE FROM $table WHERE student_id = ?");
                        $stmt->execute([$student_id]);
                    }
                }
                
                // Attempt to delete the student
                $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
                $stmt->execute([$student_id]);
                
                if ($stmt->rowCount() == 0) {
                    throw new Exception('No student found with student_id: ' . $student_id);
                }
                
                // Commit the transaction
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
            } catch (PDOException $e) {
                $pdo->rollBack();
                if ($e->getCode() == '23503') { // Foreign key violation
                    echo json_encode(['success' => false, 'message' => 'Cannot delete student because attendance records exist. Set delete_attendance=true to also delete attendance records.']);
                } else {
                    throw new Exception('Error deleting student: ' . $e->getMessage());
                }
            }
            break;

        case 'get_attendance':
            $course = $_POST['course'] ?? '';
            if (!isset($course_tables[$course])) {
                echo json_encode(['success' => false, 'message' => 'Invalid course: ' . $course]);
                break;
            }
            $table = $course_tables[$course];
            $stmt = $pdo->query("SELECT a.attendance_id, a.student_id, s.first_name, s.last_name, s.course, a.attendance_date, a.status, a.notes 
                                 FROM $table a 
                                 JOIN students s ON a.student_id = s.student_id 
                                 ORDER BY a.attendance_date DESC");
            $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $attendance]);
            break;

        case 'add_attendance':
                       $student_id = $_POST['student_id'] ?? '';
            $attendance_date = $_POST['attendance_date'] ?? '';
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $course = $_POST['course'] ?? '';
            if (empty($student_id) || empty($attendance_date) || empty($status) || empty($course)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields for adding attendance']);
                break;
            }
            if (!isset($course_tables[$course])) {
                echo json_encode(['success' => false, 'message' => 'Invalid course: ' . $course]);
                break;
            }
            $table = $course_tables[$course];
            
            // Start a transaction to ensure data consistency
            $pdo->beginTransaction();
            try {
                // Attempt to insert attendance record
                $stmt = $pdo->prepare("INSERT INTO $table (student_id, attendance_date, status, notes) VALUES (?, ?, ?, ?)");
                $stmt->execute([$student_id, $attendance_date, $status, $notes]);
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Attendance record added successfully']);
            } catch (PDOException $e) {
                $pdo->rollBack();
                if ($e->getCode() == '23503') { // Foreign key violation
                    echo json_encode(['success' => false, 'message' => 'Foreign key error: The selected student ID or course is invalid. Please check the student and course configuration.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
            }
            break;

        case 'update_attendance':
            $attendance_id = $_POST['attendance_id'] ?? '';
            $student_id = $_POST['student_id'] ?? '';
            $attendance_date = $_POST['attendance_date'] ?? '';
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $course = $_POST['course'] ?? '';
            if (empty($attendance_id) || empty($student_id) || empty($attendance_date) || empty($status) || empty($course)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields for updating attendance']);
                break;
            }
            if (!isset($course_tables[$course])) {
                echo json_encode(['success' => false, 'message' => 'Invalid course: ' . $course]);
                break;
            }
            
            // Start a transaction to ensure data consistency
            $pdo->beginTransaction();
            try {
                // Check if the student exists (without FOR UPDATE)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ?");
                $stmt->execute([$student_id]);
                if ($stmt->fetchColumn() == 0) {
                    $pdo->rollBack();
                    throw new Exception('Student ID ' . $student_id . ' does not exist');
                }
                
                $table = $course_tables[$course];
                $stmt = $pdo->prepare("UPDATE $table SET student_id = ?, attendance_date = ?, status = ?, notes = ? WHERE attendance_id = ?");
                $stmt->execute([$student_id, $attendance_date, $status, $notes, $attendance_id]);
                
                if ($stmt->rowCount() == 0) {
                    $pdo->rollBack();
                    throw new Exception('No attendance record found with attendance_id: ' . $attendance_id);
                }
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Attendance record updated successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Error in update_attendance: " . $e->getMessage());
                if ($e->getCode() == '23503') { // Foreign key violation
                    echo json_encode(['success' => false, 'message' => 'Foreign key error: The selected student ID or course is invalid. Please check the student and course configuration.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                }
            }
            break;

        case 'delete_attendance':
            $attendance_id = $_POST['attendance_id'] ?? '';
            $course = $_POST['course'] ?? '';
            if (empty($attendance_id) || empty($course)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields for deleting attendance']);
                break;
            }
            if (!isset($course_tables[$course])) {
                echo json_encode(['success' => false, 'message' => 'Invalid course: ' . $course]);
                break;
            }
            $table = $course_tables[$course];
            $stmt = $pdo->prepare("DELETE FROM $table WHERE attendance_id = ?");
            $stmt->execute([$attendance_id]);
            
            if ($stmt->rowCount() == 0) {
                throw new Exception('No attendance record found with attendance_id: ' . $attendance_id);
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
