<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

$file = basename($_GET['file'] ?? ''); // basename for security
if(empty($file)){
    header("Location: index.php");
    exit();
}

$path = "uploads/" . $file;
if(file_exists($path) && !is_dir($path)){
    unlink($path);
}

header("Location: students.php");
exit();
?>
