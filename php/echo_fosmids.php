<?php
header('Content-Disposition: attachment; filename="probes.txt"');
header('Content-type: text/plain');
$fos=$_GET['fos'];
echo str_replace(',',"\n",$fos);

?>
