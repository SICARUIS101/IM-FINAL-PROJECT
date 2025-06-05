
<?php
$host = 'localhost';
$dbname = 'EletechTrack';
$username = 'postgres'; // Replace with your PostgreSQL username
$password = 'almartinez'; // Replace with your PostgreSQL password

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
