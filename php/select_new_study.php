<?php
/******************************************************************************
	SELECT_NEW_STUDY.PHP

	Apparently it is not used anymore
	Replaced by (?)
*******************************************************************************/

    include('security_layer.php');
    include_once('db_conexion.php');


    $sql_valMethods="SELECT DISTINCT name FROM methods WHERE aim LIKE '%validation%' ORDER BY name;";

    $result_valMethods = mysql_query($sql_valMethods);
    while($thisrow = mysql_fetch_array($result_valMethods)){
	    $valMethods.="<option value='".$thisrow["name"]."'>".$thisrow["name"]."</option>\n";
    }

    mysql_close($con);

?>
