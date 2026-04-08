<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: students.php");
    exit();
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM student WHERE id=$id");

if(mysqli_num_rows($result) == 0){
    header("Location: students.php");
    exit();
}

$row = mysqli_fetch_assoc($result);

// Check for documents
$folder = "uploads/" . $id . "/";
$has_docs = is_dir($folder) && count(array_diff(scandir($folder), ['.','..'])) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($row['name']); ?> — Profile</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="main-container">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="brand-icon">🎓</span> <span>StudentMS</span></h2>
            <p>Admin Panel</p>
        </div>
        <div class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="index.php"><span class="icon">📊</span> Dashboard</a>
            <a href="students.php" class="active"><span class="icon">👨‍🎓</span> Students</a>
            <a href="students_list.php"><span class="icon">📋</span> All Students</a>
            <a href="export.php"><span class="icon">📤</span> Export Excel</a>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php"><span class="icon">🚪</span> Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="topbar">
            <h1>👤 Student Profile</h1>
            <div class="topbar-right">
                <a href="students.php" class="btn btn-primary btn-sm">← Back to List</a>
            </div>
        </div>

        <div class="profile-container">
            <div class="profile-card">

                <!-- HEADER -->
                <div class="profile-header">
                    <?php if(!empty($row['photo'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>"
                         class="profile-img" alt="Student Photo">
                    <?php else: ?>
                    <div class="profile-img" style="display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.2);font-size:50px;">👤</div>
                    <?php endif; ?>

                    <div class="profile-name"><?php echo htmlspecialchars($row['name']); ?></div>
                    <div class="profile-course-badge">📚 <?php echo htmlspecialchars($row['course']); ?></div>
                </div>

                <!-- BODY -->
                <div class="profile-body">
                    <div class="profile-info">
                        <div class="info-item">
                            <div class="info-label">🆔 Student ID</div>
                            <div class="info-value"><?php echo htmlspecialchars($row['student_id']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">📧 Email</div>
                            <div class="info-value" style="font-size:13px;"><?php echo htmlspecialchars($row['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">📱 Mobile</div>
                            <div class="info-value"><?php echo htmlspecialchars($row['mobile']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">🏠 Pin Code</div>
                            <div class="info-value"><?php echo htmlspecialchars($row['pincode']); ?></div>
                        </div>
                        <div class="info-item" style="grid-column:span 2;">
                            <div class="info-label">🪪 Aadhaar Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($row['aadhaar']); ?></div>
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div style="display:flex; gap:10px; margin-top:22px; flex-wrap:wrap;">
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">✏️ Edit Student</a>
                        <a href="documents.php?id=<?php echo $row['id']; ?>" class="btn btn-success">📁 Documents <?php echo $has_docs ? '✅' : ''; ?></a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>"
                           onclick="return confirm('Delete this student permanently?')"
                           class="btn btn-danger">🗑️ Delete</a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

</body>
</html>
