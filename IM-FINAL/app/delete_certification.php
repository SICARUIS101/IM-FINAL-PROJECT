<?php
session_start();
require_once 'Database.php';

// Database connection (update with your credentials)
$db = new Database('localhost', 'EletechTrack', 'postgres', 'almartinez');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $certification_id = $_POST['certification_id'] ?? null;

    if (!$certification_id) {
        $_SESSION['error'] = "Invalid certification ID.";
        header("Location: certification.php");
        exit;
    }

    try {
        $deleted = $db->deleteCertification($certification_id);
        if ($deleted) {
            $_SESSION['success'] = "Certification deleted successfully.";
        } else {
            $_SESSION['error'] = "Certification not found.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting certification: " . $e->getMessage();
    }
    header("Location: certification.php");
    exit;
}
?>