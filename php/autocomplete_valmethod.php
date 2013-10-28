<?php

include_once('db_conexion.php');

$sql_method="select distinct method from validation where method is not null order by method;";
//$sql_method="select distinct name from methods where name is not null and aim like '%validation%' order by name;";
//ponemos todos los metodos o solo los de validacion??????????????????????????????????????????????????????????????????????
$result_method = mysql_query($sql_method);
while($thisrow = mysql_fetch_array($result_method)){
	$methods[]=$thisrow["method"];
}

mysql_free_result($result_method);
mysql_close($link);

// check the parameter
if(isset($_GET['part']) and $_GET['part'] != '')
{
	// initialize the results array
	$results = array();

	// search fosmids
	foreach($methods as $method)
	{
		// if it starts with 'part' add to results
		if( strpos($method, $_GET['part']) === 0 ){
			$results[] = $method;
		}
	}

	// return the array as json with PHP 5.2
	echo json_encode($results);

}

?>
