<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$msg = '';
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Save Attendance
if(isset($_POST['save_attendance'])){
    $date = $_POST['date'];
    $students_list = mysqli_query($conn, "SELECT student_id FROM student");
    while($s = mysqli_fetch_assoc($students_list)){
        $sid = $s['student_id'];
        $status = isset($_POST['attendance'][$sid]) ? $_POST['attendance'][$sid] : 'absent';
        $status = mysqli_real_escape_string($conn, $status);
        mysqli_query($conn, "INSERT INTO attendance (student_id, date, status, marked_by)
            VALUES ('$sid','$date','$status','Admin')
            ON DUPLICATE KEY UPDATE status='$status'");
    }
    $msg = "✅ Attendance saved for $date!";
}

$students_q = mysqli_query($conn, "SELECT * FROM student ORDER BY name");

// Get existing attendance for selected date
$existing = [];
$att_q = mysqli_query($conn, "SELECT * FROM attendance WHERE date='$selected_date'");
while($a = mysqli_fetch_assoc($att_q)){
    $existing[$a['student_id']] = $a['status'];
}

// Attendance Summary
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM student"))['t'];
$present_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE date='$selected_date' AND status='present'"))['t'];
$absent_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE date='$selected_date' AND status='absent'"))['t'];
$late_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE date='$selected_date' AND status='late'"))['t'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance</title>
<link rel="stylesheet" href="style.css">
<style>
.att-btn { padding:7px 16px; border:2px solid #e2e8f0; border-radius:8px; cursor:pointer; font-size:13px; font-weight:700; background:white; transition:all 0.2s; }
.att-btn.present { background:#d1fae5; border-color:#10b981; color:#065f46; }
.att-btn.absent { background:#fee2e2; border-color:#ef4444; color:#991b1b; }
.att-btn.late { background:#fef3c7; border-color:#f59e0b; color:#92400e; }
</style>
</head>
<body>
<div class="main-container">
     <!-- SIDEBAR -->
   <?php include_once('sidebar.php'); ?>

    <div class="content">
        <div class="topbar">
            <h1>✅ Attendance Management</h1>
            <div class="topbar-right">
                <div class="admin-badge">📅 <?php echo date('d M Y', strtotime($selected_date)); ?></div>
            </div>
        </div>

        <?php if($msg): ?>
        <div style="background:#d1fae5;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:#065f46;"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- SUMMARY CARDS -->
        <div class="dashboard" style="margin-bottom:20px;">
            <div class="card">
                <div class="card-icon blue">👨‍🎓</div>
                <h3>Total Students</h3>
                <p><?php echo $total_students; ?></p>
            </div>
            <div class="card">
                <div class="card-icon green">✅</div>
                <h3>Present</h3>
                <p style="color:#10b981;"><?php echo $present_today; ?></p>
            </div>
            <div class="card">
                <div class="card-icon" style="background:rgba(239,68,68,0.1);">❌</div>
                <h3>Absent</h3>
                <p style="color:#ef4444;"><?php echo $absent_today; ?></p>
            </div>
            <div class="card">
                <div class="card-icon amber">⏰</div>
                <h3>Late</h3>
                <p style="color:#f59e0b;"><?php echo $late_today; ?></p>
            </div>
        </div>

        <!-- DATE SELECT -->
        <div class="box">
            <div class="box-title">📅 Date Select Karo</div>
            <form method="GET" style="display:flex;gap:12px;align-items:center;">
                <input type="date" name="date" value="<?php echo $selected_date; ?>" style="padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                <button type="submit" style="background:#4f46e5;color:white;padding:10px 20px;border:none;border-radius:8px;cursor:pointer;font-weight:700;">📅 Load</button>
            </form>
        </div>

        <!-- MARK ATTENDANCE -->
        <div class="box">
            <div class="box-title">✅ Mark Attendance — <?php echo date('d M Y', strtotime($selected_date)); ?></div>
            <div style="display:flex;gap:10px;margin-bottom:16px;">
                <button onclick="markAll('present')" style="background:#10b981;color:white;padding:8px 16px;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:13px;">✅ All Present</button>
                <button onclick="markAll('absent')" style="background:#ef4444;color:white;padding:8px 16px;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:13px;">❌ All Absent</button>
            </div>
            <form method="POST" action="attendance.php">
                <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                    <tr style="background:#4f46e5;color:white;">
                        <th style="padding:11px 14px;text-align:left;">#</th>
                        <th style="padding:11px 14px;text-align:left;">Photo</th>
                        <th style="padding:11px 14px;text-align:left;">Student</th>
                        <th style="padding:11px 14px;text-align:left;">ID</th>
                        <th style="padding:11px 14px;text-align:left;">Course</th>
                        <th style="padding:11px 14px;text-align:center;">Attendance</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i=1; mysqli_data_seek($students_q,0); while($s = mysqli_fetch_assoc($students_q)):
                        $current = $existing[$s['student_id']] ?? 'present';
                    ?>
                    <tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:10px 14px;color:#64748b;"><?php echo $i++; ?></td>
                        <td style="padding:10px 14px;">
                            <?php if(!empty($s['photo'])): ?>
                            <img src="uploads/<?php echo $s['photo']; ?>" style="width:38px;height:38px;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                            <div style="width:38px;height:38px;border-radius:50%;background:rgba(79,70,229,0.1);display:flex;align-items:center;justify-content:center;">👤</div>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px 14px;font-weight:700;"><?php echo htmlspecialchars($s['name']); ?></td>
                        <td style="padding:10px 14px;"><span style="background:rgba(79,70,229,0.1);color:#4f46e5;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?php echo $s['student_id']; ?></span></td>
                        <td style="padding:10px 14px;"><?php echo htmlspecialchars($s['course']); ?></td>
                        <td style="padding:10px 14px;text-align:center;">
                            <div style="display:flex;gap:8px;justify-content:center;">
                                <label>
                                    <input type="radio" name="attendance[<?php echo $s['student_id']; ?>]" value="present" <?php echo $current=='present'?'checked':''; ?> style="display:none;" class="att-radio" data-sid="<?php echo $s['student_id']; ?>">
                                    <span class="att-btn <?php echo $current=='present'?'present':''; ?>" onclick="setAtt('<?php echo $s['student_id']; ?>','present',this)">✅ Present</span>
                                </label>
                                <label>
                                    <input type="radio" name="attendance[<?php echo $s['student_id']; ?>]" value="absent" <?php echo $current=='absent'?'checked':''; ?> style="display:none;" class="att-radio" data-sid="<?php echo $s['student_id']; ?>">
                                    <span class="att-btn <?php echo $current=='absent'?'absent':''; ?>" onclick="setAtt('<?php echo $s['student_id']; ?>','absent',this)">❌ Absent</span>
                                </label>
                                <label>
                                    <input type="radio" name="attendance[<?php echo $s['student_id']; ?>]" value="late" <?php echo $current=='late'?'checked':''; ?> style="display:none;" class="att-radio" data-sid="<?php echo $s['student_id']; ?>">
                                    <span class="att-btn <?php echo $current=='late'?'late':''; ?>" onclick="setAtt('<?php echo $s['student_id']; ?>','late',this)">⏰ Late</span>
                                </label>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <div style="margin-top:18px;">
                    <button type="submit" name="save_attendance" style="background:#4f46e5;color:white;padding:12px 30px;border:none;border-radius:8px;cursor:pointer;font-size:15px;font-weight:700;">💾 Save Attendance</button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
function setAtt(sid, status, el) {
    // Radio check
    const radios = document.querySelectorAll(`input[name="attendance[${sid}]"]`);
    radios.forEach(r => r.checked = r.value === status);

    // Button highlight
    const btns = el.closest('div').querySelectorAll('.att-btn');
    btns.forEach(b => b.className = 'att-btn');
    el.classList.add(status);
}

function markAll(status) {
    document.querySelectorAll('.att-radio').forEach(r => {
        if(r.value === status) r.checked = true;
    });
    document.querySelectorAll('.att-btn').forEach(b => {
        b.className = 'att-btn';
    });
    document.querySelectorAll(`.att-btn`).forEach(b => {
        const label = b.closest('label');
        const radio = label.querySelector('input');
        if(radio.value === status) b.classList.add(status);
    });
}
</script>
</body>
</html>