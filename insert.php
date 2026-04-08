<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $student_id = mysqli_real_escape_string($conn, trim($_POST['student_id']));
    $name       = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email      = mysqli_real_escape_string($conn, trim($_POST['email']));
    $course     = mysqli_real_escape_string($conn, trim($_POST['course']));
    $aadhaar    = mysqli_real_escape_string($conn, preg_replace('/\D/', '', $_POST['aadhaar']));
    $mobile     = mysqli_real_escape_string($conn, preg_replace('/\D/', '', $_POST['mobile']));
    $pincode    = mysqli_real_escape_string($conn, preg_replace('/\D/', '', $_POST['pincode']));

    $photo = "";
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)){
            $photo = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['photo']['name']));
            move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photo);
        }
    }

    $query = "INSERT INTO student (student_id, name, email, course, aadhaar, mobile, pincode, photo)
              VALUES ('$student_id','$name','$email','$course','$aadhaar','$mobile','$pincode','$photo')";

    if(mysqli_query($conn, $query)){
        header("Location: students.php?msg=added");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: students.php");
    exit();
}
?>
