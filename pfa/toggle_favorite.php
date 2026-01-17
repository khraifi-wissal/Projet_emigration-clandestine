<?php
session_start();
require_once "connexion.php";

if (!isset($_SESSION['member_id']) || !isset($_POST['opp_id'])) {
    header("Location: opportunites.php");
    exit;
}

$member_id = (int)$_SESSION['member_id'];
$opp_id = (int)$_POST['opp_id'];

// Check if already favorited
$stmt = $conn->prepare("SELECT id FROM opportunity_favorites WHERE member_id = ? AND opp_id = ?");
$stmt->execute([$member_id, $opp_id]);

if ($stmt->rowCount() > 0) {
    // Remove
    $del = $conn->prepare("DELETE FROM opportunity_favorites WHERE member_id = ? AND opp_id = ?");
    $del->execute([$member_id, $opp_id]);
} else {
    // Add
    $ins = $conn->prepare("INSERT INTO opportunity_favorites (member_id, opp_id) VALUES (?, ?)");
    $ins->execute([$member_id, $opp_id]);
}

// Redirect back
$redirect = $_SERVER['HTTP_REFERER'] ?? 'opportunites.php';
header("Location: $redirect");
exit;
?>