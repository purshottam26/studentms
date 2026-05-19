<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

// Ensure dob and doj columns exist in teachers table
$check_dob = mysqli_query($conn, "SHOW COLUMNS FROM teachers LIKE 'dob'");
if(mysqli_num_rows($check_dob) == 0){
    mysqli_query($conn, "ALTER TABLE teachers ADD COLUMN dob DATE NULL");
}
$check_doj = mysqli_query($conn, "SHOW COLUMNS FROM teachers LIKE 'doj'");
if(mysqli_num_rows($check_doj) == 0){
    mysqli_query($conn, "ALTER TABLE teachers ADD COLUMN doj DATE NULL");
}

$msg = '';
$msg_type = '';
$show_cred = false;
$cred_tid = '';
$cred_pass = '';

// Auto generate teacher ID
$last_t = mysqli_fetch_assoc(mysqli_query($conn, "SELECT teacher_id FROM teachers ORDER BY id DESC LIMIT 1"));
$last_tid = $last_t['teacher_id'] ?? '';
$next_tnum = 1;
if(preg_match('/(\d+)$/', $last_tid, $m)){
    $next_tnum = intval($m[1]) + 1;
}
$auto_tid = 'TCH-' . date('dmY') . '-' . str_pad($next_tnum, 3, '0', STR_PAD_LEFT);

