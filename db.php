<?php
include_once "db_details.php";
$db_host = DB_HOST ?? 'localhost';
$db_name = DB_NAME ?? 'mydb';
$db_user = DB_USER ?? 'root';
$db_pass = DB_PASS ?? '';

$conn = mysqli_connect($db_host,$db_user, $db_pass, $db_name );

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}
?>