
<?php
$host = 'localhost';
$dbname = 'ele_tech';
$username = 'postgres'; // Replace with your PostgreSQL username
$password = '1234'; // Replace with your PostgreSQL password

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
