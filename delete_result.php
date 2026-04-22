<?php
include 'db.php';

$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM results WHERE id=$id");

header("Location: view_result.php");
?>