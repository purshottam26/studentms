<?php
session_start();
if(!isset($_SESSION['teacher_id'])){
    header("Location: teacher_login.php");
    exit();
}
include 'db.php';

// Ensure dob and doj columns exist
$check_dob = mysqli_query($conn, "SHOW COLUMNS FROM teachers LIKE 'dob'");
if(mysqli_num_rows($check_dob) == 0){
    mysqli_query($conn, "ALTER TABLE teachers ADD COLUMN dob DATE NULL");
}
$check_doj = mysqli_query($conn, "SHOW COLUMNS FROM teachers LIKE 'doj'");
if(mysqli_num_rows($check_doj) == 0){
    mysqli_query($conn, "ALTER TABLE teachers ADD COLUMN doj DATE NULL");
}

$tid = $_SESSION['teacher_id'];
$q = mysqli_query($conn, "SELECT * FROM teachers WHERE teacher_id='$tid'");
$teacher = mysqli_fetch_assoc($q);

$subjects = explode(', ', $teacher['subject']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile — Teacher</title>
<link rel="stylesheet" href="style.css">
<style>
.profile-hero {
    background: linear-gradient(135deg, #06b6d4, #4f46e5);
    border-radius: 16px;
    padding: 28px;
    color: white;
    display: flex;
    align-items: center;
    gap: 22px;
    margin-bottom: 20px;
}
.profile-hero img {
    width: 90px; height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    flex-shrink: 0;
}
.profile-hero .avatar {
    width: 90px; height: 90px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 40px;
    border: 4px solid white;
    flex-shrink: 0;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}
.info-box {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 12px rgba(6,182,212,0.08);
    border: 1px solid #e2e8f0;
}
.info-box label {
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    display: block;
    margin-bottom: 4px;
}
.info-box span {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
}
.subject-chip {
    display: inline-block;
    background: rgba(16,185,129,0.1);
    color: #059669;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    margin: 3px;
}
</style>
</head>
<body>
<div class="main-container">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="brand-icon">🎓</span> <span>StudentMS</span></h2>
            <p>Teacher Panel</p>
        </div>
        <div class="sidebar-nav">
            <div class="nav-label">My Account</div>
            <a href="teacher_dashboard.php">📊 Dashboard</a>
            <a href="teacher_id_card.php">🪪 My ID Card</a>
            <a href="teacher_profile.php" class="active">👤 My Profile</a>
            <a href="teacher_change_password.php">🔐 Change Password</a>
            <a href="library.php">📚 Library</a>
            <a href="teacher_attendance.php">✅ Mark Attendance</a>
            <a href="notice_board_view.php">📢 Notice Board</a>
            <a href="student_timetable_view.php">📅 Timetable</a>
        </div>
        <div class="sidebar-footer">
            <a href="teacher_logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="topbar">
            <h1>👤 My Profile</h1>
            <div class="topbar-right">
                <div class="admin-badge">👩‍🏫 <?php echo htmlspecialchars($teacher['name']); ?></div>
            </div>
        </div>

        <!-- HERO -->
        <div class="profile-hero">
            <?php if(!empty($teacher['photo'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($teacher['photo']); ?>">
            <?php else: ?>
            <div class="avatar">👩‍🏫</div>
            <?php endif; ?>
            <div style="flex:1;">
                <h2 style="font-size:22px;font-weight:800;margin-bottom:6px;"><?php echo htmlspecialchars($teacher['name']); ?></h2>
                <p style="font-size:13px;opacity:0.85;margin-bottom:6px;">🪪 <?php echo htmlspecialchars($teacher['teacher_id']); ?></p>
                <div>
                    <?php foreach($subjects as $sub): ?>
                    <?php if(trim($sub)): ?>
                    <span style="background:rgba(255,255,255,0.2);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;margin-right:4px;">📖 <?php echo htmlspecialchars(trim($sub)); ?></span>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- INFO GRID -->
        <div class="info-grid">
            <div class="info-box">
                <label>🪪 Teacher ID</label>
                <span><?php echo htmlspecialchars($teacher['teacher_id']); ?></span>
            </div>
            <div class="info-box">
                <label>📧 Email</label>
                <span style="font-size:13px;"><?php echo !empty($teacher['email']) ? htmlspecialchars($teacher['email']) : '—'; ?></span>
            </div>
            <div class="info-box">
                <label>📞 Phone</label>
                <span><?php echo !empty($teacher['phone']) ? htmlspecialchars($teacher['phone']) : '—'; ?></span>
            </div>
            <div class="info-box" style="grid-column:span 3;">
                <label>📖 Subjects</label>
                <div style="margin-top:4px;">
                    <?php foreach($subjects as $sub): ?>
                    <?php if(trim($sub)): ?>
                    <span class="subject-chip">📖 <?php echo htmlspecialchars(trim($sub)); ?></span>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="box">
            <div class="box-title">⚡ Quick Actions</div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="teacher_id_card.php" style="padding:10px 18px;background:#4f46e5;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">🪪 My ID Card</a>
                <a href="teacher_profile_edit.php" style="padding:10px 18px;background:#2563eb;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">✏️ Edit Profile</a>
                <a href="library.php" style="padding:10px 18px;background:#10b981;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">📚 Library</a>
                <a href="teacher_change_password.php" style="padding:10px 18px;background:#f59e0b;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">🔐 Change Password</a>
            </div>
        </div>

    </div>
</div>
</body>
</html>