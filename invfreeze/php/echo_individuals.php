<?php
/******************************************************************************
	ECHO_INDIVIDUALS.PHP

	Creates a download file ("individuals_predicted.txt") containing all the individuals for an inversion prediction.
	The script is executed automatically when the user clicks on the icon (download) from the "Individuals" field from the "Predictions" section of the report webpage.
*******************************************************************************/

	 session_start(); //Inicio la sesión

    include_once('db_conexion.php');

    header('Content-Disposition: attachment; filename="individuals_predicted.txt"');
    header('Content-type: text/plain');
    echo "code\tgender\tpopulation\tfamily\trelationship\tpanel\n";

    $prediction = $_GET['pred'];
    $invid = $_GET['invid'];

    $prediction  = explode(';',$prediction);
    $predid = $prediction[0];
    $predname = $prediction[1];

   $sql_get_rowinv = "SELECT i.code, i.gender, i.population, i.family, i.relationship, i.panel
	    				FROM individuals i
						INNER JOIN individuals_detection it
		  				WHERE i.id = it.individuals_id 
		 				AND it.inversions_id = '".$invid."'	
		 				AND it.prediction_research_name = '".$predname."'
		 				AND it.prediction_research_id = '".$predid."';";
	$query=mysql_query($sql_get_rowinv);
	while ($indrow = mysql_fetch_array($query)) {
		echo $indrow['code']."\t".$indrow['gender']."\t".$indrow['population']."\t".$indrow['family']."\t".$indrow['relationship']."\t".$indrow['panel']."\n";
   	}


?>
