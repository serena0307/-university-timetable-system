<?php
//session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "timetable_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create tables individually
$table_queries = [
    "CREATE TABLE IF NOT EXISTS courses (
        code VARCHAR(20) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
    
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
        course_code VARCHAR(20) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_code) REFERENCES courses(code) ON UPDATE CASCADE
    ) ENGINE=InnoDB",
    
    "CREATE TABLE IF NOT EXISTS subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        course_code VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_code) REFERENCES courses(code) ON UPDATE CASCADE ON DELETE CASCADE
    ) ENGINE=InnoDB",
    
    "CREATE TABLE IF NOT EXISTS rooms (
        name VARCHAR(50) PRIMARY KEY,
        capacity INT NOT NULL,
        availability ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
    
    "CREATE TABLE IF NOT EXISTS timetable (
        subject_id INT NOT NULL,
        room_name VARCHAR(50) NOT NULL,
        day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (room_name, day, start_time),
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
        FOREIGN KEY (room_name) REFERENCES rooms(name) ON DELETE CASCADE
    ) ENGINE=InnoDB"
];

foreach ($table_queries as $query) {
    if ($conn->query($query) !== TRUE) {
        error_log("Error creating table: " . $conn->error);
    }
}
?>