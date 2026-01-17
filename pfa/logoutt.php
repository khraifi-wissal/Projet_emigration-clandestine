<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php"); // Redirects to your login page
exit();
?>