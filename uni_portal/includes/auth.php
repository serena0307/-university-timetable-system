<?php
// auth.php - handle sessions properly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to access this page';
    header('Location: ../index.php');
    exit();
}
?>