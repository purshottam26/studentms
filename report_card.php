<?php
session_start();
include 'db.php';

$student_id = $_GET['student_id'];

// student
$student = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT * FROM student WHERE student_id='$student_id'
"));

// results
$results = mysqli_query($conn, "
SELECT results.*, exams.exam_name 
FROM results 
JOIN exams ON results.exam_id = exams.id
WHERE results.student_id='$student_id'
");

$total_marks = 0;
$total_subjects = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report Card</title>

<link rel="stylesheet" href="style.css?v=2">

<!-- PDF LIB -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

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
            <h1>📄 Student Report Card</h1>
        </div>

        <!-- PDF AREA -->
        <div id="reportPDF">

            <div class="report-container">
                <div class="report-box">

                    <h2 class="report-title">🎓 Student Report Card</h2>

                    <p><b>Name:</b> <?= $student['name'] ?></p>
                    <p><b>Course:</b> <?= $student['course'] ?></p>

                    <table class="report-table">
                        <tr>
                            <th>Exam</th>
                            <th>Subject</th>
                            <th>Marks</th>
                        </tr>

                        <?php while($row = mysqli_fetch_assoc($results)){ 
                            $total_marks += $row['marks'];
                            $total_subjects++;
                        ?>
                        <tr>
                            <td><?= $row['exam_name'] ?></td>
                            <td><?= $row['subject'] ?></td>
                            <td><?= $row['marks'] ?></td>
                        </tr>
                        <?php } ?>
                    </table>

                    <?php 
                    $percentage = ($total_subjects > 0) ? $total_marks / $total_subjects : 0;

                    if($percentage >= 90) $grade="A+";
                    elseif($percentage >= 75) $grade="A";
                    elseif($percentage >= 60) $grade="B";
                    elseif($percentage >= 40) $grade="C";
                    else $grade="F";
                    ?>

                    <div class="summary">
                        <p>Total: <b><?= $total_marks ?></b></p>
                        <p>Percentage: <b><?= round($percentage,2) ?>%</b></p>
                        <p>Grade: <b><?= $grade ?></b></p>
                    </div>

                </div>
            </div>

        </div>

        <!-- BUTTONS CENTER -->
        <div style="display:flex; justify-content:center; gap:20px; margin-top:30px;">

            <button onclick="window.print()" class="btn btn-primary">
                🖨 Print
            </button>

            <button onclick="downloadPDF()" class="btn btn-success">
                📄 Download PDF
            </button>

        </div>

    </div>
</div>

<!-- PDF FUNCTION -->
<script>
function downloadPDF(){
    var element = document.getElementById('reportPDF');
    html2pdf().from(element).save('report_card.pdf');
}
</script>

</body>
</html>