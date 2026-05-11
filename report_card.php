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
    ORDER BY exams.exam_date ASC
");

$subjects = [];
$total_marks = 0;
$total_max = 0;
while($r = mysqli_fetch_assoc($results_q)){
    $subjects[] = $r;
    $total_marks += $r['marks'];
    $total_max += 100;
}

$percentage = $total_max > 0 ? round(($total_marks/$total_max)*100, 2) : 0;
$grade = $percentage>=90?'A+':($percentage>=80?'A':($percentage>=70?'B':($percentage>=60?'C':($percentage>=50?'D':'F'))));
$result_status = $percentage >= 33 ? 'PASS' : 'FAIL';

// Attendance
$total_days = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE student_id='$sid'"))['t'];
$present_days = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE student_id='$sid' AND status='present'"))['t'];
$att_percent = $total_days > 0 ? round(($present_days/$total_days)*100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Report Card — <?php echo htmlspecialchars($student['name']); ?></title>
<link rel="stylesheet" href="style.css">
<style>
@media print {
    .no-print { display:none !important; }
    body { background:#fff; }
    .report-card { box-shadow:none !important; }
}
body { background:#f1f5f9; }
.report-card {
    max-width: 750px;
    margin: 20px auto;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(79,70,229,0.15);
}
.report-header {
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    padding: 28px 32px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.report-header h1 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.report-header p { font-size: 12px; opacity: 0.85; }
.student-section {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 20px 32px;
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}
.student-photo { width:70px; height:70px; border-radius:50%; object-fit:cover; border:3px solid #4f46e5; }
.student-avatar { width:70px; height:70px; border-radius:50%; background:rgba(79,70,229,0.1); display:flex; align-items:center; justify-content:center; font-size:30px; border:3px solid #e2e8f0; }
.info-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; padding:20px 32px; border-bottom:1px solid #e2e8f0; }
.info-item label { font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; display:block; margin-bottom:3px; }
.info-item span { font-size:13px; font-weight:700; color:#1e293b; }
.result-table { width:100%; border-collapse:collapse; }
.result-table th { background:#4f46e5; color:white; padding:11px 16px; text-align:left; font-size:12px; }
.result-table td { padding:11px 16px; border-bottom:1px solid #f1f5f9; font-size:13px; }
.summary-bar {
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    padding: 20px 32px;
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 16px;
    color: white;
    text-align: center;
}
.summary-item label { font-size:10px; opacity:0.8; display:block; margin-bottom:4px; }
.summary-item span { font-size:22px; font-weight:800; }
.report-footer {
    padding: 20px 32px;
    display: flex;
    justify-content: space-between;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}
.sign-box { text-align:center; }
.sign-line { width:130px; border-top:2px solid #4f46e5; margin:0 auto 6px; }
</style>
</head>
<body>

<div class="no-print" style="max-width:750px;margin:20px auto;display:flex;gap:12px;padding:0 10px;">
    <button onclick="window.print()" style="padding:11px 22px;background:#4f46e5;color:white;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;flex:1;">🖨️ Print Report Card</button>
    <a href="student_dashboard.php" style="padding:11px 22px;background:#e2e8f0;color:#1e293b;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">← Back</a>
</div>

<div class="report-card">

    <!-- HEADER -->
    <div class="report-header">
        <div>
            <div style="font-size:36px;margin-bottom:8px;">🎓</div>
            <h1>STUDENT REPORT CARD</h1>
            <p>Student Management System — Official Document</p>
            <p>Academic Year: <?php echo date('Y'); ?>-<?php echo date('Y')+1; ?></p>
        </div>
        <div style="text-align:right;">
            <div style="background:rgba(255,255,255,0.2);padding:12px 18px;border-radius:10px;">
                <div style="font-size:10px;opacity:0.8;margin-bottom:4px;">RESULT</div>
                <div style="font-size:28px;font-weight:800;"><?php echo $result_status; ?></div>
            </div>
        </div>
    </div>

    <!-- STUDENT INFO -->
    <div class="student-section">
        <?php if(!empty($student['photo'])): ?>
        <img src="uploads/<?php echo htmlspecialchars($student['photo']); ?>" class="student-photo">
        <?php else: ?>
        <div class="student-avatar">👤</div>
        <?php endif; ?>
        <div>
            <div style="font-size:20px;font-weight:800;color:#1e293b;"><?php echo htmlspecialchars($student['name']); ?></div>
            <div style="font-size:13px;color:#64748b;margin-top:2px;">
                ID: <strong><?php echo $student['student_id']; ?></strong> &nbsp;|&nbsp;
                Course: <strong><?php echo htmlspecialchars($student['course']); ?></strong>
            </div>
            <?php if(!empty($student['father_name'])): ?>
            <div style="font-size:12px;color:#94a3b8;margin-top:2px;">👨 Father: <?php echo htmlspecialchars($student['father_name']); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- INFO GRID -->
    <div class="info-grid">
        <div class="info-item">
            <label>📧 Email</label>
            <span style="font-size:11px;"><?php echo htmlspecialchars($student['email']); ?></span>
        </div>
        <div class="info-item">
            <label>📱 Mobile</label>
            <span><?php echo htmlspecialchars($student['mobile']); ?></span>
        </div>
        <div class="info-item">
            <label>📅 Date of Joining</label>
            <span><?php echo !empty($student['doj']) ? date('d M Y', strtotime($student['doj'])) : '—'; ?></span>
        </div>
        <div class="info-item">
            <label>✅ Attendance</label>
            <span style="color:<?php echo $att_percent>=75?'#10b981':'#ef4444'; ?>;"><?php echo $att_percent; ?>%</span>
        </div>
    </div>

    <!-- RESULTS TABLE -->
    <div style="padding:20px 32px;">
        <div style="font-size:14px;font-weight:800;color:#1e293b;margin-bottom:12px;">📄 Subject-wise Results</div>
        <?php if(count($subjects) > 0): ?>
        <table class="result-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Exam</th>
                    <th>Subject</th>
                    <th>Max Marks</th>
                    <th>Obtained</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($subjects as $i => $s):
                $pct = round(($s['marks']/100)*100, 1);
                $g = $s['marks']>=90?'A+':($s['marks']>=80?'A':($s['marks']>=70?'B':($s['marks']>=60?'C':($s['marks']>=50?'D':'F'))));
                $st = $s['marks'] >= 33 ? 'Pass' : 'Fail';
            ?>
            <tr>
                <td><?php echo $i+1; ?></td>
                <td><?php echo htmlspecialchars($s['exam_name'] ?? 'N/A'); ?></td>
                <td><strong><?php echo htmlspecialchars($s['subject'] ?? 'N/A'); ?></strong></td>
                <td>100</td>
                <td><strong><?php echo $s['marks']; ?></strong></td>
                <td><?php echo $pct; ?>%</td>
                <td><span style="background:rgba(79,70,229,0.1);color:#4f46e5;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;"><?php echo $g; ?></span></td>
                <td><span style="color:<?php echo $st=='Pass'?'#10b981':'#ef4444'; ?>;font-weight:700;"><?php echo $st=='Pass'?'✅ Pass':'❌ Fail'; ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align:center;color:#64748b;padding:20px;">Koi result nahi mila.</p>
        <?php endif; ?>
    </div>

    <!-- SUMMARY -->
    <div class="summary-bar">
        <div class="summary-item">
            <label>Total Marks</label>
            <span><?php echo $total_marks; ?>/<?php echo $total_max; ?></span>
        </div>
        <div class="summary-item">
            <label>Percentage</label>
            <span><?php echo $percentage; ?>%</span>
        </div>
        <div class="summary-item">
            <label>Grade</label>
            <span><?php echo $grade; ?></span>
        </div>
        <div class="summary-item">
            <label>Result</label>
            <span><?php echo $result_status; ?></span>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="report-footer">
        <div class="sign-box">
            <div class="sign-line"></div>
            <div style="font-size:12px;color:#64748b;font-weight:700;">Student Signature</div>
        </div>
        <div style="text-align:center;">
            <div style="font-size:11px;color:#94a3b8;">Generated: <?php echo date('d M Y, h:i A'); ?></div>
            <div style="font-size:11px;color:#94a3b8;">Student Management System</div>
        </div>
        <div class="sign-box">
            <div class="sign-line"></div>
            <div style="font-size:12px;color:#64748b;font-weight:700;">Principal Signature</div>
        </div>
    </div>

</div>

</body>
</html>