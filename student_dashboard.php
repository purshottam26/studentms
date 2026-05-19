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
    ORDER BY results.id DESC
");

// Attendance data
$total_days = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE student_id='$sid'"))['t'];
$present_days = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE student_id='$sid' AND status='present'"))['t'];
$absent_days = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE student_id='$sid' AND status='absent'"))['t'];
$late_days = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE student_id='$sid' AND status='late'"))['t'];
$attendance_percent = $total_days > 0 ? round(($present_days / $total_days) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard</title>
<link rel="stylesheet" href="style.css">
<style>
.att-progress {
    height: 12px;
    background: #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    margin: 8px 0;
}
.att-progress-bar {
    height: 100%;
    border-radius: 20px;
    transition: width 1s ease;
}
</style>
</head>
<body>
<div class="main-container">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="brand-icon">🎓</span> <span>StudentMS</span></h2>
            <p>Student Panel</p>
        </div>
        <div class="sidebar-nav">
            <div class="nav-label">My Account</div>
            <a href="student_dashboard.php" class="active">📊 Dashboard</a>
            <a href="student_id_card.php">🪪 My ID Card</a>
            <a href="student_admit_card.php">📋 Admit Card</a>
            <a href="student_marksheet.php">📄 Marksheet</a>
            <a href="notice_board_view.php">📢 Notice Board</a>
            <a href="student_profile.php">👤 My Profile</a>
            <a href="student_change_password.php">🔐 Change Password</a>
        </div>
        <div class="sidebar-footer">
            <a href="student_logout.php">🚪 Logout</a>
        </div>
    </div>
    <div class="content">
        <div class="topbar">
            <h1>📊 Student Dashboard</h1>
            <div class="topbar-right">
                <div class="admin-badge">👤 <?php echo htmlspecialchars($student['name']); ?></div>
            </div>
        </div>

        <!-- CARDS -->
        <div class="dashboard">
            <div class="card">
                <div class="card-icon blue">🪪</div>
                <h3>Student ID</h3>
                <p style="font-size:18px;"><?php echo htmlspecialchars($student['student_id']); ?></p>
                <div class="card-sub">My ID number</div>
            </div>
            <div class="card">
                <div class="card-icon cyan">📚</div>
                <h3>Course</h3>
                <p style="font-size:18px;"><?php echo htmlspecialchars($student['course']); ?></p>
                <div class="card-sub">Enrolled course</div>
            </div>
            <div class="card">
                <div class="card-icon green">📧</div>
                <h3>Email</h3>
                <p style="font-size:14px;"><?php echo htmlspecialchars($student['email']); ?></p>
                <div class="card-sub">My email</div>
            </div>
            <div class="card">
                <div class="card-icon <?php echo $attendance_percent>=75?'green':($attendance_percent>=50?'amber':'red'); ?>">✅</div>
                <h3>Attendance</h3>
                <p style="font-size:22px;color:<?php echo $attendance_percent>=75?'#10b981':($attendance_percent>=50?'#f59e0b':'#ef4444'); ?>;">
                    <?php echo $attendance_percent; ?>%
                </p>
                <div class="card-sub"><?php echo $present_days; ?>/<?php echo $total_days; ?> days</div>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="box">
            <div class="box-title">⚡ Quick Actions</div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <a href="student_id_card.php" style="padding:10px 20px;background:#8b5cf6;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">🪪 My ID Card</a>
                <a href="student_admit_card.php" style="padding:10px 20px;background:#4f46e5;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">📋 Admit Card</a>
                <a href="student_marksheet.php" style="padding:10px 20px;background:#10b981;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">📄 Marksheet</a>
                <a href="notice_board_view.php" style="padding:10px 20px;background:#f59e0b;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">📢 Notice Board</a>
            </div>
        </div>

        <!-- ATTENDANCE -->
        <?php if($total_days > 0): ?>
        <div class="box">
            <div class="box-title">✅ My Attendance</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:14px;margin-bottom:16px;">
                <div style="text-align:center;background:#f8fafc;border-radius:10px;padding:14px;">
                    <div style="font-size:24px;font-weight:800;color:#4f46e5;"><?php echo $total_days; ?></div>
                    <div style="font-size:12px;color:#64748b;font-weight:700;">Total Days</div>
                </div>
                <div style="text-align:center;background:#d1fae5;border-radius:10px;padding:14px;">
                    <div style="font-size:24px;font-weight:800;color:#10b981;"><?php echo $present_days; ?></div>
                    <div style="font-size:12px;color:#065f46;font-weight:700;">✅ Present</div>
                </div>
                <div style="text-align:center;background:#fee2e2;border-radius:10px;padding:14px;">
                    <div style="font-size:24px;font-weight:800;color:#ef4444;"><?php echo $absent_days; ?></div>
                    <div style="font-size:12px;color:#991b1b;font-weight:700;">❌ Absent</div>
                </div>
                <div style="text-align:center;background:#fef3c7;border-radius:10px;padding:14px;">
                    <div style="font-size:24px;font-weight:800;color:#f59e0b;"><?php echo $late_days; ?></div>
                    <div style="font-size:12px;color:#92400e;font-weight:700;">⏰ Late</div>
                </div>
            </div>
            <div>
                <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;margin-bottom:4px;">
                    <span>Attendance Percentage</span>
                    <span style="color:<?php echo $attendance_percent>=75?'#10b981':($attendance_percent>=50?'#f59e0b':'#ef4444'); ?>;">
                        <?php echo $attendance_percent; ?>%
                    </span>
                </div>
                <div class="att-progress">
                    <div class="att-progress-bar" id="attBar"
                        style="width:0%;background:<?php echo $attendance_percent>=75?'#10b981':($attendance_percent>=50?'#f59e0b':'#ef4444'); ?>;">
                    </div>
                </div>
                <div style="font-size:12px;color:#64748b;margin-top:4px;">
                    <?php if($attendance_percent >= 75): ?>
                    ✅ Attendance theek hai! Keep it up!
                    <?php elseif($attendance_percent >= 50): ?>
                    ⚠️ Attendance thodi kam hai — improve karo!
                    <?php else: ?>
                    ❌ Attendance bahut kam hai — dhyan do!
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- FEES -->
        <?php
        $fees_q = mysqli_query($conn, "SELECT * FROM fees WHERE student_id='$sid' ORDER BY id DESC");
        if(mysqli_num_rows($fees_q) > 0):
        ?>
        <div class="box">
            <div class="box-title">💰 My Fees</div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                <tr style="background:#4f46e5;color:white;">
                    <th style="padding:10px 14px;text-align:left;">Fee Type</th>
                    <th style="padding:10px 14px;text-align:center;">Amount</th>
                    <th style="padding:10px 14px;text-align:center;">Month</th>
                    <th style="padding:10px 14px;text-align:center;">Status</th>
                    <th style="padding:10px 14px;text-align:center;">Receipt</th>
                </tr>
                </thead>
                <tbody>
                <?php while($f = mysqli_fetch_assoc($fees_q)): ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:10px 14px;font-weight:700;"><?php echo htmlspecialchars($f['fee_type']); ?></td>
                    <td style="padding:10px 14px;text-align:center;font-weight:700;">₹<?php echo number_format($f['amount'],0); ?></td>
                    <td style="padding:10px 14px;text-align:center;font-size:13px;color:#64748b;"><?php echo $f['month'].' '.$f['year']; ?></td>
                    <td style="padding:10px 14px;text-align:center;">
                        <span style="background:<?php echo $f['status']=='paid'?'#d1fae5':'#fee2e2'; ?>;color:<?php echo $f['status']=='paid'?'#065f46':'#991b1b'; ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                            <?php echo $f['status']=='paid'?'✅ Paid':'⏳ Unpaid'; ?>
                        </span>
                    </td>
                    <td style="padding:10px 14px;text-align:center;">
                        <?php if($f['status']=='paid'): ?>
                        <a href="fee_receipt.php?id=<?php echo $f['id']; ?>" target="_blank" style="background:#4f46e5;color:white;padding:5px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">🧾 Download</a>
                        <?php else: ?>
                        <span style="color:#94a3b8;font-size:12px;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- NOTICES -->
        <?php
        $notices = mysqli_query($conn, "SELECT * FROM notices ORDER BY id DESC LIMIT 3");
        if(mysqli_num_rows($notices) > 0):
        ?>
        <div class="box">
            <div class="box-title">📢 Latest Notices</div>
            <?php while($n = mysqli_fetch_assoc($notices)): ?>
            <div style="background:<?php echo $n['priority']=='urgent'?'#fff1f2':($n['priority']=='important'?'#fffbeb':'#f8fafc'); ?>;border-left:4px solid <?php echo $n['priority']=='urgent'?'#ef4444':($n['priority']=='important'?'#f59e0b':'#4f46e5'); ?>;padding:12px 16px;border-radius:8px;margin-bottom:10px;">
                <div style="font-weight:700;font-size:14px;color:#1e293b;margin-bottom:4px;">
                    <?php echo $n['priority']=='urgent'?'🔴':($n['priority']=='important'?'🟡':'🔵'); ?>
                    <?php echo htmlspecialchars($n['title']); ?>
                </div>
                <div style="font-size:13px;color:#64748b;"><?php echo htmlspecialchars($n['message']); ?></div>
                <div style="font-size:11px;color:#94a3b8;margin-top:6px;">🕒 <?php echo !empty($n['created_at']) ? date('d M Y', strtotime($n['created_at'])) : 'N/A'; ?></div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <!-- RESULTS TABLE -->
        <div class="box">
            <div class="box-title">📄 My Results</div>
            <?php if(mysqli_num_rows($results_q) > 0): ?>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                <tr style="background:#4f46e5;color:white;">
                    <th style="padding:10px 14px;text-align:left;">#</th>
                    <th style="padding:10px 14px;text-align:left;">Exam</th>
                    <th style="padding:10px 14px;text-align:left;">Subject</th>
                    <th style="padding:10px 14px;text-align:center;">Marks</th>
                    <th style="padding:10px 14px;text-align:center;">Grade</th>
                    <th style="padding:10px 14px;text-align:center;">Status</th>
                    <th style="padding:10px 14px;text-align:center;">Date</th>
                </tr>
                </thead>
                <tbody>
                <?php $i=1; while($r = mysqli_fetch_assoc($results_q)):
                    $m = $r['marks'];
                    $g = $m>=90?'A+':($m>=80?'A':($m>=70?'B':($m>=60?'C':($m>=50?'D':'F'))));
                    $st = $m >= 33 ? 'Pass' : 'Fail';
                ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:10px 14px;color:#64748b;"><?php echo $i++; ?></td>
                    <td style="padding:10px 14px;font-weight:700;"><?php echo htmlspecialchars($r['exam_name'] ?? 'N/A'); ?></td>
                    <td style="padding:10px 14px;"><?php echo htmlspecialchars($r['subject'] ?? 'N/A'); ?></td>
                    <td style="padding:10px 14px;text-align:center;font-weight:700;"><?php echo $r['marks']; ?>/100</td>
                    <td style="padding:10px 14px;text-align:center;">
                        <span style="background:rgba(79,70,229,0.1);color:#4f46e5;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?php echo $g; ?></span>
                    </td>
                    <td style="padding:10px 14px;text-align:center;">
                        <span style="background:<?php echo $st=='Pass'?'rgba(16,185,129,0.1)':'rgba(239,68,68,0.1)'; ?>;color:<?php echo $st=='Pass'?'#059669':'#dc2626'; ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                            <?php echo $st=='Pass'?'✅ Pass':'❌ Fail'; ?>
                        </span>
                    </td>
                    <td style="padding:10px 14px;text-align:center;color:#64748b;font-size:13px;"><?php echo $r['exam_date'] ?? '—'; ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align:center;padding:30px;color:#64748b;">
                <div style="font-size:40px;margin-bottom:10px;">📊</div>
                <p style="font-weight:700;">Abhi koi result nahi mila.</p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
// Attendance bar animate
window.onload = function(){
    const bar = document.getElementById('attBar');
    if(bar){
        setTimeout(() => {
            bar.style.width = '<?php echo $attendance_percent; ?>%';
        }, 300);
    }
}
</script>

</body>
</html>