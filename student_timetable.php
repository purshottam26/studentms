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

$course = $student['course'];
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

$tt_q = mysqli_query($conn, "SELECT * FROM timetable WHERE course='".mysqli_real_escape_string($conn,$course)."' ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), start_time");

$timetable = [];
while($tt = mysqli_fetch_assoc($tt_q)){
    $timetable[$tt['day']][] = $tt;
}

$today = date('l');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Timetable</title>
<link rel="stylesheet" href="style.css">
<style>
.day-card {
    background:white;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 2px 12px rgba(79,70,229,0.08);
    margin-bottom:16px;
    border:1px solid #e2e8f0;
}
.day-card.today { border:2px solid #4f46e5; box-shadow:0 4px 20px rgba(79,70,229,0.2); }
.day-header {
    padding:12px 18px;
    font-weight:800;
    font-size:14px;
    display:flex;
    align-items:center;
    gap:10px;
}
.day-header.today { background:linear-gradient(135deg,#4f46e5,#06b6d4); color:white; }
.day-header.normal { background:#f8fafc; color:#1e293b; }
.period-row {
    display:flex;
    align-items:center;
    padding:12px 18px;
    border-top:1px solid #f1f5f9;
    gap:16px;
}
.period-time {
    min-width:130px;
    font-size:12px;
    font-weight:700;
    color:#4f46e5;
}
.period-subject { font-weight:700; font-size:14px; color:#1e293b; flex:1; }
.period-teacher { font-size:12px; color:#64748b; }
.period-room {
    background:#f1f5f9;
    padding:3px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:700;
    color:#64748b;
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
            <a href="student_dashboard.php">📊 Dashboard</a>
            <a href="student_id_card.php">🪪 My ID Card</a>
            <a href="student_admit_card.php">📋 Admit Card</a>
            <a href="student_marksheet.php">📄 Marksheet</a>
            <a href="report_card.php">📊 Report Card</a>
            <a href="student_timetable.php" class="active">📅 Timetable</a>
            <a href="student_profile_edit.php">👤 Edit Profile</a>
            <a href="student_change_password.php">🔐 Change Password</a>
        </div>
        <div class="sidebar-footer">
            <a href="student_logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="topbar">
            <h1>📅 My Timetable</h1>
            <div class="topbar-right">
                <div class="admin-badge">📚 <?php echo htmlspecialchars($course); ?></div>
            </div>
        </div>

        <!-- TODAY HIGHLIGHT -->
        <div style="background:linear-gradient(135deg,#4f46e5,#06b6d4);border-radius:12px;padding:16px 20px;color:white;margin-bottom:20px;display:flex;align-items:center;gap:14px;">
            <div style="font-size:32px;">📅</div>
            <div>
                <div style="font-size:12px;opacity:0.8;margin-bottom:2px;">TODAY</div>
                <div style="font-size:20px;font-weight:800;"><?php echo $today; ?>, <?php echo date('d M Y'); ?></div>
            </div>
            <?php if(isset($timetable[$today])): ?>
            <div style="margin-left:auto;background:rgba(255,255,255,0.2);padding:10px 16px;border-radius:10px;text-align:center;">
                <div style="font-size:11px;opacity:0.8;">Classes Today</div>
                <div style="font-size:24px;font-weight:800;"><?php echo count($timetable[$today]); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <?php if(empty($timetable)): ?>
        <div style="text-align:center;padding:40px;color:#64748b;">
            <div style="font-size:48px;margin-bottom:14px;">📅</div>
            <p style="font-weight:700;">Abhi koi timetable nahi hai. Admin se contact karo!</p>
        </div>
        <?php else: ?>

        <?php foreach($days as $day): ?>
        <?php if(isset($timetable[$day])): ?>
        <div class="day-card <?php echo $day==$today?'today':''; ?>">
            <div class="day-header <?php echo $day==$today?'today':'normal'; ?>">
                <?php echo $day==$today?'📍':'📅'; ?>
                <?php echo $day; ?>
                <?php if($day==$today): ?>
                <span style="background:rgba(255,255,255,0.2);padding:2px 10px;border-radius:20px;font-size:11px;">TODAY</span>
                <?php endif; ?>
                <span style="margin-left:auto;font-size:12px;opacity:0.7;"><?php echo count($timetable[$day]); ?> classes</span>
            </div>
            <?php foreach($timetable[$day] as $period): ?>
            <div class="period-row">
                <div class="period-time">
                    🕐 <?php echo date('h:i A', strtotime($period['start_time'])); ?><br>
                    <span style="color:#94a3b8;">to <?php echo date('h:i A', strtotime($period['end_time'])); ?></span>
                </div>
                <div>
                    <div class="period-subject"><?php echo htmlspecialchars($period['subject']); ?></div>
                    <?php if(!empty($period['teacher_name'])): ?>
                    <div class="period-teacher">👩‍🏫 <?php echo htmlspecialchars($period['teacher_name']); ?></div>
                    <?php endif; ?>
                </div>
                <?php if(!empty($period['room'])): ?>
                <div class="period-room">🚪 <?php echo htmlspecialchars($period['room']); ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>
</body>
</html>