<?php

session_start(); //Inicio la sesión

$id=$_GET["q"];

include_once('db_conexion.php');

$sql_pred="SELECT p.id, p.research_name, p.BP1s, p.BP1e, p.BP2s, p.BP2e
	FROM predictions p 
	WHERE p.inv_id='$id' 
	ORDER BY p.research_name;";
$result_pred = mysql_query($sql_pred);
$predictions='';
while($thisrow = mysql_fetch_array($result_pred)){
	$predictions.="<tr><td><a title=\"BP1:".$thisrow['BP1s']."-".$thisrow['BP1e']." BP2:".$thisrow['BP2s']."-".$thisrow['BP2e']."\">".$thisrow['research_name']."</a></td>";
	$predictions.="<td><input type='checkbox' value='".$thisrow['id']."' name='pinv1[]' /></td>
		<td><input type='checkbox' value='".$thisrow['id']."' name='pinv2[]' /></td></tr>";
}

$sql_val="SELECT v.id, v.research_name, v.method, v.status, v.experimental_conditions, v. primers, v.comment
	FROM validation v
	WHERE v.inv_id='$id';";
$result_val=mysql_query($sql_val);
$validations='';
while($thisrow = mysql_fetch_array($result_val)){
	$validations.="<tr><td><a title=\"Method: ".$thisrow['method']."; Status: ".$thisrow['status'];
	if ($thisrow['experimental_conditions'] != "" || $thisrow['experimental_conditions'] != NULL) {
		$validations.="; Experimental Conditions: ".$thisrow['experimental_conditions'];
	}
	if ($thisrow['primers'] != "" || $thisrow['primers'] != NULL) {
		$validations.="; Primers: ".$thisrow['primers'];
	}
	if ($thisrow['comment'] !="" || $thisrow['comment']!= NULL){
		$validations.="; Comments: ".$thisrow['comment'];
	}
	$validations.="\">".$thisrow['research_name']."</td>
		<td><input type='checkbox' value='".$thisrow['id']."' name='vinv1[]' /></td>
		<td><input type='checkbox'value ='".$thisrow['id']."' name='vinv2[]' /></td></tr>";

}

mysql_close($con);
?>
