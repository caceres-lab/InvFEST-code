<?php

include('security_layer.php');

$id=$_GET["q"];

#To add a new validation, select the distinct posibilites from the database

include_once('db_conexion.php');

$chr=''; $research_name=''; $method=''; $status='';

$sql_chr="select chr from inversions where id='$id';";
$result_chr = mysql_query($sql_chr);
$chr = mysql_fetch_array($result_chr);

$sql_research_name="select distinct name from researchs where name is not null order by name;";
$result_research_name = mysql_query($sql_research_name);
while($thisrow = mysql_fetch_array($result_research_name)){
	$research_name.="<option value='".$thisrow["name"]."'>".$thisrow["name"]."</option>\n";
}

#$sql_method="select distinct method from validation where method is not null order by method;";
$sql_method="select distinct name AS method from methods where name is not null order by method;";
$result_method = mysql_query($sql_method);
while($thisrow = mysql_fetch_array($result_method)){
	$method.="<option value='".$thisrow["method"]."'>".$thisrow["method"]."</option>\n";
}

$sql_status="select distinct status from validation where status is not null order by status;";
$result_status = mysql_query($sql_status);
while($thisrow = mysql_fetch_array($result_status)){
	$status.="<option value='".$thisrow["status"]."'>".$thisrow["status"]."</option>\n";
}


mysql_close($con);

?>
