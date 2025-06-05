<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        $response = ['success' => false, 'message' => 'Invalid input: No JSON data received.'];
    } else {
        $id_from_js = $data['id'] ?? null;
        $name = $data['name'] ?? '';
        $phone = $data['phone'] ?? '';
        $email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL) : null;
        $emergency = $data['emergency'] ?? ''; 

        try {
            if ($id_from_js === null || !is_numeric($id_from_js) || intval($id_from_js) <= 0) {
                throw new Exception("Invalid or missing Teacher ID provided: " . print_r($id_from_js, true));
            }
            $id = intval($id_from_js);

            if (empty($name) || empty($phone) || empty($email) || !$email || empty($emergency) ) {
                 throw new Exception('All fields (Name, Phone, valid Email, Emergency Contact) are required.');
            }
    
            $stmt = $pdo->prepare("UPDATE teachers SET name = ?, phone = ?, email = ?, emergency_contact = ? WHERE id = ?");
            
            if ($stmt->execute([$name, $phone, $email, $emergency, $id])) {
                if ($stmt->rowCount() > 0) {
                    $updatedTeacherData = [
                        'id' => $id, 
                        'name' => $name, 
                        'phone' => $phone, 
                        'email' => $email, 
                        'emergency_contact' => $emergency 
                    ];
                    $response = ['success' => true, 'message' => 'Teacher updated successfully.', 'teacher' => $updatedTeacherData];
                } else {
                     $response = ['success' => false, 'message' => 'Teacher not found or no changes made.'];
                }
            } else {
                throw new Exception("Failed to execute update statement.");
            }
        
        } catch (PDOException $e) { 
            error_log("PDOException in edit_teacher.php: " . $e->getMessage() . " Data: " . print_r($data, true));
            if ($e->getCode() == '23505') { 
                 $response = ['success' => false, 'message' => 'Update failed. Email might already exist for another teacher.'];
            } else {
                 $response = ['success' => false, 'message' => "Database error during update."];
            }
        } catch (Exception $e) { 
            error_log("Exception in edit_teacher.php: " . $e->getMessage() . " Data: " . print_r($data, true));
            $response = ['success' => false, 'message' => $e->getMessage()];
        }
    }
} else {
    $response = ['success' => false, 'message' => 'Invalid request method.'];
}

echo json_encode($response);
exit;
?>