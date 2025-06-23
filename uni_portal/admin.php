<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';
require 'includes/auth.php'; // Add authentication check

// Redirect non-admins
if ($_SESSION['role'] !== 'admin') {
    header('Location: timetable.php');
    exit();
}

?>

<?php include 'includes/header.php'; ?>

<div class="admin-dashboard">
    <h2>Admin Dashboard</h2>
    
    <div class="admin-cards">
        <a href="admin/courses.php" class="admin-card">
            <h3>Manage Courses</h3>
            <p>Add, edit, or remove courses</p>
        </a>
        
        <a href="admin/rooms.php" class="admin-card">
            <h3>Manage Rooms</h3>
            <p>Manage room availability and capacity</p>
        </a>
        
        <a href="admin/subjects.php" class="admin-card">
            <h3>Manage Subjects</h3>
            <p>Organize subjects by course</p>
        </a>
        
        <a href="admin/timetable.php" class="admin-card">
            <h3>Manage Timetable</h3>
            <p>Schedule classes and assign rooms</p>
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

