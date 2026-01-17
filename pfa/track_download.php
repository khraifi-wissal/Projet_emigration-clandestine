<?php
/* c:\xampp\htdocs\Projet_emigration-clandestine\track_download.php */
session_start();

// Database connection
$conn = mysqli_connect("127.0.0.1", "root", "", "nafas");
if (!$conn) {
    die("Erreur de connexion");
}

if (isset($_GET['id'])) {
    $brochure_id = (int)$_GET['id'];

    // 1. Track the download if user is logged in
    if (isset($_SESSION['member_id'])) {
        $member_id = (int)$_SESSION['member_id'];
        
        $stmt = mysqli_prepare($conn, "INSERT INTO brochure_downloads (member_id, brochure_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ii", $member_id, $brochure_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // 2. Get the file path to redirect
    $stmt_file = mysqli_prepare($conn, "SELECT file_path FROM brochures WHERE brochure_id = ?");
    mysqli_stmt_bind_param($stmt_file, "i", $brochure_id);
    mysqli_stmt_execute($stmt_file);
    mysqli_stmt_bind_result($stmt_file, $file_path);
    
    if (mysqli_stmt_fetch($stmt_file)) {
        mysqli_stmt_close($stmt_file);

        if (file_exists($file_path)) {
            // Force download headers
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        }
    }
}

// Fallback if something goes wrong
header("Location: media.php");
exit;
?>
