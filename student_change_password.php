<?php
session_start();
if(!isset($_SESSION['student_id'])){
    header("Location: student_login.php");
    exit();
}
include 'db.php';

$msg = '';
$msg_type = '';
$sid = $_SESSION['student_id'];

if(isset($_POST['change_pass'])){
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $q = mysqli_query($conn, "SELECT * FROM student WHERE student_id='$sid'");
    $student = mysqli_fetch_assoc($q);

    if(!password_verify($old_pass, $student['password'])){
        $msg = "❌ Old password galat hai!";
        $msg_type = 'error';
    } elseif(strlen($new_pass) < 6){
        $msg = "❌ Password kam se kam 6 characters ka hona chahiye!";
        $msg_type = 'error';
    } elseif($new_pass !== $confirm_pass){
        $msg = "❌ Password match nahi kar raha!";
        $msg_type = 'error';
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE student SET password='$hashed' WHERE student_id='$sid'");
        $msg = "✅ Password successfully change ho gaya!";
        $msg_type = 'success';
    }
}

$q = mysqli_query($conn, "SELECT * FROM student WHERE student_id='$sid'");
$student = mysqli_fetch_assoc($q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title>
<link rel="stylesheet" href="style.css">
<style>
.pass-card { max-width:500px; margin:30px auto; background:white; border-radius:16px; overflow:hidden; box-shadow:0 10px 40px rgba(79,70,229,0.15); }
.pass-header { background:linear-gradient(135deg,#4f46e5,#06b6d4); padding:30px; text-align:center; color:white; }
.pass-header h2 { font-size:22px; margin-bottom:6px; }
.pass-body { padding:30px; }
.pass-field { margin-bottom:18px; }
.pass-field label { font-size:12px; font-weight:700; color:#64748b; display:block; margin-bottom:6px; text-transform:uppercase; }
.pass-input-wrap { position:relative; }
.pass-input { width:100%; padding:12px 44px 12px 14px; border:2px solid #e2e8f0; border-radius:10px; font-size:14px; font-family:inherit; outline:none; transition:all 0.3s; }
.pass-input:focus { border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
.eye-btn { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:18px; }
.submit-btn { width:100%; padding:13px; background:linear-gradient(135deg,#4f46e5,#06b6d4); color:white; border:none; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer; font-family:inherit; }
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
            <a href="student_change_password.php" class="active">🔐 Change Password</a>
        </div>
        <div class="sidebar-footer">
            <a href="student_logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="topbar">
            <h1>🔐 Change Password</h1>
            <div class="topbar-right">
                <div class="admin-badge">👤 <?php echo htmlspecialchars($student['name']); ?></div>
            </div>
        </div>

        <?php if($msg): ?>
        <div style="max-width:500px;margin:0 auto 20px;background:<?php echo $msg_type=='success'?'#d1fae5':'#fee2e2'; ?>;border-left:4px solid <?php echo $msg_type=='success'?'#10b981':'#ef4444'; ?>;padding:14px 18px;border-radius:10px;font-weight:700;color:<?php echo $msg_type=='success'?'#065f46':'#991b1b'; ?>;">
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <div class="pass-card">
            <div class="pass-header">
                <div style="font-size:48px;margin-bottom:10px;">🔐</div>
                <h2>Password Change Karo</h2>
                <p>Security ke liye strong password use karo</p>
            </div>
            <div class="pass-body">
                <form method="POST" action="student_change_password.php">
                    <div class="pass-field">
                        <label>🔑 Old Password</label>
                        <div class="pass-input-wrap">
                            <input type="password" name="old_password" id="old_pass" class="pass-input" placeholder="Purana password daalo" required>
                            <button type="button" class="eye-btn" onclick="togglePass('old_pass',this)">👁️</button>
                        </div>
                    </div>
                    <div class="pass-field">
                        <label>🔒 New Password</label>
                        <div class="pass-input-wrap">
                            <input type="password" name="new_password" id="new_pass" class="pass-input" placeholder="Naya password daalo" required oninput="checkStrength(this.value)">
                            <button type="button" class="eye-btn" onclick="togglePass('new_pass',this)">👁️</button>
                        </div>
                        <div id="strength_bar" style="height:4px;border-radius:4px;margin-top:6px;background:#e2e8f0;"></div>
                        <div id="strength_text" style="font-size:11px;font-weight:700;margin-top:4px;"></div>
                    </div>
                    <div class="pass-field">
                        <label>✅ Confirm Password</label>
                        <div class="pass-input-wrap">
                            <input type="password" name="confirm_password" id="confirm_pass" class="pass-input" placeholder="Password dobara daalo" required oninput="checkMatch()">
                            <button type="button" class="eye-btn" onclick="togglePass('confirm_pass',this)">👁️</button>
                        </div>
                        <div id="match_text" style="font-size:11px;font-weight:700;margin-top:4px;"></div>
                    </div>
                    <button type="submit" name="change_pass" class="submit-btn">🔐 Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? '👁️' : '🙈';
}
function checkStrength(val) {
    const bar = document.getElementById('strength_bar');
    const text = document.getElementById('strength_text');
    let s = 0;
    if(val.length >= 6) s++;
    if(val.match(/[A-Z]/)) s++;
    if(val.match(/[0-9]/)) s++;
    if(val.match(/[^A-Za-z0-9]/)) s++;
    const c = ['#ef4444','#f59e0b','#10b981','#4f46e5'];
    const l = ['Weak 😟','Medium 😐','Strong 💪','Very Strong 🔥'];
    if(val.length === 0){ bar.style.background='#e2e8f0'; text.textContent=''; return; }
    bar.style.background = c[s-1]||'#ef4444';
    text.style.color = c[s-1]||'#ef4444';
    text.textContent = l[s-1]||'Weak 😟';
}
function checkMatch() {
    const n = document.getElementById('new_pass').value;
    const c = document.getElementById('confirm_pass').value;
    const t = document.getElementById('match_text');
    if(!c.length){ t.textContent=''; return; }
    if(n===c){ t.style.color='#10b981'; t.textContent='✅ Match!'; }
    else { t.style.color='#ef4444'; t.textContent='❌ Match nahi!'; }
}
</script>
</body>
</html>