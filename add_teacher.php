<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$msg = '';
$msg_type = '';

// Add Teacher
if(isset($_POST['submit'])){
    $tid = mysqli_real_escape_string($conn, $_POST['teacher_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $qualification = mysqli_real_escape_string($conn, $_POST['qualification'] ?? '');
    $experience = mysqli_real_escape_string($conn, $_POST['experience'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');

    // Multiple subjects — array se string banana
    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    $subject = mysqli_real_escape_string($conn, implode(', ', array_filter($subjects)));

    $photo = '';
    if(!empty($_FILES['photo']['name'])){
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','gif'];
        if(in_array(strtolower($ext), $allowed)){
            $photo = time() . '_teacher_' . rand(100,999) . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo);
        }
    }

    $check = mysqli_query($conn, "SELECT id FROM teachers WHERE teacher_id='$tid'");
    if(mysqli_num_rows($check) > 0){
        $msg = "❌ Teacher ID already exists!";
        $msg_type = 'error';
    } else {
        $r = mysqli_query($conn, "INSERT INTO teachers (teacher_id,name,subject,phone,email,photo,password)
        VALUES ('$tid','$name','$subject','$phone','$email','$photo','$password')");
        if($r){
            $msg = "✅ Teacher added! ID: <strong>$tid</strong> | Subjects: <strong>$subject</strong>";
            $msg_type = 'success';
        } else {
            $msg = "❌ Error: " . mysqli_error($conn);
            $msg_type = 'error';
        }
    }
}

// Delete Teacher
if(isset($_GET['delete'])){
    $did = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM teachers WHERE id=$did");
    header("Location: add_teacher.php?msg=deleted");
    exit();
}

$del_msg = $_GET['msg'] ?? '';
$teachers_q = mysqli_query($conn, "SELECT * FROM teachers ORDER BY id DESC");
$total_teachers = mysqli_num_rows($teachers_q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teachers — Student Management</title>
<link rel="stylesheet" href="style.css">
<style>
.subject-tags { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px; }
.subject-tag {
    background:rgba(79,70,229,0.1); color:#4f46e5;
    padding:5px 12px; border-radius:20px; font-size:13px; font-weight:700;
    display:flex; align-items:center; gap:6px;
}
.subject-tag button {
    background:none; border:none; cursor:pointer;
    color:#ef4444; font-size:14px; font-weight:800; padding:0; line-height:1;
}
.subject-input-row { display:flex; gap:8px; }
.subject-input-row input {
    flex:1; padding:10px 14px; border:1px solid #e2e8f0;
    border-radius:8px; font-size:14px;
}
.subject-input-row button {
    padding:10px 16px; background:#4f46e5; color:white;
    border:none; border-radius:8px; cursor:pointer; font-weight:700; font-size:14px;
}
.teacher-card {
    background:white; border-radius:14px; padding:0;
    box-shadow:0 4px 20px rgba(79,70,229,0.08);
    overflow:hidden; transition:transform 0.2s;
    border:1px solid #e2e8f0;
}
.teacher-card:hover { transform:translateY(-4px); box-shadow:0 8px 30px rgba(79,70,229,0.15); }
.teacher-card-header {
    background:linear-gradient(135deg,#4f46e5,#06b6d4);
    padding:20px; text-align:center; position:relative;
}
.teacher-card-photo {
    width:80px; height:80px; border-radius:50%;
    border:4px solid white; object-fit:cover;
    margin:0 auto; display:block;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
}
.teacher-card-avatar {
    width:80px; height:80px; border-radius:50%;
    border:4px solid white; background:rgba(255,255,255,0.2);
    display:flex; align-items:center; justify-content:center;
    font-size:32px; margin:0 auto;
}
.teacher-card-body { padding:16px; }
.teacher-card-name { font-size:16px; font-weight:800; color:#1e293b; margin-bottom:4px; text-align:center; }
.teacher-card-id { text-align:center; margin-bottom:10px; }
.subject-chip {
    display:inline-block; background:rgba(16,185,129,0.1); color:#059669;
    padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;
    margin:2px;
}
.teacher-card-info { font-size:12px; color:#64748b; margin:4px 0; }
.teacher-card-actions { display:flex; gap:8px; margin-top:12px; }
.teachers-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:18px; }
</style>
</head>
<body>
<div class="main-container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="brand-icon">🎓</span> <span>StudentMS</span></h2>
            <p>Admin Panel</p>
        </div>
        <div class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="index.php">📊 Dashboard</a>
            <a href="students.php">👨‍🎓 Students</a>
            <a href="students_list.php">📋 All Students</a>
            <a href="export.php">📤 Export Excel</a>
            <a href="add_exam.php">📘 Exams</a>
            <a href="add_result.php">📊 Add Result</a>
            <a href="view_result.php">📄 View Result</a>
            <a href="add_teacher.php" class="active">👩‍🏫 Teachers</a>
            <a href="library.php">📚 Library</a>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="topbar">
            <h1>👩‍🏫 Teacher Management</h1>
            <div class="topbar-right">
                <div class="admin-badge">Total Teachers: <?php echo $total_teachers; ?></div>
            </div>
        </div>

        <?php if($del_msg == 'deleted'): ?>
        <div style="background:#fee2e2;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:#991b1b;">🗑️ Teacher deleted!</div>
        <?php endif; ?>

        <?php if($msg): ?>
        <div style="background:<?php echo $msg_type=='success'?'#d1fae5':'#fee2e2'; ?>;border-radius:10px;padding:14px 18px;margin-bottom:18px;font-weight:700;color:<?php echo $msg_type=='success'?'#065f46':'#991b1b'; ?>;">
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <!-- ADD TEACHER FORM -->
        <div class="box">
            <div class="box-title">➕ Add New Teacher</div>
            <form method="POST" enctype="multipart/form-data" id="teacherForm">

                <!-- Row 1 -->
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Teacher ID *</label>
                        <input type="text" name="teacher_id" placeholder="e.g. TCH001" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Full Name *</label>
                        <input type="text" name="name" placeholder="Teacher full name" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Phone</label>
                        <input type="text" name="phone" placeholder="Phone number" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                </div>

                <!-- Row 2 -->
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Email</label>
                        <input type="email" name="email" placeholder="Email address" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Password *</label>
                        <input type="password" name="password" placeholder="Set login password" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Photo</label>
                        <input type="file" name="photo" accept="image/*" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;background:white;">
                    </div>
                </div>

                <!-- MULTIPLE SUBJECTS -->
                <div style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:8px;">📚 Subjects (Multiple add kar sakte ho) *</label>
                    <div class="subject-tags" id="subjectTags"></div>
                    <div class="subject-input-row">
                        <input type="text" id="subjectInput" placeholder="Subject likho e.g. Mathematics, Physics..." onkeydown="if(event.key==='Enter'){event.preventDefault();addSubject();}">
                        <button type="button" onclick="addSubject()">+ Add</button>
                    </div>
                    <div id="subjectsHidden"></div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:6px;">💡 Enter press karo ya + Add button dabao</div>
                </div>

                <button type="submit" name="submit" style="background:#4f46e5;color:white;padding:11px 26px;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">➕ Add Teacher</button>
            </form>
        </div>

        <!-- ALL TEACHERS - CARD VIEW -->
        <div class="box">
            <div class="box-title">👩‍🏫 All Teachers (<?php echo $total_teachers; ?>)</div>
            <?php if($total_teachers > 0): ?>
            <div class="teachers-grid">
                <?php
                mysqli_data_seek($teachers_q, 0);
                while($t = mysqli_fetch_assoc($teachers_q)):
                    $subjects = explode(', ', $t['subject']);
                ?>
                <div class="teacher-card">
                    <div class="teacher-card-header">
                        <?php if(!empty($t['photo'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($t['photo']); ?>" class="teacher-card-photo">
                        <?php else: ?>
                        <div class="teacher-card-avatar">👩‍🏫</div>
                        <?php endif; ?>
                    </div>
                    <div class="teacher-card-body">
                        <div class="teacher-card-name"><?php echo htmlspecialchars($t['name']); ?></div>
                        <div class="teacher-card-id">
                            <span style="background:rgba(6,182,212,0.1);color:#0891b2;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?php echo $t['teacher_id']; ?></span>
                        </div>

                        <!-- Subjects chips -->
                        <div style="text-align:center;margin-bottom:10px;">
                            <?php foreach($subjects as $sub): ?>
                            <?php if(trim($sub)): ?>
                            <span class="subject-chip">📖 <?php echo htmlspecialchars(trim($sub)); ?></span>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <div class="teacher-card-info">📞 <?php echo htmlspecialchars($t['phone'] ?? 'N/A'); ?></div>
                        <div class="teacher-card-info">📧 <?php echo htmlspecialchars($t['email'] ?? 'N/A'); ?></div>

                        <div class="teacher-card-actions">
                            <a href="admin_teacher_id_card.php?id=<?php echo $t['id']; ?>" style="flex:1;text-align:center;background:#4f46e5;color:white;padding:7px;border-radius:7px;text-decoration:none;font-size:12px;font-weight:700;">🪪 ID Card</a>
                            <a href="?delete=<?php echo $t['id']; ?>" onclick="return confirm('Delete <?php echo htmlspecialchars($t['name']); ?>?')" style="flex:1;text-align:center;background:#ef4444;color:white;padding:7px;border-radius:7px;text-decoration:none;font-size:12px;font-weight:700;">🗑️ Delete</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div style="text-align:center;padding:40px;color:#64748b;">
                <div style="font-size:48px;margin-bottom:14px;">👩‍🏫</div>
                <p style="font-weight:700;">Koi teacher nahi mila! Upar form se teacher add karo.</p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
let subjects = [];

function addSubject() {
    const input = document.getElementById('subjectInput');
    const val = input.value.trim();
    if(!val) return;
    if(subjects.includes(val)){
        alert('Ye subject already add hai!');
        return;
    }
    subjects.push(val);
    input.value = '';
    renderSubjects();
}

function removeSubject(idx) {
    subjects.splice(idx, 1);
    renderSubjects();
}

function renderSubjects() {
    const tags = document.getElementById('subjectTags');
    const hidden = document.getElementById('subjectsHidden');

    tags.innerHTML = subjects.map((s, i) => `
        <div class="subject-tag">
            📖 ${s}
            <button type="button" onclick="removeSubject(${i})">×</button>
        </div>
    `).join('');

    hidden.innerHTML = subjects.map(s =>
        `<input type="hidden" name="subjects[]" value="${s}">`
    ).join('');
}

// Form submit validation
document.getElementById('teacherForm').addEventListener('submit', function(e){
    if(subjects.length === 0){
        e.preventDefault();
        alert('⚠️ Kam se kam 1 subject add karo!');
        document.getElementById('subjectInput').focus();
    }
});
</script>

</body>
</html>