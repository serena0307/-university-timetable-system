<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';
// Only process if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, email, password, role, course_code FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['course_code'] = $user['course_code'];
            
            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: timetable.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid password";
        }
    } else {
        $_SESSION['error'] = "User not found";
    }
    
    header("Location: index.php");
    exit();
}

// If not POST request, redirect to login page
header("Location: index.php");
exit();
?>