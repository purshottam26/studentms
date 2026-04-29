<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $student_id = mysqli_real_escape_string($conn, trim($_POST['student_id']));
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $father_name = mysqli_real_escape_string($conn, trim($_POST['father_name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $course = mysqli_real_escape_string($conn, trim($_POST['course']));
    $aadhaar = mysqli_real_escape_string($conn, preg_replace('/\D/', '', $_POST['aadhaar']));
    $mobile = mysqli_real_escape_string($conn, preg_replace('/\D/', '', $_POST['mobile']));
    $whatsapp = mysqli_real_escape_string($conn, preg_replace('/\D/', '', $_POST['whatsapp'] ?? ''));
    $pincode = mysqli_real_escape_string($conn, preg_replace('/\D/', '', $_POST['pincode']));
    $dob = !empty($_POST['dob']) ? mysqli_real_escape_string($conn, $_POST['dob']) : NULL;
    $doj = !empty($_POST['doj']) ? mysqli_real_escape_string($conn, $_POST['doj']) : NULL;
    $dob_val = $dob ? "'$dob'" : "NULL";
    $doj_val = $doj ? "'$doj'" : "NULL";

    // Duplicate check
    $check = mysqli_query($conn, "SELECT id FROM student WHERE student_id='$student_id'");
    if(mysqli_num_rows($check) > 0){
        header("Location: students.php?msg=duplicate&sid=$student_id");
        exit();
    }

    $photo = "";
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)){
            $photo = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['photo']['name']));
            move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photo);
        }
    }

    $query = "INSERT INTO student (student_id, name, father_name, email, course, aadhaar, mobile, whatsapp, pincode, dob, doj, photo)
              VALUES ('$student_id','$name','$father_name','$email','$course','$aadhaar','$mobile','$whatsapp','$pincode',$dob_val,$doj_val,'$photo')";

    if(mysqli_query($conn, $query)){
        $default_pass = password_hash($student_id, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE student SET password='$default_pass' WHERE student_id='$student_id'");
        header("Location: students.php?msg=added");
        exit();
    } else {
        echo "<h2 style='color:red;padding:20px;'>❌ Error: " . mysqli_error($conn) . "</h2>";
        echo "<a href='javascript:history.back()' style='padding:20px;display:block;'>← Back</a>";
    }
} else {
    header("Location: students.php");
    exit();
}
?>