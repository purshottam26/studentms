<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

/* Dashboard Data */
$total_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM student");
$total_students = mysqli_fetch_assoc($total_q)['total'];

$course_q = mysqli_query($conn, "SELECT COUNT(DISTINCT course) as total FROM student");
$total_courses = mysqli_fetch_assoc($course_q)['total'];

$recent_q = mysqli_query($conn, "SELECT name FROM student ORDER BY id DESC LIMIT 1");
$recent_data = mysqli_fetch_assoc($recent_q);
$recent_student = $recent_data ? $recent_data['name'] : "None yet";

$photo_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM student WHERE photo != ''");
$total_photos = mysqli_fetch_assoc($photo_q)['total'];

$teacher_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM teachers");
$total_teachers = mysqli_fetch_assoc($teacher_q)['total'];

$book_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM library_books");
$total_books = mysqli_fetch_assoc($book_q)['total'];

$topper_q = mysqli_query($conn, "
SELECT student.name, results.marks
FROM results
JOIN student ON results.student_id = student.student_id
ORDER BY results.marks DESC LIMIT 1
");
$topper_data = mysqli_fetch_assoc($topper_q);
$topper_name = $topper_data ? $topper_data['name'] : "No Data";
$topper_marks = $topper_data ? $topper_data['marks'] : 0;

/* Recent Students */
$recent_students_q = mysqli_query($conn, "SELECT * FROM student ORDER BY id DESC LIMIT 5");

/* Recent Teachers */
$recent_teachers_q = mysqli_query($conn, "SELECT * FROM teachers ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Student Management</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="main-container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="brand-icon">🎓</span> <span>StudentMS</span></h2>
            <p>Admin Panel</p>
        </div>
        <div class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="index.php" class="active">📊 Dashboard</a>
            <a href="students.php">👨‍🎓 Students</a>
            <a href="students_list.php">📋 All Students</a>
            <a href="export.php">📤 Export Excel</a>
            <a href="add_exam.php">📘 Exams</a>
            <a href="add_result.php">📊 Add Result</a>
            <a href="view_result.php">📄 View Result</a>
            <a href="add_teacher.php">👩‍🏫 Teachers</a>
            <a href="library.php">📚 Library</a>
            <a href="notice_board.php">📢 Notice Board</a>
            <a href="attendance.php">✅ Attendance</a>
            <a href="fees.php">💰 Fee Management</a>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <!-- TOPBAR -->
        <div class="topbar">
            <h1>📊 Dashboard</h1>
            <div class="topbar-right">
                <div class="admin-badge">👤 <?php echo htmlspecialchars($_SESSION['admin']); ?></div>
            </div>
        </div>

        <!-- CARDS -->
        <div class="dashboard">

            <div class="card">
                <div class="card-icon blue">👨‍🎓</div>
                <h3>Total Students</h3>
                <p><?php echo $total_students; ?></p>
                <div class="card-sub">Registered students</div>
            </div>

            <div class="card">
                <div class="card-icon cyan">📚</div>
                <h3>Total Courses</h3>
                <p><?php echo $total_courses; ?></p>
                <div class="card-sub">Active courses</div>
            </div>

            <div class="card">
                <div class="card-icon green">👩‍🏫</div>
                <h3>Total Teachers</h3>
                <p><?php echo $total_teachers; ?></p>
                <div class="card-sub">Registered teachers</div>
            </div>

            <div class="card">
                <div class="card-icon amber">📖</div>
                <h3>Library Books</h3>
                <p><?php echo $total_books; ?></p>
                <div class="card-sub">Total books</div>
            </div>

            <div class="card">
                <div class="card-icon purple" style="background:rgba(139,92,246,0.1);">🏆</div>
                <h3>Topper</h3>
                <p style="font-size:16px;"><?php echo htmlspecialchars($topper_name); ?></p>
                <div class="card-sub">Marks: <?php echo $topper_marks; ?></div>
            </div>

            <div class="card">
                <div class="card-icon green">🌟</div>
                <h3>Last Added</h3>
                <p style="font-size:16px;"><?php echo htmlspecialchars($recent_student); ?></p>
                <div class="card-sub">Most recent student</div>
            </div>

        </div>

        <!-- QUICK ACTIONS -->
        <div class="box">
            <div class="box-title">⚡ Quick Actions</div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="students.php" class="btn btn-primary">👨‍🎓 Manage Students</a>
                <a href="students.php#add" class="btn btn-success">➕ Add New Student</a>
                <a href="add_teacher.php" class="btn btn-primary" style="background:#06b6d4;">👩‍🏫 Manage Teachers</a>
                <a href="library.php" class="btn btn-success" style="background:#8b5cf6;">📚 Library</a>
                <a href="add_exam.php" class="btn btn-primary" style="background:#f59e0b;">📘 Add Exam</a>
                <a href="add_result.php" class="btn btn-primary" style="background:#10b981;">📊 Add Result</a>
                <a href="export.php" class="btn-export">📤 Export to Excel</a>
            </div>
        </div>

        <!-- RECENT STUDENTS TABLE -->
        <div class="box">
            <div class="box-title">👨‍🎓 Recent Students</div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                <tr style="background:#4f46e5;color:white;">
                    <th style="padding:10px 14px;text-align:left;">Photo</th>
                    <th style="padding:10px 14px;text-align:left;">Student ID</th>
                    <th style="padding:10px 14px;text-align:left;">Name</th>
                    <th style="padding:10px 14px;text-align:left;">Course</th>
                    <th style="padding:10px 14px;text-align:left;">Mobile</th>
                    <th style="padding:10px 14px;text-align:left;">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while($s = mysqli_fetch_assoc($recent_students_q)): ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:10px 14px;">
                        <?php if(!empty($s['photo'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($s['photo']); ?>" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #4f46e5;">
                        <?php else: ?>
                        <div style="width:38px;height:38px;border-radius:50%;background:rgba(79,70,229,0.1);display:flex;align-items:center;justify-content:center;font-size:18px;">👤</div>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 14px;"><span class="badge badge-primary"><?php echo htmlspecialchars($s['student_id']); ?></span></td>
                    <td style="padding:10px 14px;font-weight:700;color:#1e293b;"><?php echo htmlspecialchars($s['name']); ?></td>
                    <td style="padding:10px 14px;"><span class="badge badge-success"><?php echo htmlspecialchars($s['course']); ?></span></td>
                    <td style="padding:10px 14px;font-size:13px;color:#64748b;"><?php echo htmlspecialchars($s['mobile']); ?></td>
                    <td style="padding:10px 14px;">
                        <a href="profile.php?id=<?php echo $s['id']; ?>" style="background:#4f46e5;color:white;padding:5px 10px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">👁️ View</a>
                        <a href="edit.php?id=<?php echo $s['id']; ?>" style="background:#10b981;color:white;padding:5px 10px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;margin-left:4px;">✏️ Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <div style="margin-top:14px;">
                <a href="students_list.php" style="color:#4f46e5;font-weight:700;font-size:13px;">📋 View All Students →</a>
            </div>
        </div>

        <!-- RECENT TEACHERS TABLE -->
        <div class="box">
            <div class="box-title">👩‍🏫 Recent Teachers</div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                <tr style="background:#06b6d4;color:white;">
                    <th style="padding:10px 14px;text-align:left;">Photo</th>
                    <th style="padding:10px 14px;text-align:left;">Teacher ID</th>
                    <th style="padding:10px 14px;text-align:left;">Name</th>
                    <th style="padding:10px 14px;text-align:left;">Subject</th>
                    <th style="padding:10px 14px;text-align:left;">Phone</th>
                    <th style="padding:10px 14px;text-align:left;">Email</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $has_teacher = false;
                while($t = mysqli_fetch_assoc($recent_teachers_q)):
                $has_teacher = true;
                ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:10px 14px;">
                        <?php if(!empty($t['photo'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($t['photo']); ?>" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #06b6d4;">
                        <?php else: ?>
                        <div style="width:38px;height:38px;border-radius:50%;background:rgba(6,182,212,0.1);display:flex;align-items:center;justify-content:center;font-size:18px;">👩‍🏫</div>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 14px;"><span style="background:rgba(6,182,212,0.1);color:#0891b2;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?php echo htmlspecialchars($t['teacher_id']); ?></span></td>
                    <td style="padding:10px 14px;font-weight:700;color:#1e293b;"><?php echo htmlspecialchars($t['name']); ?></td>
                    <td style="padding:10px 14px;"><span style="background:rgba(16,185,129,0.1);color:#059669;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?php echo htmlspecialchars($t['subject']); ?></span></td>
                    <td style="padding:10px 14px;font-size:13px;color:#64748b;"><?php echo htmlspecialchars($t['phone']); ?></td>
                    <td style="padding:10px 14px;font-size:13px;color:#64748b;"><?php echo htmlspecialchars($t['email']); ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if(!$has_teacher): ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:30px;color:#64748b;">👩‍🏫 Koi teacher nahi mila. <a href="add_teacher.php" style="color:#4f46e5;font-weight:700;">Teacher add karo →</a></td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top:14px;">
                <a href="add_teacher.php" style="color:#06b6d4;font-weight:700;font-size:13px;">👩‍🏫 View All Teachers →</a>
            </div>
        </div>

        <!-- WELCOME -->
        <div class="welcome-box">
            <h2>👋 Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?>!</h2>
            <p>Use the sidebar to manage students, teachers, exams, results and library.</p>
        </div>

    </div>
</div>

</body>
</html>