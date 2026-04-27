<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$msg = '';

// Add Fee
if(isset($_POST['add_fee'])){
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $amount = floatval($_POST['amount']);
    $fee_type = mysqli_real_escape_string($conn, $_POST['fee_type']);
    $month = mysqli_real_escape_string($conn, $_POST['month']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $payment_date = $status == 'paid' ? date('Y-m-d') : NULL;
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $pd = $payment_date ? "'$payment_date'" : "NULL";

    mysqli_query($conn, "INSERT INTO fees (student_id,amount,fee_type,month,year,status,payment_date,remarks)
    VALUES ('$student_id',$amount,'$fee_type','$month','$year','$status',$pd,'$remarks')");
    $msg = "✅ Fee record added!";
}

// Mark as Paid
if(isset($_GET['mark_paid'])){
    $fid = intval($_GET['mark_paid']);
    mysqli_query($conn, "UPDATE fees SET status='paid', payment_date='".date('Y-m-d')."' WHERE id=$fid");
    header("Location: fees.php?msg=paid");
    exit();
}

// Delete
if(isset($_GET['delete'])){
    $fid = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM fees WHERE id=$fid");
    header("Location: fees.php?msg=deleted");
    exit();
}

$del_msg = $_GET['msg'] ?? '';

$students_q = mysqli_query($conn, "SELECT * FROM student ORDER BY name");
$fees_q = mysqli_query($conn, "
    SELECT fees.*, student.name as student_name
    FROM fees
    LEFT JOIN student ON fees.student_id = student.student_id
    ORDER BY fees.id DESC
");

// Summary
$total_fees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM fees"))['t'] ?? 0;
$paid_fees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM fees WHERE status='paid'"))['t'] ?? 0;
$unpaid_fees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as t FROM fees WHERE status='unpaid'"))['t'] ?? 0;

// All students for search
$all_q = mysqli_query($conn, "SELECT id, student_id, name, photo, course FROM student ORDER BY name");
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
<title>Fee Management</title>
<link rel="stylesheet" href="style.css">
<style>
.search-wrapper { position:relative; }
.live-dropdown {
    display:none; position:absolute; top:110%; left:0; right:0;
    background:white; border:1px solid #e2e8f0; border-radius:10px;
    max-height:220px; overflow-y:auto; z-index:9999;
    box-shadow:0 8px 24px rgba(79,70,229,0.15);
}
.live-item {
    display:flex; align-items:center; gap:10px;
    padding:10px 14px; cursor:pointer; border-bottom:1px solid #f1f5f9;
    transition:background 0.15s;
}
.live-item:hover { background:#f8fafc; }
.live-item img { width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid #4f46e5; }
.live-item .av { width:34px;height:34px;border-radius:50%;background:rgba(79,70,229,0.1);display:flex;align-items:center;justify-content:center;font-size:16px; }
.live-item .info strong { font-size:13px;display:block;color:#1e293b; }
.live-item .info span { font-size:11px;color:#64748b; }
.live-item .sid { background:rgba(79,70,229,0.1);color:#4f46e5;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700; }
</style>
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
            <a href="fees.php" class="active">💰 Fee Management</a>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="topbar">
            <h1>💰 Fee Management</h1>
        </div>

        <?php if($del_msg == 'deleted'): ?>
        <div style="background:#fee2e2;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:#991b1b;">🗑️ Fee record deleted!</div>
        <?php elseif($del_msg == 'paid'): ?>
        <div style="background:#d1fae5;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:#065f46;">✅ Fee marked as paid!</div>
        <?php endif; ?>

        <?php if($msg): ?>
        <div style="background:#d1fae5;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:#065f46;"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- SUMMARY CARDS -->
        <div class="dashboard" style="margin-bottom:20px;">
            <div class="card">
                <div class="card-icon blue">💰</div>
                <h3>Total Fees</h3>
                <p style="font-size:20px;">₹<?php echo number_format($total_fees, 0); ?></p>
                <div class="card-sub">Total amount</div>
            </div>
            <div class="card">
                <div class="card-icon green">✅</div>
                <h3>Paid</h3>
                <p style="font-size:20px;color:#10b981;">₹<?php echo number_format($paid_fees, 0); ?></p>
                <div class="card-sub">Received amount</div>
            </div>
            <div class="card">
                <div class="card-icon" style="background:rgba(239,68,68,0.1);">⏳</div>
                <h3>Unpaid</h3>
                <p style="font-size:20px;color:#ef4444;">₹<?php echo number_format($unpaid_fees, 0); ?></p>
                <div class="card-sub">Pending amount</div>
            </div>
        </div>

        <!-- ADD FEE FORM -->
        <div class="box">
            <div class="box-title">➕ Add Fee Record</div>
            <form method="POST" action="fees.php">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px;">

                    <!-- Student Search -->
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Select Student *</label>
                        <div class="search-wrapper">
                            <input type="text" id="student_search" placeholder="Name ya ID likhо..."
                                autocomplete="off"
                                style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;"
                                oninput="filterStudents(this.value)">
                            <input type="hidden" name="student_id" id="selected_sid" required>
                            <div class="live-dropdown" id="student_dropdown">
                                <?php foreach($all_students_json as $st): ?>
                                <div class="live-item" onclick="selectStudent('<?php echo $st['student_id']; ?>','<?php echo htmlspecialchars($st['name']); ?>')">
                                    <?php if(!empty($st['photo'])): ?>
                                    <img src="uploads/<?php echo $st['photo']; ?>">
                                    <?php else: ?>
                                    <div class="av">👤</div>
                                    <?php endif; ?>
                                    <div class="info">
                                        <strong><?php echo htmlspecialchars($st['name']); ?></strong>
                                        <span><?php echo $st['course']; ?></span>
                                    </div>
                                    <span class="sid"><?php echo $st['student_id']; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div id="selected_show" style="font-size:12px;color:#10b981;font-weight:700;margin-top:4px;min-height:16px;"></div>
                    </div>

                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Fee Type *</label>
                        <select name="fee_type" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                            <option>Tuition Fee</option>
                            <option>Exam Fee</option>
                            <option>Library Fee</option>
                            <option>Sports Fee</option>
                            <option>Hostel Fee</option>
                            <option>Other</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Amount (₹) *</label>
                        <input type="number" name="amount" placeholder="e.g. 5000" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>

                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Month</label>
                        <select name="month" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                            <?php
                            $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                            $curr_month = date('F');
                            foreach($months as $m):
                            ?>
                            <option <?php echo $m==$curr_month?'selected':''; ?>><?php echo $m; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Year</label>
                        <select name="year" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                            <?php for($y=date('Y'); $y>=date('Y')-3; $y--): ?>
                            <option><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Status</label>
                        <select name="status" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                            <option value="unpaid">⏳ Unpaid</option>
                            <option value="paid">✅ Paid</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Remarks</label>
                    <input type="text" name="remarks" placeholder="Optional note..." style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                </div>

                <button type="submit" name="add_fee" style="background:#4f46e5;color:white;padding:11px 26px;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">💾 Save Fee Record</button>
            </form>
        </div>

        <!-- FEE RECORDS TABLE -->
        <div class="box">
            <div class="box-title">📋 All Fee Records</div>
            <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                <tr style="background:#4f46e5;color:white;">
                    <th style="padding:11px 14px;text-align:left;">#</th>
                    <th style="padding:11px 14px;text-align:left;">Student</th>
                    <th style="padding:11px 14px;text-align:left;">Fee Type</th>
                    <th style="padding:11px 14px;text-align:center;">Amount</th>
                    <th style="padding:11px 14px;text-align:center;">Month/Year</th>
                    <th style="padding:11px 14px;text-align:center;">Status</th>
                    <th style="padding:11px 14px;text-align:center;">Payment Date</th>
                    <th style="padding:11px 14px;text-align:center;">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php $i=1; while($f = mysqli_fetch_assoc($fees_q)): ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:10px 14px;color:#64748b;"><?php echo $i++; ?></td>
                    <td style="padding:10px 14px;font-weight:700;"><?php echo htmlspecialchars($f['student_name'] ?? $f['student_id']); ?></td>
                    <td style="padding:10px 14px;"><?php echo htmlspecialchars($f['fee_type']); ?></td>
                    <td style="padding:10px 14px;text-align:center;font-weight:700;color:#1e293b;">₹<?php echo number_format($f['amount'],0); ?></td>
                    <td style="padding:10px 14px;text-align:center;font-size:13px;color:#64748b;"><?php echo $f['month'].' '.$f['year']; ?></td>
                    <td style="padding:10px 14px;text-align:center;">
                        <?php if($f['status']=='paid'): ?>
                        <span style="background:#d1fae5;color:#065f46;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">✅ Paid</span>
                        <?php else: ?>
                        <span style="background:#fee2e2;color:#991b1b;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">⏳ Unpaid</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 14px;text-align:center;font-size:13px;color:#64748b;">
                        <?php echo $f['payment_date'] ? date('d M Y', strtotime($f['payment_date'])) : '—'; ?>
                    </td>
                    <td style="padding:10px 14px;text-align:center;">
                        <div style="display:flex;gap:6px;justify-content:center;">
                            <?php if($f['status']=='unpaid'): ?>
                            <a href="?mark_paid=<?php echo $f['id']; ?>" onclick="return confirm('Mark as paid?')" style="background:#10b981;color:white;padding:5px 10px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">✅ Mark Paid</a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $f['id']; ?>" onclick="return confirm('Delete?')" style="background:#ef4444;color:white;padding:5px 10px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>

    </div>
</div>

<script>
const allStudents = <?php echo json_encode($all_students_json); ?>;

function filterStudents(val){
    const dd = document.getElementById('student_dropdown');
    const items = dd.querySelectorAll('.live-item');
    if(val.length === 0){ dd.style.display='none'; return; }
    let found = false;
    items.forEach(item => {
        const name = item.querySelector('strong').innerText.toLowerCase();
        const sid = item.querySelector('.sid').innerText.toLowerCase();
        if(name.includes(val.toLowerCase()) || sid.includes(val.toLowerCase())){
            item.style.display='flex'; found=true;
        } else {
            item.style.display='none';
        }
    });
    dd.style.display = found ? 'block' : 'none';
}

function selectStudent(id, name){
    document.getElementById('student_search').value = name+' ('+id+')';
    document.getElementById('selected_sid').value = id;
    document.getElementById('student_dropdown').style.display = 'none';
    document.getElementById('selected_show').innerHTML = '✅ Selected: <strong>'+name+'</strong>';
}

document.addEventListener('click', function(e){
    if(!e.target.closest('.search-wrapper')){
        document.getElementById('student_dropdown').style.display='none';
    }
});
</script>
</body>
</html>