<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

$id = (int) $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM results WHERE id=$id"));

if(isset($_POST['update'])){
    $subject = $_POST['subject'];
    $marks = (int) $_POST['marks'];
    $total = (int) $_POST['total'];

    // ✅ VALIDATION
    if($marks > $total){
        echo "<script>alert('Marks cannot be greater than Total Marks');</script>";
    } else {

        $percentage = ($total > 0) ? ($marks/$total)*100 : 0;

        // ✅ GRADE LOGIC
        if($percentage >= 90) $grade="A+";
        elseif($percentage >= 75) $grade="A";
        elseif($percentage >= 60) $grade="B";
        elseif($percentage >= 40) $grade="C";
        else $grade="F";

        mysqli_query($conn, "UPDATE results SET 
            subject='$subject',
            marks='$marks',
            total_marks='$total',
            grade='$grade'
            WHERE id=$id
        ");

        echo "<script>alert('Result Updated Successfully'); window.location='view_result.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Result</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="main-container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2>🎓 StudentMS</h2>
            <p>Admin Panel</p>
        </div>

        <div class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="index.php">📊 Dashboard</a>
            <a href="students.php">👨‍🎓 Students</a>
            <a href="students_list.php">📋 All Students</a>
            <a href="export.php">📤 Export Excel</a>

            <a href="add_exam.php">📘 Exams</a>
            <a href="add_result.php">📊 Add Result</a>
            <a href="view_result.php" class="active">📄 View Result</a>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <div class="topbar">
            <h1>✏️ Edit Result</h1>
        </div>

        <div class="box">
            <div class="box-title">Update Student Result</div>

            <form method="POST" class="form-grid">

                <input type="text" name="subject" value="<?= $data['subject'] ?>" required>

                <!-- ✅ CONTROL -->
                <input type="number" name="marks" value="<?= $data['marks'] ?>" max="100" required>
                <input type="number" name="total" value="100" readonly>

                <button name="update" class="btn btn-primary" title="Update Result">
                    💾 Update Result
                </button>

            </form>
        </div>

    </div>
</div>

</body>
</html>