<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

$id = intval($_POST['id']);
$student_id = mysqli_real_escape_string($conn, trim($_POST['student_id']));
$name       = mysqli_real_escape_string($conn, trim($_POST['name']));
$email      = mysqli_real_escape_string($conn, trim($_POST['email']));
$course     = mysqli_real_escape_string($conn, trim($_POST['course']));
$aadhaar    = mysqli_real_escape_string($conn, preg_replace('/\D/','',$_POST['aadhaar']));
$mobile     = mysqli_real_escape_string($conn, preg_replace('/\D/','',$_POST['mobile']));
$pincode    = mysqli_real_escape_string($conn, preg_replace('/\D/','',$_POST['pincode']));

$errors = [];

if(strlen($mobile) != 10 && !empty($mobile)) $errors['mobile'] = "Mobile must be 10 digits";
if(strlen($aadhaar) != 12 && !empty($aadhaar)) $errors['aadhaar'] = "Aadhaar must be 12 digits";
if(strlen($pincode) != 6 && !empty($pincode)) $errors['pincode'] = "Pincode must be 6 digits";
if(empty($name)) $errors['name'] = "Name is required";
if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email address";

if(!empty($errors)){
    include 'edit.php';
    exit();
}

/* Photo Upload */
$photo_sql = '';
if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
    $allowed = ['jpg','jpeg','png'];
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

    if(!in_array($ext, $allowed)){
        $errors['photo'] = "Only JPG, JPEG, PNG allowed";
        include 'edit.php';
        exit();
    }

    $photo = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['photo']['name']));
    move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photo);
    $photo = mysqli_real_escape_string($conn, $photo);
    $photo_sql = ", photo='$photo'";
}

$query = "UPDATE student SET
    student_id='$student_id',
    name='$name',
    email='$email',
    course='$course',
    aadhaar='$aadhaar',
    mobile='$mobile',
    pincode='$pincode'
    $photo_sql
    WHERE id=$id";

if(mysqli_query($conn, $query)){
    header("Location: students.php?msg=updated");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
