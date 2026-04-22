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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard</title>
<link rel="stylesheet" href="style.css">
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
            <a href="student_profile.php">👤 My Profile</a>
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
        </div>

        <!-- QUICK ACTIONS -->
        <div class="box">
            <div class="box-title">⚡ Quick Actions</div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <a href="student_id_card.php" style="padding:10px 20px;background:#8b5cf6;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">🪪 My ID Card</a>
                <a href="student_admit_card.php" style="padding:10px 20px;background:#4f46e5;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">📋 Admit Card</a>
                <a href="student_marksheet.php" style="padding:10px 20px;background:#10b981;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">📄 Marksheet</a>
            </div>
        </div>

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
</body>
</html>