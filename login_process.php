<?php
session_start();
require "connexion.php";

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header("Location: admin_login.php?error=1");
    exit;
}

$stmt = $conn->prepare("SELECT admin_id, username, password FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();

    if (password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: index.php");
        exit;
    }
}

header("Location: admin_login.php?error=1");
exit;
