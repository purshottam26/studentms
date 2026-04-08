<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}
include 'db.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=students_" . date('Y-m-d') . ".xls");

echo "Student ID\tName\tEmail\tCourse\tAadhaar\tMobile\tPin Code\n";

$query = mysqli_query($conn, "SELECT * FROM student ORDER BY name");

while($row = mysqli_fetch_assoc($query)){
    echo htmlspecialchars($row['student_id']) . "\t" .
         htmlspecialchars($row['name']) . "\t" .
         htmlspecialchars($row['email']) . "\t" .
         htmlspecialchars($row['course']) . "\t" .
         htmlspecialchars($row['aadhaar']) . "\t" .
         htmlspecialchars($row['mobile']) . "\t" .
         htmlspecialchars($row['pincode']) . "\n";
}
?>
