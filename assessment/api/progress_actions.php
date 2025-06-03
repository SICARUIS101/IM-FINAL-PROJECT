<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Default: Invalid request or action not handled.']; 

require_once '../config.php';
require_once '../StudentModuleProgress.php';
require_once '../Student.php';
require_once '../Course.php';

if (!isset($pdo)) {
    $response = ['success' => false, 'message' => 'FATAL: PDO connection ($pdo) not found. Check config.php.'];
    echo json_encode($response);
    exit;
}

if (!class_exists('StudentModuleProgress')) {
    $response = ['success' => false, 'message' => "FATAL: Class 'StudentModuleProgress' not found. Check file and class definition."];
    echo json_encode($response);
    exit;
}
if (!class_exists('Student')) {
    $response = ['success' => false, 'message' => "ERROR: Class 'Student' not found after include. Check Student.php (path, filename, 'class Student {...}' declaration, no parse errors, no conflicting namespaces)."];
    echo json_encode($response);
    exit;
}
if (!class_exists('Course')) {
    $response = ['success' => false, 'message' => "ERROR: Class 'Course' not found after include. Check Course.php (path, filename, 'class Course {...}' declaration, no parse errors, no conflicting namespaces)."];
    echo json_encode($response);
    exit;
}
try {
    $studentModuleProgressModel = new StudentModuleProgress($pdo);
    $studentModel = new Student($pdo); 
    $courseModel = new Course($pdo);  
} catch (Throwable $e) { 
    error_log("Error instantiating models in progress_actions.php: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    $response = ['success' => false, 'message' => 'Error during model instantiation: ' . $e->getMessage()];
    echo json_encode($response);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;

    if ($action === 'get_students_for_module_status') {
        $programId = filter_input(INPUT_GET, 'program_id', FILTER_VALIDATE_INT);
        $moduleId = filter_input(INPUT_GET, 'module_id', FILTER_VALIDATE_INT);

        if ($programId && $moduleId) {
            $studentsWithProgress = $studentModuleProgressModel->getStudentsForModuleStatus($programId, $moduleId);
            if ($studentsWithProgress !== false) {
                $response = ['success' => true, 'students' => $studentsWithProgress];
            } else {
                $response = ['success' => false, 'message' => 'Error fetching student progress from the database.'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Program ID and Module ID are required.'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Unknown GET action for progress: ' . htmlspecialchars($action ?? 'None')];
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? $_POST['form_action'] ?? null;

    if ($action === 'set_progress') {
        $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
        $moduleId = filter_input(INPUT_POST, 'module_id', FILTER_VALIDATE_INT);
        $isCompletedInput = $_POST['is_completed'] ?? null;
        $isCompleted = filter_var($isCompletedInput, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $completionDate = null; 

        if ($studentId && $moduleId && $isCompleted !== null) {
            if ($studentModuleProgressModel->setProgress($studentId, $moduleId, $isCompleted, $completionDate)) {
                $response = ['success' => true, 'message' => 'Student module progress updated.'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update student module progress in database.'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Student ID, Module ID, and completion status are required.'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Unknown POST action for progress: ' . htmlspecialchars($action ?? 'None')];
    }
}

echo json_encode($response);
exit;
?>