<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$msg = '';
$msg_type = '';

if(isset($_POST['change_pass'])){
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Admin ka current password database se lo
    $admin_q = mysqli_query($conn, "SELECT * FROM admin WHERE username='".$_SESSION['admin']."'");
    $admin = mysqli_fetch_assoc($admin_q);

    if(!password_verify($old_pass, $admin['password'])){
        // Plain text bhi check karo (purane projects ke liye)
        if($old_pass !== $admin['password']){
            $msg = "❌ Old password galat hai!";
            $msg_type = 'error';
        } else {
            $goto_update = true;
        }
    } else {
        $goto_update = true;
    }

    if(isset($goto_update)){
        if(strlen($new_pass) < 6){
            $msg = "❌ New password kam se kam 6 characters ka hona chahiye!";
            $msg_type = 'error';
        } elseif($new_pass !== $confirm_pass){
            $msg = "❌ New password aur Confirm password match nahi kar rahe!";
            $msg_type = 'error';
        } else {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE admin SET password='$hashed' WHERE username='".$_SESSION['admin']."'");
            $msg = "✅ Password successfully change ho gaya!";
            $msg_type = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title>
<link rel="stylesheet" href="style.css">
<style>
.pass-card {
    max-width: 500px;
    margin: 30px auto;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(79,70,229,0.15);
}
.pass-header {
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    padding: 30px;
    text-align: center;
    color: white;
}
.pass-header h2 { font-size: 22px; margin-bottom: 6px; }
.pass-header p { font-size: 13px; opacity: 0.85; }
.pass-body { padding: 30px; }
.pass-field { margin-bottom: 18px; }
.pass-field label {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    display: block;
    margin-bottom: 6px;
    text-transform: uppercase;
}
.pass-input-wrap { position: relative; }
.pass-input {
    width: 100%;
    padding: 12px 44px 12px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    transition: all 0.3s;
}
.pass-input:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
}
.eye-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    color: #64748b;
}
.strength-bar {
    height: 4px;
    border-radius: 4px;
    margin-top: 6px;
    transition: all 0.3s;
    background: #e2e8f0;
}
.strength-text {
    font-size: 11px;
    font-weight: 700;
    margin-top: 4px;
}
.submit-btn {
    width: 100%;
    padding: 13px;
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s;
    font-family: inherit;
}
.submit-btn:hover { opacity: 0.9; }
.rules {
    background: #f8fafc;
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 18px;
    font-size: 12px;
    color: #64748b;
}
.rules li { margin-bottom: 4px; }
</style>
</head>
<body>
<div class="main-container">

    <?php include_once('sidebar.php'); ?>

    <div class="content">
        <div class="topbar">
            <h1>🔐 Change Password</h1>
            <div class="topbar-right">
                <div class="admin-badge">👤 <?php echo htmlspecialchars($_SESSION['admin']); ?></div>
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
                <h2>Admin Password Change</h2>
                <p>Security ke liye strong password use karo</p>
            </div>
            <div class="pass-body">

                <div class="rules">
                    <strong>📋 Password Rules:</strong>
                    <ul style="margin-top:8px;padding-left:16px;">
                        <li>Kam se kam 6 characters</li>
                        <li>Ek Capital letter hona chahiye</li>
                        <li>Ek Number hona chahiye</li>
                        <li>Old aur New password alag hone chahiye</li>
                    </ul>
                </div>

                <form method="POST" action="change_password.php">

                    <div class="pass-field">
                        <label>🔑 Old Password</label>
                        <div class="pass-input-wrap">
                            <input type="password" name="old_password" id="old_pass" class="pass-input" placeholder="Purana password daalo" required>
                            <button type="button" class="eye-btn" onclick="togglePass('old_pass', this)">👁️</button>
                        </div>
                    </div>

                    <div class="pass-field">
                        <label>🔒 New Password</label>
                        <div class="pass-input-wrap">
                            <input type="password" name="new_password" id="new_pass" class="pass-input" placeholder="Naya password daalo" required oninput="checkStrength(this.value)">
                            <button type="button" class="eye-btn" onclick="togglePass('new_pass', this)">👁️</button>
                        </div>
                        <div class="strength-bar" id="strength_bar"></div>
                        <div class="strength-text" id="strength_text"></div>
                    </div>

                    <div class="pass-field">
                        <label>✅ Confirm New Password</label>
                        <div class="pass-input-wrap">
                            <input type="password" name="confirm_password" id="confirm_pass" class="pass-input" placeholder="Password dobara daalo" required oninput="checkMatch()">
                            <button type="button" class="eye-btn" onclick="togglePass('confirm_pass', this)">👁️</button>
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
    if(input.type === 'password'){
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}

function checkStrength(val) {
    const bar = document.getElementById('strength_bar');
    const text = document.getElementById('strength_text');
    let strength = 0;
    if(val.length >= 6) strength++;
    if(val.match(/[A-Z]/)) strength++;
    if(val.match(/[0-9]/)) strength++;
    if(val.match(/[^A-Za-z0-9]/)) strength++;

    const colors = ['#ef4444','#f59e0b','#10b981','#4f46e5'];
    const labels = ['Weak 😟','Medium 😐','Strong 💪','Very Strong 🔥'];
    const widths = ['25%','50%','75%','100%'];

    if(val.length === 0){
        bar.style.background = '#e2e8f0';
        bar.style.width = '100%';
        text.textContent = '';
    } else {
        bar.style.background = colors[strength-1] || '#ef4444';
        text.style.color = colors[strength-1] || '#ef4444';
        text.textContent = labels[strength-1] || 'Weak 😟';
    }
}

function checkMatch() {
    const newPass = document.getElementById('new_pass').value;
    const confirmPass = document.getElementById('confirm_pass').value;
    const text = document.getElementById('match_text');
    if(confirmPass.length === 0) { text.textContent = ''; return; }
    if(newPass === confirmPass){
        text.style.color = '#10b981';
        text.textContent = '✅ Password match kar raha hai!';
    } else {
        text.style.color = '#ef4444';
        text.textContent = '❌ Password match nahi kar raha!';
    }
}
</script>

</body>
</html>