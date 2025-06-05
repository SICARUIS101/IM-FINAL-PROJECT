<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if ($id === null || !is_numeric($id) || intval($id) <= 0) {
        $response = ['success' => false, 'message' => 'Invalid or missing Teacher ID.'];
    } else {
        try {
            $id = intval($id);
            $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
            if ($stmt->execute([$id])) {
                if ($stmt->rowCount() > 0) {
                    $response = ['success' => true, 'message' => 'Teacher deleted successfully.'];
                } else {
                    $response = ['success' => false, 'message' => 'Teacher not found or already deleted.'];
                }
            } else {
                 $response = ['success' => false, 'message' => 'Failed to execute delete statement.'];
            }
        } catch (PDOException $e) {
            error_log("Delete teacher PDOException: " . $e->getMessage() . " ID: " . $id);
            $response = ['success' => false, 'message' => 'Database error during deletion.'];
        } catch (Exception $e) {
            error_log("Delete teacher Exception: " . $e->getMessage() . " ID: " . $id);
            $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
} else {
    $response = ['success' => false, 'message' => 'Invalid request method.'];
}

echo json_encode($response);
exit;
?>