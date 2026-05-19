<?php
$is_admin = isset($_SESSION['admin']);
?>

<div class="sidebar">
    <div class="sidebar-brand">
        <h2><span class="brand-icon">🎓</span> <span>StudentMS</span></h2>
        <p><?php echo $is_admin ? 'Admin Panel' : 'Teacher Panel'; ?></p>
    </div>

    <div class="sidebar-nav">

        <?php if($is_admin){ ?>

            <div class="nav-label">Main Menu</div>

            <a href="index.php">📊 Dashboard</a>
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
            <a href="timetable.php">📅 Timetable</a>
            <a href="courses.php">📚 Courses</a>
            <a href="report_card.php">📊 Report Card</a>
            <a href="student_timetable.php">📅 Timetable</a>
            <a href="student_profile_edit.php">👤 Edit Profile</a>
            <a href="change_password.php">🔐 Change Password</a>

        <?php } else { ?>

            <div class="nav-label">Teacher Menu</div>

            <a href="teacher_dashboard.php">📊 Dashboard</a>

            <a href="teacher_id_card.php">🪪 My ID Card</a>

            <a href="teacher_profile.php">👤 My Profile</a>

            <a href="teacher_change_password.php">🔐 Change Password</a>

            <a href="library.php">📚 Library</a>

        <?php } ?>

    </div>

    <div class="sidebar-footer">
        <a href="<?php echo $is_admin ? 'logout.php' : 'teacher_logout.php'; ?>">
            🚪 Logout
        </a>
    </div>
</div>