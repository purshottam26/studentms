<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: students.php");
    exit();
}

$id = intval($_GET['id']);

// Also delete student folder if exists
$result = mysqli_query($conn, "SELECT photo FROM student WHERE id=$id");
$row = mysqli_fetch_assoc($result);

if($row && !empty($row['photo'])){
    $photo_path = "uploads/" . $row['photo'];
    if(file_exists($photo_path)) unlink($photo_path);
}

$query = "DELETE FROM student WHERE id=$id";
if(mysqli_query($conn, $query)){
    header("Location: students.php?msg=deleted");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
