<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$where = $search ? "WHERE name LIKE '%$search%' OR student_id LIKE '%$search%' OR email LIKE '%$search%' OR course LIKE '%$search%' OR mobile LIKE '%$search%'" : '';

$result = mysqli_query($conn, "SELECT * FROM student $where ORDER BY name");
$total = mysqli_num_rows($result);

$all_q = mysqli_query($conn, "SELECT id, student_id, name, photo, course, mobile FROM student ORDER BY name");
$all_students_json = [];
while($row = mysqli_fetch_assoc($all_q)){
    $all_students_json[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Students</title>
<link rel="stylesheet" href="style.css">
<style>
.search-container { position:relative; width:100%; max-width:400px; }
.search-input { width:100%; padding:11px 18px 11px 42px; border:2px solid #e2e8f0; border-radius:10px; font-size:14px; font-family:inherit; font-weight:600; outline:none; transition:all 0.3s; }
.search-input:focus { border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
.search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:16px; }
.live-dropdown { display:none; position:absolute; top:110%; left:0; right:0; background:white; border:1px solid #e2e8f0; border-radius:10px; max-height:280px; overflow-y:auto; z-index:9999; box-shadow:0 8px 30px rgba(79,70,229,0.15); }
.live-option { display:flex; align-items:center; gap:12px; padding:10px 14px; cursor:pointer; border-bottom:1px solid #f1f5f9; text-decoration:none; color:#1e293b; transition:background 0.15s; }
.live-option:hover { background:#f8fafc; }
.live-option img { width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid #4f46e5; }
.live-option .avatar { width:36px; height:36px; border-radius:50%; background:rgba(79,70,229,0.1); display:flex; align-items:center; justify-content:center; font-size:16px; }
.live-option .info { flex:1; }
.live-option .info strong { font-size:13px; display:block; }
.live-option .info span { font-size:11px; color:#64748b; }
.live-option .sid { background:rgba(79,70,229,0.1); color:#4f46e5; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:700; }
.no-result { padding:16px; text-align:center; color:#64748b; font-size:13px; }
</style>
</head>
<body>

<div class="main-container">
    <?php include_once('sidebar.php'); ?>

    <div class="content">
        <div class="topbar">
            <h1>📋 All Students</h1>
            <div class="topbar-right">
                <a href="export.php" class="btn-export">📤 Export Excel</a>
                <div class="admin-badge">Total: <?php echo $total; ?></div>
            </div>
        </div>

        <!-- SEARCH -->
        <div class="box" style="margin-bottom:18px;">
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                <div class="search-container">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="liveSearch" class="search-input"
                        placeholder="Name, ID, Course, Mobile likhо..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        oninput="liveFilter(this.value)"
                        autocomplete="off">
                    <div class="live-dropdown" id="liveDropdown"></div>
                </div>
                <?php if($search): ?>
                <a href="students_list.php" style="padding:10px 16px;background:#f1f5f9;color:#64748b;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">✕ Clear</a>
                <?php endif; ?>
                <span style="font-size:13px;color:#64748b;font-weight:600;"><?php echo $total; ?> students</span>
            </div>
        </div>

        <!-- TABLE -->
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;background:white;border-radius:14px;overflow:hidden;box-shadow:0 4px 20px rgba(79,70,229,0.1);">
            <thead>
            <tr style="background:#4f46e5;color:white;">
                <th style="padding:12px 14px;text-align:left;font-size:12px;">#</th>
                <th style="padding:12px 14px;text-align:left;font-size:12px;">PHOTO</th>
                <th style="padding:12px 14px;text-align:left;font-size:12px;">STUDENT ID</th>
                <th style="padding:12px 14px;text-align:left;font-size:12px;">NAME</th>
                <th style="padding:12px 14px;text-align:left;font-size:12px;">FATHER</th>
                <th style="padding:12px 14px;text-align:left;font-size:12px;">EMAIL</th>
                <th style="padding:12px 14px;text-align:left;font-size:12px;">COURSE</th>
                <th style="padding:12px 14px;text-align:left;font-size:12px;">MOBILE</th>
                <th style="padding:12px 14px;text-align:left;font-size:12px;">WHATSAPP</th>
                <th style="padding:12px 14px;text-align:center;font-size:12px;">DOB</th>
                <th style="padding:12px 14px;text-align:center;font-size:12px;">DOJ</th>
                <th style="padding:12px 14px;text-align:center;font-size:12px;">ID CARD</th>
                <th style="padding:12px 14px;text-align:center;font-size:12px;">ACTIONS</th>
            </tr>
            </thead>
            <tbody id="tableBody">
            <?php $i=1; while($row = mysqli_fetch_assoc($result)): ?>
            <tr style="border-bottom:1px solid #e2e8f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                <td style="padding:11px 14px;color:#64748b;font-size:13px;"><?php echo $i++; ?></td>
                <td style="padding:11px 14px;">
                    <?php if(!empty($row['photo'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>" style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #4f46e5;">
                    <?php else: ?>
                    <div style="width:42px;height:42px;border-radius:50%;background:rgba(79,70,229,0.1);display:flex;align-items:center;justify-content:center;font-size:18px;">👤</div>
                    <?php endif; ?>
                </td>
                <td style="padding:11px 14px;">
                    <span style="background:rgba(79,70,229,0.1);color:#4f46e5;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?php echo htmlspecialchars($row['student_id']); ?></span>
                </td>
                <td style="padding:11px 14px;">
                    <a href="profile.php?id=<?php echo $row['id']; ?>" style="font-weight:700;color:#1e293b;text-decoration:none;font-size:14px;"><?php echo htmlspecialchars($row['name']); ?></a>
                </td>
                <td style="padding:11px 14px;font-size:13px;color:#64748b;"><?php echo !empty($row['father_name']) ? htmlspecialchars($row['father_name']) : '—'; ?></td>
                <td style="padding:11px 14px;font-size:13px;color:#64748b;"><?php echo htmlspecialchars($row['email']); ?></td>
                <td style="padding:11px 14px;">
                    <span style="background:rgba(16,185,129,0.1);color:#059669;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?php echo htmlspecialchars($row['course']); ?></span>
                </td>
                <td style="padding:11px 14px;font-size:13px;color:#64748b;"><?php echo htmlspecialchars($row['mobile']); ?></td>
                <td style="padding:11px 14px;font-size:13px;color:#64748b;"><?php echo !empty($row['whatsapp']) ? htmlspecialchars($row['whatsapp']) : '—'; ?></td>
                <td style="padding:11px 14px;text-align:center;font-size:12px;color:#64748b;"><?php echo !empty($row['dob']) ? date('d M Y', strtotime($row['dob'])) : '—'; ?></td>
                <td style="padding:11px 14px;text-align:center;font-size:12px;color:#64748b;"><?php echo !empty($row['doj']) ? date('d M Y', strtotime($row['doj'])) : '—'; ?></td>
                <td style="padding:11px 14px;text-align:center;">
               <a href="admin_student_id_card.php?id=<?php echo $row['id']; ?>" style="background:#4f46e5;color:white;padding:6px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;white-space:nowrap;display:inline-block;">🪪 ID Card</a>
                </td>
                <td style="padding:11px 14px;text-align:center;">
                    <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                        <a href="profile.php?id=<?php echo $row['id']; ?>" style="background:#06b6d4;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">👁️</a>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" style="background:#10b981;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">✏️</a>
                        <a href="documents.php?id=<?php echo $row['id']; ?>" style="background:#f59e0b;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">📁</a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete <?php echo htmlspecialchars($row['name']); ?>?')" style="background:#ef4444;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">🗑️</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>

    </div>
</div>

<script>
const allStudents = <?php echo json_encode($all_students_json); ?>;

function liveFilter(val) {
    const dropdown = document.getElementById('liveDropdown');
    const tableBody = document.getElementById('tableBody');
    const v = val.toLowerCase().trim();

    const rows = tableBody.querySelectorAll('tr');
    rows.forEach(row => {
        if(v.length === 0) row.style.display = '';
        else row.style.display = row.innerText.toLowerCase().includes(v) ? '' : 'none';
    });

    if(v.length === 0){ dropdown.style.display = 'none'; return; }

    const matches = allStudents.filter(s =>
        s.name.toLowerCase().includes(v) ||
        s.student_id.toLowerCase().includes(v) ||
        s.course.toLowerCase().includes(v) ||
        s.mobile.includes(v)
    );

    if(matches.length > 0){
        dropdown.style.display = 'block';
        dropdown.innerHTML = matches.slice(0, 8).map(s => `
            <a href="profile.php?id=${s.id}" class="live-option">
                ${s.photo ? `<img src="uploads/${s.photo}">` : `<div class="avatar">👤</div>`}
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
    if(!e.target.closest('.search-container')){
        document.getElementById('liveDropdown').style.display = 'none';
    }
});
</script>

</body>
</html>