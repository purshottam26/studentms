<?php
session_start();
include 'db.php';

$is_admin = isset($_SESSION['admin']);
$is_teacher = isset($_SESSION['teacher_id']);

if(!$is_admin && !$is_teacher){
    header("Location: login.php");
    exit();
}

$msg = '';

// Add Book
if(isset($_POST['add_book'])){
    $title = mysqli_real_escape_string($conn, $_POST['book_title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $copies = intval($_POST['total_copies']);
    $date = date('Y-m-d');
    $added_by = $is_admin ? 'admin' : $_SESSION['teacher_id'];

    mysqli_query($conn, "INSERT INTO library_books (book_title,author,isbn,category,total_copies,available_copies,added_by,added_date)
    VALUES ('$title','$author','$isbn','$category',$copies,$copies,'$added_by','$date')");
    $msg = "✅ Book added successfully!";
}

// Issue Book to Student
if(isset($_POST['issue_student'])){
    $book_id = intval($_POST['book_id_s']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $issue_date = $_POST['issue_date_s'];
    $return_date = $_POST['return_date_s'];

    $avail = mysqli_fetch_assoc(mysqli_query($conn, "SELECT available_copies FROM library_books WHERE id=$book_id"));
    if($avail && $avail['available_copies'] > 0){
        mysqli_query($conn, "INSERT INTO library_issues (book_id,student_id,teacher_id,issued_to,issue_date,return_date)
        VALUES ($book_id,'$student_id','','student','$issue_date','$return_date')");
        mysqli_query($conn, "UPDATE library_books SET available_copies = available_copies - 1 WHERE id=$book_id");
        $msg = "✅ Book issued to student successfully!";
    } else {
        $msg = "❌ No copies available!";
    }
}

// Issue Book to Teacher
if(isset($_POST['issue_teacher'])){
    $book_id = intval($_POST['book_id_t']);
    $teacher_id = mysqli_real_escape_string($conn, $_POST['teacher_id_issue']);
    $issue_date = $_POST['issue_date_t'];
    $return_date = $_POST['return_date_t'];

    $avail = mysqli_fetch_assoc(mysqli_query($conn, "SELECT available_copies FROM library_books WHERE id=$book_id"));
    if($avail && $avail['available_copies'] > 0){
        mysqli_query($conn, "INSERT INTO library_issues (book_id,student_id,teacher_id,issued_to,issue_date,return_date)
        VALUES ($book_id,'','$teacher_id','teacher','$issue_date','$return_date')");
        mysqli_query($conn, "UPDATE library_books SET available_copies = available_copies - 1 WHERE id=$book_id");
        $msg = "✅ Book issued to teacher successfully!";
    } else {
        $msg = "❌ No copies available!";
    }
}

// Return Book
if(isset($_GET['return_id'])){
    $issue_id = intval($_GET['return_id']);
    $issue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM library_issues WHERE id=$issue_id"));
    if($issue && !$issue['returned']){
        mysqli_query($conn, "UPDATE library_issues SET returned=1 WHERE id=$issue_id");
        mysqli_query($conn, "UPDATE library_books SET available_copies = available_copies + 1 WHERE id=".$issue['book_id']);
        $msg = "✅ Book returned!";
    }
}

// Delete Book
if(isset($_GET['delete_book'])){
    $bid = intval($_GET['delete_book']);
    mysqli_query($conn, "DELETE FROM library_books WHERE id=$bid");
    $msg = "✅ Book deleted!";
}

$books_q = mysqli_query($conn, "SELECT * FROM library_books ORDER BY id DESC");

$issues_q = mysqli_query($conn, "
    SELECT li.*, lb.book_title,
        s.name as student_name,
        t.name as teacher_name
    FROM library_issues li
    LEFT JOIN library_books lb ON li.book_id = lb.id
    LEFT JOIN student s ON li.student_id = s.student_id
    LEFT JOIN teachers t ON li.teacher_id = t.teacher_id
    ORDER BY li.id DESC
");

// Overdue books — return date past ho gayi
$overdue_q = mysqli_query($conn, "
    SELECT li.*, lb.book_title,
        s.name as student_name,
        t.name as teacher_name,
        DATEDIFF(CURDATE(), li.return_date) as days_overdue
    FROM library_issues li
    LEFT JOIN library_books lb ON li.book_id = lb.id
    LEFT JOIN student s ON li.student_id = s.student_id
    LEFT JOIN teachers t ON li.teacher_id = t.teacher_id
    WHERE li.returned = 0 AND li.return_date < CURDATE()
    ORDER BY li.return_date ASC
");
$overdue_count = mysqli_num_rows($overdue_q);

// Due today or tomorrow — reminder
$due_soon_q = mysqli_query($conn, "
    SELECT li.*, lb.book_title,
        s.name as student_name,
        t.name as teacher_name,
        DATEDIFF(li.return_date, CURDATE()) as days_left
    FROM library_issues li
    LEFT JOIN library_books lb ON li.book_id = lb.id
    LEFT JOIN student s ON li.student_id = s.student_id
    LEFT JOIN teachers t ON li.teacher_id = t.teacher_id
    WHERE li.returned = 0
    AND li.return_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)
    ORDER BY li.return_date ASC
");
$due_soon_count = mysqli_num_rows($due_soon_q);

// Students & Teachers list
$all_students = mysqli_query($conn, "SELECT student_id, name, photo FROM student ORDER BY name");
$students_list = [];
while($st = mysqli_fetch_assoc($all_students)) $students_list[] = $st;

$all_teachers = mysqli_query($conn, "SELECT teacher_id, name, photo, subject FROM teachers ORDER BY name");
$teachers_list = [];
while($t = mysqli_fetch_assoc($all_teachers)) $teachers_list[] = $t;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Library Management</title>
<link rel="stylesheet" href="style.css">
<style>
.search-wrapper { position:relative; }
.search-dropdown {
    display:none; position:absolute; top:110%; left:0; right:0;
    background:white; border:1px solid #e2e8f0; border-radius:10px;
    max-height:220px; overflow-y:auto; z-index:9999;
    box-shadow:0 8px 24px rgba(79,70,229,0.15);
}
.search-option {
    display:flex; align-items:center; gap:10px;
    padding:10px 14px; cursor:pointer; border-bottom:1px solid #f1f5f9;
    transition:background 0.15s;
}
.search-option:hover { background:#f8fafc; }
.search-option img { width:34px; height:34px; border-radius:50%; object-fit:cover; border:2px solid #4f46e5; }
.search-option .av { width:34px; height:34px; border-radius:50%; background:rgba(79,70,229,0.1); display:flex; align-items:center; justify-content:center; font-size:16px; }
.search-option .info strong { font-size:13px; display:block; color:#1e293b; }
.search-option .info span { font-size:11px; color:#64748b; }
.search-option .badge { background:rgba(79,70,229,0.1); color:#4f46e5; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:700; }

/* Tabs */
.tab-btns { display:flex; gap:0; margin-bottom:0; }
.tab-btn {
    padding:12px 24px; border:none; cursor:pointer;
    font-size:14px; font-weight:700; font-family:inherit;
    border-radius:10px 10px 0 0; transition:all 0.2s;
    border-bottom:3px solid transparent;
}
.tab-btn.active { background:white; color:#4f46e5; border-bottom:3px solid #4f46e5; }
.tab-btn.inactive { background:#f1f5f9; color:#64748b; }
.tab-content { display:none; }
.tab-content.active { display:block; }

/* Alert */
.alert-overdue { background:#fff1f2; border:2px solid #ef4444; border-radius:12px; padding:16px 20px; margin-bottom:18px; }
.alert-due { background:#fef3c7; border:2px solid #f59e0b; border-radius:12px; padding:16px 20px; margin-bottom:18px; }
</style>
</head>
<body>
<div class="main-container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="brand-icon">🎓</span> <span>StudentMS</span></h2>
            <p><?php echo $is_admin ? 'Admin Panel' : 'Teacher Panel'; ?></p>
        </div>
        <div class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <?php if($is_admin): ?>
            <a href="index.php">📊 Dashboard</a>
            <a href="students.php">👨‍🎓 Students</a>
            <a href="students_list.php">📋 All Students</a>
            <a href="export.php">📤 Export Excel</a>
            <a href="add_exam.php">📘 Exams</a>
            <a href="add_result.php">📊 Add Result</a>
            <a href="view_result.php">📄 View Result</a>
            <a href="add_teacher.php">👩‍🏫 Teachers</a>
            <a href="library.php" class="active">📚 Library</a>
            <a href="notice_board.php">📢 Notice Board</a>
            <a href="attendance.php">✅ Attendance</a>
            <a href="fees.php">💰 Fee Management</a>
            <a href="timetable.php">📅 Timetable</a>
            <a href="change_password.php">🔐 Change Password</a>
            <?php else: ?>
            <a href="teacher_dashboard.php">📊 Dashboard</a>
            <a href="teacher_id_card.php">🪪 My ID Card</a>
            <a href="teacher_profile.php">👤 My Profile</a>
            <a href="library.php" class="active">📚 Library</a>
            <a href="teacher_change_password.php">🔐 Change Password</a>
            <?php endif; ?>
        </div>
        <div class="sidebar-footer">
            <a href="<?php echo $is_admin ? 'logout.php' : 'teacher_logout.php'; ?>">🚪 Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="topbar">
            <h1>📚 Library Management</h1>
            <div class="topbar-right">
                <?php if($overdue_count > 0): ?>
                <div style="background:#fee2e2;color:#991b1b;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;" onclick="document.getElementById('overdueSection').scrollIntoView({behavior:'smooth'})">
                    🔴 <?php echo $overdue_count; ?> Overdue
                </div>
                <?php endif; ?>
                <?php if($due_soon_count > 0): ?>
                <div style="background:#fef3c7;color:#92400e;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;" onclick="document.getElementById('dueSoonSection').scrollIntoView({behavior:'smooth'})">
                    ⚠️ <?php echo $due_soon_count; ?> Due Soon
                </div>
                <?php endif; ?>
                <div class="admin-badge">👤 <?php echo $is_admin ? htmlspecialchars($_SESSION['admin']) : htmlspecialchars($_SESSION['teacher_id']); ?></div>
            </div>
        </div>

        <?php if($msg): ?>
        <div style="background:<?php echo str_contains($msg,'❌')?'#fee2e2':'#d1fae5'; ?>;border:1px solid <?php echo str_contains($msg,'❌')?'#ef4444':'#10b981'; ?>;color:<?php echo str_contains($msg,'❌')?'#991b1b':'#065f46'; ?>;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-weight:700;">
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <!-- OVERDUE ALERT -->
        <?php if($overdue_count > 0): ?>
        <div class="alert-overdue" id="overdueSection">
            <div style="font-size:16px;font-weight:800;color:#991b1b;margin-bottom:10px;">🔴 Overdue Books — Return Nahi Hua! (<?php echo $overdue_count; ?>)</div>
            <?php mysqli_data_seek($overdue_q, 0); while($ov = mysqli_fetch_assoc($overdue_q)): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #fecdd3;">
                <div>
                    <strong><?php echo htmlspecialchars($ov['book_title']); ?></strong>
                    <span style="font-size:12px;color:#64748b;margin-left:8px;">
                        <?php echo $ov['issued_to']=='student' ? '👨‍🎓 '.htmlspecialchars($ov['student_name'] ?? $ov['student_id']) : '👩‍🏫 '.htmlspecialchars($ov['teacher_name'] ?? $ov['teacher_id']); ?>
                    </span>
                </div>
                <div style="text-align:right;">
                    <span style="background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                        ⏰ <?php echo $ov['days_overdue']; ?> days overdue
                    </span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <!-- DUE SOON ALERT -->
        <?php if($due_soon_count > 0): ?>
        <div class="alert-due" id="dueSoonSection">
            <div style="font-size:16px;font-weight:800;color:#92400e;margin-bottom:10px;">⚠️ Return Date Aa Rahi Hai! (<?php echo $due_soon_count; ?>)</div>
            <?php mysqli_data_seek($due_soon_q, 0); while($ds = mysqli_fetch_assoc($due_soon_q)): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #fde68a;">
                <div>
                    <strong><?php echo htmlspecialchars($ds['book_title']); ?></strong>
                    <span style="font-size:12px;color:#64748b;margin-left:8px;">
                        <?php echo $ds['issued_to']=='student' ? '👨‍🎓 '.htmlspecialchars($ds['student_name'] ?? $ds['student_id']) : '👩‍🏫 '.htmlspecialchars($ds['teacher_name'] ?? $ds['teacher_id']); ?>
                    </span>
                </div>
                <div>
                    <span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                        <?php echo $ds['days_left']==0 ? '🔔 Today!' : '🔔 '.$ds['days_left'].' day(s) left'; ?>
                    </span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <!-- ADD BOOK -->
        <div class="box">
            <div class="box-title">➕ Add New Book</div>
            <form method="POST" action="library.php">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Book Title *</label>
                        <input type="text" name="book_title" placeholder="Enter book title" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Author</label>
                        <input type="text" name="author" placeholder="Author name" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">ISBN</label>
                        <input type="text" name="isbn" placeholder="ISBN number" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Category</label>
                        <input type="text" name="category" placeholder="e.g. Science, Math" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Total Copies</label>
                        <input type="number" name="total_copies" value="1" min="1" style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                </div>
                <button type="submit" name="add_book" style="background:#4f46e5;color:white;padding:10px 22px;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">➕ Add Book</button>
            </form>
        </div>

        <!-- ISSUE BOOK — TABS -->
        <div class="box">
            <div class="box-title">📤 Issue Book</div>

            <div class="tab-btns">
                <button class="tab-btn active" onclick="switchTab('student')">👨‍🎓 Issue to Student</button>
                <button class="tab-btn inactive" onclick="switchTab('teacher')">👩‍🏫 Issue to Teacher</button>
            </div>

            <!-- STUDENT TAB -->
            <div class="tab-content active" id="tab_student" style="background:#f8fafc;padding:20px;border-radius:0 10px 10px 10px;border:1px solid #e2e8f0;">
                <form method="POST" action="library.php" id="issueStudentForm">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
                        <div>
                            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Select Book *</label>
                            <select name="book_id_s" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                                <option value="">-- Select Book --</option>
                                <?php
                                $bq = mysqli_query($conn, "SELECT * FROM library_books WHERE available_copies > 0");
                                while($b = mysqli_fetch_assoc($bq)):
                                ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['book_title']); ?> (<?php echo $b['available_copies']; ?> left)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Search Student *</label>
                            <div class="search-wrapper">
                                <input type="text" id="student_search" placeholder="Name ya ID likhо..."
                                    autocomplete="off"
                                    style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;"
                                    oninput="filterSearch('student', this.value)">
                                <input type="hidden" name="student_id" id="selected_student_id" required>
                                <div class="search-dropdown" id="student_dropdown">
                                    <?php foreach($students_list as $st): ?>
                                    <div class="search-option"
                                        data-id="<?php echo htmlspecialchars($st['student_id']); ?>"
                                        data-name="<?php echo htmlspecialchars($st['name']); ?>"
                                        onclick="selectPerson('student','<?php echo htmlspecialchars($st['student_id']); ?>','<?php echo htmlspecialchars($st['name']); ?>')">
                                        <?php if(!empty($st['photo'])): ?>
                                        <img src="uploads/<?php echo $st['photo']; ?>">
                                        <?php else: ?>
                                        <div class="av">👨‍🎓</div>
                                        <?php endif; ?>
                                        <div class="info">
                                            <strong><?php echo htmlspecialchars($st['name']); ?></strong>
                                        </div>
                                        <span class="badge"><?php echo htmlspecialchars($st['student_id']); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div id="student_selected" style="font-size:12px;color:#10b981;font-weight:700;margin-top:4px;min-height:16px;"></div>
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Issue Date *</label>
                            <input type="date" name="issue_date_s" value="<?php echo date('Y-m-d'); ?>" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Return Date *</label>
                            <input type="date" name="return_date_s" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                        </div>
                    </div>
                    <button type="submit" name="issue_student" style="background:#10b981;color:white;padding:10px 22px;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">📤 Issue to Student</button>
                </form>
            </div>

            <!-- TEACHER TAB -->
            <div class="tab-content" id="tab_teacher" style="background:#f8fafc;padding:20px;border-radius:0 10px 10px 10px;border:1px solid #e2e8f0;">
                <form method="POST" action="library.php" id="issueTeacherForm">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
                        <div>
                            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Select Book *</label>
                            <select name="book_id_t" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                                <option value="">-- Select Book --</option>
                                <?php
                                $bq2 = mysqli_query($conn, "SELECT * FROM library_books WHERE available_copies > 0");
                                while($b = mysqli_fetch_assoc($bq2)):
                                ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['book_title']); ?> (<?php echo $b['available_copies']; ?> left)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Search Teacher *</label>
                            <div class="search-wrapper">
                                <input type="text" id="teacher_search" placeholder="Name ya ID likhо..."
                                    autocomplete="off"
                                    style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;"
                                    oninput="filterSearch('teacher', this.value)">
                                <input type="hidden" name="teacher_id_issue" id="selected_teacher_id" required>
                                <div class="search-dropdown" id="teacher_dropdown">
                                    <?php foreach($teachers_list as $t): ?>
                                    <div class="search-option"
                                        data-id="<?php echo htmlspecialchars($t['teacher_id']); ?>"
                                        data-name="<?php echo htmlspecialchars($t['name']); ?>"
                                        onclick="selectPerson('teacher','<?php echo htmlspecialchars($t['teacher_id']); ?>','<?php echo htmlspecialchars($t['name']); ?>')">
                                        <?php if(!empty($t['photo'])): ?>
                                        <img src="uploads/<?php echo $t['photo']; ?>">
                                        <?php else: ?>
                                        <div class="av">👩‍🏫</div>
                                        <?php endif; ?>
                                        <div class="info">
                                            <strong><?php echo htmlspecialchars($t['name']); ?></strong>
                                            <span><?php echo htmlspecialchars($t['subject']); ?></span>
                                        </div>
                                        <span class="badge" style="background:rgba(6,182,212,0.1);color:#0891b2;"><?php echo htmlspecialchars($t['teacher_id']); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div id="teacher_selected" style="font-size:12px;color:#06b6d4;font-weight:700;margin-top:4px;min-height:16px;"></div>
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Issue Date *</label>
                            <input type="date" name="issue_date_t" value="<?php echo date('Y-m-d'); ?>" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Return Date *</label>
                            <input type="date" name="return_date_t" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                        </div>
                    </div>
                    <button type="submit" name="issue_teacher" style="background:#06b6d4;color:white;padding:10px 22px;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">📤 Issue to Teacher</button>
                </form>
            </div>
        </div>

        <!-- BOOKS LIST -->
        <div class="box">
            <div class="box-title">📚 All Books</div>
            <table style="width:100%;border-collapse:collapse;">
                <thead><tr style="background:#4f46e5;color:white;">
                    <th style="padding:11px 14px;text-align:left;">Title</th>
                    <th style="padding:11px 14px;text-align:center;">Author</th>
                    <th style="padding:11px 14px;text-align:center;">Category</th>
                    <th style="padding:11px 14px;text-align:center;">Total</th>
                    <th style="padding:11px 14px;text-align:center;">Available</th>
                    <th style="padding:11px 14px;text-align:center;">Action</th>
                </tr></thead>
                <tbody>
                <?php mysqli_data_seek($books_q, 0); while($b = mysqli_fetch_assoc($books_q)): ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:11px 14px;font-weight:700;"><?php echo htmlspecialchars($b['book_title']); ?></td>
                    <td style="padding:11px 14px;text-align:center;"><?php echo htmlspecialchars($b['author']); ?></td>
                    <td style="padding:11px 14px;text-align:center;"><?php echo htmlspecialchars($b['category']); ?></td>
                    <td style="padding:11px 14px;text-align:center;"><?php echo $b['total_copies']; ?></td>
                    <td style="padding:11px 14px;text-align:center;">
                        <span style="background:<?php echo $b['available_copies']>0?'#d1fae5':'#fee2e2'; ?>;color:<?php echo $b['available_copies']>0?'#065f46':'#991b1b'; ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                            <?php echo $b['available_copies']; ?>
                        </span>
                    </td>
                    <td style="padding:11px 14px;text-align:center;">
                        <a href="?delete_book=<?php echo $b['id']; ?>" onclick="return confirm('Delete?')" style="background:#ef4444;color:white;padding:5px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">🗑️ Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- ISSUE RECORDS -->
        <div class="box">
            <div class="box-title">📋 Issue Records</div>
            <table style="width:100%;border-collapse:collapse;">
                <thead><tr style="background:#4f46e5;color:white;">
                    <th style="padding:11px 14px;text-align:left;">Book</th>
                    <th style="padding:11px 14px;text-align:center;">Issued To</th>
                    <th style="padding:11px 14px;text-align:center;">Type</th>
                    <th style="padding:11px 14px;text-align:center;">Issue Date</th>
                    <th style="padding:11px 14px;text-align:center;">Return Date</th>
                    <th style="padding:11px 14px;text-align:center;">Status</th>
                    <th style="padding:11px 14px;text-align:center;">Action</th>
                </tr></thead>
                <tbody>
                <?php mysqli_data_seek($issues_q, 0); while($iss = mysqli_fetch_assoc($issues_q)):
                    $is_overdue = !$iss['returned'] && $iss['return_date'] < date('Y-m-d');
                    $is_due_soon = !$iss['returned'] && $iss['return_date'] >= date('Y-m-d') && $iss['return_date'] <= date('Y-m-d', strtotime('+2 days'));
                ?>
                <tr style="border-bottom:1px solid #e2e8f0;background:<?php echo $is_overdue?'#fff1f2':($is_due_soon?'#fefce8':'white'); ?>">
                    <td style="padding:11px 14px;font-weight:700;"><?php echo htmlspecialchars($iss['book_title']); ?></td>
                    <td style="padding:11px 14px;text-align:center;font-weight:700;">
                        <?php if($iss['issued_to'] == 'student'): ?>
                        <?php echo htmlspecialchars($iss['student_name'] ?? $iss['student_id']); ?>
                        <?php else: ?>
                        <?php echo htmlspecialchars($iss['teacher_name'] ?? $iss['teacher_id']); ?>
                        <?php endif; ?>
                    </td>
                    <td style="padding:11px 14px;text-align:center;">
                        <?php if($iss['issued_to'] == 'student'): ?>
                        <span style="background:rgba(16,185,129,0.1);color:#059669;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">👨‍🎓 Student</span>
                        <?php else: ?>
                        <span style="background:rgba(6,182,212,0.1);color:#0891b2;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">👩‍🏫 Teacher</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:11px 14px;text-align:center;"><?php echo $iss['issue_date']; ?></td>
                    <td style="padding:11px 14px;text-align:center;">
                        <?php echo $iss['return_date']; ?>
                        <?php if($is_overdue): ?>
                        <span style="display:block;font-size:11px;color:#ef4444;font-weight:700;">⏰ Overdue!</span>
                        <?php elseif($is_due_soon): ?>
                        <span style="display:block;font-size:11px;color:#f59e0b;font-weight:700;">🔔 Due Soon!</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:11px 14px;text-align:center;">
                        <?php if($iss['returned']): ?>
                        <span style="background:#d1fae5;color:#065f46;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">✅ Returned</span>
                        <?php else: ?>
                        <span style="background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">📤 Issued</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:11px 14px;text-align:center;">
                        <?php if(!$iss['returned']): ?>
                        <a href="?return_id=<?php echo $iss['id']; ?>" onclick="return confirm('Mark as returned?')" style="background:#10b981;color:white;padding:5px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">↩️ Return</a>
                        <?php else: echo '—'; endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
// Tab switching
function switchTab(type) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('active'); b.classList.add('inactive'); });
    document.getElementById('tab_' + type).classList.add('active');
    event.target.classList.add('active');
    event.target.classList.remove('inactive');
}

// Search filter
function filterSearch(type, val) {
    const dropdown = document.getElementById(type + '_dropdown');
    const options = dropdown.querySelectorAll('.search-option');
    const v = val.toLowerCase().trim();

    if(v.length === 0){ dropdown.style.display = 'none'; return; }

    let found = false;
    options.forEach(function(opt){
        const name = opt.getAttribute('data-name').toLowerCase();
        const id = opt.getAttribute('data-id').toLowerCase();
        if(name.includes(v) || id.includes(v)){
            opt.style.display = 'flex'; found = true;
        } else {
            opt.style.display = 'none';
        }
    });
    dropdown.style.display = found ? 'block' : 'none';
}

// Select person
function selectPerson(type, id, name) {
    document.getElementById(type + '_search').value = name + ' (' + id + ')';
    document.getElementById('selected_' + type + '_id').value = id;
    document.getElementById(type + '_dropdown').style.display = 'none';
    document.getElementById(type + '_selected').innerHTML = '✅ Selected: <strong>' + name + '</strong>';
}

// Click bahar pe dropdown band
document.addEventListener('click', function(e){
    if(!e.target.closest('.search-wrapper')){
        document.querySelectorAll('.search-dropdown').forEach(d => d.style.display = 'none');
    }
});

// Form validation
document.getElementById('issueStudentForm').addEventListener('submit', function(e){
    if(!document.getElementById('selected_student_id').value){
        e.preventDefault();
        alert('⚠️ Pehle student select karo!');
    }
});
document.getElementById('issueTeacherForm').addEventListener('submit', function(e){
    if(!document.getElementById('selected_teacher_id').value){
        e.preventDefault();
        alert('⚠️ Pehle teacher select karo!');
    }
});

// 🔔 Browser Notification — Overdue & Due Soon Alert
<?php if($overdue_count > 0 || $due_soon_count > 0): ?>
if('Notification' in window){
    Notification.requestPermission().then(function(perm){
        if(perm === 'granted'){
            <?php if($overdue_count > 0): ?>
            new Notification('📚 Library Alert!', {
                body: '🔴 <?php echo $overdue_count; ?> book(s) overdue hain! Turant return karwao.',
                icon: ''
            });
            <?php endif; ?>
            <?php if($due_soon_count > 0): ?>
            setTimeout(function(){
                new Notification('📚 Library Reminder!', {
                    body: '⚠️ <?php echo $due_soon_count; ?> book(s) ki return date aa rahi hai!',
                    icon: ''
                });
            }, 2000);
            <?php endif; ?>
        }
    });
}
<?php endif; ?>

// Page pe bhi sound alert
<?php if($overdue_count > 0): ?>
window.onload = function(){
    const audio = new AudioContext();
    const osc = audio.createOscillator();
    const gain = audio.createGain();
    osc.connect(gain);
    gain.connect(audio.destination);
    osc.frequency.value = 880;
    gain.gain.value = 0.1;
    osc.start();
    setTimeout(() => osc.stop(), 500);
}
<?php endif; ?>
</script>

</body>
</html>