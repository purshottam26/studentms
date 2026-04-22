<?php
session_start();
if(!isset($_SESSION['student_id'])){
    header("Location: student_login.php");
    exit();
}
include 'db.php';

$sid = $_SESSION['student_id'];
$q = mysqli_query($conn, "SELECT * FROM student WHERE student_id='$sid'");
$student = mysqli_fetch_assoc($q);

$results_q = mysqli_query($conn, "
    SELECT results.*, exams.exam_name, exams.exam_date 
    FROM results 
    LEFT JOIN exams ON results.exam_id = exams.id 
    WHERE results.student_id='$sid'
    ORDER BY results.id ASC
");

$total_marks = 0;
$total_max = 0;
$subjects = [];
while($r = mysqli_fetch_assoc($results_q)){
    $subjects[] = $r;
    $total_marks += $r['marks'];
    $total_max += 100;
}
$percentage = $total_max > 0 ? round(($total_marks/$total_max)*100, 2) : 0;
$grade = $percentage>=90?'A+':($percentage>=80?'A':($percentage>=70?'B':($percentage>=60?'C':($percentage>=50?'D':'F'))));
$result_status = $percentage >= 33 ? 'PASS' : 'FAIL';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Marksheet - <?php echo $student['name']; ?></title>
<link rel="stylesheet" href="style.css">
<style>
@media print { .no-print { display:none !important; } body { background:#fff; } }
.marksheet {
    max-width: 700px; margin: 30px auto; background: #fff;
    border: 3px double #4f46e5; border-radius: 14px; overflow: hidden;
    box-shadow: 0 10px 40px rgba(79,70,229,0.2);
}
.ms-header { background: linear-gradient(135deg,#4f46e5,#06b6d4); color:white; padding:30px; text-align:center; }
.ms-header h1 { font-size:24px; margin-bottom:6px; }
.ms-header p { font-size:13px; opacity:0.85; }
.ms-body { padding:28px 30px; }
.ms-info { display:flex; justify-content:space-between; background:#f8fafc; border-radius:10px; padding:18px; margin-bottom:22px; }
.ms-info-col label { font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; display:block; }
.ms-info-col span { font-size:15px; color:#1e293b; font-weight:700; }
.mark-table { width:100%; border-collapse:collapse; }
.mark-table th { background:#4f46e5; color:white; padding:11px 14px; text-align:left; font-size:13px; }
.mark-table td { padding:11px 14px; border-bottom:1px solid #e2e8f0; font-size:13px; }
.mark-table tr:last-child td { border-bottom:none; }
.summary-box { margin-top:22px; background:linear-gradient(135deg,#4f46e5,#06b6d4); color:white; border-radius:12px; padding:22px; display:grid; grid-template-columns:repeat(4,1fr); gap:16px; text-align:center; }
.summary-item label { font-size:11px; opacity:0.8; display:block; margin-bottom:5px; }
.summary-item span { font-size:22px; font-weight:800; }
.status-pass { color:#10b981; font-weight:800; font-size:16px; }
.status-fail { color:#ef4444; font-weight:800; font-size:16px; }
.ms-footer { background:#f1f5f9; padding:18px 30px; display:flex; justify-content:space-between; border-top:1px solid #e2e8f0; }
.sign-box { text-align:center; }
.sign-line { width:130px; border-top:2px solid #4f46e5; margin:0 auto 6px; }
</style>
</head>
<body>
<div class="no-print" style="padding:20px;">
    <div style="display:flex;gap:12px;margin-bottom:20px;">
        <button onclick="window.print()" style="padding:10px 20px;background:#4f46e5;color:white;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">🖨️ Print / Download PDF</button>
        <a href="student_dashboard.php" style="padding:10px 20px;background:#e2e8f0;color:#1e293b;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">← Back</a>
    </div>
</div>

<div class="marksheet">
    <div class="ms-header">
        <div style="font-size:40px;margin-bottom:8px;">🎓</div>
        <h1>MARKSHEET / RESULT CARD</h1>
        <p>Student Management System — Official Document</p>
        <p>Academic Year: <?php echo date('Y'); ?>-<?php echo date('Y')+1; ?></p>
    </div>
    <div class="ms-body">
        <div class="ms-info">
            <div class="ms-info-col">
                <label>Student Name</label><span><?php echo htmlspecialchars($student['name']); ?></span><br><br>
                <label>Student ID</label><span><?php echo $student['student_id']; ?></span>
            </div>
            <div class="ms-info-col">
                <label>Course</label><span><?php echo htmlspecialchars($student['course']); ?></span><br><br>
                <label>Email</label><span><?php echo htmlspecialchars($student['email']); ?></span>
            </div>
            <?php if(!empty($student['photo'])): ?>
            <div>
                <img src="uploads/<?php echo $student['photo']; ?>" style="width:90px;height:110px;object-fit:cover;border-radius:8px;border:3px solid #4f46e5;">
            </div>
            <?php endif; ?>
        </div>

        <?php if(count($subjects) > 0): ?>
        <table class="mark-table">
            <thead><tr><th>#</th><th>Subject / Exam</th><th>Max Marks</th><th>Obtained</th><th>Grade</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($subjects as $i => $s):
                $g = $s['marks']>=90?'A+':($s['marks']>=80?'A':($s['marks']>=70?'B':($s['marks']>=60?'C':($s['marks']>=50?'D':'F'))));
                $st = $s['marks'] >= 33 ? 'Pass' : 'Fail';
            ?>
            <tr>
                <td><?php echo $i+1; ?></td>
                <td><?php echo htmlspecialchars($s['exam_name'] ?? $s['subject'] ?? 'N/A'); ?></td>
                <td>100</td>
                <td><strong><?php echo $s['marks']; ?></strong></td>
                <td><strong><?php echo $g; ?></strong></td>
                <td class="<?php echo $st=='Pass'?'status-pass':'status-fail'; ?>"><?php echo $st; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-item"><label>Total Marks</label><span><?php echo $total_marks."/".$total_max; ?></span></div>
            <div class="summary-item"><label>Percentage</label><span><?php echo $percentage; ?>%</span></div>
            <div class="summary-item"><label>Grade</label><span><?php echo $grade; ?></span></div>
            <div class="summary-item"><label>Result</label><span><?php echo $result_status; ?></span></div>
        </div>
        <?php else: ?>
        <p style="text-align:center;color:#64748b;padding:30px;">No results available yet.</p>
        <?php endif; ?>
    </div>
    <div class="ms-footer">
        <div class="sign-box"><div class="sign-line"></div><div style="font-size:12px;color:#64748b;font-weight:700;">Student Signature</div></div>
        <div style="text-align:center;"><div style="font-size:24px;">🎓</div><div style="font-size:11px;color:#64748b;">Official Document</div></div>
        <div class="sign-box"><div class="sign-line"></div><div style="font-size:12px;color:#64748b;font-weight:700;">Principal Signature</div></div>
    </div>
</div>
</body>
</html>