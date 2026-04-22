<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$students = mysqli_query($conn, "SELECT * FROM student");
$exams = mysqli_query($conn, "SELECT * FROM exams");

$success = '';
$error = '';

if(isset($_POST['save_result'])){
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $exam_id = mysqli_real_escape_string($conn, $_POST['exam_id']);
    $subject = mysqli_real_escape_string($conn, trim($_POST['subject']));
    $marks = (int) $_POST['marks'];
    $total = 100;

    if(empty($student_id) || empty($exam_id) || empty($subject)){
        $error = "❌ Sab fields fill karo!";
    } elseif($marks > 100 || $marks < 0){
        $error = "❌ Marks 0 se 100 ke beech hone chahiye!";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM results
        WHERE student_id='$student_id'
        AND exam_id='$exam_id'
        AND subject='$subject'");

        if(mysqli_num_rows($check) > 0){
            $error = "❌ Is student ka is subject mein result already exists!";
        } else {
            if($marks >= 90) $grade="A+";
            elseif($marks >= 75) $grade="A";
            elseif($marks >= 60) $grade="B";
            elseif($marks >= 40) $grade="C";
            else $grade="F";

            $insert = mysqli_query($conn, "INSERT INTO results
            (student_id, exam_id, subject, marks, total_marks, grade)
            VALUES ('$student_id','$exam_id','$subject','$marks','$total','$grade')");

            if($insert){
                $success = "✅ Result successfully add ho gaya!";
                // Reset queries
                $students = mysqli_query($conn, "SELECT * FROM student");
                $exams = mysqli_query($conn, "SELECT * FROM exams");
            } else {
                $error = "❌ Database error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Result</title>
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
            <a href="add_exam.php">📘 Exams</a>
            <a href="add_result.php" class="active">📊 Add Result</a>
            <a href="view_result.php">📄 View Result</a>
            <a href="add_teacher.php">👩‍🏫 Teachers</a>
            <a href="library.php">📚 Library</a>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <div class="topbar">
            <h1>📊 Add Result</h1>
            <div class="topbar-right">
                <div class="admin-badge">👤 <?php echo htmlspecialchars($_SESSION['admin']); ?></div>
            </div>
        </div>

        <?php if($success): ?>
        <div style="background:#d1fae5;border-left:4px solid #10b981;padding:14px 18px;border-radius:10px;margin-bottom:18px;font-weight:700;color:#065f46;">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>

        <?php if($error): ?>
        <div style="background:#fee2e2;border-left:4px solid #ef4444;padding:14px 18px;border-radius:10px;margin-bottom:18px;font-weight:700;color:#991b1b;">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="box">
            <div class="box-title">Enter Student Result</div>

            <form method="POST" action="add_result.php" class="form-grid">

                <select name="student_id" required>
                    <option value="">-- Select Student --</option>
                    <?php while($s = mysqli_fetch_assoc($students)){ ?>
                    <option value="<?= htmlspecialchars($s['student_id']) ?>"
                        <?= (isset($_POST['student_id']) && $_POST['student_id'] == $s['student_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['student_id']) ?>)
                    </option>
                    <?php } ?>
                </select>

                <select name="exam_id" required>
                    <option value="">-- Select Exam --</option>
                    <?php while($e = mysqli_fetch_assoc($exams)){ ?>
                    <option value="<?= $e['id'] ?>"
                        <?= (isset($_POST['exam_id']) && $_POST['exam_id'] == $e['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['exam_name']) ?>
                    </option>
                    <?php } ?>
                </select>

                <input type="text" name="subject" placeholder="Subject Name likhो" required
                    value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>">

                <input type="number" value="100" readonly style="background:#f1f5f9;cursor:not-allowed;color:#64748b;">

                <input type="number" name="marks" placeholder="Marks daalo (0-100)" max="100" min="0" required
                    value="<?= isset($_POST['marks']) ? htmlspecialchars($_POST['marks']) : '' ?>">

                <button name="save_result" type="submit" class="btn btn-primary" style="padding:12px;font-size:15px;font-weight:700;">
                    💾 Save Result
                </button>

            </form>
        </div>

        <!-- VIEW RESULTS LINK -->
        <div class="box">
            <div class="box-title">⚡ Quick Links</div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <a href="view_result.php" style="padding:10px 20px;background:#4f46e5;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">📄 View All Results</a>
                <a href="add_exam.php" style="padding:10px 20px;background:#10b981;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">📘 Add Exam</a>
            </div>
        </div>

    </div>
</div>

</body>
</html>