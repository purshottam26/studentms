<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$result = mysqli_query($conn, "SELECT * FROM student ORDER BY name");
$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Students — Student Management</title>
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
            <a href="students.php"><span class="icon">👨‍🎓</span> Students</a>
            <a href="students_list.php" class="active"><span class="icon">📋</span> All Students</a>
            <a href="export.php"><span class="icon">📤</span> Export Excel</a>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php"><span class="icon">🚪</span> Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="topbar">
            <h1>📋 All Students</h1>
            <div class="topbar-right">
                <a href="export.php" class="btn-export">📤 Export Excel</a>
                <div class="admin-badge">Total: <?php echo $total; ?></div>
            </div>
        </div>

        <div class="table-container">
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Photo</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Aadhaar</th>
                <th>Mobile</th>
                <th>Pin Code</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1; while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td style="color:var(--text-light); font-size:12px;"><?php echo $i++; ?></td>
                <td>
                    <?php if(!empty($row['photo'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="">
                    <?php else: ?>
                    <div style="width:38px;height:38px;border-radius:50%;background:rgba(79,70,229,0.1);display:flex;align-items:center;justify-content:center;font-size:16px;margin:0 auto;">👤</div>
                    <?php endif; ?>
                </td>
                <td><span class="badge badge-primary"><?php echo htmlspecialchars($row['student_id']); ?></span></td>
                <td>
                    <a href="profile.php?id=<?php echo $row['id']; ?>"
                       style="font-weight:700; color:var(--primary); text-decoration:none;">
                        <?php echo htmlspecialchars($row['name']); ?>
                    </a>
                </td>
                <td style="font-size:13px; color:var(--text-light);"><?php echo htmlspecialchars($row['email']); ?></td>
                <td><span class="badge badge-success"><?php echo htmlspecialchars($row['course']); ?></span></td>
                <td style="font-size:13px;"><?php echo htmlspecialchars($row['aadhaar']); ?></td>
                <td style="font-size:13px;"><?php echo htmlspecialchars($row['mobile']); ?></td>
                <td style="font-size:13px;"><?php echo htmlspecialchars($row['pincode']); ?></td>
                <td>
                    <div class="action-btn">
                        <a href="edit.php?id=<?php echo $row['id']; ?>">✏️ Edit</a>
                        <a href="documents.php?id=<?php echo $row['id']; ?>">📁 Docs</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

</body>
</html>
