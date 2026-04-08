<?php
include 'db.php';

$student_id = $_POST['student_id'];
$month = $_POST['month'];

$salary = $_FILES['salary']['name'];
$bank = $_FILES['bank']['name'];
$report = $_FILES['report']['name'];
$attendance = $_FILES['attendance']['name'];

move_uploaded_file($_FILES['salary']['tmp_name'],"uploads/".$salary);
move_uploaded_file($_FILES['bank']['tmp_name'],"uploads/".$bank);
move_uploaded_file($_FILES['report']['tmp_name'],"uploads/".$report);
move_uploaded_file($_FILES['attendance']['tmp_name'],"uploads/".$attendance);

$query = "INSERT INTO student_documents
(student_id,month,salary,bank,report,attendance)
VALUES
('$student_id','$month','$salary','$bank','$report','$attendance')";

mysqli_query($conn,$query);

header("Location:index.php");
?>
