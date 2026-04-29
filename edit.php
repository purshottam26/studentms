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
<style>
.edit-input {
    width:100%;
    padding:10px 14px;
    border:1px solid #e2e8f0;
    border-radius:8px;
    font-size:14px;
    font-family:inherit;
    color:#1e293b;
    outline:none;
    transition:border-color 0.2s;
    background:white;
    margin-bottom:14px;
}
.edit-input:focus {
    border-color:#4f46e5;
    box-shadow:0 0 0 3px rgba(79,70,229,0.08);
}
.edit-label {
    font-size:12px;
    font-weight:700;
    color:#64748b;
    display:block;
    margin-bottom:5px;
    text-transform:uppercase;
}
.edit-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}
.edit-grid-3 {
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    gap:14px;
}
</style>
</head>
<body>

<div class="main-container">

    <?php include_once('sidebar.php'); ?>

    <div class="content">
        <div class="topbar">
            <h1>✏️ Edit Student</h1>
            <div class="topbar-right">
                <a href="students.php" style="padding:9px 18px;background:#e2e8f0;color:#1e293b;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">← Back</a>
                <div class="admin-badge">👤 <?php echo htmlspecialchars($_SESSION['admin']); ?></div>
            </div>
        </div>

        <div class="box">
            <div class="box-title">✏️ Edit Student Info</div>

            <!-- PHOTO PREVIEW -->
            <div style="text-align:center;margin-bottom:22px;">
                <?php if(!empty($row['photo'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>"
                    style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:4px solid #4f46e5;box-shadow:0 4px 16px rgba(79,70,229,0.2);">
                <?php else: ?>
                <div style="width:100px;height:100px;border-radius:50%;background:rgba(79,70,229,0.1);display:flex;align-items:center;justify-content:center;font-size:40px;margin:0 auto;border:4px solid #e2e8f0;">👤</div>
                <?php endif; ?>
                <div style="font-size:12px;color:#64748b;margin-top:8px;font-weight:700;">
                    <?php echo htmlspecialchars($row['name']); ?> — <?php echo htmlspecialchars($row['student_id']); ?>
                </div>
            </div>

            <form method="POST" action="update.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <!-- ROW 1 — ID & Name -->
                <div class="edit-grid" style="margin-bottom:14px;">
                    <div>
                        <label class="edit-label">🪪 Student ID</label>
                        <input type="text" name="student_id" class="edit-input"
                            value="<?php echo htmlspecialchars($row['student_id']); ?>" readonly
                            style="background:#f8fafc;cursor:not-allowed;">
                    </div>
                    <div>
                        <label class="edit-label">👤 Full Name *</label>
                        <input type="text" name="name" class="edit-input" required
                            value="<?php echo htmlspecialchars($row['name']); ?>"
                            placeholder="Student full name">
                    </div>
                </div>

                <!-- ROW 2 — Father & Email -->
                <div class="edit-grid" style="margin-bottom:14px;">
                    <div>
                        <label class="edit-label">👨 Father's Name</label>
                        <input type="text" name="father_name" class="edit-input"
                            value="<?php echo htmlspecialchars($row['father_name'] ?? ''); ?>"
                            placeholder="Father's full name">
                    </div>
                    <div>
                        <label class="edit-label">📧 Email Address *</label>
                        <input type="email" name="email" class="edit-input" required
                            value="<?php echo htmlspecialchars($row['email']); ?>"
                            placeholder="student@email.com">
                    </div>
                </div>

                <!-- ROW 3 — Course & DOB & DOJ -->
                <div class="edit-grid-3" style="margin-bottom:14px;">
                    <div>
                        <label class="edit-label">📚 Course *</label>
                        <input type="text" name="course" class="edit-input" required
                            value="<?php echo htmlspecialchars($row['course']); ?>"
                            placeholder="e.g. B.Tech, BCA">
                    </div>
                    <div>
                        <label class="edit-label">🎂 Date of Birth</label>
                        <input type="date" name="dob" class="edit-input"
                            value="<?php echo $row['dob'] ?? ''; ?>">
                    </div>
                    <div>
                        <label class="edit-label">📅 Date of Joining</label>
                        <input type="date" name="doj" class="edit-input"
                            value="<?php echo $row['doj'] ?? ''; ?>">
                    </div>
                </div>

                <!-- ROW 4 — Aadhaar & Mobile & WhatsApp -->
                <div class="edit-grid-3" style="margin-bottom:14px;">
                    <div>
                        <label class="edit-label">🪪 Aadhaar Number</label>
                        <input type="text" name="aadhaar" class="edit-input" maxlength="12"
                            value="<?php echo htmlspecialchars($row['aadhaar']); ?>"
                            placeholder="12-digit Aadhaar">
                    </div>
                    <div>
                        <label class="edit-label">📱 Mobile Number</label>
                        <input type="text" name="mobile" class="edit-input" maxlength="10"
                            value="<?php echo htmlspecialchars($row['mobile']); ?>"
                            placeholder="10-digit mobile">
                    </div>
                    <div>
                        <label class="edit-label">💬 WhatsApp Number</label>
                        <input type="text" name="whatsapp" class="edit-input" maxlength="10"
                            value="<?php echo htmlspecialchars($row['whatsapp'] ?? ''); ?>"
                            placeholder="10-digit WhatsApp">
                    </div>
                </div>

                <!-- ROW 5 — Pincode & Photo -->
                <div class="edit-grid" style="margin-bottom:20px;">
                    <div>
                        <label class="edit-label">📮 Pin Code</label>
                        <input type="text" name="pincode" class="edit-input" maxlength="6"
                            value="<?php echo htmlspecialchars($row['pincode']); ?>"
                            placeholder="6-digit pin code">
                    </div>
                    <div>
                        <label class="edit-label">📸 New Photo (optional)</label>
                        <input type="file" name="photo" class="edit-input" accept="image/jpg,image/jpeg,image/png">
                    </div>
                </div>

                <!-- BUTTONS -->
                <div style="display:flex;gap:12px;">
                    <button type="submit" style="flex:1;padding:12px;background:linear-gradient(135deg,#4f46e5,#06b6d4);color:white;border:none;border-radius:10px;cursor:pointer;font-size:15px;font-weight:700;font-family:inherit;">
                        ✅ Update Student
                    </button>
                    <a href="students.php" style="flex:0.3;text-align:center;padding:12px;background:#f1f5f9;color:#1e293b;border-radius:10px;text-decoration:none;font-size:15px;font-weight:700;">
                        ❌ Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>