// Add Teacher
if(isset($_POST['submit'])){
    $tid = mysqli_real_escape_string($conn, $_POST['teacher_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : NULL;
    $doj = !empty($_POST['doj']) ? $_POST['doj'] : NULL;
    $plain_pass = $_POST['password'];
    $password = password_hash($plain_pass, PASSWORD_DEFAULT);

    $error = '';
    $today = date('Y-m-d');

    // DOB validation
    if($dob){
        if($dob >= $today){
            $error = "❌ Date of Birth future mein nahi ho sakti!";
        } else {
            $age = date_diff(date_create($dob), date_create($today))->y;
            if($age < 18){
                $error = "❌ Teacher ki age kam se kam 18 saal honi chahiye!";
            } elseif($age > 70){
                $error = "❌ Teacher ki age 70 saal se zyada nahi ho sakti!";
            }
        }
    }

    // DOJ validation aur DOB-DOJ gap check
    if($doj && !$error){
        if($doj > $today){
            $error = "❌ Date of Joining future mein nahi ho sakti!";
        }
        if($dob && $doj < $dob){
            $error = "❌ Date of Joining, Date of Birth se pehle nahi ho sakti!";
        }
        if($dob && !$error){
            $gap = date_diff(date_create($dob), date_create($doj))->y;
            if($gap < 20){
                $error = "❌ Joining ke waqt teacher ki age kam se kam 20 saal honi chahiye!";
            }
        }
    }

    if($error){
        $msg = $error;
        $msg_type = 'error';
    } else {
    // Multiple subjects
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
        $dob_val = $dob ? "'$dob'" : "NULL";
        $doj_val = $doj ? "'$doj'" : "NULL";
        $r = mysqli_query($conn, "INSERT INTO teachers (teacher_id,name,subject,phone,email,photo,password,dob,doj)
        VALUES ('$tid','$name','$subject','$phone','$email','$photo','$password',$dob_val,$doj_val)");
        if($r){
            $msg = "✅ Teacher added successfully!";
            $msg_type = 'success';
            $show_cred = true;
            $cred_tid = $tid;
            $cred_pass = $plain_pass;
        } else {
            $msg = "❌ Error: " . mysqli_error($conn);
            $msg_type = 'error';
        }
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
.subject-tag { background:rgba(79,70,229,0.1); color:#4f46e5; padding:5px 12px; border-radius:20px; font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px; }
.subject-tag button { background:none; border:none; cursor:pointer; color:#ef4444; font-size:14px; font-weight:800; padding:0; line-height:1; }
.subject-input-row { display:flex; gap:8px; }
.subject-input-row input { flex:1; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; }
.subject-input-row button { padding:10px 16px; background:#4f46e5; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:700; font-size:14px; }
.teacher-card { background:white; border-radius:14px; padding:0; box-shadow:0 4px 20px rgba(79,70,229,0.08); overflow:hidden; transition:transform 0.2s; border:1px solid #e2e8f0; }
.teacher-card:hover { transform:translateY(-4px); box-shadow:0 8px 30px rgba(79,70,229,0.15); }
.teacher-card-header { background:linear-gradient(135deg,#4f46e5,#06b6d4); padding:20px; text-align:center; }
.teacher-card-photo { width:80px; height:80px; border-radius:50%; border:4px solid white; object-fit:cover; margin:0 auto; display:block; box-shadow:0 4px 12px rgba(0,0,0,0.2); }
.teacher-card-avatar { width:80px; height:80px; border-radius:50%; border:4px solid white; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; font-size:32px; margin:0 auto; }
.teacher-card-body { padding:16px; }
.teacher-card-name { font-size:16px; font-weight:800; color:#1e293b; margin-bottom:4px; text-align:center; }
.teacher-card-id { text-align:center; margin-bottom:10px; }
.subject-chip { display:inline-block; background:rgba(16,185,129,0.1); color:#059669; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; margin:2px; }
.teacher-card-info { font-size:12px; color:#64748b; margin:4px 0; }
.teacher-card-actions { display:flex; gap:8px; margin-top:12px; }
.teachers-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:18px; }
.auto-id-box { background:linear-gradient(135deg,rgba(79,70,229,0.08),rgba(6,182,212,0.08)); border:2px dashed #4f46e5; border-radius:10px; padding:12px 16px; display:flex; align-items:center; gap:12px; }
.radio-group { display:flex; gap:16px; margin-bottom:8px; }
.radio-group label { display:flex; align-items:center; gap:6px; font-size:13px; font-weight:700; cursor:pointer; }

/* Credentials Card */
.cred-card { background:white; border-radius:16px; overflow:hidden; box-shadow:0 10px 40px rgba(6,182,212,0.2); border:2px solid #06b6d4; margin-bottom:20px; }
.cred-header { background:linear-gradient(135deg,#06b6d4,#4f46e5); padding:20px; color:white; text-align:center; }
.cred-body { padding:22px; }
.cred-box { background:#f8fafc; border:2px dashed #06b6d4; border-radius:12px; padding:16px; margin-bottom:12px; }
.cred-box label { font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; display:block; margin-bottom:6px; }
.cred-value { font-size:20px; font-weight:800; color:#06b6d4; letter-spacing:1px; font-family:monospace; }
</style>
</head>
<body>
<div class="main-container">

    <?php include_once('sidebar.php'); ?>

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

        <!-- CREDENTIALS CARD — Teacher add hone ke baad -->
        <?php if($show_cred): ?>
        <div class="cred-card">
            <div class="cred-header">
                <div style="font-size:28px;margin-bottom:6px;">👩‍🏫</div>
                <h2 style="font-size:16px;font-weight:800;margin-bottom:4px;">Teacher Login Credentials</h2>
                <p style="font-size:12px;opacity:0.85;">Ye credentials teacher ko de do</p>
            </div>
            <div class="cred-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div class="cred-box">
                        <label>🪪 Login ID (Teacher ID)</label>
                        <div class="cred-value"><?php echo htmlspecialchars($cred_tid); ?></div>
                    </div>
                    <div class="cred-box" style="border-color:#10b981;">
                        <label>🔒 Password</label>
                        <div class="cred-value" style="color:#10b981;"><?php echo htmlspecialchars($cred_pass); ?></div>
                    </div>
                </div>
                <div style="background:#fef3c7;border-left:4px solid #f59e0b;padding:10px 14px;border-radius:8px;font-size:12px;color:#92400e;font-weight:700;margin-bottom:14px;">
                    ⚠️ Ye credentials print karke teacher ko de do. Login ke baad password change karna zaroori hai!
                </div>
                <div style="display:flex;gap:10px;">
                    <a href="teacher_credentials.php?tid=<?php echo urlencode($cred_tid); ?>&pass=<?php echo urlencode($cred_pass); ?>"
                       target="_blank"
                       style="flex:1;text-align:center;padding:11px;background:#06b6d4;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">
                        🖨️ Print Full Credentials
                    </a>
                    <a href="admin_teacher_id_card.php?id=<?php echo mysqli_insert_id($conn); ?>"
                       target="_blank"
                       style="flex:1;text-align:center;padding:11px;background:#4f46e5;color:white;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">
                        🪪 Print ID Card
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ADD TEACHER FORM -->
        <div class="box">
            <div class="box-title">➕ Add New Teacher</div>
            <form method="POST" enctype="multipart/form-data" id="teacherForm">

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px;">

                    <!-- TEACHER ID AUTO/MANUAL -->
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Teacher ID *</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="tid_type" value="auto" checked onchange="toggleTID(this.value)">
                                🔄 Auto
                            </label>
                            <label>
                                <input type="radio" name="tid_type" value="manual" onchange="toggleTID(this.value)">
                                ✏️ Manual
                            </label>
                        </div>
                        <div class="auto-id-box" id="auto_tid_box">
                            <span style="font-size:20px;">🪪</span>
                            <div>
                                <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;">Auto Generated</div>
                                <div style="font-size:16px;font-weight:800;color:#4f46e5;"><?php echo $auto_tid; ?></div>
                            </div>
                            <input type="hidden" name="teacher_id" id="auto_tid_input" value="<?php echo $auto_tid; ?>">
                        </div>
                        <div id="manual_tid_box" style="display:none;margin-top:6px;">
                            <input type="text" id="manual_tid_input" placeholder="e.g. TCH001" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                        </div>
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

                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Date of Birth</label>
                        <input type="date" name="dob" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Date of Joining</label>
                        <input type="date" name="doj" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Password *</label>
                        <input type="password" name="password" placeholder="Set login password" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Photo</label>
                        <input type="file" name="photo" accept="image/*" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;background:white;">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:8px;">📚 Subjects *</label>
                    <div class="subject-tags" id="subjectTags"></div>
                    <div class="subject-input-row">
                        <input type="text" id="subjectInput" placeholder="Subject likho e.g. Mathematics, Physics..." onkeydown="if(event.key==='Enter'){event.preventDefault();addSubject();}">
                        <button type="button" onclick="addSubject()">+ Add</button>
                    </div>
                    <div id="subjectsHidden"></div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:6px;">💡 Enter press karo ya + Add button dabao</div>
                </div>

                <div style="background:#fef3c7;border-left:4px solid #f59e0b;padding:10px 14px;border-radius:8px;font-size:12px;color:#92400e;font-weight:700;margin-bottom:14px;">
                    ℹ️ DOB aur DOJ mein 20 saal ka gap zaroori hai. Joining ke waqt teacher ki age kam se kam 20 saal honi chahiye.
                </div>

                <button type="submit" name="submit" style="background:#4f46e5;color:white;padding:11px 26px;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">➕ Add Teacher</button>
            </form>
        </div>

        <!-- ALL TEACHERS CARD VIEW -->
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
                            <a href="admin_teacher_view.php?id=<?php echo $t['id']; ?>" style="flex:1;text-align:center;background:#10b981;color:white;padding:7px;border-radius:7px;text-decoration:none;font-size:12px;font-weight:700;">👁️ View</a>
                            <a href="admin_teacher_edit.php?id=<?php echo $t['id']; ?>" style="flex:1;text-align:center;background:#4f46e5;color:white;padding:7px;border-radius:7px;text-decoration:none;font-size:12px;font-weight:700;">✏️ Edit</a>
                            <a href="admin_teacher_id_card.php?id=<?php echo $t['id']; ?>" style="flex:1;text-align:center;background:#0ea5e9;color:white;padding:7px;border-radius:7px;text-decoration:none;font-size:12px;font-weight:700;">🪪 ID Card</a>
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
    if(subjects.includes(val)){ alert('Ye subject already add hai!'); return; }
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

function toggleTID(val){
    const autoBox = document.getElementById('auto_tid_box');
    const manualBox = document.getElementById('manual_tid_box');
    const autoInput = document.getElementById('auto_tid_input');
    const manualInput = document.getElementById('manual_tid_input');
    if(val === 'auto'){
        autoBox.style.display = 'flex';
        manualBox.style.display = 'none';
        autoInput.name = 'teacher_id';
        manualInput.name = '';
        manualInput.required = false;
    } else {
        autoBox.style.display = 'none';
        manualBox.style.display = 'block';
        autoInput.name = '';
        manualInput.name = 'teacher_id';
        manualInput.required = true;
    }
}

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
