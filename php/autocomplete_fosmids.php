<?php

include_once('db_conexion.php');

$result = mysql_query("SELECT DISTINCT name FROM fosmids ORDER BY name;");
//$result = mysql_query("SELECT DISTINCT symbol FROM  HsRefSeqGenes;");
while ($row = mysql_fetch_assoc($result)) {
   		$fosmids[]=$row['name'];
}
mysql_free_result($result);
mysql_close($link);

// check the parameter
if(isset($_GET['part']) and $_GET['part'] != '')
{
	// initialize the results array
	$results = array();

	// search fosmids
	foreach($fosmids as $fosmid)
	{
		// if it starts with 'part' add to results
		if( strpos($fosmid, $_GET['part']) === 0 ){
			$results[] = $fosmid;
		}
	}

	// return the array as json with PHP 5.2
	echo json_encode($results);

}

?>
