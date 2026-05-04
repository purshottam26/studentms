<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$tid = $_GET['tid'] ?? '';
$pass = $_GET['pass'] ?? '';

$q = mysqli_query($conn, "SELECT * FROM teachers WHERE teacher_id='".mysqli_real_escape_string($conn, $tid)."'");
$teacher = mysqli_fetch_assoc($q);

if(!$teacher){
    header("Location: add_teacher.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Login Credentials</title>
<link rel="stylesheet" href="style.css">
<style>
@media print {
    .no-print { display:none !important; }
    body { background:#fff; }
}
body { background:#f1f5f9; }
.cred-wrapper { max-width:500px; margin:30px auto; }
.cred-card { background:white; border-radius:16px; overflow:hidden; box-shadow:0 10px 40px rgba(6,182,212,0.2); border:2px solid #06b6d4; }
.cred-header { background:linear-gradient(135deg,#06b6d4,#4f46e5); padding:24px; color:white; text-align:center; }
.cred-header h2 { font-size:18px; font-weight:800; margin-bottom:4px; }
.cred-header p { font-size:12px; opacity:0.85; }
.cred-body { padding:24px; }
.cred-photo { width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid #06b6d4; display:block; margin:0 auto 14px; }
.cred-avatar { width:80px; height:80px; border-radius:50%; background:rgba(6,182,212,0.1); display:flex; align-items:center; justify-content:center; font-size:36px; margin:0 auto 14px; }
.cred-name { text-align:center; font-size:20px; font-weight:800; color:#1e293b; margin-bottom:4px; }
.cred-subject { text-align:center; font-size:13px; color:#64748b; margin-bottom:20px; }
.cred-box { background:#f8fafc; border:2px dashed #06b6d4; border-radius:12px; padding:18px; margin-bottom:14px; }
.cred-box label { font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; display:block; margin-bottom:6px; }
.cred-value { font-size:20px; font-weight:800; color:#06b6d4; letter-spacing:1px; font-family:monospace; }
.warning-box { background:#fef3c7; border-left:4px solid #f59e0b; padding:12px 16px; border-radius:8px; font-size:12px; color:#92400e; margin-bottom:16px; font-weight:700; }
.cred-footer { background:#f8fafc; padding:16px 24px; border-top:1px solid #e2e8f0; text-align:center; font-size:11px; color:#94a3b8; }
</style>
</head>
<body>

<div class="no-print cred-wrapper" style="margin-bottom:0;">
    <div style="display:flex;gap:10px;margin-bottom:16px;">
        <button onclick="window.print()" style="padding:10px 20px;background:#06b6d4;color:white;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;flex:1;">🖨️ Print Credentials</button>
        <a href="add_teacher.php" style="padding:10px 20px;background:#10b981;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;text-align:center;flex:1;">➕ Add Another Teacher</a>
        <a href="add_teacher.php" style="padding:10px 20px;background:#e2e8f0;color:#1e293b;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;text-align:center;flex:1;">📋 All Teachers</a>
    </div>
</div>

<div class="cred-wrapper">
    <div class="cred-card">
        <div class="cred-header">
            <div style="font-size:32px;margin-bottom:8px;">👩‍🏫</div>
            <h2>Teacher Login Credentials</h2>
            <p>Student Management System — Confidential</p>
        </div>

        <div class="cred-body">

            <?php if(!empty($teacher['photo'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($teacher['photo']); ?>" class="cred-photo">
            <?php else: ?>
            <div class="cred-avatar">👩‍🏫</div>
            <?php endif; ?>

            <div class="cred-name"><?php echo htmlspecialchars($teacher['name']); ?></div>
            <div class="cred-subject">📖 <?php echo htmlspecialchars($teacher['subject']); ?></div>

            <div class="cred-box">
                <label>🪪 Login ID (Teacher ID)</label>
                <div class="cred-value"><?php echo htmlspecialchars($teacher['teacher_id']); ?></div>
            </div>

            <div class="cred-box" style="border-color:#10b981;">
                <label>🔒 Password</label>
                <div class="cred-value" style="color:#10b981;"><?php echo htmlspecialchars($pass); ?></div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                <div>
                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:3px;">📧 Email</div>
                    <div style="font-size:13px;font-weight:700;color:#1e293b;"><?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?></div>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:3px;">📱 Phone</div>
                    <div style="font-size:13px;font-weight:700;color:#1e293b;"><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></div>
                </div>
            </div>

            <div class="warning-box">
                ⚠️ Ye credentials sirf teacher ko dene ke liye hain. Login karne ke baad password zaroor change karein!
            </div>

            <div style="background:#f8fafc;border-radius:10px;padding:14px;text-align:center;margin-bottom:14px;">
                <div style="font-size:11px;color:#64748b;font-weight:700;margin-bottom:6px;">🌐 LOGIN URL</div>
                <div style="font-size:13px;font-weight:700;color:#06b6d4;">localhost/studentmsnew/teacher_login.php</div>
            </div>

            <div style="font-family:monospace;font-size:13px;letter-spacing:3px;color:#06b6d4;text-align:center;">
                ||| <?php echo strtoupper($teacher['teacher_id']); ?> |||
            </div>

        </div>

        <div class="cred-footer">
            Generated on: <?php echo date('d M Y, h:i A'); ?> &nbsp;|&nbsp; Student Management System
        </div>
    </div>
</div>

</body>
</html>