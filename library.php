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

// Issue Book
if(isset($_POST['issue_book'])){
    $book_id = intval($_POST['book_id']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $issue_date = $_POST['issue_date'];
    $return_date = $_POST['return_date'];

    $avail = mysqli_fetch_assoc(mysqli_query($conn, "SELECT available_copies FROM library_books WHERE id=$book_id"));
    if($avail && $avail['available_copies'] > 0){
        mysqli_query($conn, "INSERT INTO library_issues (book_id,student_id,issue_date,return_date) VALUES ($book_id,'$student_id','$issue_date','$return_date')");
        mysqli_query($conn, "UPDATE library_books SET available_copies = available_copies - 1 WHERE id=$book_id");
        $msg = "✅ Book issued successfully!";
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
    SELECT li.*, lb.book_title, s.name as student_name
    FROM library_issues li
    LEFT JOIN library_books lb ON li.book_id = lb.id
    LEFT JOIN student s ON li.student_id = s.student_id
    ORDER BY li.id DESC
");

// All students for search
$all_students = mysqli_query($conn, "SELECT student_id, name FROM student ORDER BY name");
$students_list = [];
while($st = mysqli_fetch_assoc($all_students)){
    $students_list[] = $st;
}

$back_link = $is_admin ? 'index.php' : 'teacher_dashboard.php';
$panel_name = $is_admin ? 'Admin Panel' : 'Teacher Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Library Management</title>
<link rel="stylesheet" href="style.css">
<style>
.search-wrapper { position: relative; }
.student-dropdown {
    display:none;
    position:absolute;
    top:100%;
    left:0;
    right:0;
    background:white;
    border:1px solid #e2e8f0;
    border-radius:8px;
    max-height:200px;
    overflow-y:auto;
    z-index:9999;
    box-shadow:0 8px 24px rgba(0,0,0,0.12);
}
.student-option {
    padding:10px 14px;
    cursor:pointer;
    font-size:13px;
    border-bottom:1px solid #f1f5f9;
    transition:background 0.15s;
}
.student-option:hover { background:#f1f5f9; }
.student-option strong { color:#4f46e5; }
</style>
</head>
<body>
<div class="main-container">
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span class="brand-icon">🎓</span> <span>StudentMS</span></h2>
            <p><?php echo $panel_name; ?></p>
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
            <a href="library.php" class="active">📚 Library</a>
            <a href="notice_board.php">📢 Notice Board</a>
            <a href="attendance.php">✅ Attendance</a>
            <a href="fees.php">💰 Fee Management</a>
        </div>
        <div class="sidebar-footer">
            <a href="<?php echo $is_admin ? 'logout.php' : 'teacher_logout.php'; ?>">🚪 Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="topbar">
            <h1>📚 Library Management</h1>
            <div class="topbar-right">
                <div class="admin-badge">👤 <?php echo $is_admin ? htmlspecialchars($_SESSION['admin']) : htmlspecialchars($_SESSION['teacher_id']); ?></div>
            </div>
        </div>

        <?php if($msg): ?>
        <div style="background:<?php echo str_contains($msg,'❌')?'#fee2e2':'#d1fae5'; ?>;border:1px solid <?php echo str_contains($msg,'❌')?'#ef4444':'#10b981'; ?>;color:<?php echo str_contains($msg,'❌')?'#991b1b':'#065f46'; ?>;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-weight:700;">
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <!-- ADD BOOK FORM -->
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

        <!-- ISSUE BOOK -->
        <div class="box">
            <div class="box-title">📤 Issue Book to Student</div>
            <form method="POST" action="library.php" id="issueForm">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Select Book *</label>
                        <select name="book_id" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                            <option value="">-- Select Book --</option>
                            <?php
                            $bq = mysqli_query($conn, "SELECT * FROM library_books WHERE available_copies > 0");
                            while($b = mysqli_fetch_assoc($bq)):
                            ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['book_title']); ?> (<?php echo $b['available_copies']; ?> left)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- STUDENT SEARCH -->
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Search Student *</label>
                        <div class="search-wrapper">
                            <input type="text" id="student_search"
                                placeholder="Name ya ID likhо..."
                                autocomplete="off"
                                style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;"
                                oninput="filterStudents(this.value)">
                            <input type="hidden" name="student_id" id="selected_student_id" required>
                            <div class="student-dropdown" id="student_dropdown">
                                <?php foreach($students_list as $st): ?>
                                <div class="student-option"
                                    data-id="<?php echo htmlspecialchars($st['student_id']); ?>"
                                    data-name="<?php echo htmlspecialchars($st['name']); ?>"
                                    onclick="selectStudent('<?php echo htmlspecialchars($st['student_id']); ?>','<?php echo htmlspecialchars($st['name']); ?>')">
                                    <strong><?php echo htmlspecialchars($st['student_id']); ?></strong> — <?php echo htmlspecialchars($st['name']); ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div id="selected_show" style="font-size:12px;color:#10b981;font-weight:700;margin-top:5px;min-height:18px;"></div>
                    </div>

                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Issue Date *</label>
                        <input type="date" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:5px;">Return Date *</label>
                        <input type="date" name="return_date" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;">
                    </div>
                </div>
                <button type="submit" name="issue_book" style="background:#10b981;color:white;padding:10px 22px;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;">📤 Issue Book</button>
            </form>
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
                <?php
                mysqli_data_seek($books_q, 0);
                while($b = mysqli_fetch_assoc($books_q)):
                ?>
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
                        <a href="?delete_book=<?php echo $b['id']; ?>" onclick="return confirm('Delete this book?')" style="background:#ef4444;color:white;padding:5px 12px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:700;">🗑️ Delete</a>
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
                    <th style="padding:11px 14px;text-align:center;">Student</th>
                    <th style="padding:11px 14px;text-align:center;">Issue Date</th>
                    <th style="padding:11px 14px;text-align:center;">Return Date</th>
                    <th style="padding:11px 14px;text-align:center;">Status</th>
                    <th style="padding:11px 14px;text-align:center;">Action</th>
                </tr></thead>
                <tbody>
                <?php while($iss = mysqli_fetch_assoc($issues_q)): ?>
                <tr style="border-bottom:1px solid #e2e8f0;">
                    <td style="padding:11px 14px;font-weight:700;"><?php echo htmlspecialchars($iss['book_title']); ?></td>
                    <td style="padding:11px 14px;text-align:center;"><?php echo htmlspecialchars($iss['student_name'] ?? $iss['student_id']); ?></td>
                    <td style="padding:11px 14px;text-align:center;"><?php echo $iss['issue_date']; ?></td>
                    <td style="padding:11px 14px;text-align:center;"><?php echo $iss['return_date']; ?></td>
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
// Student search filter
function filterStudents(val) {
    const dropdown = document.getElementById('student_dropdown');
    const options = document.querySelectorAll('.student-option');

    if(val.length === 0){
        dropdown.style.display = 'none';
        document.getElementById('selected_student_id').value = '';
        document.getElementById('selected_show').innerHTML = '';
        return;
    }

    let found = false;
    options.forEach(function(opt){
        const name = opt.getAttribute('data-name').toLowerCase();
        const id = opt.getAttribute('data-id').toLowerCase();
        if(name.includes(val.toLowerCase()) || id.includes(val.toLowerCase())){
            opt.style.display = 'block';
            found = true;
        } else {
            opt.style.display = 'none';
        }
    });

    dropdown.style.display = found ? 'block' : 'none';
}

// Student select
function selectStudent(id, name) {
    document.getElementById('student_search').value = name + ' (' + id + ')';
    document.getElementById('selected_student_id').value = id;
    document.getElementById('student_dropdown').style.display = 'none';
    document.getElementById('selected_show').innerHTML = '✅ Selected: <strong>' + name + '</strong>';
}

// Click bahar pe dropdown band
document.addEventListener('click', function(e){
    if(!e.target.closest('.search-wrapper')){
        document.getElementById('student_dropdown').style.display = 'none';
    }
});

// Form submit check
document.getElementById('issueForm').addEventListener('submit', function(e){
    const sid = document.getElementById('selected_student_id').value;
    if(!sid){
        e.preventDefault();
        alert('⚠️ Pehle student select karo!');
        document.getElementById('student_search').focus();
    }
});
</script>

</body>
</html>