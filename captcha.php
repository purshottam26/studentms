<?php
session_start();
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$captcha = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 6);
$_SESSION['captcha'] = $captcha;

$image = imagecreate(120,40);
$bg = imagecolorallocate($image,255,255,255);
$textcolor = imagecolorallocate($image,0,0,0);

imagestring($image,5,20,10,$captcha,$textcolor);

header("Content-type:image/png");
imagepng($image);
imagedestroy($image);
?>