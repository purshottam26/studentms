<?php
session_start();
if(!isset($_SESSION['student_id'])){
    header("Location: student_login.php");
    exit();
}
include 'db.php';

$sid = $_SESSION['student_id'];
$q = mysqli_query($conn, "SELECT * FROM student WHERE student_id='$sid'");
$student = mysqli_fetch_assoc($q);

$msg = '';
$msg_type = '';

if(isset($_POST['update'])){
    $mobile = mysqli_real_escape_string($conn, preg_replace('/\D/', '', $_POST['mobile']));
    $whatsapp = mysqli_real_escape_string($conn, preg_replace('/\D/', '', $_POST['whatsapp'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    // Photo update
    $photo = $student['photo'];
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)){
            $new_photo = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['photo']['name']));
            if(move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $new_photo)){
                $photo = $new_photo;
            }
        }
    }

    mysqli_query($conn, "UPDATE student SET mobile='$mobile', whatsapp='$whatsapp', email='$email', photo='$photo' WHERE student_id='$sid'");

    $msg = "✅ Profile updated successfully!";
    $msg_type = 'success';

    // Refresh student data
    $q = mysqli_query($conn, "SELECT * FROM student WHERE student_id='$sid'");
    $student = mysqli_fetch_assoc($q);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile</title>
<link rel="stylesheet" href="style.css">
<style>
.edit-input {
    width:100%;
    padding:11px 14px;
    border:2px solid #e2e8f0;
    border-radius:10px;
    font-size:14px;
    font-family:inherit;
    color:#1e293b;
    outline:none;
    transition:border-color 0.2s;
    background:white;
    margin-bottom:14px;
}
.edit-input:focus { border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,0.08); }
.edit-label { font-size:12px; font-weight:700; color:#64748b; display:block; margin-bottom:5px; text-transform:uppercase; }
.readonly-input {
    width:100%;
    padding:11px 14px;
    border:2px solid #f1f5f9;
    border-radius:10px;
    font-size:14px;
    font-family:inherit;
    color:#94a3b8;
    background:#f8fafc;
    margin-bottom:14px;
    cursor:not-allowed;
}
.photo-preview {
    width:120px; height:120px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #4f46e5;
    display:block;
    margin:0 auto 16px;
    box-shadow:0 4px 16px rgba(79,70,229,0.2);
}
.photo-avatar {
    width:120px; height:120px;
    border-radius:50%;
    background:rgba(79,70,229,0.1);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:50px;
    margin:0 auto 16px;
    border:4px solid #e2e8f0;
}
</style>
</head>
<body>
<div class="main-container">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="brand-icon">🎓</span> <span>StudentMS</span></h2>
            <p>Student Panel</p>
        </div>
        <div class="sidebar-nav">
            <div class="nav-label">My Account</div>
            <a href="student_dashboard.php">📊 Dashboard</a>
            <a href="student_id_card.php">🪪 My ID Card</a>
            <a href="student_admit_card.php">📋 Admit Card</a>
            <a href="student_marksheet.php">📄 Marksheet</a>
            <a href="report_card.php">📊 Report Card</a>
            <a href="student_timetable.php">📅 Timetable</a>
            <a href="student_profile_edit.php" class="active">👤 Edit Profile</a>
            <a href="student_change_password.php">🔐 Change Password</a>
        </div>
        <div class="sidebar-footer">
            <a href="student_logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="topbar">
            <h1>👤 Edit Profile</h1>
            <div class="topbar-right">
                <div class="admin-badge">👤 <?php echo htmlspecialchars($student['name']); ?></div>
            </div>
        </div>

        <?php if($msg): ?>
        <div style="background:<?php echo $msg_type=='success'?'#d1fae5':'#fee2e2'; ?>;border-left:4px solid <?php echo $msg_type=='success'?'#10b981':'#ef4444'; ?>;padding:14px 18px;border-radius:10px;margin-bottom:18px;font-weight:700;color:<?php echo $msg_type=='success'?'#065f46':'#991b1b'; ?>;">
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;">

            <!-- LEFT — Photo -->
            <div class="box" style="text-align:center;">
                <div class="box-title">📸 Profile Photo</div>
                <?php if(!empty($student['photo'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($student['photo']); ?>" class="photo-preview" id="photoPreview">
                <?php else: ?>
                <div class="photo-avatar" id="photoPreview">👤</div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <label style="display:inline-block;padding:10px 20px;background:#4f46e5;color:white;border-radius:8px;cursor:pointer;font-size:13px;font-weight:700;margin-bottom:10px;">
                        📷 Change Photo
                        <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewPhoto(this)">
                    </label>
                    <br>
                    <button type="submit" name="update" style="padding:10px 20px;background:#10b981;color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:700;">✅ Save Photo</button>
                </form>
            </div>

            <!-- RIGHT — Info -->
            <div class="box">
                <div class="box-title">✏️ Edit Information</div>

                <!-- READ ONLY INFO -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:6px;">
                    <div>
                        <label class="edit-label">🪪 Student ID (Read Only)</label>
                        <input type="text" class="readonly-input" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                    </div>
                    <div>
                        <label class="edit-label">👤 Full Name (Read Only)</label>
                        <input type="text" class="readonly-input" value="<?php echo htmlspecialchars($student['name']); ?>" readonly>
                    </div>
                    <div>
                        <label class="edit-label">📚 Course (Read Only)</label>
                        <input type="text" class="readonly-input" value="<?php echo htmlspecialchars($student['course']); ?>" readonly>
                    </div>
                    <div>
                        <label class="edit-label">🎂 Date of Birth (Read Only)</label>
                        <input type="text" class="readonly-input" value="<?php echo !empty($student['dob']) ? date('d M Y', strtotime($student['dob'])) : '—'; ?>" readonly>
                    </div>
                </div>

                <!-- EDITABLE FIELDS -->
                <form method="POST" enctype="multipart/form-data">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div>
                            <label class="edit-label">📧 Email</label>
                            <input type="email" name="email" class="edit-input" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                        </div>
                        <div>
                            <label class="edit-label">📱 Mobile Number</label>
                            <input type="text" name="mobile" class="edit-input" maxlength="10" value="<?php echo htmlspecialchars($student['mobile']); ?>">
                        </div>
                        <div>
                            <label class="edit-label">💬 WhatsApp Number</label>
                            <input type="text" name="whatsapp" class="edit-input" maxlength="10" value="<?php echo htmlspecialchars($student['whatsapp'] ?? ''); ?>">
                        </div>
                    </div>
                    <button type="submit" name="update" style="background:linear-gradient(135deg,#4f46e5,#06b6d4);color:white;padding:12px 28px;border:none;border-radius:10px;cursor:pointer;font-size:14px;font-weight:700;width:100%;margin-top:6px;">
                        ✅ Update Profile
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function previewPhoto(input){
    if(input.files && input.files[0]){
        const reader = new FileReader();
        reader.onload = function(e){
            const preview = document.getElementById('photoPreview');
            if(preview.tagName === 'IMG'){
                preview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'photo-preview';
                img.id = 'photoPreview';
                preview.parentNode.replaceChild(img, preview);
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

</body>
</html>