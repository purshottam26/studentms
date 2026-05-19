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

$msg = '';
$msg_type = '';

// Add Course
if(isset($_POST['add_course'])){
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $level = mysqli_real_escape_string($conn, trim($_POST['level']));
    $duration = mysqli_real_escape_string($conn, trim($_POST['duration']));
    $fees = floatval($_POST['fees']);
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $capacity = intval($_POST['capacity']);

    if($name === '' || $level === '' || $duration === ''){
        $msg = "❌ Required fields are missing!";
        $msg_type = 'error';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO courses (name, level, duration, fees, description, capacity) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssidi', $name, $level, $duration, $fees, $description, $capacity);
        if(mysqli_stmt_execute($stmt)){
            $msg = "✅ Course added successfully!";
            $msg_type = 'success';
        } else {
            $msg = "❌ Error while adding course!";
            $msg_type = 'error';
        }
        mysqli_stmt_close($stmt);
    }
}

// Delete Course
if(isset($_GET['delete']) && ctype_digit($_GET['delete'])){
    $did = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM courses WHERE id=$did");
    header("Location: courses.php?msg=deleted");
    exit();
}

$del_msg = $_GET['msg'] ?? '';
$courses_q = mysqli_query($conn, "SELECT * FROM courses ORDER BY id DESC");
$total_courses = mysqli_num_rows($courses_q);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Courses Management</title>
<link rel="stylesheet" href="style.css">
<style>
.course-card {
    background: white;
    border-radius: 16px;
    padding: 0;
    box-shadow: 0 4px 20px rgba(79,70,229,0.08);
    overflow: hidden;
    transition: transform 0.2s;
    border: 1px solid #e2e8f0;
}
.course-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(79,70,229,0.15); }
.course-header { background: linear-gradient(135deg,#4f46e5,#06b6d4); padding: 20px; color: white; }
.course-header h3 { margin: 0; font-size: 18px; font-weight: 800; }
.course-header p { margin: 4px 0 0; font-size: 13px; opacity: 0.85; }
.course-body { padding: 20px; }
.course-meta { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px; }
.meta-box { background: #f8fafc; border-radius: 10px; padding: 12px; text-align: center; }
.meta-label { font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; }
.meta-value { font-size: 16px; font-weight: 800; color: #1e293b; margin-top: 4px; }
.meta-icon { font-size: 20px; margin-bottom: 4px; }
.course-desc { font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 16px; }
.course-actions { display: flex; gap: 8px; }
.course-actions a { flex: 1; text-align: center; padding: 10px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700; color: white; }
.action-edit { background: #4f46e5; }
.action-delete { background: #ef4444; }
.courses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px,1fr)); gap: 20px; }
.level-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}
.level-bachelor { background: rgba(79,70,229,0.1); color: #4f46e5; }
.level-diploma { background: rgba(6,182,212,0.1); color: #0891b2; }
.level-certificate { background: rgba(16,185,129,0.1); color: #059669; }
.level-masters { background: rgba(139,92,246,0.1); color: #7c3aed; }
.level-others { background: rgba(156,163,175,0.1); color: #6b7280; }
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
            <h1>📚 Courses Management</h1>
            <div class="topbar-right">
                <div class="admin-badge">Total: <?php echo $total_courses; ?> courses</div>
            </div>
        </div>

        <?php if($del_msg === 'deleted'): ?>
        <div style="background:#fee2e2;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:#991b1b;">🗑️ Course deleted!</div>
        <?php endif; ?>

        <?php if($msg): ?>
        <div style="background:<?php echo $msg_type==='success'?'#d1fae5':'#fee2e2'; ?>;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:<?php echo $msg_type==='success'?'#065f46':'#991b1b'; ?>;">
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <!-- ADD COURSE FORM -->
        <div class="box">
            <div class="box-title">➕ Add New Course</div>
            <form method="POST" action="courses.php">
                <div class="form-grid" style="margin-bottom:14px;">
                    <div>
                        <label class="form-label">Course Name *</label>
                        <input type="text" name="name" class="form-input" placeholder="e.g. B.Tech (Computer Science)" required>
                    </div>
                    <div>
                        <label class="form-label">Level *</label>
                        <select name="level" class="form-select" required>
                            <option value="">Select Level</option>
                            <option value="Bachelor">Bachelor Degree</option>
                            <option value="Diploma">Diploma</option>
                            <option value="Certificate">Certificate</option>
                            <option value="Masters">Masters</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Duration *</label>
                        <input type="text" name="duration" class="form-input" placeholder="e.g. 4 Years" required>
                    </div>
                </div>

                <div class="form-grid" style="margin-bottom:14px;">
                    <div>
                        <label class="form-label">Annual Fees (₹)</label>
                        <input type="number" name="fees" class="form-input" placeholder="e.g. 50000" min="0" step="1000" value="0">
                    </div>
                    <div>
                        <label class="form-label">Student Capacity</label>
                        <input type="number" name="capacity" class="form-input" placeholder="e.g. 60" min="0" value="0">
                    </div>
                    <div>
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" name="add_course" style="width:100%;background:#4f46e5;color:white;padding:10px 14px;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:14px;">➕ Add Course</button>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="3" placeholder="Course details, objectives, eligibility..."></textarea>
                </div>
            </form>
        </div>

        <!-- COURSES GRID -->
        <div class="box">
            <div class="box-title">📚 All Courses</div>
            <?php if($total_courses > 0): ?>
            <div class="courses-grid">
                <?php
                mysqli_data_seek($courses_q, 0);
                while($c = mysqli_fetch_assoc($courses_q)):
                    $level_class = 'level-' . strtolower(str_replace(' ', '-', $c['level']));
                ?>
                <div class="course-card">
                    <div class="course-header">
                        <h3><?php echo htmlspecialchars($c['name']); ?></h3>
                        <p>
                            <?php echo match($c['level']) {
                                'Bachelor' => '🎓 Bachelor Degree',
                                'Diploma' => '📜 Diploma',
                                'Certificate' => '✅ Certificate',
                                'Masters' => '👨‍🎓 Masters',
                                default => '📚 Course'
                            }; ?>
                        </p>
                    </div>
                    <div class="course-body">
                        <div style="margin-bottom:12px;">
                            <span class="level-badge <?php echo $level_class; ?>"><?php echo htmlspecialchars($c['level']); ?></span>
                        </div>

                        <div class="course-meta">
                            <div class="meta-box">
                                <div class="meta-icon">⏱️</div>
                                <div class="meta-label">Duration</div>
                                <div class="meta-value"><?php echo htmlspecialchars($c['duration']); ?></div>
                            </div>
                            <div class="meta-box">
                                <div class="meta-icon">💰</div>
                                <div class="meta-label">Annual Fees</div>
                                <div class="meta-value">₹<?php echo number_format($c['fees'], 0); ?></div>
                            </div>
                            <div class="meta-box">
                                <div class="meta-icon">👥</div>
                                <div class="meta-label">Capacity</div>
                                <div class="meta-value"><?php echo $c['capacity']; ?> students</div>
                            </div>
                        </div>

                        <?php if(!empty($c['description'])): ?>
                        <div class="course-desc">
                            <?php echo htmlspecialchars(substr($c['description'], 0, 100)); ?>
                            <?php echo strlen($c['description']) > 100 ? '...' : ''; ?>
                        </div>
                        <?php endif; ?>

                        <div class="course-actions">
                            <a href="course_edit.php?id=<?php echo $c['id']; ?>" class="action-edit">✏️ Edit</a>
                            <a href="?delete=<?php echo $c['id']; ?>" onclick="return confirm('Delete this course?')" class="action-delete">🗑️ Delete</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div style="text-align:center;padding:40px;color:#64748b;">
                <div style="font-size:48px;margin-bottom:14px;">📚</div>
                <p style="font-weight:700;">Koi course nahi mila! Upar se add karo.</p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>
</body>
</html>