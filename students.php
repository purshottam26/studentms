<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

/* Graph Data */
$graph_q = mysqli_query($conn, "SELECT course, COUNT(*) as total FROM student GROUP BY course");
$course_names = [];
$course_counts = [];
while($row = mysqli_fetch_assoc($graph_q)){
    $course_names[] = $row['course'];
    $course_counts[] = $row['total'];
}

/* Search & Pagination */
$limit = 8;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = max(1, $page);
$start = ($page - 1) * $limit;

$search = '';
$where = '';
if(isset($_GET['search']) && $_GET['search'] != ''){
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
    $where = "WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR course LIKE '%$search%' OR student_id LIKE '%$search%'";
}

$count_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM student $where");
$total_records = mysqli_fetch_assoc($count_q)['total'];
$total_pages = ceil($total_records / $limit);

$result = mysqli_query($conn, "SELECT * FROM student $where ORDER BY id DESC LIMIT $start, $limit");

/* Success message */
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Students — Student Management</title>
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
            <a href="index.php"><span class="icon">📊</span> Dashboard</a>
            <a href="students.php" class="active"><span class="icon">👨‍🎓</span> Students</a>
            <a href="students_list.php"><span class="icon">📋</span> All Students</a>
            <a href="export.php"><span class="icon">📤</span> Export Excel</a>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php"><span class="icon">🚪</span> Logout</a>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <div class="topbar">
            <h1>👨‍🎓 Student Management</h1>
            <div class="topbar-right">
                <a href="export.php" class="btn-export">📤 Export Excel</a>
                <div class="admin-badge">👤 <?php echo htmlspecialchars($_SESSION['admin']); ?></div>
            </div>
        </div>

        <?php if($msg == 'added'): ?>
        <div class="alert alert-success">✅ Student added successfully!</div>
        <?php elseif($msg == 'updated'): ?>
        <div class="alert alert-success">✅ Student updated successfully!</div>
        <?php elseif($msg == 'deleted'): ?>
        <div class="alert alert-danger">🗑️ Student deleted.</div>
        <?php endif; ?>

        <!-- CHART -->
        <?php if(!empty($course_names)): ?>
        <div class="graph-box">
            <h2>📊 Students Per Course</h2>
            <canvas id="myChart" style="max-height:200px;"></canvas>
        </div>
        <?php endif; ?>

        <!-- MAIN LAYOUT -->
        <div class="student-container">

            <!-- ADD STUDENT FORM -->
            <div class="form-section" id="add">
                <div class="box">
                    <div class="box-title">➕ Add New Student</div>
                    <form method="POST" action="insert.php" enctype="multipart/form-data">
                        <label>Student ID *</label>
                        <input type="text" name="student_id" placeholder="e.g. STU001" required>

                        <label>Full Name *</label>
                        <input type="text" name="name" placeholder="Student full name" required>

                        <label>Email Address *</label>
                        <input type="email" name="email" placeholder="student@email.com" required>

                        <label>Course *</label>
                        <input type="text" name="course" placeholder="e.g. B.Tech, BCA..." required>

                        <label>Aadhaar Number *</label>
                        <input type="text" name="aadhaar" placeholder="12-digit Aadhaar" maxlength="12" required>

                        <label>Mobile Number *</label>
                        <input type="text" name="mobile" placeholder="10-digit mobile" maxlength="10" required>

                        <label>Pin Code *</label>
                        <input type="text" name="pincode" placeholder="6-digit pin code" maxlength="6" required>

                        <label>Photo</label>
                        <input type="file" name="photo" accept="image/jpg, image/jpeg, image/png">

                        <button type="submit">➕ Add Student</button>
                    </form>
                </div>
            </div>

            <!-- TABLE SECTION -->
            <div class="table-section">

                <!-- SEARCH -->
                <div class="filter-bar">
                    <form method="GET">
                        <input type="text" name="search" placeholder="🔍 Search by name, email, course, ID..."
                            value="<?php echo htmlspecialchars($search); ?>" style="width:280px;">
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        <?php if($search): ?>
                        <a href="students.php" class="btn btn-sm" style="background:var(--bg); border:2px solid var(--border); color:var(--text-dark);">✕ Clear</a>
                        <?php endif; ?>
                    </form>
                    <span style="margin-left:auto; font-size:13px; color:var(--text-light); font-weight:600;">
                        <?php echo $total_records; ?> student<?php echo $total_records != 1 ? 's' : ''; ?> found
                    </span>
                </div>

                <!-- TABLE -->
                <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Mobile</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = $start + 1;
                    while($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td style="color:var(--text-light); font-size:13px;"><?php echo $i++; ?></td>
                        <td>
                            <?php if(!empty($row['photo'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="Photo">
                            <?php else: ?>
                            <div style="width:40px;height:40px;border-radius:50%;background:rgba(79,70,229,0.1);display:flex;align-items:center;justify-content:center;font-size:18px;margin:0 auto;">👤</div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($row['student_id']); ?></span></td>
                        <td>
                            <a href="profile.php?id=<?php echo $row['id']; ?>"
                               style="font-weight:700; color:var(--primary); text-decoration:none;">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </a>
                        </td>
                        <td style="color:var(--text-light); font-size:13px;"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><span class="badge badge-success"><?php echo htmlspecialchars($row['course']); ?></span></td>
                        <td style="font-size:13px;"><?php echo htmlspecialchars($row['mobile']); ?></td>
                        <td>
                            <div class="action-btn">
                                <a href="profile.php?id=<?php echo $row['id']; ?>" title="View Profile">👁️ View</a>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" title="Edit">✏️ Edit</a>
                                <a href="documents.php?id=<?php echo $row['id']; ?>" title="Documents">📁 Docs</a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>"
                                   onclick="return confirm('Delete <?php echo htmlspecialchars($row['name']); ?>? This cannot be undone.')"
                                   title="Delete" style="background:rgba(239,68,68,0.1); color:var(--danger);">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($total_records == 0): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:40px; color:var(--text-light);">
                            <?php if($search): ?>
                            🔍 No students found for "<strong><?php echo htmlspecialchars($search); ?></strong>"
                            <?php else: ?>
                            👨‍🎓 No students yet. Add your first student!
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                </div>

                <!-- PAGINATION -->
                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">‹ Prev</a>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                       class="<?php echo $i == $page ? 'active' : ''; ?>">
                       <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">Next ›</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div> <!-- table-section -->
        </div> <!-- student-container -->

    </div>
</div>

<?php if(!empty($course_names)): ?>
<script>
const ctx = document.getElementById('myChart');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($course_names); ?>,
        datasets: [{
            data: <?php echo json_encode($course_counts); ?>,
            backgroundColor: ['#4f46e5','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'right' } }
    }
});
</script>
<?php endif; ?>

</body>
</html>
