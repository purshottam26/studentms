<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$query = mysqli_query($conn, "
SELECT results.*, student.name, exams.exam_name
FROM results
JOIN student ON results.student_id = student.student_id
JOIN exams ON results.exam_id = exams.id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Results</title>
<link rel="stylesheet" href="style.css">
<style>
.tooltip { position: relative; }
.tooltip:hover::after {
    content: attr(data-title);
    position: absolute;
    bottom: 120%; left: 50%;
    transform: translateX(-50%);
    background: #333; color: #fff;
    padding: 5px 8px; border-radius: 5px;
    font-size: 12px; white-space: nowrap;
}
</style>
</head>
<body>

<div class="main-container">
<!-- SIDEBAR -->
   <?php include_once('sidebar.php'); ?>

    <!-- CONTENT -->
    <div class="content">

        <div class="topbar">
            <h1>📄 Student Results</h1>
            <div class="topbar-right">
                <div class="admin-badge">👤 <?php echo htmlspecialchars($_SESSION['admin']); ?></div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Exam</th>
                    <th>Subject</th>
                    <th>Total</th>
                    <th>Marks</th>
                    <th>Grade</th>
                    <th>Actions</th>
                </tr>
                <?php $i=1; while($row = mysqli_fetch_assoc($query)){ ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['exam_name']) ?></td>
                    <td><?= htmlspecialchars($row['subject']) ?></td>
                    <td><?= $row['total_marks'] ?></td>
                    <td><?= $row['marks'] ?></td>
                    <td><span class="badge"><?= $row['grade'] ?></span></td>
                    <td>
                        <a href="edit_result.php?id=<?= $row['id'] ?>" class="btn btn-primary tooltip" data-title="Edit">✏️</a>
                        <a href="delete_result.php?id=<?= $row['id'] ?>" class="btn btn-danger tooltip" data-title="Delete" onclick="return confirm('Delete this result?')">🗑</a>
                        <a href="report_card.php?student_id=<?= $row['student_id'] ?>" class="btn btn-success tooltip" data-title="Report Card">📄</a>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>

    </div>
</div>

</body>
</html>