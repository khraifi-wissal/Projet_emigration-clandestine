<?php
$host = 'localhost';
$dbname = 'nafas';
$user = 'root';
$pass = '';

try {
    // La variable DOIT s'appeler $pdo
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
