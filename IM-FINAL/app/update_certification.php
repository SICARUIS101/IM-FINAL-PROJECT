<?php
session_start();
require_once 'Database.php';

$db = new Database();

try {
    // Check if this is a status update (Approve/Reject)
    if (isset($_POST['certification_id']) && isset($_POST['status'])) {
        $certificationId = $_POST['certification_id'];
        $status = $_POST['status'];
        
        // Validate status
        if (!in_array($status, ['Issued', 'Rejected'])) {
            $_SESSION['error'] = "Invalid status provided.";
            header("Location: certification.php");
            exit;
        }

        // Update status
        $result = $db->updateCertificationStatus($certificationId, $status);
        
        if ($result) {
            $_SESSION['success'] = "Certification status updated to $status successfully.";
        } else {
            $_SESSION['error'] = "Failed to update certification status.";
        }
    } 
    // Check if this is an edit form submission
    elseif (isset($_POST['certification_id']) && isset($_POST['student_id']) && isset($_POST['course_id']) && 
            isset($_POST['certificate_number']) && isset($_POST['certification_date'])) {
        $certificationId = $_POST['certification_id'];
        $studentId = $_POST['student_id'];
        $courseId = $_POST['course_id'];
        $certificateNumber = $_POST['certificate_number'];
        $certificationDate = $_POST['certification_date'];

        // Update certification details
        $result = $db->updateCertification($certificationId, $studentId, $courseId, $certificateNumber, $certificationDate);
        
        if ($result) {
            $_SESSION['success'] = "Certification updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update certification.";
        }
    } else {
        $_SESSION['error'] = "Invalid form submission.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

// Redirect back to certification page
header("Location: certification.php");
exit;
?>