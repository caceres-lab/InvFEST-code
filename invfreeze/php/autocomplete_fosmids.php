<?php
/******************************************************************************
	AUTOCOMPLETE_FOSMIDS.PHP

	When adding a validation in the inversion report webpage, it displays a box containing a list of the available fosmids' names to autocomplete the current fosmid's name search
*******************************************************************************/

    include_once('db_conexion.php');


    $result = mysql_query("SELECT DISTINCT name FROM fosmids ORDER BY name;");
    //$result = mysql_query("SELECT DISTINCT symbol FROM  HsRefSeqGenes;");
    
    while ($row = mysql_fetch_assoc($result)) {
   		    $fosmids[]=$row['name'];
    }
    mysql_free_result($result);
    mysql_close($link);

    // Check the parameter
    if(isset($_GET['part']) and $_GET['part'] != '') {
	    // Initialize the results array
	    $results = array();

	    // Search fosmids
	    foreach($fosmids as $fosmid) {
		    // If it starts with 'part' add to results
		    if( strpos($fosmid, $_GET['part']) === 0 ) {
			    $results[] = $fosmid;
		    }
	    }

	    // Return the array as json with PHP 5.2
	    echo json_encode($results);
    }

?>
