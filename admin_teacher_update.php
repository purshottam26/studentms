<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: add_teacher.php");
    exit();
}

$id = intval($_POST['id']);
$name = mysqli_real_escape_string($conn, trim($_POST['name']));
$email = mysqli_real_escape_string($conn, trim($_POST['email']));
$phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
$dob = !empty($_POST['dob']) ? $_POST['dob'] : NULL;
$doj = !empty($_POST['doj']) ? $_POST['doj'] : NULL;
$password = trim($_POST['password'] ?? '');
$subjects = isset($_POST['subjects']) ? array_filter(array_map('trim', $_POST['subjects'])) : [];
$subject_str = mysqli_real_escape_string($conn, implode(', ', $subjects));

// DOB and DOJ validation
$error = '';
$today = date('Y-m-d');

if($dob){
    if($dob >= $today){
        $error = "❌ Date of Birth future mein nahi ho sakti!";
    } else {
        $age = date_diff(date_create($dob), date_create($today))->y;
        if($age < 18){
            $error = "❌ Teacher ki age kam se kam 18 saal honi chahiye!";
        } elseif($age > 70){
            $error = "❌ Teacher ki age 70 saal se zyada nahi ho sakti!";
        }
    }
}

if($doj && !$error){
    if($doj > $today){
        $error = "❌ Date of Joining future mein nahi ho sakti!";
    }
    if($dob && $doj < $dob){
        $error = "❌ Date of Joining, Date of Birth se pehle nahi ho sakti!";
    }
    if($dob && !$error){
        $gap = date_diff(date_create($dob), date_create($doj))->y;
        if($gap < 20){
            $error = "❌ Joining ke waqt teacher ki age kam se kam 20 saal honi chahiye!";
        }
    }
}

if($error){
    header("Location: admin_teacher_edit.php?id=$id&msg=" . urlencode($error));
    exit();
}

$current_q = mysqli_query($conn, "SELECT photo FROM teachers WHERE id=$id");
$current = mysqli_fetch_assoc($current_q);
$photo = $current['photo'];

if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
    $allowed = ['jpg','jpeg','png','gif'];
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if(in_array($ext, $allowed)){
        $new_photo = time() . '_teacher_' . rand(100,999) . '.' . $ext;
        if(move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $new_photo)){
            $photo = $new_photo;
        }
    }
}

$set_clause = "name='$name', email='$email', phone='$phone', subject='$subject_str', photo='$photo'";
if($password !== ''){
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $set_clause .= ", password='$hash'";
}
if($dob !== NULL){
    $set_clause .= ", dob='$dob'";
}
if($doj !== NULL){
    $set_clause .= ", doj='$doj'";
}

$query = "UPDATE teachers SET $set_clause WHERE id=$id";
if(mysqli_query($conn, $query)){
    header("Location: admin_teacher_view.php?id=$id&msg=updated");
    exit();
} else {
    echo "<h2 style='color:red;padding:20px;'>❌ Error: " . mysqli_error($conn) . "</h2>";
    echo "<a href='javascript:history.back()' style='padding:20px;display:block;'>← Back</a>";
}
?>