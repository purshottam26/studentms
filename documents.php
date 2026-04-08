<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include "db.php";

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: students.php");
    exit();
}

$student_id = intval($_GET['id']);

// Get student info
$student_q = mysqli_query($conn, "SELECT * FROM student WHERE id=$student_id");
if(mysqli_num_rows($student_q) == 0){
    header("Location: students.php");
    exit();
}
$student = mysqli_fetch_assoc($student_q);

$months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
$folder = "uploads/" . $student_id . "/";

if(!file_exists($folder)){
    mkdir($folder, 0777, true);
}

/* DELETE FILE */
if(isset($_GET['delete']) && !empty($_GET['delete'])){
    $file = basename($_GET['delete']); // security: basename only
    $file_path = $folder . $file;
    if(file_exists($file_path)){
        unlink($file_path);
    }
    header("Location: documents.php?id=" . $student_id . "&msg=deleted");
    exit();
}

/* UPLOAD */
if(isset($_POST['upload'])){
    $month = $_POST['month'];
    $uploaded = 0;

    $doc_types = ['salary','bank','report','attendance'];
    foreach($doc_types as $type){
        if(isset($_FILES[$type]) && $_FILES[$type]['error'] == 0 && $_FILES[$type]['name'] != ''){
            $ext = strtolower(pathinfo($_FILES[$type]['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','pdf'];
            if(in_array($ext, $allowed)){
                $name = $month . "_" . $type . "_" . time() . "." . $ext;
                move_uploaded_file($_FILES[$type]['tmp_name'], $folder . $name);
                $uploaded++;
            }
        }
    }
    header("Location: documents.php?id=" . $student_id . "&msg=" . ($uploaded > 0 ? 'uploaded' : 'none'));
    exit();
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Documents — <?php echo htmlspecialchars($student['name']); ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="main-container">
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

    <div class="content">
        <div class="topbar">
            <h1>📁 Student Documents</h1>
            <a href="profile.php?id=<?php echo $student_id; ?>" class="btn btn-primary btn-sm">← Back to Profile</a>
        </div>

        <!-- STUDENT BANNER -->
        <div class="box" style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
            <?php if(!empty($student['photo'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($student['photo']); ?>"
                style="width:54px;height:54px;border-radius:50%;object-fit:cover;border:3px solid var(--primary-light);">
            <?php endif; ?>
            <div>
                <div style="font-weight:800; font-size:16px; color:var(--text-dark);"><?php echo htmlspecialchars($student['name']); ?></div>
                <div style="font-size:13px; color:var(--text-light);"><?php echo htmlspecialchars($student['student_id']); ?> · <?php echo htmlspecialchars($student['course']); ?></div>
            </div>
        </div>

        <?php if($msg == 'uploaded'): ?>
        <div class="alert alert-success">✅ Documents uploaded successfully!</div>
        <?php elseif($msg == 'none'): ?>
        <div class="alert alert-warning">⚠️ No files were uploaded. Please select files.</div>
        <?php elseif($msg == 'deleted'): ?>
        <div class="alert alert-danger">🗑️ Document deleted.</div>
        <?php endif; ?>

        <div class="student-container">

            <!-- UPLOAD FORM -->
            <div class="form-section">
                <div class="box">
                    <div class="box-title">📤 Upload Documents</div>
                    <form method="POST" enctype="multipart/form-data">
                        <label>📅 Month</label>
                        <select name="month">
                            <?php foreach($months as $m): ?>
                            <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>💰 Salary Slip</label>
                        <input type="file" name="salary" accept=".jpg,.jpeg,.png,.pdf">

                        <label>🏦 Bank Statement</label>
                        <input type="file" name="bank" accept=".jpg,.jpeg,.png,.pdf">

                        <label>📊 Monthly Report</label>
                        <input type="file" name="report" accept=".jpg,.jpeg,.png,.pdf">

                        <label>📋 Attendance</label>
                        <input type="file" name="attendance" accept=".jpg,.jpeg,.png,.pdf">

                        <button name="upload" type="submit">📤 Upload Documents</button>
                    </form>
                </div>
            </div>

            <!-- DOCUMENTS TABLE -->
            <div class="table-section">
                <div class="box">
                    <div class="box-title">📅 Month-wise Documents</div>
                    <div class="table-container">
                    <table>
                        <thead>
                        <tr>
                            <th>Month</th>
                            <th>💰 Salary</th>
                            <th>🏦 Bank</th>
                            <th>📊 Report</th>
                            <th>📋 Attendance</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $files = is_dir($folder) ? scandir($folder) : [];

                        foreach($months as $month):
                            $docs = ['salary' => null, 'bank' => null, 'report' => null, 'attendance' => null];

                            foreach($files as $file){
                                foreach($docs as $type => $val){
                                    if(strpos($file, $month . "_" . $type . "_") === 0){
                                        $docs[$type] = $file;
                                    }
                                }
                            }

                            $has_any = array_filter($docs);
                        ?>
                        <tr <?php echo $has_any ? '' : 'style="opacity:0.55;"'; ?>>
                            <td><strong><?php echo $month; ?></strong></td>
                            <?php foreach($docs as $type => $file): ?>
                            <td>
                                <?php if($file): ?>
                                <span class="doc-status uploaded">✅ Done</span>
                                <div style="margin-top:6px; display:flex; gap:5px; justify-content:center; flex-wrap:wrap;">
                                    <?php
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    if(in_array($ext, ['jpg','jpeg','png'])):
                                    ?>
                                    <a href="#" onclick="openPreview('<?php echo $folder . $file; ?>')"
                                       style="font-size:11px; background:rgba(79,70,229,0.1); color:var(--primary); padding:3px 8px; border-radius:5px; text-decoration:none;">👁️ View</a>
                                    <?php else: ?>
                                    <a href="<?php echo $folder . $file; ?>" target="_blank"
                                       style="font-size:11px; background:rgba(6,182,212,0.1); color:var(--secondary); padding:3px 8px; border-radius:5px; text-decoration:none;">📄 Open</a>
                                    <?php endif; ?>
                                    <a href="?id=<?php echo $student_id; ?>&delete=<?php echo urlencode($file); ?>"
                                       onclick="return confirm('Delete this file?')"
                                       style="font-size:11px; background:rgba(239,68,68,0.1); color:var(--danger); padding:3px 8px; border-radius:5px; text-decoration:none;">🗑️</a>
                                </div>
                                <?php else: ?>
                                <span class="doc-status missing">❌ None</span>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- PREVIEW MODAL -->
<div id="previewModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.88); z-index:9999; justify-content:center; align-items:center; backdrop-filter:blur(4px);">
    <span class="modal-close" onclick="closePreview()">✕</span>
    <img id="previewImage" style="max-width:90%; max-height:88vh; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.5);">
</div>

<script>
function openPreview(src){
    document.getElementById("previewModal").style.display = "flex";
    document.getElementById("previewImage").src = src;
}
function closePreview(){
    document.getElementById("previewModal").style.display = "none";
}
document.getElementById("previewModal").addEventListener("click", function(e){
    if(e.target === this) closePreview();
});
</script>

</body>
</html>
