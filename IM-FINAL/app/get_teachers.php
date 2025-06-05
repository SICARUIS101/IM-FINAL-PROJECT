<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Failed to fetch teachers.'];

try {
    $stmt = $pdo->query("SELECT id, name, phone, email, emergency_contact FROM teachers ORDER BY name ASC");
    $raw_teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $teachers_for_json = [];

    if ($raw_teachers !== false) {
        foreach ($raw_teachers as $teacher_row) {
            $teachers_for_json[] = [
                'id' => $teacher_row['id'],
                'name' => $teacher_row['name'],
                'phone' => $teacher_row['phone'],
                'email' => $teacher_row['email'],
                'emergency_contact' => $teacher_row['emergency_contact']
            ];
        }
        $response = ['success' => true, 'teachers' => $teachers_for_json];
    } else {
         $response['message'] = 'Could not fetch teacher records.';
    }

} catch (PDOException $e) {
    error_log("Get teachers PDOException: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Database error while fetching teachers.'];
} catch (Exception $e) {
    error_log("Get teachers Exception: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

echo json_encode($response);
exit;
?>