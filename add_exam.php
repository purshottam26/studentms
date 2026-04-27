<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

if(isset($_POST['submit'])){
    $exam_name = $_POST['exam_name'];
    $course = $_POST['course'];
    $exam_date = $_POST['exam_date'];

    mysqli_query($conn, "INSERT INTO exams (exam_name, course, exam_date)
    VALUES ('$exam_name','$course','$exam_date')");

    echo "<script>alert('Exam Added Successfully');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Exam</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="main-container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="brand-icon">🎓</span> StudentMS</h2>
            <p>Admin Panel</p>
        </div>
        <div class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="index.php">📊 Dashboard</a>
            <a href="students.php">👨‍🎓 Students</a>
            <a href="students_list.php">📋 All Students</a>
            <a href="export.php">📤 Export Excel</a>
            <a href="add_exam.php" class="active">📘 Exams</a>
            <a href="add_result.php">📊 Add Result</a>
            <a href="view_result.php">📄 View Result</a>
            <a href="add_teacher.php">👩‍🏫 Teachers</a>
            <a href="library.php">📚 Library</a>
            <a href="notice_board.php">📢 Notice Board</a>
            <a href="attendance.php">✅ Attendance</a>
            <a href="fees.php">💰 Fee Management</a>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <div class="topbar">
            <h1>📘 Add Exam</h1>
            <div class="topbar-right">
                <div class="admin-badge">👤 <?php echo htmlspecialchars($_SESSION['admin']); ?></div>
            </div>
        </div>

        <div class="box">
            <div class="box-title">Create New Exam</div>

            <form method="POST" class="form-grid">
                <input type="text" name="exam_name" placeholder="Exam Name" required>
                <input type="text" name="course" placeholder="Course" required>
                <input type="date" name="exam_date" required>
                <button name="submit" class="btn btn-success">➕ Add Exam</button>
            </form>
        </div>

    </div>
</div>

</body>
</html>