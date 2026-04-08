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

// Count with photo
$photo_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM student WHERE photo != ''");
$total_photos = mysqli_fetch_assoc($photo_q)['total'];
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
            <a href="index.php" class="active"><span class="icon">📊</span> Dashboard</a>
            <a href="students.php"><span class="icon">👨‍🎓</span> Students</a>
            <a href="students_list.php"><span class="icon">📋</span> All Students</a>
            <a href="export.php"><span class="icon">📤</span> Export Excel</a>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php"><span class="icon">🚪</span> Logout</a>
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
                <div class="card-icon green">🌟</div>
                <h3>Last Added</h3>
                <p style="font-size:18px; padding-top:4px;"><?php echo htmlspecialchars($recent_student); ?></p>
                <div class="card-sub">Most recent student</div>
            </div>
            <div class="card">
                <div class="card-icon amber">🖼️</div>
                <h3>With Photos</h3>
                <p><?php echo $total_photos; ?></p>
                <div class="card-sub">Students with photos</div>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="box">
            <div class="box-title">⚡ Quick Actions</div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="students.php" class="btn btn-primary">👨‍🎓 Manage Students</a>
                <a href="students.php#add" class="btn btn-success">➕ Add New Student</a>
                <a href="export.php" class="btn-export">📤 Export to Excel</a>
            </div>
        </div>

        <!-- COURSE CHART -->
        <?php
        $graph_q = mysqli_query($conn, "SELECT course, COUNT(*) as total FROM student GROUP BY course");
        $course_names = [];
        $course_counts = [];
        while($row = mysqli_fetch_assoc($graph_q)){
            $course_names[] = $row['course'];
            $course_counts[] = $row['total'];
        }
        ?>
        <?php if(!empty($course_names)): ?>
        <div class="graph-box">
            <h2>📊 Students Per Course</h2>
            <canvas id="courseChart" style="max-height:280px;"></canvas>
        </div>
        <script>
        const ctx = document.getElementById('courseChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($course_names); ?>,
                datasets: [{
                    label: 'Students',
                    data: <?php echo json_encode($course_counts); ?>,
                    backgroundColor: ['#4f46e5','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'],
                    borderRadius: 8, borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
        </script>
        <?php endif; ?>

        <!-- WELCOME -->
        <div class="welcome-box">
            <h2>👋 Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?>!</h2>
            <p>Use the sidebar or quick actions above to manage students, view reports, and export data.</p>
        </div>

    </div>
</div>

</body>
</html>
