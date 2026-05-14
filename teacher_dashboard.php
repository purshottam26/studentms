<?php
session_start();
if(!isset($_SESSION['teacher_id'])){
    header("Location: teacher_login.php");
    exit();
}
include 'db.php';

$tid = $_SESSION['teacher_id'];
$q = mysqli_query($conn, "SELECT * FROM teachers WHERE teacher_id='$tid'");
$teacher = mysqli_fetch_assoc($q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="main-container">

    <?php include_once('sidebar.php'); ?>

    <div class="content">

        <div class="topbar">
            <h1>📊 Teacher Dashboard</h1>

            <div class="topbar-right">
                <div class="admin-badge">
                    👩‍🏫 <?php echo htmlspecialchars($teacher['name']); ?>
                </div>
            </div>
        </div>

        <div class="dashboard">

            <div class="card">
                <div class="card-icon blue">🪪</div>
                <h3>Teacher ID</h3>
                <p style="font-size:18px;">
                    <?php echo $teacher['teacher_id']; ?>
                </p>
            </div>

            <div class="card">
                <div class="card-icon cyan">📖</div>
                <h3>Subject</h3>
                <p style="font-size:18px;">
                    <?php echo htmlspecialchars($teacher['subject']); ?>
                </p>
            </div>

            <div class="card">
                <div class="card-icon green">📧</div>
                <h3>Email</h3>
                <p style="font-size:14px;">
                    <?php echo htmlspecialchars($teacher['email']); ?>
                </p>
            </div>

        </div>

        <div class="box">

            <div class="box-title">⚡ Quick Actions</div>

            <div style="display:flex;gap:12px;flex-wrap:wrap;">

                <a href="teacher_id_card.php" class="btn btn-primary">
                    🪪 View My ID Card
                </a>

                <a href="teacher_profile.php" class="btn btn-success">
                    👤 My Profile
                </a>

                <a href="teacher_change_password.php" class="btn btn-primary">
                    🔐 Change Password
                </a>

                <a href="library.php" class="btn btn-success">
                    📚 Library Management
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>