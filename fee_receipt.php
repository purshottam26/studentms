<?php
session_start();
include 'db.php';

$is_admin = isset($_SESSION['admin']);
$is_student = isset($_SESSION['student_id']);

if(!$is_admin && !$is_student){
    header("Location: login.php");
    exit();
}

$id = intval($_GET['id']);
$fee_q = mysqli_query($conn, "
    SELECT fees.*, student.name, student.course, student.email, student.mobile, student.photo
    FROM fees
    LEFT JOIN student ON fees.student_id = student.student_id
    WHERE fees.id = $id
");

if(mysqli_num_rows($fee_q) == 0){
    echo "Receipt not found!"; exit();
}

$fee = mysqli_fetch_assoc($fee_q);

// Student sirf apni receipt dekh sakta hai
if(!$is_admin && $is_student && $fee['student_id'] != $_SESSION['student_id']){
    echo "Access denied!"; exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Receipt</title>
<link rel="stylesheet" href="style.css">
<style>
@media print {
    .no-print { display:none !important; }
    body { background:#fff; }
    .receipt { box-shadow:none !important; }
}
body { background:#f1f5f9; }
.receipt {
    max-width: 680px;
    margin: 30px auto;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(79,70,229,0.2);
}
.receipt-header {
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    padding: 30px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.receipt-header h1 { font-size: 22px; margin-bottom: 4px; }
.receipt-header p { font-size: 12px; opacity: 0.85; }
.receipt-no {
    background: rgba(255,255,255,0.2);
    padding: 10px 18px;
    border-radius: 10px;
    text-align: center;
    min-width: 120px;
}
.receipt-no span { font-size: 11px; opacity: 0.85; display: block; }
.receipt-no strong { font-size: 16px; letter-spacing: 1px; }
.receipt-body { padding: 28px 30px; }
.student-info {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 24px;
}
.student-photo {
    width: 60px; height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #4f46e5;
    flex-shrink: 0;
}
.student-avatar {
    width: 60px; height: 60px;
    border-radius: 50%;
    background: rgba(79,70,229,0.1);
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    flex-shrink: 0;
}
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
}
.info-item label {
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    display: block;
    margin-bottom: 4px;
}
.info-item span {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
}
.amount-box {
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    border-radius: 12px;
    padding: 20px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
    margin-bottom: 24px;
}
.amount-box .label { font-size: 13px; opacity: 0.85; margin-bottom: 4px; }
.amount-box .amount { font-size: 32px; font-weight: 800; }
.status-badge {
    background: rgba(255,255,255,0.2);
    padding: 8px 18px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
}
.receipt-footer {
    background: #f8fafc;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #e2e8f0;
}
.sign-box { text-align: center; }
.sign-line { width: 130px; border-top: 2px solid #4f46e5; margin: 0 auto 6px; }
.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 80px;
    font-weight: 900;
    color: rgba(16,185,129,0.06);
    white-space: nowrap;
    pointer-events: none;
    user-select: none;
}
</style>
</head>
<body>

<div class="no-print" style="max-width:680px;margin:20px auto;display:flex;gap:12px;padding-top:20px;">
    <button onclick="window.print()" style="padding:11px 22px;background:#4f46e5;color:white;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">🖨️ Print Receipt</button>
    <?php if($is_admin): ?>
    <a href="fees.php" style="padding:11px 22px;background:#e2e8f0;color:#1e293b;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">← Back to Fees</a>
    <?php else: ?>
    <a href="student_dashboard.php" style="padding:11px 22px;background:#e2e8f0;color:#1e293b;border-radius:8px;text-decoration:none;font-size:14px;font-weight:700;">← Back</a>
    <?php endif; ?>
</div>

<div class="receipt" style="position:relative;">

    <?php if($fee['status'] == 'paid'): ?>
    <div class="watermark">PAID</div>
    <?php endif; ?>

    <div class="receipt-header">
        <div>
            <div style="font-size:32px;margin-bottom:8px;">🎓</div>
            <h1>Student Management System</h1>
            <p>Official Fee Payment Receipt</p>
            <p>Date: <?php echo date('d M Y'); ?></p>
        </div>
        <div class="receipt-no">
            <span>Receipt No.</span>
            <strong>RCP<?php echo str_pad($fee['id'], 5, '0', STR_PAD_LEFT); ?></strong>
        </div>
    </div>

    <div class="receipt-body">

        <div class="student-info">
            <?php if(!empty($fee['photo'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($fee['photo']); ?>" class="student-photo">
            <?php else: ?>
            <div class="student-avatar">👤</div>
            <?php endif; ?>
            <div>
                <div style="font-size:18px;font-weight:800;color:#1e293b;"><?php echo htmlspecialchars($fee['name']); ?></div>
                <div style="font-size:13px;color:#64748b;margin-top:2px;">
                    ID: <strong><?php echo $fee['student_id']; ?></strong> &nbsp;|&nbsp;
                    Course: <strong><?php echo htmlspecialchars($fee['course']); ?></strong>
                </div>
                <div style="font-size:12px;color:#94a3b8;margin-top:2px;">
                    📧 <?php echo htmlspecialchars($fee['email']); ?> &nbsp;|&nbsp;
                    📞 <?php echo htmlspecialchars($fee['mobile']); ?>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Fee Type</label>
                <span>💳 <?php echo htmlspecialchars($fee['fee_type']); ?></span>
            </div>
            <div class="info-item">
                <label>Period</label>
                <span>📅 <?php echo $fee['month'].' '.$fee['year']; ?></span>
            </div>
            <div class="info-item">
                <label>Payment Date</label>
                <span>🗓️ <?php echo $fee['payment_date'] ? date('d M Y', strtotime($fee['payment_date'])) : 'Not Paid'; ?></span>
            </div>
            <div class="info-item">
                <label>Status</label>
                <span style="color:<?php echo $fee['status']=='paid'?'#10b981':'#ef4444'; ?>;">
                    <?php echo $fee['status']=='paid'?'✅ Paid':'⏳ Unpaid'; ?>
                </span>
            </div>
            <?php if(!empty($fee['remarks'])): ?>
            <div class="info-item" style="grid-column:1/-1;">
                <label>Remarks</label>
                <span>📝 <?php echo htmlspecialchars($fee['remarks']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="amount-box">
            <div>
                <div class="label">Total Amount</div>
                <div class="amount">₹<?php echo number_format($fee['amount'], 2); ?></div>
            </div>
            <div class="status-badge">
                <?php echo $fee['status']=='paid'?'✅ PAID':'⏳ UNPAID'; ?>
            </div>
        </div>

    </div>

    <div class="receipt-footer">
        <div class="sign-box">
            <div class="sign-line"></div>
            <div style="font-size:12px;color:#64748b;font-weight:700;">Student Signature</div>
        </div>
        <div style="text-align:center;">
            <div style="font-size:20px;">🎓</div>
            <div style="font-size:10px;color:#94a3b8;">Official Receipt</div>
            <div style="font-size:10px;color:#94a3b8;">Generated: <?php echo date('d M Y, h:i A'); ?></div>
        </div>
        <div class="sign-box">
            <div class="sign-line"></div>
            <div style="font-size:12px;color:#64748b;font-weight:700;">Authorized Signature</div>
        </div>
    </div>

</div>

</body>
</html>