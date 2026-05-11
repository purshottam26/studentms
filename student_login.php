<?php
session_start();
include 'db.php';

if(isset($_SESSION['student_id'])){
    header("Location: student_dashboard.php");
    exit();
}

$error = '';

if(isset($_POST['login'])){
    $sid = trim($_POST['student_id']);
    $pass = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM student WHERE student_id=?");
    $stmt->bind_param("s", $sid);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows == 1){
        $student = $res->fetch_assoc();
        if(password_verify($pass, $student['password'])){
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['name'];
            header("Location: student_dashboard.php");
            exit();
        } else {
            $error = "❌ Wrong password!";
        }
    } else {
        $error = "❌ Student ID not found!";
    }
}

// Students for suggestion
$students_q = mysqli_query($conn, "SELECT student_id, name, photo, course FROM student ORDER BY name");
$students_list = [];
while($s = mysqli_fetch_assoc($students_q)) $students_list[] = $s;

$prefill_id = $_GET['id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Login</title>
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
    border: 2px solid #10b981;
    flex-shrink: 0;
}
.suggest-item .av {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(16,185,129,0.1);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.suggest-item .info { flex: 1; }
.suggest-item .info strong { font-size: 13px; display: block; color: #1e293b; }
.suggest-item .info span { font-size: 11px; color: #64748b; }
.suggest-item .sid {
    background: rgba(16,185,129,0.1);
    color: #059669;
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
        <div class="logo-icon">👨‍🎓</div>
        <h1>Student Portal</h1>
        <p>Login to access your dashboard</p>
    </div>
    <div class="login-card">
        <form method="POST" autocomplete="off">

            <div class="form-group">
                <label>🪪 Student ID</label>
                <div class="suggest-wrapper">
                    <input type="text" name="student_id" id="studentIdInput"
                        placeholder="Enter your Student ID"
                        value="<?php echo htmlspecialchars($prefill_id); ?>"
                        oninput="filterStudents(this.value)"
                        onfocus="filterStudents(this.value)"
                        autocomplete="off"
                        required>
                    <div class="suggest-dropdown" id="suggestDropdown">
                        <?php foreach($students_list as $s): ?>
                        <div class="suggest-item"
                            data-name="<?php echo strtolower($s['name']); ?>"
                            data-sid="<?php echo strtolower($s['student_id']); ?>"
                            onclick="selectStudent('<?php echo htmlspecialchars($s['student_id']); ?>')">
                            <?php if(!empty($s['photo'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($s['photo']); ?>">
                            <?php else: ?>
                            <div class="av">👨‍🎓</div>
                            <?php endif; ?>
                            <div class="info">
                                <strong><?php echo htmlspecialchars($s['name']); ?></strong>
                                <span>📚 <?php echo htmlspecialchars($s['course']); ?></span>
                            </div>
                            <span class="sid"><?php echo htmlspecialchars($s['student_id']); ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($students_list)): ?>
                        <div class="no-suggest">Koi student nahi mila!</div>
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
            <a href="teacher_login.php" style="color:#4f46e5;">Teacher Login →</a>
        </p>
    </div>
</div>

<script>
function filterStudents(val) {
    const dropdown = document.getElementById('suggestDropdown');
    const items = dropdown.querySelectorAll('.suggest-item');
    const v = val.toLowerCase().trim();

    if(v.length === 0){
        items.forEach(i => i.style.display = 'flex');
        dropdown.style.display = 'block';
        return;
    }

    let found = 0;
    items.forEach(function(item){
        const name = item.getAttribute('data-name');
        const sid = item.getAttribute('data-sid');
        if(name.includes(v) || sid.includes(v)){
            item.style.display = 'flex';
            found++;
        } else {
            item.style.display = 'none';
        }
    });

    const temp = dropdown.querySelector('.temp');
    if(found === 0){
        if(!temp){
            const div = document.createElement('div');
            div.className = 'no-suggest temp';
            div.textContent = '🔍 Koi student nahi mila!';
            dropdown.appendChild(div);
        }
    } else {
        if(temp) temp.remove();
    }

    dropdown.style.display = 'block';
}

function selectStudent(sid) {
    document.getElementById('studentIdInput').value = sid;
    document.getElementById('suggestDropdown').style.display = 'none';
    document.getElementById('passInput').focus();
}

document.addEventListener('click', function(e){
    if(!e.target.closest('.suggest-wrapper')){
        document.getElementById('suggestDropdown').style.display = 'none';
    }
});

// Prefill hone pe password pe focus
<?php if($prefill_id): ?>
window.onload = function(){ document.getElementById('passInput').focus(); }
<?php endif; ?>
</script>

</body>
</html>