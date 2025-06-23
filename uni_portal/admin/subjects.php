<?php
// Calculate root directory path
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
// Add new subject
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $course_code = $conn->real_escape_string($_POST['course_code']);
    
    $sql = "INSERT INTO subjects (name, course_code) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $course_code);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Subject added successfully";
    } else {
        $_SESSION['error'] = "Error adding subject: " . $stmt->error;
    }
}

// Delete subject
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $sql = "DELETE FROM subjects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Subject deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting subject: " . $stmt->error;
    }
    
    header("Location: subjects.php");
    exit();
}

// Get all courses for dropdown
$courses = [];
$sql_courses = "SELECT code, name FROM courses";
$result_courses = $conn->query($sql_courses);
if ($result_courses->num_rows > 0) {
    while($row = $result_courses->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Get all subjects
$sql = "SELECT s.id, s.name, s.created_at, c.name AS course_name, c.code AS course_code
        FROM subjects s
        JOIN courses c ON s.course_code = c.code";
$result = $conn->query($sql);
$subjects = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}
?>

<?php 
$page_title = "Manage Subjects";
require_once $rootDir . '/includes/header.php'; 
?>

<div class="admin-content">
    <h2>Manage Subjects</h2>
    
    <div class="content-section">
        <h3>Add New Subject</h3>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Subject Name:</label>
                    <input type="text" id="name" name="name" required>
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
            </div>
            <button type="submit" name="add_subject">Add Subject</button>
        </form>
    </div>
    
    <div class="content-section">
        <h3>Existing Subjects</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Subject Name</th>
                    <th>Course</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subjects)): ?>
                    <tr>
                        <td colspan="4">No subjects found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?= $subject['name'] ?></td>
                            <td><?= $subject['course_name'] ?></td>
                            <td><?= $subject['created_at'] ?></td>
                            <td>
                                <a href="?delete=<?= $subject['id'] ?>" class="btn-delete" 
                                   onclick="return confirm('Are you sure? This will delete all related timetable entries.');">
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

<?php require_once $rootDir . '/includes/footer.php'; ?>