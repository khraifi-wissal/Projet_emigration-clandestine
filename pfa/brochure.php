<?php
session_start();
require_once "connexion.php";

// Check if a member is logged in
$isUser = isset($_SESSION['member_id']);

// Fetch all brochures
$query = $conn->prepare("SELECT * FROM brochures ORDER BY brochure_id DESC");
$query->execute();
$brochures = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="style2.css">
<title>Nos Brochures</title>

<style>
/* --- PREVIEW LIMITED TO 30% --- */
.pdf-preview {
    position: relative;
    overflow: hidden;
    height: 250px; /* simulate 30% view */
    border: 1px solid #ccc;
}

.pdf-preview iframe {
    width: 100%;
    height: 100%;
}

.overlay {
    position: absolute;
    bottom: 0;
    width: 100%;
    padding: 20px;
    text-align: center;
    background: rgba(0,0,0,0.6);
    color: white;
    font-weight: bold;
}

/* --- FULL VIEW FOR LOGGED USERS --- */
.pdf-full {
    width: 100%;
    height: 600px;
    border: none;
}

.btn {
    display: inline-block;
    padding: 10px;
    margin-top: 10px;
    background: #1C79B4;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
}

.btn.disabled {
    backgrou
