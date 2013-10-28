<?php
header('Content-Disposition: attachment; filename="individuals_predicted.txt"');
header('Content-type: text/plain');
$ind=$_GET['ind'];
echo str_replace(',',"\n",$ind);

?>
