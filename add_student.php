<?php
include 'db.php';
$errors = [];

if(isset($_POST['submit'])){
    $id = trim($_POST['student_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $aadhaar = trim($_POST['aadhaar'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $photo = '';

    if($id === ''){
        $errors[] = 'Student ID is required.';
    }
    if($name === ''){
        $errors[] = 'Student name is required.';
    }
    if($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = 'A valid email address is required.';
    }

    if(!empty($_FILES['photo']['name'])){
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $upload_dir = __DIR__ . '/uploads/';
        if(!is_dir($upload_dir)){
            mkdir($upload_dir, 0755, true);
        }

        $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if(!in_array($extension, $allowed_extensions, true)){
            $errors[] = 'Photo must be a JPG, JPEG, PNG, or GIF file.';
        } else {
            $photo = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['photo']['name']));
            if(!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo)){
                $errors[] = 'Unable to save the uploaded photo.';
            }
        }
    }

    if(empty($errors)){
        $stmt = mysqli_prepare($conn, "INSERT INTO students (student_id, name, email, course, aadhaar, mobile, pincode, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssssss', $id, $name, $email, $course, $aadhaar, $mobile, $pincode, $photo);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: index.php"); // wapas list page
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
</head>
<body>

<h2>Add Student</h2>

<?php if(!empty($errors)): ?>
    <div style="background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:18px;">
        <strong>Please fix these issues:</strong>
        <ul style="margin:10px 0 0 18px;">
            <?php foreach($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="student_id" placeholder="Student ID" required><br><br>
    <input type="text" name="name" placeholder="Name" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="text" name="course" placeholder="Course"><br><br>
    <input type="text" name="aadhaar" placeholder="Aadhaar"><br><br>
    <input type="text" name="mobile" placeholder="Mobile"><br><br>
    <input type="text" name="pincode" placeholder="Pin Code"><br><br>

    <input type="file" name="photo"><br><br>

    <button type="submit" name="submit">Add Student</button>
</form>

</body>
</html>