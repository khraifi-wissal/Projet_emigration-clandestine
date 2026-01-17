<?php
session_start();
require_once "connexion.php";

// 1. Security Checks
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    
    $member_id = $_SESSION['member_id'];
    $file = $_FILES['avatar'];
    
    // 2. Validate File
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = "Format d'image non supporté (JPG, PNG, WEBP uniquement).";
        header("Location: profile.php");
        exit;
    }
    
    if ($file['size'] > $max_size) {
        $_SESSION['error'] = "L'image est trop volumineuse (Max 5MB).";
        header("Location: profile.php");
        exit;
    }

    // 3. Handle Upload
    // Create folder if it doesn't exist
    $upload_dir = "uploads/profiles/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique name
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "user_" . $member_id . "_" . time() . "." . $ext;
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        
        // 4. Update Database
        // Use full path relative to your site root
        $db_path = $target_path; // e.g. "uploads/profiles/user_1_12345.jpg"
        
        $stmt = $conn->prepare("UPDATE members SET profile_image = ? WHERE member_id = ?");
        if ($stmt->execute([$db_path, $member_id])) {
            $_SESSION['success'] = "Photo de profil mise à jour !";
        } else {
            $_SESSION['error'] = "Erreur base de données.";
        }
    } else {
        $_SESSION['error'] = "Erreur lors du téléchargement.";
    }
}

header("Location: profile.php");
exit;
?>