<?php

session_start(); //Inicio la sesión

include_once('db_conexion.php');

$research_name_user="";
$inversion_status_option=""; $research_name_option=""; 
$validation_method_option=""; $validation_status_option=""; 
$individuals_option=""; $population_option=""; $orientation_option="";

$sql_inversion_status="select distinct status from inversions where status is not null order by status;";
$result_inversion_status = mysql_query($sql_inversion_status);
while($thisrow = mysql_fetch_array($result_inversion_status)){
	$inversion_status_option.="<option value=\"".$thisrow["status"]."\">".$thisrow["status"]."</option>";
}

$sql_research_name="select distinct name from researchs where name is not null order by name;";
$result_research_name = mysql_query($sql_research_name);
while($thisrow = mysql_fetch_array($result_research_name)){
	$research_name_option.="<option value=\"".$thisrow["name"]."\">".$thisrow["name"]."</option>";
}

$sql_validation_method="select distinct method from validation where method is not null order by method;";
$result_validation_method = mysql_query($sql_validation_method);
while($thisrow = mysql_fetch_array($result_validation_method)){
	$validation_method_option.="<option value=\"".$thisrow["method"]."\">".$thisrow["method"]."</option>";
}

$sql_validation_status="select distinct status from validation where status is not null order by status;";
$result_validation_status = mysql_query($sql_validation_status);
while($thisrow = mysql_fetch_array($result_validation_status)){
	$validation_status_option.="<option value=\"".$thisrow["status"]."\">".$thisrow["status"]."</option>";
}

$sql_individuals="select distinct id, code from individuals where id is not null order by id;";
$result_individuals = mysql_query($sql_individuals);
while($thisrow = mysql_fetch_array($result_individuals)){
	$individuals_option.="<option value=\"".$thisrow["id"]."\">".$thisrow["code"]."</option>";
}

$sql_population="select distinct name from population where name is not null order by name;";
$result_population = mysql_query($sql_population);
while($thisrow = mysql_fetch_array($result_population)){
	$population_option.="<option value=\"".$thisrow["name"]."\">".$thisrow["name"]."</option>";
}

$sql_orientation="select distinct orientation from inversions_in_species where orientation is not null;";
$result_orientation = mysql_query($sql_orientation);
while($thisrow = mysql_fetch_array($result_orientation)){
	$orientation_option.="<option value='".$thisrow["orientation"]."'>".$thisrow["orientation"]."</option>\n";
}


$sql_research_name_user="select distinct name from researchs where name is not null order by name;";
//select distinct researchs_name as name from researchs_has_user rhs, user u where researchs_name is not null and user='".$_SESSION['user']."' and rhs.user_id=u.id order by researchs_name;
$result_research_name_user = mysql_query($sql_research_name_user);
while($thisrow = mysql_fetch_array($result_research_name_user)){
	$research_name_user.="<option value='".$thisrow["name"]."'>".$thisrow["name"]."</option>\n";
}

?>
