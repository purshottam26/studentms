<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $id = intval($_POST['id']);
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

    // Photo handle
    $current_q = mysqli_query($conn, "SELECT photo FROM student WHERE id=$id");
    $current = mysqli_fetch_assoc($current_q);
    $photo = $current['photo'];

    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)){
            $new_photo = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['photo']['name']));
            if(move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $new_photo)){
                $photo = $new_photo;
            }
        }
    }

    $query = "UPDATE student SET
        name='$name',
        father_name='$father_name',
        email='$email',
        course='$course',
        aadhaar='$aadhaar',
        mobile='$mobile',
        whatsapp='$whatsapp',
        pincode='$pincode',
        dob=$dob_val,
        doj=$doj_val,
        photo='$photo'
        WHERE id=$id";

    if(mysqli_query($conn, $query)){
        header("Location: students.php?msg=updated");
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