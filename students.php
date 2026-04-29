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

/* All students for live search */
$all_q = mysqli_query($conn, "SELECT id, student_id, name, email, course, mobile, photo FROM student ORDER BY name");
$all_students_json = [];
while($row = mysqli_fetch_assoc($all_q)){
    $all_students_json[] = $row;
}

/* Auto generate student ID */
$last_q = mysqli_fetch_assoc(mysqli_query($conn, "SELECT student_id FROM student ORDER BY id DESC LIMIT 1"));
$last_id = $last_q['student_id'] ?? '';
$next_num = 1;
if(preg_match('/(\d+)$/', $last_id, $m)){
    $next_num = intval($m[1]) + 1;
}
$auto_id = 'ST-' . date('dmY') . '-' . str_pad($next_num, 4, '0', STR_PAD_LEFT);

$msg = $_GET['msg'] ?? '';

$input_style = "width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;font-family:inherit;color:#1e293b;outline:none;transition:border-color 0.2s;";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Students — Student Management</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.live-search-wrapper { position: relative; }
.live-search-input {
    width: 100%;
    padding: 10px 16px 10px 38px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    outline: none;
    transition: all 0.3s;
    font-family: inherit;
}
.live-search-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
.live-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 15px; }
.live-dropdown {
    display: none; position: absolute; top: 110%; left: 0; right: 0;
    background: white; border: 1px solid #e2e8f0; border-radius: 10px;
    max-height: 260px; overflow-y: auto; z-index: 9999;
    box-shadow: 0 8px 30px rgba(79,70,229,0.15);
}
.live-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9;
    text-decoration: none; color: #1e293b; transition: background 0.15s;
}
.live-item:hover { background: #f8fafc; }
.live-item img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #4f46e5; flex-shrink: 0; }
.live-item .av { width: 36px; height: 36px; border-radius: 50%; background: rgba(79,70,229,0.1); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.live-item .info { flex: 1; min-width: 0; }
.live-item .info strong { font-size: 13px; display: block; color: #1e293b; }
.live-item .info span { font-size: 11px; color: #64748b; }
.live-item .sid { background: rgba(79,70,229,0.1); color: #4f46e5; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; flex-shrink: 0; }
.no-result { padding: 16px; text-align: center; color: #64748b; font-size: 13px; }
.auto-id-box {
    background: linear-gradient(135deg, rgba(79,70,229,0.08), rgba(6,182,212,0.08));
    border: 2px dashed #4f46e5;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.radio-group { display: flex; gap: 16px; margin-bottom: 8px; }
.radio-group label { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; cursor: pointer; }
.form-section input[type="text"],
.form-section input[type="email"],
.form-section input[type="date"],
.form-section input[type="file"],
.form-section input[type="number"],
.form-section select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    color: #1e293b;
    outline: none;
    transition: border-color 0.2s;
    background: white;
}
.form-section input:focus,
.form-section select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79,70,229,0.08);
}
</style>
</head>
<body>

<div class="main-container">

    <?php include_once('sidebar.php'); ?>

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
        <?php elseif($msg == 'duplicate'): ?>
        <div style="background:#fee2e2;border-left:4px solid #ef4444;padding:14px 18px;border-radius:10px;margin-bottom:18px;font-weight:700;color:#991b1b;">
            ❌ Student ID <strong><?php echo htmlspecialchars($_GET['sid'] ?? ''); ?></strong> already exists!
        </div>
        <?php endif; ?>

        <!-- CHART -->
        <?php if(!empty($course_names)): ?>
        <div class="graph-box">
            <h2>📊 Students Per Course</h2>
            <canvas id="myChart" style="max-height:200px;"></canvas>
        </div>
        <?php endif; ?>

        <div class="student-container">

            <!-- ADD STUDENT FORM -->
            <div class="form-section" id="add">
                <div class="box">
                    <div class="box-title">➕ Add New Student</div>
                    <form method="POST" action="insert.php" enctype="multipart/form-data">

                        <!-- ID Type Select -->
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="id_type" value="auto" checked onchange="toggleIDField(this.value)">
                                🔄 Auto Generate
                            </label>
                            <label>
                                <input type="radio" name="id_type" value="manual" onchange="toggleIDField(this.value)">
                                ✏️ Manual Enter
                            </label>
                        </div>

                        <!-- Auto ID Preview -->
                        <div class="auto-id-box" id="auto_id_box">
                            <span style="font-size:20px;">🪪</span>
                            <div>
                                <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;">Auto Generated ID</div>
                                <div style="font-size:18px;font-weight:800;color:#4f46e5;"><?php echo $auto_id; ?></div>
                            </div>
                            <input type="hidden" name="student_id" id="auto_id_input" value="<?php echo $auto_id; ?>">
                        </div>

                        <!-- Manual ID Input -->
                        <div id="manual_id_box" style="display:none;margin-bottom:14px;">
                            <label>Student ID *</label>
                            <input type="text" id="manual_id_input" placeholder="e.g. STU001">
                        </div>

                        <label>Full Name *</label>
                        <input type="text" name="name" placeholder="Student full name" required>

                        <label>Father's Name</label>
                        <input type="text" name="father_name" placeholder="Father's full name">

                        <label>Email Address *</label>
                        <input type="email" name="email" placeholder="student@email.com" required>

                        <label>Course *</label>
                        <input type="text" name="course" placeholder="e.g. B.Tech, BCA..." required>

                        <label>Date of Birth</label>
                        <input type="date" name="dob">

                        <label>Date of Joining</label>
                        <input type="date" name="doj" value="<?php echo date('Y-m-d'); ?>">

                        <label>Aadhaar Number *</label>
                        <input type="text" name="aadhaar" placeholder="12-digit Aadhaar" maxlength="12" required>

                        <label>Mobile Number *</label>
                        <input type="text" name="mobile" placeholder="10-digit mobile" maxlength="10" required>

                        <label>WhatsApp Number</label>
                        <input type="text" name="whatsapp" placeholder="10-digit WhatsApp" maxlength="10">

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

                <!-- LIVE SEARCH -->
                <div class="filter-bar">
                    <div class="live-search-wrapper" style="flex:1;max-width:360px;">
                        <span class="live-search-icon">🔍</span>
                        <input type="text" id="liveSearch" class="live-search-input"
                            placeholder="Name, ID, Course, Email likhо..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            oninput="liveFilter(this.value)"
                            autocomplete="off">
                        <div class="live-dropdown" id="liveDropdown"></div>
                    </div>
                    <?php if($search): ?>
                    <a href="students.php" class="btn btn-sm" style="background:var(--bg);border:2px solid var(--border);color:var(--text-dark);">✕ Clear</a>
                    <?php endif; ?>
                    <span style="margin-left:auto;font-size:13px;color:var(--text-light);font-weight:600;">
                        <?php echo $total_records; ?> students found
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
                        <th>Course</th>
                        <th>DOB</th>
                        <th>DOJ</th>
                        <th>Mobile</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="tableBody">
                    <?php
                    $i = $start + 1;
                    while($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td style="color:var(--text-light);font-size:13px;"><?php echo $i++; ?></td>
                        <td>
                            <?php if(!empty($row['photo'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="Photo">
                            <?php else: ?>
                            <div style="width:40px;height:40px;border-radius:50%;background:rgba(79,70,229,0.1);display:flex;align-items:center;justify-content:center;font-size:18px;margin:0 auto;">👤</div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-primary"><?php echo htmlspecialchars($row['student_id']); ?></span></td>
                        <td>
                            <a href="profile.php?id=<?php echo $row['id']; ?>" style="font-weight:700;color:var(--primary);text-decoration:none;">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </a>
                            <?php if(!empty($row['father_name'])): ?>
                            <div style="font-size:11px;color:#94a3b8;">👨 <?php echo htmlspecialchars($row['father_name']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-success"><?php echo htmlspecialchars($row['course']); ?></span></td>
                        <td style="font-size:12px;color:#64748b;"><?php echo !empty($row['dob']) ? date('d M Y', strtotime($row['dob'])) : '—'; ?></td>
                        <td style="font-size:12px;color:#64748b;"><?php echo !empty($row['doj']) ? date('d M Y', strtotime($row['doj'])) : '—'; ?></td>
                        <td style="font-size:13px;"><?php echo htmlspecialchars($row['mobile']); ?></td>
                        <td>
                            <div class="action-btn">
                                <a href="profile.php?id=<?php echo $row['id']; ?>" title="View">👁️ View</a>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" title="Edit">✏️ Edit</a>
                                <a href="documents.php?id=<?php echo $row['id']; ?>" title="Docs">📁 Docs</a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>"
                                   onclick="return confirm('Delete <?php echo htmlspecialchars($row['name']); ?>?')"
                                   style="background:rgba(239,68,68,0.1);color:var(--danger);">🗑️</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($total_records == 0): ?>
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;color:var(--text-light);">
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
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">Next ›</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>

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
    options: { responsive: true, plugins: { legend: { position: 'right' } } }
});
</script>
<?php endif; ?>

<script>
const allStudents = <?php echo json_encode($all_students_json); ?>;

function toggleIDField(val){
    const autoBox = document.getElementById('auto_id_box');
    const manualBox = document.getElementById('manual_id_box');
    const autoInput = document.getElementById('auto_id_input');
    const manualInput = document.getElementById('manual_id_input');

    if(val === 'auto'){
        autoBox.style.display = 'flex';
        manualBox.style.display = 'none';
        autoInput.name = 'student_id';
        manualInput.name = '';
        manualInput.required = false;
    } else {
        autoBox.style.display = 'none';
        manualBox.style.display = 'block';
        autoInput.name = '';
        manualInput.name = 'student_id';
        manualInput.required = true;
    }
}

function liveFilter(val) {
    const dropdown = document.getElementById('liveDropdown');
    const tableBody = document.getElementById('tableBody');
    const v = val.toLowerCase().trim();

    const rows = tableBody.querySelectorAll('tr');
    rows.forEach(function(row){
        if(v.length === 0){ row.style.display = ''; }
        else {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(v) ? '' : 'none';
        }
    });

    if(v.length === 0){ dropdown.style.display = 'none'; return; }

    const matches = allStudents.filter(s =>
        s.name.toLowerCase().includes(v) ||
        s.student_id.toLowerCase().includes(v) ||
        s.email.toLowerCase().includes(v) ||
        s.course.toLowerCase().includes(v) ||
        s.mobile.includes(v)
    );

    if(matches.length > 0){
        dropdown.style.display = 'block';
        dropdown.innerHTML = matches.slice(0, 8).map(s => `
            <a href="profile.php?id=${s.id}" class="live-item">
                ${s.photo ? `<img src="uploads/${s.photo}" alt="">` : `<div class="av">👤</div>`}
                <div class="info">
                    <strong>${s.name}</strong>
                    <span>${s.course} • ${s.mobile}</span>
                </div>
                <span class="sid">${s.student_id}</span>
            </a>
        `).join('');
    } else {
        dropdown.style.display = 'block';
        dropdown.innerHTML = '<div class="no-result">🔍 Koi student nahi mila!</div>';
    }
}

document.addEventListener('click', function(e){
    if(!e.target.closest('.live-search-wrapper')){
        document.getElementById('liveDropdown').style.display = 'none';
    }
});

document.getElementById('liveSearch').addEventListener('keydown', function(e){
    if(e.key === 'Enter'){
        window.location.href = 'students.php?search=' + encodeURIComponent(this.value);
    }
});
</script>

</body>
</html>