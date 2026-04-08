<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$errors = $errors ?? [];

if(!isset($_GET['id']) && !isset($id)){
    header("Location: students.php");
    exit();
}

$id = intval($_GET['id'] ?? $id);

$query = "SELECT * FROM student WHERE id='$id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

if(!$row){
    header("Location: students.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Student — Student Management</title>
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
            <h1>✏️ Edit Student</h1>
            <a href="students.php" class="btn btn-primary btn-sm">← Back to List</a>
        </div>

        <div class="box" style="max-width:600px;">
            <div class="box-title">✏️ Edit Student Info</div>

            <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">⚠️ Please fix the errors below.</div>
            <?php endif; ?>

            <form method="POST" action="update.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <!-- Current photo preview -->
                <?php if(!empty($row['photo'])): ?>
                <div style="margin-bottom:16px; text-align:center;">
                    <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>"
                        style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--primary-light);">
                    <div style="font-size:12px;color:var(--text-light);margin-top:6px;">Current Photo</div>
                </div>
                <?php endif; ?>

                <label>Student ID</label>
                <input type="text" name="student_id"
                    value="<?php echo htmlspecialchars($_POST['student_id'] ?? $row['student_id']); ?>" required>

                <label>Full Name *</label>
                <input type="text" name="name"
                    value="<?php echo htmlspecialchars($_POST['name'] ?? $row['name']); ?>" required>
                <?php if(!empty($errors['name'])): ?>
                <div class="error">⚠️ <?php echo $errors['name']; ?></div>
                <?php endif; ?>

                <label>Email Address *</label>
                <input type="email" name="email"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? $row['email']); ?>" required>
                <?php if(!empty($errors['email'])): ?>
                <div class="error">⚠️ <?php echo $errors['email']; ?></div>
                <?php endif; ?>

                <label>Course</label>
                <input type="text" name="course"
                    value="<?php echo htmlspecialchars($_POST['course'] ?? $row['course']); ?>">

                <label>Aadhaar Number (12 digits)</label>
                <input type="text" name="aadhaar" maxlength="12"
                    value="<?php echo htmlspecialchars($_POST['aadhaar'] ?? $row['aadhaar']); ?>">
                <?php if(!empty($errors['aadhaar'])): ?>
                <div class="error">⚠️ <?php echo $errors['aadhaar']; ?></div>
                <?php endif; ?>

                <label>Mobile Number (10 digits)</label>
                <input type="text" name="mobile" maxlength="10"
                    value="<?php echo htmlspecialchars($_POST['mobile'] ?? $row['mobile']); ?>">
                <?php if(!empty($errors['mobile'])): ?>
                <div class="error">⚠️ <?php echo $errors['mobile']; ?></div>
                <?php endif; ?>

                <label>Pin Code (6 digits)</label>
                <input type="text" name="pincode" maxlength="6"
                    value="<?php echo htmlspecialchars($_POST['pincode'] ?? $row['pincode']); ?>">
                <?php if(!empty($errors['pincode'])): ?>
                <div class="error">⚠️ <?php echo $errors['pincode']; ?></div>
                <?php endif; ?>

                <label>New Photo (optional)</label>
                <input type="file" name="photo" accept="image/jpg, image/jpeg, image/png">
                <?php if(!empty($errors['photo'])): ?>
                <div class="error">⚠️ <?php echo $errors['photo']; ?></div>
                <?php endif; ?>

                <div style="display:flex; gap:12px; margin-top:10px;">
                    <button type="submit" style="flex:1;">✅ Update Student</button>
                    <a href="students.php" class="btn btn-secondary" style="flex:0.4; text-align:center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
