<?php
header('Content-Type: application/json');

require_once '../config.php'; 
require_once '../Course.php';  

$response = ['success' => false, 'message' => 'Invalid request or action.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? null; 

    if (!$action && isset($_POST['action'])) { 
        $action = $_POST['action'];
    }

    $courseModel = new Course($pdo);

    switch ($action) {
        case 'add_program':
            $programName = trim($_POST['program_name'] ?? '');
            $programDescription = trim($_POST['program_description'] ?? null);

            if (!empty($programName)) {
                $newProgramId = $courseModel->create($programName, $programDescription); 
                if ($newProgramId) {
                    $response = ['success' => true, 'message' => 'Program added successfully!', 'new_id' => $newProgramId];
                } else {
                    $response['message'] = 'Failed to add program. DB error or duplicate.';
                }
            } else {
                $response['message'] = 'Program name is required.';
            }
            break;

        case 'edit_program':
            $programId = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
            $programName = trim($_POST['program_name'] ?? '');
            $programDescription = trim($_POST['program_description'] ?? null);

            if ($programId && !empty($programName)) {
                if ($courseModel->update($programId, $programName, $programDescription)) {
                    $response = ['success' => true, 'message' => 'Program updated successfully!'];
                } else {
                    $response['message'] = 'Failed to update program.';
                }
            } else {
                $response['message'] = 'Program ID and Name are required for update.';
            }
            break;

        case 'delete_program':
            $programId = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
            if ($programId) {
                if ($courseModel->delete($programId)) {
                    $response = ['success' => true, 'message' => 'Program deleted successfully!'];
                } else {
                    $response['message'] = 'Failed to delete program. It might be in use or a database error occurred. Check server logs.';
                }
            } else {
                $response['message'] = 'Program ID is required for deletion.';
            }
            break;
        
        default:
            $response['message'] = 'Unknown program action specified: ' . htmlspecialchars($action ?? 'None');
            break;
    }
} else {
    $response['message'] = 'Invalid request method. Only POST is accepted for these program actions.';
}
echo json_encode($response);
exit; 
?>