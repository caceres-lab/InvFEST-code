<?php
/******************************************************************************
	ECHO_FOSMIDS.PHP

	Creates a download file ("probes.txt") containing all the supporting fosmid probes for an inversion prediction.
	The script is executed automatically when the user clicks on the icon (download) from the "Support" field from the "Predictions" section of the report webpage.
*******************************************************************************/

    header('Content-Disposition: attachment; filename="probes.txt"');
    header('Content-type: text/plain');
    $fos=$_GET['fos'];
    echo str_replace(',',"\n",$fos);

?>
