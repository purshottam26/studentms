<?php
session_start();
if(!isset($_SESSION['teacher_id'])){
    header("Location: teacher_login.php");
    exit();
}
include 'db.php';

$tid = $_SESSION['teacher_id'];
$q = mysqli_query($conn, "SELECT * FROM teachers WHERE teacher_id='$tid'");
$teacher = mysqli_fetch_assoc($q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher ID Card</title>
<link rel="stylesheet" href="style.css">
<style>
@media print { .no-print { display:none !important; } body { background:#fff; } .id-card-wrapper { grid-template-columns:1fr !important; } }
.id-card-wrapper { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:20px; justify-content:center; padding:30px; }
.id-card {
    width: 100%; max-width:340px; background: white;
    border-radius: 20px; overflow: hidden;
    box-shadow: 0 20px 60px rgba(79,70,229,0.3);
    border: 3px solid #4f46e5;
}
.id-header {
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    padding: 22px 20px; text-align: center; color: white;
}
.id-header h2 { font-size: 15px; font-weight: 800; margin-bottom: 3px; }
.id-header p { font-size: 11px; opacity: 0.8; }
.id-photo-area { display: flex; justify-content: center; margin: -40px 0 12px; }
.id-photo {
    width: 90px; height: 110px; border-radius: 12px;
    border: 4px solid white; object-fit: cover;
    background: #e2e8f0; display: flex; align-items: center; justify-content: center;
    font-size: 40px; box-shadow: 0 8px 20px rgba(0,0,0,0.2); overflow: hidden;
}
.id-body { padding: 10px 22px 22px; text-align: center; }
.teacher-name { font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 4px; }
.teacher-subject { font-size: 13px; color: #4f46e5; font-weight: 700; margin-bottom: 18px; }
.id-details { text-align: left; }
.id-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.id-row label { color: #64748b; font-weight: 700; }
.id-row span { color: #1e293b; font-weight: 700; }
.id-footer { background: linear-gradient(135deg,#4f46e5,#06b6d4); padding: 14px 20px; text-align: center; }
.id-barcode { font-size: 11px; color: rgba(255,255,255,0.7); font-family: monospace; letter-spacing: 3px; }
.id-valid { font-size: 11px; color: rgba(255,255,255,0.85); margin-top: 4px; }
.back-card { background:#f8fafc; }
.back-title { font-size:14px; font-weight:800; margin-bottom:10px; color:#1e293b; }
.back-text { font-size:13px; color:#475569; line-height:1.7; margin-bottom:10px; }
.back-info { background:white; border-radius:14px; padding:16px; box-shadow:0 8px 20px rgba(79,70,229,0.08); }
.back-info .info-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #e2e8f0; font-size:13px; }
.back-info .info-row:last-child { border-bottom:none; }
</style>
</head>
<body>
<div class="no-print" style="padding:20px;max-width:500px;margin:0 auto;">
    <div style="display:flex;gap:12px;margin-bottom:10px;">
        <button onclick="window.print()" style="padding:10px 20px;background:#4f46e5;color:white;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">🖨️ Print ID Card</button>
        <a href="teacher_dashboard.php" style="padding:10px 20px;background:#e2e8f0;color:#1e293b;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">← Back</a>
    </div>
</div>

<div class="id-card-wrapper">
    <div class="id-card">
        <div class="id-header">
            <h2>🎓 STUDENT MANAGEMENT SYSTEM</h2>
            <p>Official Teacher Identity Card</p>
        </div>
        <div style="background:linear-gradient(135deg,#4f46e5,#06b6d4);height:50px;"></div>
        <div class="id-photo-area">
            <div class="id-photo">
                <?php if(!empty($teacher['photo'])): ?>
                <img src="uploads/<?php echo $teacher['photo']; ?>" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                👩‍🏫
                <?php endif; ?>
            </div>
        </div>
        <div class="id-body">
            <div class="teacher-name"><?php echo htmlspecialchars($teacher['name']); ?></div>
            <div class="teacher-subject">📖 <?php echo htmlspecialchars($teacher['subject']); ?></div>
            <div class="id-details">
                <div class="id-row"><label>Teacher ID</label><span><?php echo $teacher['teacher_id']; ?></span></div>
                <div class="id-row"><label>Email</label><span><?php echo htmlspecialchars($teacher['email']); ?></span></div>
                <div class="id-row"><label>Phone</label><span><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></span></div>
                <div class="id-row"><label>Issue Date</label><span><?php echo date('d/m/Y'); ?></span></div>
            </div>
        </div>
        <div class="id-footer">
            <div class="id-barcode">||| <?php echo strtoupper($teacher['teacher_id']); ?> |||</div>
            <div class="id-valid">Valid: <?php echo date('Y'); ?>-<?php echo date('Y')+1; ?></div>
        </div>
    </div>

    <div class="id-card back-card">
        <div class="id-header">
            <h2>🎓 BACKSIDE / TEACHER INFO</h2>
            <p>Contact & Subject Details</p>
        </div>
        <div class="id-body" style="padding-top:18px;text-align:left;">
            <div class="back-title">Teacher Details</div>
            <div class="back-info">
                <div class="info-row"><span>Teacher ID</span><strong><?php echo htmlspecialchars($teacher['teacher_id']); ?></strong></div>
                <div class="info-row"><span>Subjects</span><strong><?php echo htmlspecialchars($teacher['subject'] ?: 'N/A'); ?></strong></div>
                <div class="info-row"><span>Email</span><strong><?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?></strong></div>
                <div class="info-row"><span>Phone</span><strong><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></strong></div>
                <div class="info-row"><span>Emergency</span><strong>1800-555-1234</strong></div>
            </div>
            <div class="back-title" style="margin-top:18px;">Important Notes</div>
            <p class="back-text">Yeh ID card sirf adhikariyon ke liye valid hai. Apne school pass ko surakshit jagah par rakhein aur har class mein saath leke jayen.</p>
            <p class="back-text" style="font-weight:700;color:#1e293b;">Issued by Student Management System</p>
        </div>
    </div>
</div>
</body>
</html>