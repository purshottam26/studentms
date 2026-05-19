<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
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

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: add_teacher.php");
    exit();
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM teachers WHERE id=$id");
if(mysqli_num_rows($result) == 0){
    header("Location: add_teacher.php");
    exit();
}
$teacher = mysqli_fetch_assoc($result);
$subjects = array_filter(array_map('trim', explode(',', $teacher['subject'])));
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Details — Student Management</title>
<link rel="stylesheet" href="style.css">
<style>
.profile-header { display:flex;align-items:center;gap:20px;background:#ffffff;border-radius:16px;padding:24px;box-shadow:0 8px 30px rgba(15,23,42,0.06);margin-bottom:20px; }
.profile-photo { width:100px;height:100px;border-radius:50%;object-fit:cover;border:4px solid #4f46e5; }
.profile-avatar { width:100px;height:100px;border-radius:50%;background:rgba(79,70,229,0.1);display:flex;align-items:center;justify-content:center;font-size:40px;border:4px solid #e2e8f0; }
.profile-info h2 { margin:0;font-size:24px;font-weight:800;color:#111827; }
.profile-info p { margin:6px 0;color:#475569;font-size:14px; }
.chip { display:inline-flex;align-items:center;gap:8px;background:rgba(79,70,229,0.08);color:#4f46e5;padding:8px 12px;border-radius:999px;font-size:13px;font-weight:700;margin:4px 4px 0 0; }
.badge { display:inline-flex;align-items:center;gap:6px;background:#eff6ff;color:#1d4ed8;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:700; }
.action-buttons { display:flex;flex-wrap:wrap;gap:10px;margin-top:16px; }
.action-buttons a { padding:10px 16px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:700;color:white;display:inline-flex;align-items:center;justify-content:center; }
.action-view { background:#10b981; }
.action-edit { background:#4f46e5; }
.action-card { background:#0ea5e9; }
.action-back { background:#64748b; }
</style>
</head>
<body>
<div class="main-container">
    <?php include_once('sidebar.php'); ?>
    <div class="content">
        <div class="topbar">
            <h1>👩‍🏫 Teacher Details</h1>
            <div class="topbar-right">
                <div class="admin-badge">Admin</div>
            </div>
        </div>

        <?php if($msg === 'updated'): ?>
        <div style="background:#d1fae5;border-radius:10px;padding:14px 18px;margin-bottom:18px;font-weight:700;color:#065f46;">✅ Teacher updated successfully!</div>
        <?php endif; ?>

        <div class="profile-header">
            <?php if(!empty($teacher['photo'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($teacher['photo']); ?>" class="profile-photo" alt="Teacher Photo">
            <?php else: ?>
                <div class="profile-avatar">👩‍🏫</div>
            <?php endif; ?>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($teacher['name']); ?></h2>
                <p>🪪 Teacher ID: <strong><?php echo htmlspecialchars($teacher['teacher_id']); ?></strong></p>
                <p>📧 Email: <strong><?php echo htmlspecialchars($teacher['email'] ?: 'N/A'); ?></strong></p>
                <p>📞 Phone: <strong><?php echo htmlspecialchars($teacher['phone'] ?: 'N/A'); ?></strong></p>
                <div class="action-buttons">
                    <a href="admin_teacher_edit.php?id=<?php echo $teacher['id']; ?>" class="action-edit">✏️ Edit</a>
                    <a href="admin_teacher_id_card.php?id=<?php echo $teacher['id']; ?>" class="action-card">🪪 ID Card</a>
                    <a href="add_teacher.php" class="action-back">← Back</a>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-title">📚 Subjects</div>
            <div style="padding:18px;display:flex;flex-wrap:wrap;gap:10px;">
                <?php if(count($subjects) > 0): ?>
                    <?php foreach($subjects as $sub): ?>
                        <?php if($sub): ?>
                        <div class="chip"><?php echo htmlspecialchars($sub); ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:#64748b;font-size:14px;">No subjects assigned.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="box">
            <div class="box-title">🔎 About Teacher</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:14px;">
                <div style="background:#ffffff;border-radius:14px;padding:18px;border:1px solid #e2e8f0;">
                    <div style="font-size:12px;color:#64748b;font-weight:700;margin-bottom:8px;">Email</div>
                    <div style="font-size:15px;color:#111827;"><?php echo htmlspecialchars($teacher['email'] ?: 'N/A'); ?></div>
                </div>
                <div style="background:#ffffff;border-radius:14px;padding:18px;border:1px solid #e2e8f0;">
                    <div style="font-size:12px;color:#64748b;font-weight:700;margin-bottom:8px;">Phone</div>
                    <div style="font-size:15px;color:#111827;"><?php echo htmlspecialchars($teacher['phone'] ?: 'N/A'); ?></div>
                </div>
                <div style="background:#ffffff;border-radius:14px;padding:18px;border:1px solid #e2e8f0;">
                    <div style="font-size:12px;color:#64748b;font-weight:700;margin-bottom:8px;">🎂 Date of Birth</div>
                    <div style="font-size:15px;color:#111827;"><?php echo !empty($teacher['dob']) ? date('d M Y', strtotime($teacher['dob'])) : 'N/A'; ?></div>
                </div>
                <div style="background:#ffffff;border-radius:14px;padding:18px;border:1px solid #e2e8f0;">
                    <div style="font-size:12px;color:#64748b;font-weight:700;margin-bottom:8px;">📅 Date of Joining</div>
                    <div style="font-size:15px;color:#111827;"><?php echo !empty($teacher['doj']) ? date('d M Y', strtotime($teacher['doj'])) : 'N/A'; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
