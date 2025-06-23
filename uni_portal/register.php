<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';
// Only process if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'student';
    $course_code = isset($_POST['course_code']) ? $conn->real_escape_string($_POST['course_code']) : null;

    // Check if email exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['error'] = "Email already registered";
        $check_stmt->close();
        header("Location: register.php");
        exit();
    }
    $check_stmt->close();

    // Insert new user
    $insert_sql = "INSERT INTO users (email, password, role, course_code) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssss", $email, $password, $role, $course_code);

    if ($insert_stmt->execute()) {
        $_SESSION['success'] = "Registration successful. Please login.";
        $insert_stmt->close();
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $insert_stmt->error;
        $insert_stmt->close();
    }
}

// Get courses for dropdown
$courses = [];
$course_sql = "SELECT code, name FROM courses";
$course_result = $conn->query($course_sql);

if ($course_result) {
    while ($row = $course_result->fetch_assoc()) {
        $courses[] = $row;
    }
    $course_result->free();
} else {
    $_SESSION['error'] = "Error loading courses: " . $conn->error;
}
?>

<?php include 'includes/header.php'; ?>

<div class="register-container">
    <h2>Create Account</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="course_code">Course:</label>
            <select id="course_code" name="course_code" required>
                <option value="">Select a course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['code'] ?>"><?= $course['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Register</button>
        <p>Already have an account? <a href="index.php">Login here</a></p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>