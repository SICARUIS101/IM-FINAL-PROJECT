<?php

header('Content-Type: application/json');

require_once '../config.php'; 
require_once '../Assessment.php'; 
$response = ['success' => false, 'message' => 'Invalid request to assessment actions.'];

if (!isset($pdo)) {
    $response = ['success' => false, 'message' => 'ERROR: Database connection ($pdo) not found.'];
    echo json_encode($response);
    exit;
}
if (!class_exists('Assessment')) {
    $response = ['success' => false, 'message' => 'ERROR: Assessment class not found.'];
    echo json_encode($response);
    exit;
}

$assessmentModel = new Assessment($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;

    if ($action === 'get_assessment_details') {
        $assessmentId = filter_input(INPUT_GET, 'assessment_id', FILTER_VALIDATE_INT);
        if ($assessmentId) {
            $assessmentDetails = $assessmentModel->getById($assessmentId);
            if ($assessmentDetails) {
                $response = ['success' => true, 'assessment' => $assessmentDetails];
            } else {
                $response['message'] = 'Assessment not found or error fetching details.';
            }
        } else {
            $response['message'] = 'Assessment ID is required.';
        }
    } else {
        $response['message'] = 'Unknown GET action for assessments.';
    }
} else {

    $response['message'] = 'Only GET requests are handled by this specific API script for now.';
}

echo json_encode($response);
exit;
?>