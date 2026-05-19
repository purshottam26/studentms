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
$error_msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Teacher — Student Management</title>
<link rel="stylesheet" href="style.css">
<style>
.edit-grid { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
.edit-grid-3 { display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px; }
.edit-input { width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;font-family:inherit;color:#1e293b;outline:none;background:white; }
.edit-input:focus { border-color:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,0.08); }
.edit-label { display:block;font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px;text-transform:uppercase; }
.subject-tags { display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px; }
.subject-tag { background:rgba(79,70,229,0.1);color:#4f46e5;padding:6px 12px;border-radius:999px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:8px; }
.subject-tag button { background:none;border:none;color:#ef4444;cursor:pointer;font-size:14px;line-height:1; }
.subject-input-row { display:flex;gap:8px; }
.subject-input-row input { flex:1;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px; }
.subject-input-row button { padding:10px 16px;background:#4f46e5;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:14px; }
</style>
</head>
<body>
<div class="main-container">
    <?php include_once('sidebar.php'); ?>
    <div class="content">
        <div class="topbar">
            <h1>✏️ Edit Teacher</h1>
            <div class="topbar-right">
                <a href="add_teacher.php" style="padding:9px 16px;background:#e2e8f0;color:#1e293b;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">← Back</a>
                <div class="admin-badge">Admin</div>
            </div>
        </div>

        <?php if($error_msg): ?>
        <div style="background:#fee2e2;border-left:4px solid #ef4444;padding:14px 18px;border-radius:10px;margin-bottom:18px;font-weight:700;color:#991b1b;">
            <?php echo htmlspecialchars($error_msg); ?>
        </div>
        <?php endif; ?>

        <div class="box">
            <div class="box-title">✏️ Edit Teacher Info</div>
            <form method="POST" action="admin_teacher_update.php" enctype="multipart/form-data" id="teacherEditForm">
                <input type="hidden" name="id" value="<?php echo $teacher['id']; ?>">
                <div class="edit-grid" style="margin-bottom:14px;">
                    <div>
                        <label class="edit-label">🪪 Teacher ID</label>
                        <input type="text" class="edit-input" value="<?php echo htmlspecialchars($teacher['teacher_id']); ?>" readonly style="background:#f8fafc;cursor:not-allowed;">
                    </div>
                    <div>
                        <label class="edit-label">👩‍🏫 Full Name *</label>
                        <input type="text" name="name" class="edit-input" required value="<?php echo htmlspecialchars($teacher['name']); ?>" placeholder="Teacher name">
                    </div>
                </div>
                <div class="edit-grid" style="margin-bottom:14px;">
                    <div>
                        <label class="edit-label">📧 Email</label>
                        <input type="email" name="email" class="edit-input" value="<?php echo htmlspecialchars($teacher['email']); ?>" placeholder="Email address">
                    </div>
                    <div>
                        <label class="edit-label">📞 Phone</label>
                        <input type="text" name="phone" class="edit-input" value="<?php echo htmlspecialchars($teacher['phone']); ?>" placeholder="Phone number">
                    </div>
                </div>
                <div class="edit-grid" style="margin-bottom:14px;">
                    <div>
                        <label class="edit-label">🔒 New Password</label>
                        <input type="password" name="password" class="edit-input" placeholder="Leave blank to keep current password">
                    </div>
                    <div>
                        <label class="edit-label">📸 New Photo</label>
                        <input type="file" name="photo" class="edit-input" accept="image/*">
                    </div>
                </div>
                <div class="edit-grid" style="margin-bottom:14px;">
                    <div>
                        <label class="edit-label">🎂 Date of Birth</label>
                        <input type="date" name="dob" class="edit-input" value="<?php echo $teacher['dob'] ?? ''; ?>">
                    </div>
                    <div>
                        <label class="edit-label">📅 Date of Joining</label>
                        <input type="date" name="doj" class="edit-input" value="<?php echo $teacher['doj'] ?? ''; ?>">
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="edit-label">📚 Subjects</label>
                    <div class="subject-tags" id="subjectTags"></div>
                    <div class="subject-input-row">
                        <input type="text" id="subjectInput" placeholder="Add subject and press Enter" onkeydown="if(event.key==='Enter'){event.preventDefault();addSubject();}">
                        <button type="button" onclick="addSubject()">+ Add</button>
                    </div>
                    <div id="subjectsHidden"></div>
                    <div style="font-size:12px;color:#64748b;margin-top:6px;">Subjects comma separated nahi, har ek subject ek tag mein add karo.</div>
                </div>
                <button type="submit" style="background:#4f46e5;color:white;padding:12px 22px;border:none;border-radius:10px;cursor:pointer;font-size:15px;font-weight:700;">✅ Update Teacher</button>
            </form>
        </div>
    </div>
</div>

<script>
let subjects = <?php echo json_encode($subjects); ?>;
function renderSubjects() {
    const tags = document.getElementById('subjectTags');
    const hidden = document.getElementById('subjectsHidden');
    tags.innerHTML = subjects.map((sub,i) => `
        <div class="subject-tag">
            ${sub}
            <button type="button" onclick="removeSubject(${i})">×</button>
        </div>
    `).join('');
    hidden.innerHTML = subjects.map(sub => `<input type="hidden" name="subjects[]" value="${sub}">`).join('');
}
function addSubject() {
    const input = document.getElementById('subjectInput');
    const val = input.value.trim();
    if(!val) return;
    if(subjects.includes(val)){
        alert('Ye subject pehle se added hai!');
        return;
    }
    subjects.push(val);
    input.value = '';
    renderSubjects();
}
function removeSubject(index) {
    subjects.splice(index, 1);
    renderSubjects();
}

renderSubjects();
</script>
</body>
</html>
