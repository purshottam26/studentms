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
    $password_raw = trim($_POST['password'] ?? '');

    $today = date('Y-m-d');
    $error = '';

    // DOB Validation
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : NULL;
    if($dob){
        if($dob >= $today){
            $error = "❌ Date of Birth future mein nahi ho sakti!";
        } else {
            $age = date_diff(date_create($dob), date_create($today))->y;
            if($age < 5){
                $error = "❌ Student ki age kam se kam 5 saal honi chahiye!";
            } elseif($age > 40){
                $error = "❌ Student ki age 40 saal se zyada nahi ho sakti!";
            }
        }
    }

    // DOJ Validation
    $doj = !empty($_POST['doj']) ? $_POST['doj'] : NULL;
    if($doj && !$error){
        if($doj > $today){
            $error = "❌ Date of Joining future mein nahi ho sakti!";
        }
        if($dob && $doj < $dob){
            $error = "❌ Date of Joining, Date of Birth se pehle nahi ho sakti!";
        }
        if($dob && !$error){
            $age_at_join = date_diff(date_create($dob), date_create($doj))->y;
            if($age_at_join < 5){
                $error = "❌ Joining ke waqt student ki age kam se kam 5 saal honi chahiye!";
            }
        }
    }

    if($error){
        echo "<!DOCTYPE html><html><head><link rel='stylesheet' href='style.css'></head><body>";
        echo "<div style='max-width:500px;margin:50px auto;padding:24px;background:#fee2e2;border-radius:14px;border-left:5px solid #ef4444;'>";
        echo "<h2 style='color:#991b1b;margin-bottom:12px;'>⚠️ Validation Error</h2>";
        echo "<p style='color:#991b1b;font-weight:700;font-size:15px;'>".$error."</p>";
        echo "<a href='javascript:history.back()' style='display:inline-block;margin-top:16px;padding:10px 20px;background:#4f46e5;color:white;border-radius:8px;text-decoration:none;font-weight:700;'>← Wapas Jao</a>";
        echo "</div></body></html>";
        exit();
    }

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

    $plain_pass = $password_raw !== '' ? $password_raw : $student_id;
    $hashed_pass = password_hash($plain_pass, PASSWORD_DEFAULT);

    $query = "INSERT INTO student (student_id, name, father_name, email, course, aadhaar, mobile, whatsapp, pincode, dob, doj, photo, password)
              VALUES ('$student_id','$name','$father_name','$email','$course','$aadhaar','$mobile','$whatsapp','$pincode',$dob_val,$doj_val,'$photo','$hashed_pass')";

    if(mysqli_query($conn, $query)){
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