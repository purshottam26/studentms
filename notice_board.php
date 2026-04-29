<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$msg = '';

// Add Notice
if(isset($_POST['add_notice'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    mysqli_query($conn, "INSERT INTO notices (title, message, priority, posted_by) VALUES ('$title','$message','$priority','Admin')");
    $msg = "✅ Notice posted successfully!";
}

// Delete Notice
if(isset($_GET['delete'])){
    $did = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM notices WHERE id=$did");
    header("Location: notice_board.php?msg=deleted");
    exit();
}

$del_msg = $_GET['msg'] ?? '';
$notices_q = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC");
$total_notices = mysqli_num_rows($notices_q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notice Board</title>
<link rel="stylesheet" href="style.css">
<style>
.notice-card {
    background: white;
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 14px;
    box-shadow: 0 2px 12px rgba(79,70,229,0.08);
    border-left: 5px solid #4f46e5;
    position: relative;
}
.notice-card.important { border-left-color: #f59e0b; }
.notice-card.urgent { border-left-color: #ef4444; }
.notice-title { font-size: 16px; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
.notice-msg { font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 10px; }
.notice-meta { font-size: 12px; color: #94a3b8; display: flex; align-items: center; gap: 12px; }
.priority-badge {
    padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 700;
}
.priority-normal { background: rgba(79,70,229,0.1); color: #4f46e5; }
.priority-important { background: rgba(245,158,11,0.1); color: #d97706; }
.priority-urgent { background: rgba(239,68,68,0.1); color: #dc2626; }
</style>
</head>
<body>
<div class="main-container">
    <!-- SIDEBAR -->
   <?php include_once('sidebar.php'); ?>>

    <div class="content">
        <div class="topbar">
            <h1>📢 Notice Board</h1>
            <div class="topbar-right">
                <div class="admin-badge">Total: <?php echo $total_notices; ?> notices</div>
            </div>
        </div>

        <?php if($del_msg == 'deleted'): ?>
        <div style="background:#fee2e2;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:#991b1b;">🗑️ Notice deleted!</div>
        <?php endif; ?>

        <?php if($msg): ?>
        <div style="background:#d1fae5;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:#065f46;"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- ADD NOTICE -->
        <div class="box">
            <div class="box-title">➕ Post New Notice</div>
            <form method="POST" action="notice_board.php">
                <div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Notice Title *</label>
                        <input type="text" name="title" placeholder="Notice ka title likhо" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Priority *</label>
                        <select name="priority" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                            <option value="normal">🔵 Normal</option>
                            <option value="important">🟡 Important</option>
                            <option value="urgent">🔴 Urgent</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Message *</label>
                    <textarea name="message" rows="4" placeholder="Notice ka message likhо..." required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;resize:vertical;font-family:inherit;"></textarea>
                </div>
                <button type="submit" name="add_notice" style="background:#4f46e5;color:white;padding:11px 26px;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">📢 Post Notice</button>
            </form>
        </div>

        <!-- NOTICES LIST -->
        <div class="box">
            <div class="box-title">📋 All Notices</div>
            <?php if($total_notices > 0): ?>
            <?php mysqli_data_seek($notices_q, 0); while($n = mysqli_fetch_assoc($notices_q)): ?>
            <div class="notice-card <?php echo $n['priority']; ?>">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                    <div class="notice-title">
                        <?php echo $n['priority']=='urgent'?'🔴':($n['priority']=='important'?'🟡':'🔵'); ?>
                        <?php echo htmlspecialchars($n['title']); ?>
                    </div>
                    <span class="priority-badge priority-<?php echo $n['priority']; ?>">
                        <?php echo ucfirst($n['priority']); ?>
                    </span>
                </div>
                <div class="notice-msg"><?php echo nl2br(htmlspecialchars($n['message'])); ?></div>
                <div class="notice-meta">
                    <span>👤 <?php echo $n['posted_by']; ?></span>
                    <span>🕒 <?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?></span>
                    <a href="?delete=<?php echo $n['id']; ?>" onclick="return confirm('Delete this notice?')" style="margin-left:auto;background:#ef4444;color:white;padding:4px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">🗑️ Delete</a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div style="text-align:center;padding:40px;color:#64748b;">
                <div style="font-size:48px;margin-bottom:14px;">📢</div>
                <p style="font-weight:700;">Koi notice nahi hai. Upar se post karo!</p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>
</body>
</html>