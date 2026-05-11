<?php
session_start();
include 'db.php';

if(isset($_SESSION['admin'])){
    header("Location: index.php");
    exit();
}

$error = '';

if(isset($_POST['login'])){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $q = mysqli_query($conn, "SELECT * FROM admin WHERE username='".mysqli_real_escape_string($conn,$username)."'");
    $admin = mysqli_fetch_assoc($q);

    if($admin){
        if(password_verify($password, $admin['password']) || $password === $admin['password']){
            $_SESSION['admin'] = $admin['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "❌ Wrong password!";
        }
    } else {
        $error = "❌ Username not found!";
    }
}

// Students list for suggestion
$students_q = mysqli_query($conn, "SELECT student_id, name, photo, course FROM student ORDER BY name");
$students_list = [];
while($s = mysqli_fetch_assoc($students_q)) $students_list[] = $s;

// Teachers list for suggestion
$teachers_q = mysqli_query($conn, "SELECT teacher_id, name, photo, subject FROM teachers ORDER BY name");
$teachers_list = [];
while($t = mysqli_fetch_assoc($teachers_q)) $teachers_list[] = $t;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudentMS — Login</title>
<link rel="stylesheet" href="style.css">
<style>
.portal-select {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
    margin-bottom: 28px;
}
.portal-btn {
    padding: 16px 10px;
    border-radius: 12px;
    text-align: center;
    font-weight: 700;
    font-size: 13px;
    transition: all 0.2s;
    border: 2px solid transparent;
    cursor: pointer;
    text-decoration: none;
}
.portal-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
.portal-admin { background: linear-gradient(135deg,#4f46e5,#6366f1); color: white; }
.portal-student { background: linear-gradient(135deg,#10b981,#059669); color: white; }
.portal-teacher { background: linear-gradient(135deg,#06b6d4,#0891b2); color: white; }
.portal-icon { font-size: 28px; display: block; margin-bottom: 6px; }
.divider { display:flex; align-items:center; gap:12px; margin-bottom:20px; }
.divider hr { flex:1; border:none; border-top:1px solid #e2e8f0; }
.divider span { font-size:12px; color:#94a3b8; font-weight:700; }

/* Modal */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active { display: flex; }
.modal-box {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 480px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
}
.modal-header {
    padding: 20px 24px;
    color: white;
    display: flex;
    align-items: center;
    gap: 12px;
}
.modal-search {
    padding: 14px 20px;
    border-bottom: 1px solid #e2e8f0;
}
.modal-search input {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    transition: border-color 0.2s;
}
.modal-search input:focus { border-color: #4f46e5; }
.modal-list {
    overflow-y: auto;
    flex: 1;
    padding: 8px 0;
}
.modal-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    cursor: pointer;
    transition: background 0.15s;
    text-decoration: none;
    color: #1e293b;
}
.modal-item:hover { background: #f8fafc; }
.modal-item img {
    width: 42px; height: 42px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.modal-item .avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.modal-item .info { flex: 1; }
.modal-item .info strong { font-size: 14px; display: block; color: #1e293b; }
.modal-item .info span { font-size: 12px; color: #64748b; }
.modal-item .id-badge {
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
}
.modal-close {
    margin-left: auto;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 30px; height: 30px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
}
.no-result { text-align:center; padding:30px; color:#94a3b8; font-size:13px; }
</style>
</head>
<body class="login-page">

<!-- STUDENT MODAL -->
<div class="modal-overlay" id="studentModal">
    <div class="modal-box">
        <div class="modal-header" style="background:linear-gradient(135deg,#10b981,#059669);">
            <span style="font-size:24px;">👨‍🎓</span>
            <div>
                <div style="font-weight:800;font-size:16px;">Student Login</div>
                <div style="font-size:12px;opacity:0.85;">Apna ID select karo</div>
            </div>
            <button class="modal-close" onclick="closeModal('studentModal')">✕</button>
        </div>
        <div class="modal-search">
            <input type="text" id="studentSearch" placeholder="🔍 Name ya ID se search karo..." oninput="filterModal('student', this.value)">
        </div>
        <div class="modal-list" id="studentList">
            <?php foreach($students_list as $s): ?>
            <a href="student_login.php?id=<?php echo urlencode($s['student_id']); ?>" class="modal-item student-item"
               data-name="<?php echo strtolower($s['name']); ?>"
               data-id="<?php echo strtolower($s['student_id']); ?>">
                <?php if(!empty($s['photo'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($s['photo']); ?>">
                <?php else: ?>
                <div class="avatar" style="background:rgba(16,185,129,0.1);">👨‍🎓</div>
                <?php endif; ?>
                <div class="info">
                    <strong><?php echo htmlspecialchars($s['name']); ?></strong>
                    <span><?php echo htmlspecialchars($s['course']); ?></span>
                </div>
                <span class="id-badge" style="background:rgba(16,185,129,0.1);color:#059669;"><?php echo htmlspecialchars($s['student_id']); ?></span>
            </a>
            <?php endforeach; ?>
            <?php if(empty($students_list)): ?>
            <div class="no-result">Koi student nahi mila!</div>
            <?php endif; ?>
        </div>
        <div style="padding:14px 20px;border-top:1px solid #e2e8f0;text-align:center;">
            <a href="student_login.php" style="font-size:13px;color:#10b981;font-weight:700;">Direct Login karo →</a>
        </div>
    </div>
</div>

<!-- TEACHER MODAL -->
<div class="modal-overlay" id="teacherModal">
    <div class="modal-box">
        <div class="modal-header" style="background:linear-gradient(135deg,#06b6d4,#0891b2);">
            <span style="font-size:24px;">👩‍🏫</span>
            <div>
                <div style="font-weight:800;font-size:16px;">Teacher Login</div>
                <div style="font-size:12px;opacity:0.85;">Apna ID select karo</div>
            </div>
            <button class="modal-close" onclick="closeModal('teacherModal')">✕</button>
        </div>
        <div class="modal-search">
            <input type="text" id="teacherSearch" placeholder="🔍 Name ya ID se search karo..." oninput="filterModal('teacher', this.value)">
        </div>
        <div class="modal-list" id="teacherList">
            <?php foreach($teachers_list as $t): ?>
            <a href="teacher_login.php?id=<?php echo urlencode($t['teacher_id']); ?>" class="modal-item teacher-item"
               data-name="<?php echo strtolower($t['name']); ?>"
               data-id="<?php echo strtolower($t['teacher_id']); ?>">
                <?php if(!empty($t['photo'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($t['photo']); ?>">
                <?php else: ?>
                <div class="avatar" style="background:rgba(6,182,212,0.1);">👩‍🏫</div>
                <?php endif; ?>
                <div class="info">
                    <strong><?php echo htmlspecialchars($t['name']); ?></strong>
                    <span><?php echo htmlspecialchars($t['subject']); ?></span>
                </div>
                <span class="id-badge" style="background:rgba(6,182,212,0.1);color:#0891b2;"><?php echo htmlspecialchars($t['teacher_id']); ?></span>
            </a>
            <?php endforeach; ?>
            <?php if(empty($teachers_list)): ?>
            <div class="no-result">Koi teacher nahi mila!</div>
            <?php endif; ?>
        </div>
        <div style="padding:14px 20px;border-top:1px solid #e2e8f0;text-align:center;">
            <a href="teacher_login.php" style="font-size:13px;color:#06b6d4;font-weight:700;">Direct Login karo →</a>
        </div>
    </div>
</div>

<div class="login-wrapper">
    <div class="login-logo">
        <div class="logo-icon">🎓</div>
        <h1>StudentMS</h1>
        <p>Student Management System</p>
    </div>
    <div class="login-card">

        <!-- PORTAL SELECT -->
        <div class="portal-select">
            <a href="login.php" class="portal-btn portal-admin">
                <span class="portal-icon">👤</span>
                Admin Login
            </a>
            <button onclick="openModal('studentModal')" class="portal-btn portal-student" style="border:none;font-family:inherit;">
                <span class="portal-icon">👨‍🎓</span>
                Student Login
            </button>
            <button onclick="openModal('teacherModal')" class="portal-btn portal-teacher" style="border:none;font-family:inherit;">
                <span class="portal-icon">👩‍🏫</span>
                Teacher Login
            </button>
        </div>

        <div class="divider">
            <hr>
            <span>ADMIN LOGIN</span>
            <hr>
        </div>

        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label>👤 Username</label>
                <input type="text" name="username" placeholder="Enter admin username" required>
            </div>
            <div class="form-group">
                <label>🔒 Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <?php if(!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            <button type="submit" name="login">🚀 Login as Admin</button>
        </form>

    </div>
</div>

<script>
function openModal(id){
    document.getElementById(id).classList.add('active');
    // Focus search
    setTimeout(() => {
        if(id === 'studentModal') document.getElementById('studentSearch').focus();
        else document.getElementById('teacherSearch').focus();
    }, 100);
}

function closeModal(id){
    document.getElementById(id).classList.remove('active');
}

// Close on overlay click
document.querySelectorAll('.modal-overlay').forEach(function(overlay){
    overlay.addEventListener('click', function(e){
        if(e.target === overlay) overlay.classList.remove('active');
    });
});

function filterModal(type, val){
    const v = val.toLowerCase().trim();
    const items = document.querySelectorAll('.' + type + '-item');
    let found = 0;

    items.forEach(function(item){
        const name = item.getAttribute('data-name');
        const id = item.getAttribute('data-id');
        if(v === '' || name.includes(v) || id.includes(v)){
            item.style.display = 'flex';
            found++;
        } else {
            item.style.display = 'none';
        }
    });

    // Show no result
    const listId = type === 'student' ? 'studentList' : 'teacherList';
    const list = document.getElementById(listId);
    const noResult = list.querySelector('.no-result');

    if(found === 0){
        if(!noResult){
            const div = document.createElement('div');
            div.className = 'no-result temp-no-result';
            div.textContent = '🔍 Koi nahi mila!';
            list.appendChild(div);
        }
    } else {
        const temp = list.querySelector('.temp-no-result');
        if(temp) temp.remove();
    }
}

// ESC key se close
document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
        document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
    }
});
</script>

</body>
</html>