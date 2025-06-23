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

// Add new room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $capacity = intval($_POST['capacity']);
    $availability = $conn->real_escape_string($_POST['availability']);
    
    $sql = "INSERT INTO rooms (name, capacity, availability) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $name, $capacity, $availability);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Room added successfully";
    } else {
        $_SESSION['error'] = "Error adding room: " . $stmt->error;
    }
}

// Update room
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_room'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $capacity = intval($_POST['capacity']);
    $availability = $conn->real_escape_string($_POST['availability']);
    
    $sql = "UPDATE rooms SET capacity=?, availability=? WHERE name=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $capacity, $availability, $name);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Room updated successfully";
    } else {
        $_SESSION['error'] = "Error updating room: " . $stmt->error;
    }
}

// Delete room
if (isset($_GET['delete'])) {
    $name = $conn->real_escape_string($_GET['delete']);
    
    $sql = "DELETE FROM rooms WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Room deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting room: " . $stmt->error;
    }
    
    header("Location: rooms.php");
    exit();
}

// Get all rooms
$sql = "SELECT * FROM rooms";
$result = $conn->query($sql);
$rooms = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}
?>

<?php 
$page_title = "Manage Rooms";
require_once $rootDir . '/includes/header.php'; 
?>

<div class="admin-content">
    <h2>Manage Rooms</h2>
    
    <div class="content-section">
        <h3>Add New Room</h3>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Room Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="capacity">Capacity:</label>
                    <input type="number" id="capacity" name="capacity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="availability">Availability:</label>
                    <select id="availability" name="availability" required>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="add_room">Add Room</button>
        </form>
    </div>
    
    <div class="content-section">
        <h3>Existing Rooms</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Room Name</th>
                    <th>Capacity</th>
                    <th>Availability</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rooms)): ?>
                    <tr>
                        <td colspan="5">No rooms found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?= $room['name'] ?></td>
                            <td><?= $room['capacity'] ?></td>
                            <td><?= $room['availability'] ?></td>
                            <td><?= $room['created_at'] ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-edit" onclick="openEditModal(
                                        '<?= $room['name'] ?>', 
                                        <?= $room['capacity'] ?>, 
                                        '<?= $room['availability'] ?>'
                                    )">Edit</button>
                                    
                                    <a href="?delete=<?= $room['name'] ?>" class="btn-delete" 
                                       onclick="return confirm('Are you sure? This will delete all related timetable entries.');">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Edit Room</h3>
        <form id="editForm" method="post">
            <input type="hidden" name="name" id="edit_name">
            <div class="form-group">
                <label for="edit_capacity">Capacity:</label>
                <input type="number" id="edit_capacity" name="capacity" min="1" required>
            </div>
            <div class="form-group">
                <label for="edit_availability">Availability:</label>
                <select id="edit_availability" name="availability" required>
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>
            <button type="submit" name="update_room">Update Room</button>
        </form>
    </div>
</div>  

<script>
// Modal functions
const modal = document.getElementById("editModal");
const span = document.querySelector(".close");

function openEditModal(name, capacity, availability) {
    document.getElementById("edit_name").value = name;
    document.getElementById("edit_capacity").value = capacity;
    document.getElementById("edit_availability").value = availability;
    modal.style.display = "block";
}

span.onclick = function() {
    modal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<?php require_once $rootDir . '/includes/footer.php'; ?>