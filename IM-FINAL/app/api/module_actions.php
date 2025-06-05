<?php
header('Content-Type: application/json'); 

require_once '../config.php';   
require_once '../Module.php';  

$response = ['success' => false, 'message' => 'Invalid request to module actions.'];

if (!isset($pdo)) {
    $response = ['success' => false, 'message' => 'Database connection failed (pdo object not found). Check config.php.'];
    echo json_encode($response);
    exit;
}
if (!class_exists('Module')) {
    $response = ['success' => false, 'message' => 'Module class not found. Check Module.php include path.'];
    echo json_encode($response);
    exit;
}

$moduleModel = new Module($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? $_POST['action'] ?? null;

    switch ($action) {
        case 'add_module':
            $programId = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT);
            $moduleName = trim($_POST['module_name'] ?? '');
            $moduleDescription = trim($_POST['module_description'] ?? null);

            if ($programId && !empty($moduleName)) {
                $newModuleId = $moduleModel->create($programId, $moduleName, $moduleDescription);
                if ($newModuleId) {
                    $response = ['success' => true, 'message' => 'Module added successfully!', 'new_module_id' => $newModuleId];
                } else {
                    $response['message'] = 'Failed to add module to the database. It might already exist for this program or a DB error occurred.';
                }
            } else {
                $response['message'] = 'Program ID and Module Name are required.';
            }
            break;

        case 'edit_module':
            $moduleId = filter_input(INPUT_POST, 'module_id', FILTER_VALIDATE_INT);
            $programId = filter_input(INPUT_POST, 'program_id', FILTER_VALIDATE_INT); // program_id might also be sent for context or if it can change
            $moduleName = trim($_POST['module_name'] ?? '');
            $moduleDescription = trim($_POST['module_description'] ?? null);

            if ($moduleId && $programId && !empty($moduleName)) {
                if ($moduleModel->update($moduleId, $programId, $moduleName, $moduleDescription)) {
                    $response = ['success' => true, 'message' => 'Module updated successfully!'];
                } else {
                    $response['message'] = 'Failed to update module.';
                }
            } else {
                $response['message'] = 'Module ID, Program ID, and Module Name are required for update.';
            }
            break;

        case 'delete_module':
            $moduleId = filter_input(INPUT_POST, 'module_id', FILTER_VALIDATE_INT);
            if ($moduleId) {
                if ($moduleModel->delete($moduleId)) {
                    $response = ['success' => true, 'message' => 'Module deleted successfully!'];
                } else {
                    $response['message'] = 'Failed to delete module. It might be in use or a database error occurred.';
                }
            } else {
                $response['message'] = 'Module ID is required for deletion.';
            }
            break;

        default:
            $response['message'] = 'Unknown POST action for modules: ' . htmlspecialchars($action ?? 'None');
            break;
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;

    if ($action === 'get_modules_by_program') {
        $programId = filter_input(INPUT_GET, 'program_id', FILTER_VALIDATE_INT);
        if ($programId) {
            $modules = $moduleModel->getByCourseId($programId);
            if ($modules !== false) {
                $response = ['success' => true, 'modules' => $modules];
            } else {
                $response['message'] = 'Error fetching modules from database.';
            }
        } else {
            $response['message'] = 'Program ID is required to fetch modules.';
        }
    } else {
        $response['message'] = 'Unknown GET action for modules.';
    }
}

echo json_encode($response);
exit;
?>