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

$msg = '';
$msg_type = 'success';

if(isset($_POST['update'])){
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, preg_replace('/\D/', '', $_POST['phone'] ?? ''));
    $subjects = mysqli_real_escape_string($conn, trim($_POST['subjects'] ?? ''));

    $photo = $teacher['photo'];
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

    mysqli_query($conn, "UPDATE teachers SET name='$name', email='$email', phone='$phone', subject='$subjects', photo='$photo' WHERE teacher_id='$tid'");
    $msg = '✅ Profile updated successfully!';
    $msg_type = 'success';

    $q = mysqli_query($conn, "SELECT * FROM teachers WHERE teacher_id='$tid'");
    $teacher = mysqli_fetch_assoc($q);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Teacher Profile</title>
<link rel="stylesheet" href="style.css">
<style>
.edit-input { width:100%; padding:11px 14px; border:2px solid #e2e8f0; border-radius:10px; font-size:14px; font-family:inherit; color:#1e293b; outline:none; transition:border-color 0.2s; background:white; margin-bottom:14px; }
.edit-input:focus { border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,0.08); }
.edit-label { font-size:12px; font-weight:700; color:#64748b; display:block; margin-bottom:5px; text-transform:uppercase; }
.readonly-input { width:100%; padding:11px 14px; border:2px solid #f1f5f9; border-radius:10px; font-size:14px; font-family:inherit; color:#94a3b8; background:#f8fafc; margin-bottom:14px; cursor:not-allowed; }
.photo-preview { width:120px; height:120px; border-radius:50%; object-fit:cover; border:4px solid #4f46e5; display:block; margin:0 auto 16px; box-shadow:0 4px 16px rgba(79,70,229,0.2); }
.photo-avatar { width:120px; height:120px; border-radius:50%; background:rgba(79,70,229,0.1); display:flex; align-items:center; justify-content:center; font-size:50px; margin:0 auto 16px; border:4px solid #e2e8f0; }
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
            <a href="teacher_profile.php">👤 My Profile</a>
            <a href="teacher_profile_edit.php" class="active">✏️ Edit Profile</a>
            <a href="teacher_change_password.php">🔐 Change Password</a>
            <a href="library.php">📚 Library</a>
        </div>
        <div class="sidebar-footer">
            <a href="teacher_logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="topbar">
            <h1>✏️ Edit Profile</h1>
            <div class="topbar-right">
                <div class="admin-badge">👩‍🏫 <?php echo htmlspecialchars($teacher['name']); ?></div>
            </div>
        </div>

        <?php if($msg): ?>
        <div style="background:<?php echo $msg_type=='success'?'#d1fae5':'#fee2e2'; ?>;border-left:4px solid <?php echo $msg_type=='success'?'#10b981':'#ef4444'; ?>;padding:14px 18px;border-radius:10px;margin-bottom:18px;font-weight:700;color:<?php echo $msg_type=='success'?'#065f46':'#991b1b'; ?>;">
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;">
            <div class="box" style="text-align:center;">
                <div class="box-title">📸 Profile Photo</div>
                <?php if(!empty($teacher['photo'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($teacher['photo']); ?>" class="photo-preview" id="photoPreview">
                <?php else: ?>
                <div class="photo-avatar" id="photoPreview">👩‍🏫</div>
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
            <div class="box">
                <div class="box-title">📝 Update Your Details</div>
                <form method="POST" enctype="multipart/form-data">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div>
                            <label class="edit-label">🪪 Teacher ID</label>
                            <input type="text" class="readonly-input" value="<?php echo htmlspecialchars($teacher['teacher_id']); ?>" readonly>
                        </div>
                        <div>
                            <label class="edit-label">👤 Full Name</label>
                            <input type="text" name="name" class="edit-input" value="<?php echo htmlspecialchars($teacher['name']); ?>" required>
                        </div>
                        <div>
                            <label class="edit-label">📧 Email</label>
                            <input type="email" name="email" class="edit-input" value="<?php echo htmlspecialchars($teacher['email']); ?>">
                        </div>
                        <div>
                            <label class="edit-label">📞 Phone</label>
                            <input type="text" name="phone" class="edit-input" maxlength="10" value="<?php echo htmlspecialchars($teacher['phone'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="edit-label">🎂 Date of Birth</label>
                            <input type="date" name="dob" class="edit-input" value="<?php echo $teacher['dob'] ?? ''; ?>" readonly style="background:#f8fafc;cursor:not-allowed;">
                        </div>
                        <div>
                            <label class="edit-label">📅 Date of Joining</label>
                            <input type="date" name="doj" class="edit-input" value="<?php echo $teacher['doj'] ?? ''; ?>" readonly style="background:#f8fafc;cursor:not-allowed;">
                        </div>
                        <div style="grid-column:span 2;">
                            <label class="edit-label">📚 Subjects</label>
                            <input type="text" name="subjects" class="edit-input" value="<?php echo htmlspecialchars($teacher['subject']); ?>" placeholder="Separate with comma, e.g. Math, Physics">
                        </div>
                    </div>
                    <button type="submit" name="update" style="background:linear-gradient(135deg,#4f46e5,#06b6d4);color:white;padding:12px 28px;border:none;border-radius:10px;cursor:pointer;font-size:14px;font-weight:700;width:100%;margin-top:10px;">✅ Update Profile</button>
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
