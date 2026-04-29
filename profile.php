<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: students.php");
    exit();
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM student WHERE id=$id");

if(mysqli_num_rows($result) == 0){
    header("Location: students.php");
    exit();
}

$row = mysqli_fetch_assoc($result);

// Documents check
$folder = "uploads/" . $id . "/";
$has_docs = is_dir($folder) && count(array_diff(scandir($folder), ['.','..'])) > 0;

// Results
$results_q = mysqli_query($conn, "
    SELECT results.*, exams.exam_name, exams.exam_date
    FROM results
    LEFT JOIN exams ON results.exam_id = exams.id
    WHERE results.student_id='".$row['student_id']."'
    ORDER BY results.id DESC
");

// Attendance
$total_days = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE student_id='".$row['student_id']."'"))['t'];
$present_days = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE student_id='".$row['student_id']."' AND status='present'"))['t'];
$absent_days = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE student_id='".$row['student_id']."' AND status='absent'"))['t'];
$late_days = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM attendance WHERE student_id='".$row['student_id']."' AND status='late'"))['t'];
$att_percent = $total_days > 0 ? round(($present_days/$total_days)*100, 1) : 0;

// Fees
$fees_q = mysqli_query($conn, "SELECT * FROM fees WHERE student_id='".$row['student_id']."' ORDER BY id DESC");
$total_fee = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM fees WHERE student_id='".$row['student_id']."'"))['t'] ?? 0;
$paid_fee = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM fees WHERE student_id='".$row['student_id']."' AND status='paid'"))['t'] ?? 0;
$unpaid_fee = $total_fee - $paid_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($row['name']); ?> — Profile</title>
<link rel="stylesheet" href="style.css">
<style>
.profile-hero {
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    border-radius: 16px;
    padding: 30px;
    color: white;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 20px;
}
.profile-hero img {
    width: 100px; height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    flex-shrink: 0;
}
.profile-hero .avatar {
    width: 100px; height: 100px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 44px;
    border: 4px solid white;
    flex-shrink: 0;
}
.profile-hero h2 { font-size: 24px; font-weight: 800; margin-bottom: 6px; }
.profile-hero p { font-size: 13px; opacity: 0.85; margin-bottom: 4px; }
.info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}
.info-box {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 12px rgba(79,70,229,0.08);
    border: 1px solid #e2e8f0;
}
.info-box label {
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    display: block;
    margin-bottom: 4px;
}
.info-box span {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
}
.att-bar { height: 8px; background: #e2e8f0; border-radius: 20px; overflow: hidden; margin: 6px 0; }
.att-bar-fill { height: 100%; border-radius: 20px; transition: width 1s ease; }
</style>
</head>
<body>

<div class="main-container">

    <?php include_once('sidebar.php'); ?>

    <div class="content">
        <div class="topbar">
            <h1>👤 Student Profile</h1>
            <div class="topbar-right">
                <a href="edit.php?id=<?php echo $row['id']; ?>" style="padding:9px 16px;background:#4f46e5;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">✏️ Edit</a>
                <a href="admin_student_id_card.php?id=<?php echo $row['id']; ?>" style="padding:9px 16px;background:#8b5cf6;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">🪪 ID Card</a>
                <a href="students.php" style="padding:9px 16px;background:#e2e8f0;color:#1e293b;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">← Back</a>
            </div>
        </div>

        <!-- HERO -->
        <div class="profile-hero">
            <?php if(!empty($row['photo'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="Photo">
            <?php else: ?>
            <div class="avatar">👤</div>
            <?php endif; ?>
            <div>
                <h2><?php echo htmlspecialchars($row['name']); ?></h2>
                <p>🪪 ID: <strong><?php echo htmlspecialchars($row['student_id']); ?></strong></p>
                <p>📚 Course: <strong><?php echo htmlspecialchars($row['course']); ?></strong></p>
                <?php if(!empty($row['father_name'])): ?>
                <p>👨 Father: <strong><?php echo htmlspecialchars($row['father_name']); ?></strong></p>
                <?php endif; ?>
            </div>
            <div style="margin-left:auto;text-align:right;">
                <div style="background:rgba(255,255,255,0.2);padding:10px 18px;border-radius:10px;margin-bottom:8px;">
                    <div style="font-size:11px;opacity:0.8;">Attendance</div>
                    <div style="font-size:24px;font-weight:800;"><?php echo $att_percent; ?>%</div>
                </div>
                <div style="background:rgba(255,255,255,0.2);padding:10px 18px;border-radius:10px;">
                    <div style="font-size:11px;opacity:0.8;">Fee Pending</div>
                    <div style="font-size:20px;font-weight:800;">₹<?php echo number_format($unpaid_fee, 0); ?></div>
                </div>
            </div>
        </div>

        <!-- INFO GRID -->
        <div class="info-grid">
            <div class="info-box">
                <label>📧 Email</label>
                <span style="font-size:13px;"><?php echo htmlspecialchars($row['email']); ?></span>
            </div>
            <div class="info-box">
                <label>📱 Mobile</label>
                <span><?php echo htmlspecialchars($row['mobile']); ?></span>
            </div>
            <div class="info-box">
                <label>💬 WhatsApp</label>
                <span><?php echo !empty($row['whatsapp']) ? htmlspecialchars($row['whatsapp']) : '—'; ?></span>
            </div>
            <div class="info-box">
                <label>🎂 Date of Birth</label>
                <span><?php echo !empty($row['dob']) ? date('d M Y', strtotime($row['dob'])) : '—'; ?></span>
            </div>
            <div class="info-box">
                <label>📅 Date of Joining</label>
                <span><?php echo !empty($row['doj']) ? date('d M Y', strtotime($row['doj'])) : '—'; ?></span>
            </div>
            <div class="info-box">
                <label>📮 Pin Code</label>
                <span><?php echo htmlspecialchars($row['pincode']); ?></span>
            </div>
            <div class="info-box" style="grid-column:span 3;">
                <label>🪪 Aadhaar Number</label>
                <span><?php echo htmlspecialchars($row['aadhaar']); ?></span>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="box">
            <div class="box-title">⚡ Quick Actions</div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="edit.php?id=<?php echo $row['id']; ?>" style="padding:10px 18px;background:#4f46e5;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">✏️ Edit Student</a>
                <a href="admin_student_id_card.php?id=<?php echo $row['id']; ?>" style="padding:10px 18px;background:#8b5cf6;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">🪪 ID Card</a>
                <a href="documents.php?id=<?php echo $row['id']; ?>" style="padding:10px 18px;background:#10b981;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">📁 Documents <?php echo $has_docs ? '✅' : ''; ?></a>
                <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete permanently?')" style="padding:10px 18px;background:#ef4444;color:white;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">🗑️ Delete</a>
            </div>
        </div>

        <!-- ATTENDANCE -->
        <?php if($total_days > 0): ?>
        <div class="box">
            <div class="box-title">✅ Attendance</div>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:14px;">
                <div style="text-align:center;background:#f8fafc;border-radius:10px;padding:14px;">
                    <div style="font-size:22px;font-weight:800;color:#4f46e5;"><?php echo $total_days; ?></div>
                    <div style="font-size:12px;color:#64748b;font-weight:700;">Total Days</div>
                </div>
                <div style="text-align:center;background:#d1fae5;border-radius:10px;padding:14px;">
                    <div style="font-size:22px;font-weight:800;color:#10b981;"><?php echo $present_days; ?></div>
                    <div style="font-size:12px;color:#065f46;font-weight:700;">✅ Present</div>
                </div>
                <div style="text-align:center;background:#fee2e2;border-radius:10px;padding:14px;">
                    <div style="font-size:22px;font-weight:800;color:#ef4444;"><?php echo $absent_days; ?></div>
                    <div style="font-size:12px;color:#991b1b;font-weight:700;">❌ Absent</div>
                </div>
                <div style="text-align:center;background:#fef3c7;border-radius:10px;padding:14px;">
                    <div style="font-size:22px;font-weight:800;color:#f59e0b;"><?php echo $late_days; ?></div>
                    <div style="font-size:12px;color:#92400e;font-weight:700;">⏰ Late</div>
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;margin-bottom:4px;">
                <span>Attendance %</span>
                <span style="color:<?php echo $att_percent>=75?'#10b981':($att_percent>=50?'#f59e0b':'#ef4444'); ?>;"><?php echo $att_percent; ?>%</span>
            </div>
            <div class="att-bar">
                <div class="att-bar-fill" id="attBar" style="width:0%;background:<?php echo $att_percent>=75?'#10b981':($att_percent>=50?'#f59e0b':'#ef4444'); ?>;"></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- FEES -->
        <?php if(mysqli_num_rows($fees_q) > 0): ?>
        <div class="box">
            <div class="box-title">💰 Fee Details</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:16px;">
                <div style="background:#f8fafc;border-radius:10px;padding:14px;text-align:center;">
                    <div style="font-size:11px;color:#64748b;font-weight:700;margin-bottom:4px;">TOTAL</div>
                    <div style="font-size:20px;font-weight:800;color:#1e293b;">₹<?php echo number_format($total_fee,0); ?></div>
                </div>
                <div style="background:#d1fae5;border-radius:10px;padding:14px;text-align:center;">
                    <div style="font-size:11px;color:#065f46;font-weight:700;margin-bottom:4px;">PAID</div>
                    <div style="font-size:20px;font-weight:800;color:#10b981;">₹<?php echo number_format($paid_fee,0); ?></div>
                </div>
                <div style="background:#fee2e2;border-radius:10px;padding:14px;text-align:center;">
                    <div style="font-size:11px;color:#991b1b;font-weight:700;margin-bottom:4px;">UNPAID</div>
                    <div style="font-size:20px;font-weight:800;color:#ef4444;">₹<?php echo number_format($unpaid_fee,0); ?></div>
                </div>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                <tr style="background:#4f46e5;color:white;">
                    <th style="padding:10px 14px;text-align:left;">Fee Type</th>
                    <th style="padding:10px 14px;text-align:center;">Amount</th>
                    <th style="padding:10px 14px;text-align:center;">Month</th>
                    <th style="padding:10px 14px;text-align:center;">Status</th>
                    <th style="padding:10px 14px;text-align:center;">Receipt</th>
                </tr>
                </thead>
                <tbody>
                <?php while($f = mysqli_fetch_assoc($fees_q)): ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:10px 14px;font-weight:700;"><?php echo htmlspecialchars($f['fee_type']); ?></td>
                    <td style="padding:10px 14px;text-align:center;font-weight:700;">₹<?php echo number_format($f['amount'],0); ?></td>
                    <td style="padding:10px 14px;text-align:center;font-size:13px;color:#64748b;"><?php echo $f['month'].' '.$f['year']; ?></td>
                    <td style="padding:10px 14px;text-align:center;">
                        <span style="background:<?php echo $f['status']=='paid'?'#d1fae5':'#fee2e2'; ?>;color:<?php echo $f['status']=='paid'?'#065f46':'#991b1b'; ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                            <?php echo $f['status']=='paid'?'✅ Paid':'⏳ Unpaid'; ?>
                        </span>
                    </td>
                    <td style="padding:10px 14px;text-align:center;">
                        <?php if($f['status']=='paid'): ?>
                        <a href="fee_receipt.php?id=<?php echo $f['id']; ?>" style="background:#06b6d4;color:white;padding:5px 10px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">🧾 Receipt</a>
                        <?php else: echo '—'; endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- RESULTS -->
        <?php if(mysqli_num_rows($results_q) > 0): ?>
        <div class="box">
            <div class="box-title">📄 Exam Results</div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                <tr style="background:#4f46e5;color:white;">
                    <th style="padding:10px 14px;text-align:left;">#</th>
                    <th style="padding:10px 14px;text-align:left;">Exam</th>
                    <th style="padding:10px 14px;text-align:left;">Subject</th>
                    <th style="padding:10px 14px;text-align:center;">Marks</th>
                    <th style="padding:10px 14px;text-align:center;">Grade</th>
                    <th style="padding:10px 14px;text-align:center;">Status</th>
                </tr>
                </thead>
                <tbody>
                <?php $i=1; while($r = mysqli_fetch_assoc($results_q)):
                    $m = $r['marks'];
                    $g = $m>=90?'A+':($m>=80?'A':($m>=70?'B':($m>=60?'C':($m>=50?'D':'F'))));
                    $st = $m >= 33 ? 'Pass' : 'Fail';
                ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:10px 14px;color:#64748b;"><?php echo $i++; ?></td>
                    <td style="padding:10px 14px;font-weight:700;"><?php echo htmlspecialchars($r['exam_name'] ?? 'N/A'); ?></td>
                    <td style="padding:10px 14px;"><?php echo htmlspecialchars($r['subject'] ?? 'N/A'); ?></td>
                    <td style="padding:10px 14px;text-align:center;font-weight:700;"><?php echo $r['marks']; ?>/100</td>
                    <td style="padding:10px 14px;text-align:center;">
                        <span style="background:rgba(79,70,229,0.1);color:#4f46e5;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?php echo $g; ?></span>
                    </td>
                    <td style="padding:10px 14px;text-align:center;">
                        <span style="background:<?php echo $st=='Pass'?'rgba(16,185,129,0.1)':'rgba(239,68,68,0.1)'; ?>;color:<?php echo $st=='Pass'?'#059669':'#dc2626'; ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                            <?php echo $st=='Pass'?'✅ Pass':'❌ Fail'; ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
window.onload = function(){
    const bar = document.getElementById('attBar');
    if(bar){
        setTimeout(() => { bar.style.width = '<?php echo $att_percent; ?>%'; }, 300);
    }
}
</script>

</body>
</html>