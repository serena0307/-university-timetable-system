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
// Add new timetable entry
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_entry'])) {
    $subject_id = intval($_POST['subject_id']);
    $room_name = $conn->real_escape_string($_POST['room_name']);
    $day = $conn->real_escape_string($_POST['day']);
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $end_time = $conn->real_escape_string($_POST['end_time']);
    
    // Check for time conflict
    $conflict_sql = "SELECT 1 FROM timetable 
                    WHERE room_name = ? 
                    AND day = ? 
                    AND NOT (end_time <= ? OR start_time >= ?)";
    $stmt = $conn->prepare($conflict_sql);
    $stmt->bind_param("ssss", $room_name, $day, $start_time, $end_time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "There is a time conflict for the selected room and day.";
    } else {
        $sql = "INSERT INTO timetable (subject_id, room_name, day, start_time, end_time) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $subject_id, $room_name, $day, $start_time, $end_time);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Timetable entry added successfully";
        } else {
            $_SESSION['error'] = "Error adding timetable entry: " . $stmt->error;
        }
    }
}

// Delete timetable entry
if (isset($_GET['delete_room']) && isset($_GET['delete_day']) && isset($_GET['delete_time'])) {
    $room_name = $conn->real_escape_string($_GET['delete_room']);
    $day = $conn->real_escape_string($_GET['delete_day']);
    $start_time = $conn->real_escape_string($_GET['delete_time']);
    
    $sql = "DELETE FROM timetable 
            WHERE room_name = ? 
            AND day = ? 
            AND start_time = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $room_name, $day, $start_time);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Timetable entry deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting timetable entry: " . $stmt->error;
    }
    
    header("Location: timetable.php");
    exit();
}

// Get all subjects for dropdown
$subjects = [];
$sql_subjects = "SELECT id, name FROM subjects";
$result_subjects = $conn->query($sql_subjects);
if ($result_subjects->num_rows > 0) {
    while($row = $result_subjects->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// Get all available rooms
$rooms = [];
$sql_rooms = "SELECT name FROM rooms WHERE availability = 'available'";
$result_rooms = $conn->query($sql_rooms);
if ($result_rooms->num_rows > 0) {
    while($row = $result_rooms->fetch_assoc()) {
        $rooms[] = $row;
    }
}

// Get all timetable entries
$sql = "SELECT t.*, s.name AS subject_name, r.capacity
        FROM timetable t
        JOIN subjects s ON t.subject_id = s.id
        JOIN rooms r ON t.room_name = r.name
        ORDER BY FIELD(t.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.start_time";
$result = $conn->query($sql);
$timetable = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $timetable[] = $row;
    }
}
?>

<?php 
$page_title = "Manage Timetables";
require_once $rootDir . '/includes/header.php';  
?>

<div class="admin-content">
    <h2>Manage Timetable</h2>
    
    <div class="content-section">
        <h3>Add New Timetable Entry</h3>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="subject_id">Subject:</label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">Select a subject</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= $subject['id'] ?>"><?= $subject['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="room_name">Room:</label>
                    <select id="room_name" name="room_name" required>
                        <option value="">Select a room</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?= $room['name'] ?>"><?= $room['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="day">Day:</label>
                    <select id="day" name="day" required>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_time">Start Time:</label>
                    <input type="time" id="start_time" name="start_time" required>
                </div>
                <div class="form-group">
                    <label for="end_time">End Time:</label>
                    <input type="time" id="end_time" name="end_time" required>
                </div>
            </div>
            
            <button type="submit" name="add_entry">Add Entry</button>
        </form>
    </div>
    
    <div class="content-section">
        <h3>Existing Timetable Entries</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Room</th>
                    <th>Day</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Capacity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($timetable)): ?>
                    <tr>
                        <td colspan="7">No timetable entries found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($timetable as $entry): ?>
                        <tr>
                            <td><?= $entry['subject_name'] ?></td>
                            <td><?= $entry['room_name'] ?></td>
                            <td><?= $entry['day'] ?></td>
                            <td><?= date('h:i A', strtotime($entry['start_time'])) ?></td>
                            <td><?= date('h:i A', strtotime($entry['end_time'])) ?></td>
                            <td><?= $entry['capacity'] ?></td>
                            <td>
                                <a href="?delete_room=<?= $entry['room_name'] ?>&delete_day=<?= $entry['day'] ?>&delete_time=<?= $entry['start_time'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this entry?');">
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