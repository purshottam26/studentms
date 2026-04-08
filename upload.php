<h2>Uploaded Files</h2>

<table>

<tr>
<th>File Name</th>
<th>Preview</th>
<th>Size</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php

$folder = "uploads/";

if(is_dir($folder)){

$files = scandir($folder);

foreach($files as $file){

if($file != "." && $file != ".."){

$path = $folder.$file;

/* File Exist Check */

if(!file_exists($path)){
continue;
}

$size = round(filesize($path)/1024,2);
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

?>

<tr>

<td><?php echo $file; ?></td>

<td>

<?php

/* Image Preview */

if(in_array($ext,['jpg','jpeg','png','gif'])){

?>

<img src="uploads/<?php echo $file; ?>" width="60">

<?php

}else{

echo "📄 File";

}

?>

</td>

<td><?php echo $size; ?> KB</td>

<td style="color:green;">Uploaded ✔</td>

<td>

<a href="uploads/<?php echo $file; ?>" target="_blank">View</a>

<a href="delete_file.php?file=<?php echo $file; ?>"
onclick="return confirm('Delete this file?')">Delete</a>

</td>

</tr>

<?php

}

}

}

?>

</table>