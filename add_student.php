<?php
include 'db.php';

if(isset($_POST['submit'])){
    $id = $_POST['student_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $aadhaar = $_POST['aadhaar'];
    $mobile = $_POST['mobile'];
    $pincode = $_POST['pincode'];

    // Photo Upload
    $photo = $_FILES['photo']['name'];
    $temp = $_FILES['photo']['tmp_name'];
    move_uploaded_file($temp, "uploads/".$photo);

    $query = "INSERT INTO students 
    (student_id, name, email, course, aadhaar, mobile, pincode, photo) 
    VALUES ('$id','$name','$email','$course','$aadhaar','$mobile','$pincode','$photo')";

    mysqli_query($conn, $query);

    header("Location: index.php"); // wapas list page
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
</head>
<body>

<h2>Add Student</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="student_id" placeholder="Student ID" required><br><br>
    <input type="text" name="name" placeholder="Name" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="text" name="course" placeholder="Course"><br><br>
    <input type="text" name="aadhaar" placeholder="Aadhaar"><br><br>
    <input type="text" name="mobile" placeholder="Mobile"><br><br>
    <input type="text" name="pincode" placeholder="Pin Code"><br><br>

    <input type="file" name="photo"><br><br>

    <button type="submit" name="submit">Add Student</button>
</form>

</body>
</html>