

<?php
// Get the absolute path to the root directory
$rootDir = realpath(__DIR__ . '/..');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once $rootDir . '/config.php';
require_once $rootDir . '/includes/auth.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ' . $rootDir . '/timetable.php');
    exit();
}

// Add new course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_course'])) {
    $code = $conn->real_escape_string($_POST['code']);
    $name = $conn->real_escape_string($_POST['name']);
    
    $sql = "INSERT INTO courses (code, name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $code, $name);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Course added successfully";
    } else {
        $_SESSION['error'] = "Error adding course: " . $stmt->error;
    }
    $stmt->close();
}

// Delete course
if (isset($_GET['delete'])) {
    $code = $conn->real_escape_string($_GET['delete']);
    
    $sql = "DELETE FROM courses WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Course deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting course: " . $stmt->error;
    }
    $stmt->close();
    header("Location: courses.php");
    exit();
}

// Get all courses
$sql = "SELECT * FROM courses";
$result = $conn->query($sql);
$courses = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
$result->free();
?>

<?php 
$page_title = "Manage Courses";
require_once $rootDir . '/includes/header.php'; 
?>

<div class="admin-content">
    <h2>Manage Courses</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="content-section">
        <h3>Add New Course</h3>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="code">Course Code:</label>
                    <input type="text" id="code" name="code" required>
                </div>
                <div class="form-group">
                    <label for="name">Course Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
            </div>
            <button type="submit" name="add_course">Add Course</button>
        </form>
    </div>
    
    <div class="content-section">
        <h3>Existing Courses</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="4">No courses found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?= $course['code'] ?></td>
                            <td><?= $course['name'] ?></td>
                            <td><?= $course['created_at'] ?></td>
                            <td class="actions">
                                <a href="?delete=<?= $course['code'] ?>" class="btn-delete" 
                                   onclick="return confirm('Are you sure? This will delete all related data.');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
require_once $rootDir . '/includes/footer.php'; 
?>