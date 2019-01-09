<?php
/******************************************************************************
	SELECT_INDEX.PHP

	Sends multiple querys to the database to select/store specific data into variables which are retrieved in other PHP pages
*******************************************************************************/
    
    // Session start for the PHP
    session_start();
    
    // Connection to the database
    include_once('db_conexion.php');
    // Include global variables
    include_once('php_global_variables.php');


    // Send multiple querys to the database to select/store specific data into variables which are retrieved in other php pages
    $research_name_user="";
    $inversion_status_option=""; $research_name_option=""; 
    $validation_method_option=""; $validation_status_option=""; 
    $individuals_option=""; $population_option=""; $orientation_option="";


    $sql_inversion_status="select distinct status from inversions where status is not null order by status;";
    $result_inversion_status=mysql_query($sql_inversion_status);
    while($thisrow=mysql_fetch_array($result_inversion_status)) {
	    $inversion_status_option.="<option value=\"".$thisrow["status"]."\">".$array_status_no_format[$thisrow["status"]]."</option>";
    }

    $sql_research_name="select distinct name from researchs where name is not null order by name;";
    $result_research_name=mysql_query($sql_research_name);
    while($thisrow=mysql_fetch_array($result_research_name)) {
	    $research_name_option.="<option value=\"".$thisrow["name"]."\">".$thisrow["name"]."</option>";
    }


     $sql_prediction_method="select distinct prediction_method as method from researchs where prediction_method is not null AND prediction_method != ' ' order by prediction_method;";
    $result_prediction_method=mysql_query($sql_prediction_method);
    $transform = array();
    while($thisrow=mysql_fetch_array($result_prediction_method)) {
        $transform[] = $thisrow["method"]; 
    }
    $transform_string = implode(",", $transform);
    $transform_array = explode(",", $transform_string);
    $transform_array = array_map("ltrim", $transform_array);
    $transform_array = array_unique($transform_array);
    foreach ($transform_array as $thisrow2) {
        $prediction_method_option.="<option value=\"".$thisrow2."\">".$thisrow2."</option>";
    }
        

    $sql_validation_method="select distinct method from validation where method is not null order by method;";
    $result_validation_method=mysql_query($sql_validation_method);
    $checkpoint_method = array();
    while($thisrow=mysql_fetch_array($result_validation_method)) {
	    $validation_method_option.="<option value=\"".$thisrow["method"]."\">".$thisrow["method"]."</option>";
        $checkpoint_method[] = $thisrow["method"];
    }

    $sql_validation_status="select distinct status from validation where status is not null order by status;";
    $result_validation_status=mysql_query($sql_validation_status);
    $checkpoint_status = array();
    while($thisrow=mysql_fetch_array($result_validation_status)) {
	    $validation_status_option.="<option value=\"".$thisrow["status"]."\">".$thisrow["status"]."</option>";
         $checkpoint_status[] = $thisrow["status"];
    }

    $sql_individuals="select distinct id, code from individuals where id is not null order by id;";
    $result_individuals=mysql_query($sql_individuals);
    while($thisrow=mysql_fetch_array($result_individuals)) {
	    $individuals_option.="<option value=\"".$thisrow["id"]."\">".$thisrow["code"]."</option>";
    }

    $sql_population="select distinct name from population where name is not null order by name;";
    $result_population=mysql_query($sql_population);
    while($thisrow=mysql_fetch_array($result_population)) {
	    $population_option.="<option value=\"".$thisrow["name"]."\">".$thisrow["name"]."</option>";
    }

    $sql_orientation="select distinct orientation from inversions_in_species where orientation is not null;";
    $result_orientation=mysql_query($sql_orientation);
    while($thisrow=mysql_fetch_array($result_orientation)) {
	    $orientation_option.="<option value='".$thisrow["orientation"]."'>".$thisrow["orientation"]."</option>\n";
    }


    $sql_research_name_user="select distinct name from researchs where name is not null order by name;";
    //select distinct researchs_name as name from researchs_has_user rhs, user u where researchs_name is not null and user='".$_SESSION['user']."' and rhs.user_id=u.id order by researchs_name;
    $result_research_name_user=mysql_query($sql_research_name_user);
    $checkpoint_research = array();
    while($thisrow=mysql_fetch_array($result_research_name_user)) {
	    $research_name_user.="<option value='".$thisrow["name"]."'>".$thisrow["name"]."</option>\n";
        $checkpoint_research[] = $thisrow["name"];
    }

?>
