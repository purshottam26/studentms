<?php
// SIRF EK BAAR CHALAO — phir is file ko delete kar do
include 'db.php';

$students = mysqli_query($conn, "SELECT student_id FROM student WHERE password = '' OR password IS NULL");
$count = 0;

while($s = mysqli_fetch_assoc($students)){
    $sid = $s['student_id'];
    $hashed = password_hash($sid, PASSWORD_DEFAULT); // Default password = Student ID
    mysqli_query($conn, "UPDATE student SET password='$hashed' WHERE student_id='$sid'");
    echo "✅ Password set for: <strong>$sid</strong> (Password: $sid)<br>";
    $count++;
}

if($count == 0){
    echo "✅ Sab students ka password already set hai!";
} else {
    echo "<br><strong>Total: $count students updated!</strong>";
}
echo "<br><br>⚠️ <strong>Is file ko delete kar do abhi!</strong>";
?>