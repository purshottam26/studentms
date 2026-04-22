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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Teacher Login</title>
<link rel="stylesheet" href="style.css">
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
                <input type="text" name="teacher_id" placeholder="Enter your Teacher ID" required>
            </div>
            <div class="form-group">
                <label>🔒 Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
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
</body>
</html>