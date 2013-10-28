<?php
$id=$_GET["id"];
$val_id=$_GET["val_id"];

include_once('db_conexion.php');

$sql_valInd="SELECT ind.code, ind.gender, ind.population, ind2.genotype 
	FROM validation v
		INNER JOIN individuals_detection ind2 ON ind2.validation_id=v.id 
		INNER JOIN individuals ind ON ind2.individuals_id=ind.id 
	WHERE v.inv_id='$id' and v.id='$val_id';";
$result_valInd=mysql_query($sql_valInd);
$individuals='';


while($indrow = mysql_fetch_array($result_valInd)){
	$individuals.="<li>";
	if ($indrow['code'] != "" || $indrow['code'] != NULL) {
		$individuals.=$indrow['code'];
	}
	if ($indrow['gender'] != "" || $indrow['gender'] != NULL) {
		$individuals.=" - ".$indrow['gender'];
	}
	if ($indrow['population'] != "" || $indrow['population'] != NULL) {
		$individuals.=" - ".$indrow['population'];
	}
	if ($indrow['genotype'] != "" || $indrow['genotype'] != NULL) {
		$individuals.=" - ".$indrow['genotype'];
	}
	$individuals.='</li>';
}
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<link rel="stylesheet" type="text/css" href="../css/style.css" />
</head>
<body>';
if ($individuals != ""){ echo "<h3>Validated individuals</h3>$individuals<br>";}
else { echo "<strong>None individuals have been validated</strong>";}
echo "</body></html>";

?>
