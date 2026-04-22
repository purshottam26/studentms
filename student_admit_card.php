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

$exam_q = mysqli_query($conn, "SELECT * FROM exams WHERE course='".$student['course']."' ORDER BY exam_date ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admit Card - <?php echo $student['name']; ?></title>
<link rel="stylesheet" href="style.css">
<style>
@media print { .no-print { display:none !important; } body { background:#fff; } }
.admit-card {
    max-width: 750px; margin: 30px auto; background: #fff;
    border: 3px solid #4f46e5; border-radius: 14px; padding: 0; overflow: hidden;
    box-shadow: 0 10px 40px rgba(79,70,229,0.2);
}
.admit-header {
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    color: white; padding: 28px 30px; display: flex; align-items: center; gap: 20px;
}
.admit-header h1 { font-size: 22px; margin-bottom: 5px; }
.admit-header p { font-size: 13px; opacity: 0.85; }
.admit-body { padding: 28px 30px; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 22px; }
.info-item label { font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; display: block; }
.info-item span { font-size: 15px; color: #1e293b; font-weight: 700; }
.photo-box { width: 100px; height: 120px; border: 2px solid rgba(255,255,255,0.5); border-radius: 8px; object-fit: cover; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin-left: auto; overflow: hidden; }
.exam-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.exam-table th { background: #4f46e5; color: white; padding: 10px 14px; text-align: left; font-size: 13px; }
.exam-table td { padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
.admit-footer { background: #f1f5f9; padding: 16px 30px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; }
.sign-box { text-align: center; }
.sign-line { width: 140px; border-top: 2px solid #4f46e5; margin: 0 auto 6px; }
.important { background: #fff7ed; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 6px; margin: 16px 0; font-size: 13px; color: #92400e; }
</style>
</head>
<body>
<div class="content no-print" style="margin:0;padding:20px;">
    <div style="display:flex;gap:12px;margin-bottom:20px;">
        <button onclick="window.print()" class="btn btn-primary" style="padding:10px 20px;background:#4f46e5;color:white;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">🖨️ Print / Download PDF</button>
        <a href="student_dashboard.php" style="padding:10px 20px;background:#e2e8f0;color:#1e293b;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">← Back</a>
    </div>
</div>

<div class="admit-card">
    <div class="admit-header">
        <div style="flex:1;">
            <h1>🎓 Student Admit Card</h1>
            <p>Student Management System — Official Admit Card</p>
            <p>Academic Year: <?php echo date('Y'); ?>-<?php echo date('Y')+1; ?></p>
        </div>
        <div class="photo-box">
            <?php if(!empty($student['photo'])): ?>
            <img src="uploads/<?php echo $student['photo']; ?>" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
            <span style="font-size:36px;">👤</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="admit-body">
        <div class="info-grid">
            <div class="info-item"><label>Student Name</label><span><?php echo htmlspecialchars($student['name']); ?></span></div>
            <div class="info-item"><label>Student ID</label><span><?php echo $student['student_id']; ?></span></div>
            <div class="info-item"><label>Course</label><span><?php echo htmlspecialchars($student['course']); ?></span></div>
            <div class="info-item"><label>Email</label><span><?php echo htmlspecialchars($student['email']); ?></span></div>
            <div class="info-item"><label>Phone</label><span><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></span></div>
            <div class="info-item"><label>Issue Date</label><span><?php echo date('d M Y'); ?></span></div>
        </div>

        <div class="important">
            ⚠️ <strong>Important:</strong> Carry this admit card to the examination hall. Without this, entry will not be permitted.
        </div>

        <?php if(mysqli_num_rows($exam_q) > 0): ?>
        <div class="box-title" style="font-size:15px;font-weight:700;margin-bottom:10px;">📅 Examination Schedule</div>
        <table class="exam-table">
            <thead><tr><th>#</th><th>Exam Name</th><th>Date</th><th>Course</th></tr></thead>
            <tbody>
            <?php $i=1; while($ex = mysqli_fetch_assoc($exam_q)): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($ex['exam_name']); ?></td>
                <td><?php echo date('d M Y', strtotime($ex['exam_date'])); ?></td>
                <td><?php echo htmlspecialchars($ex['course']); ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color:#64748b;">No exams scheduled for your course.</p>
        <?php endif; ?>
    </div>
    <div class="admit-footer">
        <div class="sign-box">
            <div class="sign-line"></div>
            <div style="font-size:12px;color:#64748b;font-weight:700;">Student Signature</div>
        </div>
        <div style="text-align:center;">
            <div style="font-size:24px;">🎓</div>
            <div style="font-size:11px;color:#64748b;">Official Admit Card</div>
        </div>
        <div class="sign-box">
            <div class="sign-line"></div>
            <div style="font-size:12px;color:#64748b;font-weight:700;">Principal Signature</div>
        </div>
    </div>
</div>
</body>
</html>