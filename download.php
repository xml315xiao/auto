<?php 
$filename = $_GET['filename'];
header('Content-Type:application/octet-stream');
header('Content-Disposition:attachment;filename='.basename($filename));
header('Content-Length:'.filesize($filename));
readfile($filename);
?>



