<?php
session_start();
include 'db.php';

if(isset($_SESSION['admin']) && !empty($_SESSION['admin'])){
    header("Location: index.php");
    exit();
}

/* LOGIN SECURITY */
if(!isset($_SESSION['attempt'])) $_SESSION['attempt'] = 0;
if(!isset($_SESSION['lock_time'])) $_SESSION['lock_time'] = 0;

$error = '';

/* CHECK LOCK */
if($_SESSION['attempt'] >= 3){
    $time_diff = time() - $_SESSION['lock_time'];
    if($time_diff < 300){
        $remaining = 300 - $time_diff;
        $error = "⏳ Account locked! Try after ".ceil($remaining/60)." min ".($remaining%60)." sec.";
    } else {
        $_SESSION['attempt'] = 0;
        $_SESSION['lock_time'] = 0;
    }
}

if(isset($_POST['login']) && $_SESSION['attempt'] < 3){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $captcha_input = trim($_POST['captcha']);

    if(empty($username) || empty($password)){
        $error = "⚠️ Username and Password are required.";
    } elseif(empty($captcha_input)){
        $error = "⚠️ Please enter the CAPTCHA.";
    } elseif(!isset($_SESSION['captcha']) || strtoupper($captcha_input) !== $_SESSION['captcha']){
        $_SESSION['attempt']++;
        $_SESSION['lock_time'] = time();
        $remaining_attempts = 3 - $_SESSION['attempt'];
        $error = "❌ Wrong CAPTCHA. $remaining_attempts attempt(s) left.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 1){
            $admin = $result->fetch_assoc();
            if(password_verify($password, $admin['password'])){
                $_SESSION['admin'] = $username;
                $_SESSION['attempt'] = 0;
                unset($_SESSION['captcha']);
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['attempt']++;
                $_SESSION['lock_time'] = time();
                $remaining_attempts = 3 - $_SESSION['attempt'];
                $error = "❌ Wrong password. $remaining_attempts attempt(s) left.";
            }
        } else {
            $_SESSION['attempt']++;
            $_SESSION['lock_time'] = time();
            $remaining_attempts = 3 - $_SESSION['attempt'];
            $error = "❌ Username not found. $remaining_attempts attempt(s) left.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — Student Management</title>
<link rel="stylesheet" href="style.css">
</head>

<body class="login-page">

<div class="login-wrapper">

    <!-- LOGO -->
    <div class="login-logo">
        <div class="logo-icon">🎓</div>
        <h1>Student Portal</h1>
        <p>Admin Management System</p>
    </div>

    <!-- CARD -->
    <div class="login-card">
        <form method="POST" autocomplete="off">

            <div class="form-group">
                <label>👤 Username</label>
                <input type="text" name="username" placeholder="Enter your username"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>🔒 Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <div class="form-group">
                <label>🔐 CAPTCHA Verification</label>
                <div class="captcha-box">
                    <input type="text" name="captcha" placeholder="Enter CAPTCHA" maxlength="6" required style="text-transform:uppercase;">
                    <img src="captcha.php" id="captchaImg" alt="CAPTCHA" title="Click to refresh">
                    <button type="button" onclick="refreshCaptcha()" title="Refresh">↻</button>
                </div>
            </div>

            <?php if(!empty($error)): ?>
            <div class="error-msg">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <button type="submit" name="login">🚀 Login to Dashboard</button>

        </form>

        <?php if($_SESSION['attempt'] > 0 && $_SESSION['attempt'] < 3): ?>
        <div class="lock-info">
            ⚠️ <?php echo $_SESSION['attempt']; ?>/3 failed attempts — Account locks after 3 failures
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
function refreshCaptcha(){
    document.getElementById("captchaImg").src = "captcha.php?" + Date.now();
}
document.getElementById("captchaImg").addEventListener("click", refreshCaptcha);
</script>

</body>
</html>
