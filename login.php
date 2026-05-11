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
    text-decoration: none;
    text-align: center;
    font-weight: 700;
    font-size: 13px;
    transition: all 0.2s;
    border: 2px solid transparent;
    cursor: pointer;
}
.portal-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
.portal-admin { background: linear-gradient(135deg,#4f46e5,#6366f1); color: white; }
.portal-student { background: linear-gradient(135deg,#10b981,#059669); color: white; }
.portal-teacher { background: linear-gradient(135deg,#06b6d4,#0891b2); color: white; }
.portal-icon { font-size: 28px; display: block; margin-bottom: 6px; }
.divider { display:flex; align-items:center; gap:12px; margin-bottom:20px; }
.divider hr { flex:1; border:none; border-top:1px solid #e2e8f0; }
.divider span { font-size:12px; color:#94a3b8; font-weight:700; }
</style>
</head>
<body class="login-page">
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
            <a href="student_login.php" class="portal-btn portal-student">
                <span class="portal-icon">👨‍🎓</span>
                Student Login
            </a>
            <a href="teacher_login.php" class="portal-btn portal-teacher">
                <span class="portal-icon">👩‍🏫</span>
                Teacher Login
            </a>
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
</body>
</html>