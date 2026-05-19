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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student ID Card - <?php echo $student['name']; ?></title>
<link rel="stylesheet" href="style.css">
<style>
@media print { .no-print { display:none !important; } body { background:#fff; } .id-card-wrapper { grid-template-columns:1fr !important; } }
.id-card-wrapper { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:20px; justify-content:center; padding:30px; }
.id-card { width:100%; max-width:340px; background:white; border-radius:20px; overflow:hidden; box-shadow:0 20px 60px rgba(79,70,229,0.3); border:3px solid #4f46e5; }
.id-header { background:linear-gradient(135deg,#4f46e5,#06b6d4); padding:22px 20px; text-align:center; color:white; }
.id-header h2 { font-size:15px; font-weight:800; margin-bottom:3px; }
.id-header p { font-size:11px; opacity:0.8; }
.id-photo-area { display:flex; justify-content:center; margin:-40px 0 12px; }
.id-photo { width:90px; height:110px; border-radius:12px; border:4px solid white; object-fit:cover; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-size:40px; box-shadow:0 8px 20px rgba(0,0,0,0.2); overflow:hidden; }
.id-body { padding:10px 22px 22px; text-align:center; }
.student-name { font-size:20px; font-weight:800; color:#1e293b; margin-bottom:4px; }
.student-course { font-size:13px; color:#4f46e5; font-weight:700; margin-bottom:18px; }
.id-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f1f5f9; font-size:13px; }
.id-row label { color:#64748b; font-weight:700; }
.id-row span { color:#1e293b; font-weight:700; }
.id-footer { background:linear-gradient(135deg,#4f46e5,#06b6d4); padding:14px 20px; text-align:center; }
.id-barcode { font-size:11px; color:rgba(255,255,255,0.7); font-family:monospace; letter-spacing:3px; }
.id-valid { font-size:11px; color:rgba(255,255,255,0.85); margin-top:4px; }
.back-card { background:#f8fafc; }
.back-title { font-size:14px; font-weight:800; margin-bottom:10px; color:#1e293b; }
.back-text { font-size:13px; color:#475569; line-height:1.7; margin-bottom:10px; }
.back-info { background:white; border-radius:14px; padding:16px; box-shadow:0 8px 20px rgba(79,70,229,0.08); }
.back-info .info-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #e2e8f0; font-size:13px; }
.back-info .info-row:last-child { border-bottom:none; }
</style>
</head>
<body>

<div class="main-container no-print">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="brand-icon">🎓</span> <span>StudentMS</span></h2>
            <p>Student Panel</p>
        </div>
        <div class="sidebar-nav">
            <div class="nav-label">My Account</div>
            <a href="student_dashboard.php">📊 Dashboard</a>
            <a href="student_id_card.php" class="active">🪪 My ID Card</a>
            <a href="student_admit_card.php">📋 Admit Card</a>
            <a href="student_marksheet.php">📄 Marksheet</a>
        </div>
        <div class="sidebar-footer">
            <a href="student_logout.php">🚪 Logout</a>
        </div>
    </div>
    <div class="content">
        <div class="topbar">
            <h1>🪪 My ID Card</h1>
            <div class="topbar-right">
                <button onclick="window.print()" style="padding:9px 20px;background:#4f46e5;color:white;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">🖨️ Print ID Card</button>
            </div>
        </div>
</div>
</div>

<div class="id-card-wrapper">
    <div class="id-card">
        <div class="id-header">
            <h2>🎓 STUDENT MANAGEMENT SYSTEM</h2>
            <p>Official Student Identity Card</p>
        </div>
        <div style="background:linear-gradient(135deg,#4f46e5,#06b6d4);height:50px;"></div>
        <div class="id-photo-area">
            <div class="id-photo">
                <?php if(!empty($student['photo'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($student['photo']); ?>" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                👨‍🎓
                <?php endif; ?>
            </div>
        </div>
        <div class="id-body">
            <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
            <div class="student-course">📚 <?php echo htmlspecialchars($student['course']); ?></div>
            <div>
                <div class="id-row"><label>Student ID</label><span><?php echo htmlspecialchars($student['student_id']); ?></span></div>
                <div class="id-row"><label>Email</label><span style="font-size:11px;"><?php echo htmlspecialchars($student['email']); ?></span></div>
                <div class="id-row"><label>Mobile</label><span><?php echo htmlspecialchars($student['mobile'] ?? 'N/A'); ?></span></div>
                <div class="id-row"><label>Issue Date</label><span><?php echo date('d/m/Y'); ?></span></div>
                <div class="id-row"><label>Valid Until</label><span><?php echo date('d/m/Y', strtotime('+1 year')); ?></span></div>
            </div>
        </div>
        <div class="id-footer">
            <div class="id-barcode">||| <?php echo strtoupper($student['student_id']); ?> |||</div>
            <div class="id-valid">Academic Year: <?php echo date('Y'); ?>-<?php echo date('Y')+1; ?></div>
        </div>
    </div>

    <div class="id-card back-card">
        <div class="id-header">
            <h2>🎓 BACKSIDE / STUDENT INFO</h2>
            <p>Emergency & Validation Details</p>
        </div>
        <div class="id-body" style="padding-top:18px;text-align:left;">
            <div class="back-title">Student Details</div>
            <div class="back-info">
                <div class="info-row"><span>Father's Name</span><strong><?php echo htmlspecialchars($student['father_name'] ?? 'N/A'); ?></strong></div>
                <div class="info-row"><span>DOB</span><strong><?php echo !empty($student['dob']) ? date('d M Y', strtotime($student['dob'])) : 'N/A'; ?></strong></div>
                <div class="info-row"><span>Course</span><strong><?php echo htmlspecialchars($student['course']); ?></strong></div>
                <div class="info-row"><span>Aadhaar</span><strong><?php echo htmlspecialchars($student['aadhaar'] ?? 'N/A'); ?></strong></div>
                <div class="info-row"><span>Emergency</span><strong>1800-555-1234</strong></div>
            </div>
            <div class="back-title" style="margin-top:18px;">Important Notes</div>
            <p class="back-text">Yeh ID card school ke liye valid hai. Isse surakshit jagah par rakhein. Kisi bhi samasya mein school office ko turant suchit karein.</p>
            <p class="back-text" style="font-weight:700;color:#1e293b;">Issued by Student Management System</p>
        </div>
    </div>
</div>

</body>
</html>