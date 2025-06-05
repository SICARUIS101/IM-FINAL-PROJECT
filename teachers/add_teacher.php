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
        echo json_encode($response);
        exit;
    }

    $name = isset($data['name']) ? trim($data['name']) : null;
    $phone = isset($data['phone']) ? trim($data['phone']) : null;
    $email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL) : null;
    $emergency = isset($data['emergency']) ? trim($data['emergency']) : null;

    if (empty($name) || empty($phone) || empty($email) || !$email || empty($emergency)) {
        $response = ['success' => false, 'message' => 'All fields (Name, Phone, valid Email, Emergency Contact) are required.'];
    } else {
        try {
           
            $stmt = $pdo->prepare("INSERT INTO teachers (name, phone, email, emergency_contact) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $phone, $email, $emergency]);
            
            $newTeacherId = $pdo->lastInsertId(); 

            if ($newTeacherId) {
                $response = [
                    'success' => true,
                    'message' => 'Teacher added successfully!',
                    'teacher' => [
                        'id' => $newTeacherId, 
                        'name' => $name,
                        'phone' => $phone,
                        'email' => $email,
                        
                        'emergency_contact' => $emergency 
                    ]
                ];
            } else {
                $response = ['success' => false, 'message' => 'Teacher added but could not retrieve ID.'];
            }

        } catch (PDOException $e) {
            error_log("Add teacher PDOException: " . $e->getMessage() . " Data: " . print_r($data, true));
           
            if ($e->getCode() == '23505') { 
                 $response = ['success' => false, 'message' => 'Failed to add teacher. Email might already exist.'];
            } else {
                 $response = ['success' => false, 'message' => 'Database error while adding teacher.'];
            }
        } catch (Exception $e) {
            error_log("Add teacher Exception: " . $e->getMessage() . " Data: " . print_r($data, true));
            $response = ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()];
        }
    }
} else {
    $response = ['success' => false, 'message' => 'Invalid request method. Only POST is accepted.'];
}

echo json_encode($response);
exit;
?>