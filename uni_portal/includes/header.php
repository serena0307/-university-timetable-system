<?php
$page_title = 'University Timetable System';
?>

        <?php
$page_title = 'University Timetable System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="<?= $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] ?>/uni_portal/assets/style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <h1>University Timetable System</h1>
            <nav>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <a href="admin.php">Admin Dashboard</a>
                    <?php endif; ?>
                    <a href="timetable.php">My Timetable</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="index.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>