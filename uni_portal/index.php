<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';
?>

<?php include 'includes/header.php'; ?>

<div class="login-container">
    <h2>University Timetable System</h2>
    <form action="login.php" method="post">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>