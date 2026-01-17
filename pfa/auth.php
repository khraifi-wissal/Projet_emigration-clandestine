<?php
session_start();

if (!isset($_SESSION['member_id'])) {
    // Save the page the user wanted
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}
