<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';
require 'includes/auth.php';
// Get user's course
$course_code = $_SESSION['course_code'];

// Get timetable for student's course
$sql = "SELECT t.day, t.start_time, t.end_time, s.name AS subject_name, 
               r.name AS room_name, r.capacity, c.name AS course_name
        FROM timetable t
        JOIN subjects s ON t.subject_id = s.id
        JOIN courses c ON s.course_code = c.code
        JOIN rooms r ON t.room_name = r.name
        WHERE c.code = ?
        ORDER BY FIELD(t.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.start_time";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $course_code);
$stmt->execute();
$result = $stmt->get_result();
$timetable = [];

while ($row = $result->fetch_assoc()) {
    $timetable[$row['day']][] = $row;
}

// Get course name
$course_sql = "SELECT name FROM courses WHERE code = ?";
$course_stmt = $conn->prepare($course_sql);
$course_stmt->bind_param("s", $course_code);
$course_stmt->execute();
$course_result = $course_stmt->get_result();
$course = $course_result->fetch_assoc();
$course_name = $course['name'] ?? '';
?>

<?php include 'includes/header.php'; ?>

<div class="timetable-container">
    <h2><?= $course_name ?> Timetable</h2>
    
    <?php if (empty($timetable)): ?>
        <p>No classes scheduled for your course.</p>
    <?php else: ?>
        <div class="timetable">
            <?php 
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            foreach ($days as $day): 
                if (!empty($timetable[$day])): 
            ?>
                    <div class="day-column">
                        <h3><?= $day ?></h3>
                        <?php foreach ($timetable[$day] as $class): ?>
                            <div class="class-card">
                                <div class="subject"><?= $class['subject_name'] ?></div>
                                <div class="time"><?= date('h:i A', strtotime($class['start_time'])) ?> - <?= date('h:i A', strtotime($class['end_time'])) ?></div>
                                <div class="room">Room: <?= $class['room_name'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>