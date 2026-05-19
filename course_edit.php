<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

// Create courses table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    level VARCHAR(100),
    duration VARCHAR(100),
    fees DECIMAL(10,2) DEFAULT 0,
    capacity INT DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_table);

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: courses.php");
    exit();
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM courses WHERE id=$id");
if(mysqli_num_rows($result) == 0){
    header("Location: courses.php");
    exit();
}
$course = mysqli_fetch_assoc($result);

$msg = '';
$msg_type = '';

// Update Course
if(isset($_POST['update_course'])){
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $level = mysqli_real_escape_string($conn, trim($_POST['level']));
    $duration = mysqli_real_escape_string($conn, trim($_POST['duration']));
    $fees = floatval($_POST['fees']);
    $capacity = intval($_POST['capacity']);
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    if($name === '' || $level === ''){
        $msg = "❌ Required fields are missing!";
        $msg_type = 'error';
    } else {
        $query = "UPDATE courses SET name='$name', level='$level', duration='$duration', fees=$fees, capacity=$capacity, description='$description' WHERE id=$id";
        if(mysqli_query($conn, $query)){
            $msg = "✅ Course updated successfully!";
            $msg_type = 'success';
            $course = ['id'=>$id, 'name'=>$name, 'level'=>$level, 'duration'=>$duration, 'fees'=>$fees, 'capacity'=>$capacity, 'description'=>$description];
        } else {
            $msg = "❌ Error updating course!";
            $msg_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Course</title>
<link rel="stylesheet" href="style.css">
<style>
.form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-input, .form-textarea, .form-select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    color: #1e293b;
    outline: none;
    background: white;
}
.form-input:focus, .form-textarea:focus, .form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79,70,229,0.08);
}
.form-label { display: block; font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase; }
</style>
</head>
<body>
<div class="main-container">
    <?php include_once('sidebar.php'); ?>
    <div class="content">
        <div class="topbar">
            <h1>✏️ Edit Course</h1>
            <div class="topbar-right">
                <a href="courses.php" style="padding:9px 16px;background:#e2e8f0;color:#1e293b;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">← Back</a>
                <div class="admin-badge">Admin</div>
            </div>
        </div>

        <?php if($msg): ?>
        <div style="background:<?php echo $msg_type==='success'?'#d1fae5':'#fee2e2'; ?>;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:<?php echo $msg_type==='success'?'#065f46':'#991b1b'; ?>;">
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <div class="box">
            <div class="box-title">✏️ Update Course Details</div>
            <form method="POST" action="">
                <div class="form-grid" style="margin-bottom:14px;">
                    <div>
                        <label class="form-label">Course Name *</label>
                        <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($course['name']); ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Level *</label>
                        <select name="level" class="form-select" required>
                            <option value="Bachelor" <?php echo $course['level']==='Bachelor'?'selected':''; ?>>Bachelor Degree</option>
                            <option value="Diploma" <?php echo $course['level']==='Diploma'?'selected':''; ?>>Diploma</option>
                            <option value="Certificate" <?php echo $course['level']==='Certificate'?'selected':''; ?>>Certificate</option>
                            <option value="Masters" <?php echo $course['level']==='Masters'?'selected':''; ?>>Masters</option>
                            <option value="Others" <?php echo $course['level']==='Others'?'selected':''; ?>>Others</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Duration *</label>
                        <input type="text" name="duration" class="form-input" value="<?php echo htmlspecialchars($course['duration']); ?>" required>
                    </div>
                </div>

                <div class="form-grid" style="margin-bottom:14px;">
                    <div>
                        <label class="form-label">Annual Fees (₹)</label>
                        <input type="number" name="fees" class="form-input" value="<?php echo $course['fees']; ?>" min="0" step="1000">
                    </div>
                    <div>
                        <label class="form-label">Student Capacity</label>
                        <input type="number" name="capacity" class="form-input" value="<?php echo $course['capacity']; ?>" min="0">
                    </div>
                    <div>&nbsp;</div>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="4"><?php echo htmlspecialchars($course['description']); ?></textarea>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" name="update_course" style="background:#4f46e5;color:white;padding:12px 22px;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:14px;">✅ Update Course</button>
                    <a href="courses.php" style="background:#e2e8f0;color:#1e293b;padding:12px 22px;border-radius:8px;text-decoration:none;font-weight:700;font-size:14px;">← Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>