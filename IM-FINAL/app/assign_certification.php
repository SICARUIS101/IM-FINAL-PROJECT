<?php
session_start();
require_once 'Database.php';

// Database connection (update with your credentials)
$db = new Database('localhost', 'EletechTrack', 'postgres', 'almartinez');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? null;
    $course_id = $_POST['course_id'] ?? null;
    $certificate_number = $_POST['certificate_number'] ?? null;
    $certification_date = $_POST['certification_date'] ?? null;

    // Basic server-side validation
    if (!$student_id || !$course_id || !$certificate_number || !$certification_date) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: certification.php");
        exit;
    }

    try {
        $db->assignCertification($student_id, $course_id, $certificate_number, $certification_date);
        $_SESSION['success'] = "Certification assigned successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error assigning certification: " . $e->getMessage();
    }
    header("Location: certification.php");
    exit;
}
?>