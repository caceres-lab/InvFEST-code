<?php
/******************************************************************************
	ECHO_INDIVIDUALS.PHP

	Creates a download file ("individuals_predicted.txt") containing all the individuals for an inversion prediction.
	The script is executed automatically when the user clicks on the icon (download) from the "Individuals" field from the "Predictions" section of the report webpage.
*******************************************************************************/

    header('Content-Disposition: attachment; filename="individuals_predicted.txt"');
    header('Content-type: text/plain');
    $ind=$_GET['ind'];
    echo str_replace(',',"\n",$ind);

?>
