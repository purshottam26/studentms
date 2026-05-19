<?php
session_start();
include 'db.php';

if(isset($_SESSION['student_id'])){
    $backUrl = 'student_dashboard.php';
    $title = 'Student Notice Board';
} elseif(isset($_SESSION['teacher_id'])){
    $backUrl = 'teacher_dashboard.php';
    $title = 'Teacher Notice Board';
} else {
    header('Location: login.php');
    exit();
}

$notices_q = mysqli_query($conn, "SELECT * FROM notices ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $title; ?></title>
<link rel="stylesheet" href="style.css">
<style>
.notice-card { background:white; border-radius:16px; padding:18px; box-shadow:0 10px 30px rgba(15,23,42,0.06); border:1px solid #e2e8f0; margin-bottom:18px; }
.notice-card.important { border-left:4px solid #f59e0b; }
.notice-card.urgent { border-left:4px solid #ef4444; }
.notice-title { font-size:16px; font-weight:800; color:#111827; margin-bottom:8px; }
.notice-meta { font-size:12px; color:#6b7280; display:flex; align-items:center; gap:12px; margin-top:10px; }
.notice-msg { font-size:14px; color:#4b5563; line-height:1.7; }
</style>
</head>
<body>
<div class="main-container">
    <div class="content">
        <div class="topbar">
            <h1>📢 <?php echo $title; ?></h1>
            <div class="topbar-right">
                <a href="<?php echo $backUrl; ?>" style="padding:10px 20px;background:#4f46e5;color:white;border-radius:10px;text-decoration:none;font-weight:700;">← Back</a>
            </div>
        </div>

        <?php if(mysqli_num_rows($notices_q) > 0): ?>
            <?php while($notice = mysqli_fetch_assoc($notices_q)): ?>
                <div class="notice-card <?php echo htmlspecialchars($notice['priority']); ?>">
                    <div class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></div>
                    <div class="notice-msg"><?php echo nl2br(htmlspecialchars($notice['message'])); ?></div>
                    <div class="notice-meta">
                        <span>Posted: <?php echo date('d M Y, H:i', strtotime($notice['created_at'])); ?></span>
                        <span>By: <?php echo htmlspecialchars($notice['posted_by'] ?? 'Admin'); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="notice-card" style="text-align:center;color:#4b5563;">
                <p style="margin:0; font-weight:700;">Koi notice abhi available nahi hai.</p>
                <p style="margin:8px 0 0; font-size:13px;">Admin ne jab notice post kiya tabhi yaha dikhega.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
