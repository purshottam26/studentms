<?php
session_start();
include 'db.php';

if(isset($_SESSION['teacher_id'])){
    header("Location: teacher_dashboard.php");
    exit();
}

$error = '';

if(isset($_POST['login'])){
    $tid = trim($_POST['teacher_id']);
    $pass = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM teachers WHERE teacher_id=?");
    $stmt->bind_param("s", $tid);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows == 1){
        $teacher = $res->fetch_assoc();
        if(password_verify($pass, $teacher['password'])){
            $_SESSION['teacher_id'] = $teacher['teacher_id'];
            $_SESSION['teacher_name'] = $teacher['name'];
            header("Location: teacher_dashboard.php");
            exit();
        } else {
            $error = "❌ Wrong password!";
        }
    } else {
        $error = "❌ Teacher ID not found!";
    }
}

// Teachers for suggestion
$teachers_q = mysqli_query($conn, "SELECT teacher_id, name, photo, subject FROM teachers ORDER BY name");
$teachers_list = [];
while($t = mysqli_fetch_assoc($teachers_q)) $teachers_list[] = $t;

$prefill_id = $_GET['id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Login</title>
<link rel="stylesheet" href="style.css">
<style>
.suggest-wrapper { position: relative; }
.suggest-dropdown {
    display: none;
    position: absolute;
    top: 110%;
    left: 0; right: 0;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    max-height: 220px;
    overflow-y: auto;
    z-index: 9999;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}
.suggest-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}
.suggest-item:hover { background: #f8fafc; }
.suggest-item img {
    width: 36px; height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #06b6d4;
    flex-shrink: 0;
}
.suggest-item .av {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(6,182,212,0.1);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.suggest-item .info { flex: 1; }
.suggest-item .info strong { font-size: 13px; display: block; color: #1e293b; }
.suggest-item .info span { font-size: 11px; color: #64748b; }
.suggest-item .tid {
    background: rgba(6,182,212,0.1);
    color: #0891b2;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
}
.no-suggest { padding: 14px; text-align: center; color: #94a3b8; font-size: 13px; }
</style>
</head>
<body class="login-page">
<div class="login-wrapper">
    <div class="login-logo">
        <div class="logo-icon">👩‍🏫</div>
        <h1>Teacher Portal</h1>
        <p>Login to access your dashboard</p>
    </div>
    <div class="login-card">
        <form method="POST" autocomplete="off">

            <div class="form-group">
                <label>🪪 Teacher ID</label>
                <div class="suggest-wrapper">
                    <input type="text" name="teacher_id" id="teacherIdInput"
                        placeholder="Enter your Teacher ID"
                        value="<?php echo htmlspecialchars($prefill_id); ?>"
                        oninput="filterTeachers(this.value)"
                        onfocus="filterTeachers(this.value)"
                        autocomplete="off"
                        required>
                    <div class="suggest-dropdown" id="suggestDropdown">
                        <?php foreach($teachers_list as $t): ?>
                        <div class="suggest-item"
                            data-id="<?php echo htmlspecialchars($t['teacher_id']); ?>"
                            data-name="<?php echo strtolower($t['name']); ?>"
                            data-tid="<?php echo strtolower($t['teacher_id']); ?>"
                            onclick="selectTeacher('<?php echo htmlspecialchars($t['teacher_id']); ?>')">
                            <?php if(!empty($t['photo'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($t['photo']); ?>">
                            <?php else: ?>
                            <div class="av">👩‍🏫</div>
                            <?php endif; ?>
                            <div class="info">
                                <strong><?php echo htmlspecialchars($t['name']); ?></strong>
                                <span>📖 <?php echo htmlspecialchars($t['subject']); ?></span>
                            </div>
                            <span class="tid"><?php echo htmlspecialchars($t['teacher_id']); ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($teachers_list)): ?>
                        <div class="no-suggest">Koi teacher nahi mila!</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>🔒 Password</label>
                <input type="password" name="password" id="passInput"
                    placeholder="Enter your password" required>
            </div>

            <?php if(!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <button type="submit" name="login">🚀 Login</button>
        </form>
        <p style="text-align:center;margin-top:14px;font-size:13px;color:#64748b;">
            <a href="login.php" style="color:#4f46e5;">Admin Login →</a> &nbsp;|&nbsp;
            <a href="student_login.php" style="color:#4f46e5;">Student Login →</a>
        </p>
    </div>
</div>

<script>
const teachers = <?php echo json_encode($teachers_list); ?>;

function filterTeachers(val) {
    const dropdown = document.getElementById('suggestDropdown');
    const items = dropdown.querySelectorAll('.suggest-item');
    const v = val.toLowerCase().trim();

    // Agar prefill ID hai to auto focus password
    <?php if($prefill_id): ?>
    document.getElementById('passInput').focus();
    <?php endif; ?>

    if(v.length === 0){
        // Sab dikhao
        items.forEach(i => i.style.display = 'flex');
        dropdown.style.display = 'block';
        return;
    }

    let found = 0;
    items.forEach(function(item){
        const name = item.getAttribute('data-name');
        const tid = item.getAttribute('data-tid');
        if(name.includes(v) || tid.includes(v)){
            item.style.display = 'flex';
            found++;
        } else {
            item.style.display = 'none';
        }
    });

    // No result
    const noSuggest = dropdown.querySelector('.no-suggest');
    if(found === 0){
        if(!noSuggest){
            const div = document.createElement('div');
            div.className = 'no-suggest temp';
            div.textContent = '🔍 Koi teacher nahi mila!';
            dropdown.appendChild(div);
        }
    } else {
        const temp = dropdown.querySelector('.temp');
        if(temp) temp.remove();
    }

    dropdown.style.display = 'block';
}

function selectTeacher(tid) {
    document.getElementById('teacherIdInput').value = tid;
    document.getElementById('suggestDropdown').style.display = 'none';
    document.getElementById('passInput').focus();
}

// Click bahar pe dropdown band
document.addEventListener('click', function(e){
    if(!e.target.closest('.suggest-wrapper')){
        document.getElementById('suggestDropdown').style.display = 'none';
    }
});
</script>

</body>
</html>