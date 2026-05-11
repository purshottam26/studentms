<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$msg = '';

// Add
if(isset($_POST['add_tt'])){
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $day = mysqli_real_escape_string($conn, $_POST['day']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $teacher_name = mysqli_real_escape_string($conn, $_POST['teacher_name']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $room = mysqli_real_escape_string($conn, $_POST['room']);

    mysqli_query($conn, "INSERT INTO timetable (course,day,subject,teacher_name,start_time,end_time,room)
    VALUES ('$course','$day','$subject','$teacher_name','$start_time','$end_time','$room')");
    $msg = "✅ Timetable entry added!";
}

// Delete
if(isset($_GET['delete'])){
    $did = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM timetable WHERE id=$did");
    header("Location: timetable.php?msg=deleted");
    exit();
}

$del_msg = $_GET['msg'] ?? '';

// Get courses
$courses_q = mysqli_query($conn, "SELECT DISTINCT course FROM student ORDER BY course");
$courses = [];
while($c = mysqli_fetch_assoc($courses_q)) $courses[] = $c['course'];

// Get teachers
$teachers_q = mysqli_query($conn, "SELECT name FROM teachers ORDER BY name");

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

// Get timetable grouped by course
$filter_course = $_GET['course'] ?? ($courses[0] ?? '');
$tt_q = mysqli_query($conn, "SELECT * FROM timetable WHERE course='".mysqli_real_escape_string($conn,$filter_course)."' ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), start_time");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Timetable Management</title>
<link rel="stylesheet" href="style.css">
<style>
.day-badge { padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }
.day-monday { background:rgba(79,70,229,0.1); color:#4f46e5; }
.day-tuesday { background:rgba(6,182,212,0.1); color:#0891b2; }
.day-wednesday { background:rgba(16,185,129,0.1); color:#059669; }
.day-thursday { background:rgba(245,158,11,0.1); color:#d97706; }
.day-friday { background:rgba(239,68,68,0.1); color:#dc2626; }
.day-saturday { background:rgba(139,92,246,0.1); color:#7c3aed; }
</style>
</head>
<body>
<div class="main-container">
    <?php include_once('sidebar.php'); ?>

    <div class="content">
        <div class="topbar">
            <h1>📅 Timetable Management</h1>
            <div class="topbar-right">
                <div class="admin-badge">👤 <?php echo htmlspecialchars($_SESSION['admin']); ?></div>
            </div>
        </div>

        <?php if($del_msg == 'deleted'): ?>
        <div style="background:#fee2e2;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:#991b1b;">🗑️ Entry deleted!</div>
        <?php endif; ?>
        <?php if($msg): ?>
        <div style="background:#d1fae5;border-radius:10px;padding:12px 18px;margin-bottom:18px;font-weight:700;color:#065f46;"><?php echo $msg; ?></div>
        <?php endif; ?>

        <!-- ADD FORM -->
        <div class="box">
            <div class="box-title">➕ Add Timetable Entry</div>
            <form method="POST" action="timetable.php">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Course *</label>
                        <select name="course" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                            <?php foreach($courses as $c): ?>
                            <option><?php echo htmlspecialchars($c); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Day *</label>
                        <select name="day" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                            <?php foreach($days as $d): ?>
                            <option><?php echo $d; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Subject *</label>
                        <input type="text" name="subject" placeholder="Subject name" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Teacher</label>
                        <select name="teacher_name" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                            <option value="">-- Select Teacher --</option>
                            <?php
                            mysqli_data_seek($teachers_q, 0);
                            while($t = mysqli_fetch_assoc($teachers_q)):
                            ?>
                            <option><?php echo htmlspecialchars($t['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Start Time *</label>
                        <input type="time" name="start_time" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">End Time *</label>
                        <input type="time" name="end_time" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Room</label>
                        <input type="text" name="room" placeholder="e.g. Room 101" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                </div>
                <button type="submit" name="add_tt" style="background:#4f46e5;color:white;padding:10px 24px;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">➕ Add Entry</button>
            </form>
        </div>

        <!-- FILTER BY COURSE -->
        <div class="box">
            <div class="box-title">📅 View Timetable</div>
            <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
                <?php foreach($courses as $c): ?>
                <a href="?course=<?php echo urlencode($c); ?>"
                   style="padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;
                   background:<?php echo $c==$filter_course?'#4f46e5':'#f1f5f9'; ?>;
                   color:<?php echo $c==$filter_course?'white':'#64748b'; ?>;">
                    <?php echo htmlspecialchars($c); ?>
                </a>
                <?php endforeach; ?>
            </div>

            <table style="width:100%;border-collapse:collapse;">
                <thead>
                <tr style="background:#4f46e5;color:white;">
                    <th style="padding:11px 14px;text-align:left;">Day</th>
                    <th style="padding:11px 14px;text-align:left;">Subject</th>
                    <th style="padding:11px 14px;text-align:left;">Teacher</th>
                    <th style="padding:11px 14px;text-align:center;">Time</th>
                    <th style="padding:11px 14px;text-align:center;">Room</th>
                    <th style="padding:11px 14px;text-align:center;">Delete</th>
                </tr>
                </thead>
                <tbody>
                <?php while($tt = mysqli_fetch_assoc($tt_q)): ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:11px 14px;">
                        <span class="day-badge day-<?php echo strtolower($tt['day']); ?>">
                            <?php echo $tt['day']; ?>
                        </span>
                    </td>
                    <td style="padding:11px 14px;font-weight:700;"><?php echo htmlspecialchars($tt['subject']); ?></td>
                    <td style="padding:11px 14px;color:#64748b;"><?php echo htmlspecialchars($tt['teacher_name'] ?? '—'); ?></td>
                    <td style="padding:11px 14px;text-align:center;font-weight:700;color:#4f46e5;">
                        <?php echo date('h:i A', strtotime($tt['start_time'])); ?> — <?php echo date('h:i A', strtotime($tt['end_time'])); ?>
                    </td>
                    <td style="padding:11px 14px;text-align:center;color:#64748b;"><?php echo htmlspecialchars($tt['room'] ?? '—'); ?></td>
                    <td style="padding:11px 14px;text-align:center;">
                        <a href="?delete=<?php echo $tt['id']; ?>&course=<?php echo urlencode($filter_course); ?>" onclick="return confirm('Delete?')" style="background:#ef4444;color:white;padding:5px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">🗑️</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
</body>
</html>