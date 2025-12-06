<?php
session_start();
require 'connexion.php';

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM admins WHERE email = ?";
$req = $conn->prepare($sql);
$req->bind_param("s", $email);
$req->execute();
$result = $req->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();

    if (password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];

        header("Location: index.php");
        exit;
    }
}

header("Location: index.php?error=1");
?>
