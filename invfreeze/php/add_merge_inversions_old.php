<?php
/******************************************************************************
	ADD_MERGE_INVERSIONS.PHP

	Merges two or multiple inversion into one new inversion.
	It is executed when merging two or more inversions by the "Merge current inversion with another" subsection from the "Advanced inversion edition" section of the current inversion report webpage.
	It then adds the new inversion to the database, and also changes the status of the "old" merged inversions as "Obsolete".
	After adding the new generated inversion to the database, it executes automatically run_breakseq.sh for the inversion's BreakSeq annotation.
*******************************************************************************/
?>


<?php
	session_start();
	include('security_layer.php');
?>

<!DOCTYPE html>
<html>

<?php 

#################################
# IMPORT VALUES FROM report.php #
#################################

#Inv to merge
$inv_to_merge_ids_array=$_POST["id_invs_to_merge"]; #Merged inversions
$inv_to_merge_array_size=sizeof($inv_to_merge_ids_array);
// $invs_ids_comma_delfor_procedure=implode(",", $inv_to_merge_ids_array); #String with all invs id to merge just for the procedure
if($inv_to_merge_array_size < 2){echo "You must select at least 2 inversions to merge";die;}

#Bps
$inv_bp1s_id=$_POST["id_bp1s_invs_to_merge"]; #bp1s id 
$inv_bp1e_id=$_POST["id_bp1e_invs_to_merge"]; #bp1e id 
$inv_bp2s_id=$_POST["id_bp2s_invs_to_merge"]; #bp2s id 
$inv_bp2e_id=$_POST["id_bp2e_invs_to_merge"]; #bp2e id

######################
# INPUT DATA PARSING #
######################

# Name
if (!isset($_POST['new_name_inv'])){$inv_to_merge_name_array = 'NULL';}
else{ $inv_to_merge_name_array=$_POST['new_name_inv']; #name id

}

#Mech
if (!isset($_POST['new_origin_inv'])){echo "You must specify a mechanism of origin for the new inversion";die;}
$inv_to_merge_mech_array=$_POST["new_origin_inv"]; #mech id
$excluded = array();
$excluded[] = "";
$inv_to_merge_mech_array = array_diff($inv_to_merge_mech_array, $excluded);
$invs_ids_comma_delfor_mech=implode(",", $inv_to_merge_mech_array); #String with all invs mech
echo ($invs_ids_comma_delfor_mech);
print_r($inv_to_merge_mech_array );
if(!empty($invs_ids_comma_delfor_mech)){$new_origin=$invs_ids_comma_delfor_mech;}
else{$new_origin = '';}

#Overlap
$overlap=0;
if(empty($inv_bp1s_id) && empty($inv_bp1e_id) && empty($inv_bp2s_id) && empty($inv_bp2e_id)){
	
	// echo "<script src="js/validations.js"></script>"; ?>
	<script>
		var current_url = window.location.href;
		var answer = alert ("You haven't selected any breakpoint for the new inversion");
		//$("#myLink").click(function() {
    		window.location = "<?php echo $_SESSION['current_report']; ?>";
		//});
	</script>
	
	<?php

}

if($overlap == 0){
	if(empty($inv_bp1s_id)){echo "You must select at least 1bp start for the new inversion";die;}
	if(empty($inv_bp1e_id)){echo "You must select at least 1bp end for the new inversion";die;}
	if(empty($inv_bp2s_id)){echo "You must select at least 2bp start for the new inversion";die;}
	if(empty($inv_bp2e_id)){echo "You must select at least 2bp end for the new inversion";die;}
}
#Evo
$inv_evo_id=$_POST["id_evo_invs_to_merge"]; #evolutinary id
if(empty($inv_evo_id)){echo "You must select the 'Evolutionary field' for the new inversion";die;}

#Functional
$inv_fun_id=$_POST["id_fun_invs_to_merge"]; #functional id

#Comments
$inv_com_id_array=$_POST["id_com_invs_to_merge"]; #comments id

#Status
$new_status=$_POST["status"]; #new_inv_status

echo "All parameters were successfully imported".'<br >';






########################
# INVERSIONS TO MERGE  #
########################

if(isset($_POST["id_invs_to_merge"]) && $inv_to_merge_array_size >= 2){
	echo "Merging ";
	include('db_conexion.php');
	foreach ($inv_to_merge_ids_array as $inv_merged){
		
  		$query = "SELECT name from inversions where id=\"$inv_merged\";";
		$result = mysql_query($query) or die("Query fail: " . mysql_error());
		$row = mysql_fetch_array($result);
		if ($row){
			$inv_name=$row[0].',';
			$all_inv_merged_names.=$inv_name;
		}
		echo "$inv_name ";
	}
	echo '<br >';
	mysql_free_result($result);
	mysql_close($con);
	$all_inv_merged_names=rtrim($all_inv_merged_names, ","); #remove last comma
} else{echo "You must select at least two inversion to merge";die;}


###################
# MERGE PROCEDURE #
###################
include('db_conexion.php');

# Delete the custom name
if (isset($_POST['new_name_inv'])){
	$inv_to_merge_ids_array = array_diff( $inv_to_merge_ids_array, [ $inv_to_merge_name_array]);
}
$invs_ids_comma_delfor_procedure=implode(",", $inv_to_merge_ids_array);


#Merge procedure	
# $query = "SELECT merge_inv_newmerge('$invs_ids_comma_delfor_procedure','".$_SESSION["userID"]."') AS new_inv_id;";
$query = "CALL merge_inversions('$invs_ids_comma_delfor_procedure',".$inv_to_merge_name_array.", '".$_SESSION["userID"]."', @newinv);";

print "Your query: $query".'<br>';
$result = mysql_query($query) or die("Query fails when performing merge procedure: " . mysql_error());
while($row = mysql_fetch_array($result)) {
	$new_inv_id=$row[0];
}
echo "Merge done succesfully in ". $new_inv_id.'<br >';
mysql_free_result($result);
mysql_close($con);

###############
# Breakpoints #
###############
#Logic to follow to update breakpoints of the new inversion
if($overlap == 0){


	#Current inv breakpoints
	$bps_array1= array("$inv_bp1s_id","$inv_bp1e_id","$inv_bp2s_id","$inv_bp2e_id");
	if(count(array_unique($bps_array1)) == 1){
		$method == "current_inv_bp";
		$m=1;
	}
	#Custom
	else{
		$method == "custom_inv_bp";
		$m=3;
	}

	##########################
	# Current and custom bps #
	##########################
	if($m == 1 || $m == 3){
		#BP1s
		if(isset($_POST["id_bp1s_invs_to_merge"])){
	
			include('db_conexion.php');

			$query = "select i.name, b.id, b.inv_id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b where i.id=b.inv_id AND b.inv_id=$inv_bp1s_id AND b.id= (SELECT id FROM breakpoints b2 WHERE inv_id=i.id AND b2.inv_id=$inv_bp1s_id ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC LIMIT 1);";
			#echo "$query".'<br >';;
			$result = mysql_query($query) or die("Can't select breakpoints coordinates, check the sql query\n: Query fail: " . mysql_error());
			while($row = mysql_fetch_array($result)){
				$val_id= $row['val_id'];
				$new_inv_BP1s= $row['bp1_start'];
			}

			mysql_free_result($result);
			mysql_close($con);
			#echo "$new_inv_BP1s".'<br >';
	
		}

		else{echo "You must select thre breakpoint 1 start";die;}

		#BP1e
		if(isset($_POST["id_bp1e_invs_to_merge"])){
	
			include('db_conexion.php');

			$query = "select i.name, b.id, b.inv_id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b where i.id=b.inv_id AND b.inv_id=$inv_bp1e_id AND b.id= (SELECT id FROM breakpoints b2 WHERE inv_id=i.id AND b2.inv_id=$inv_bp1e_id ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC LIMIT 1);";
			#echo "$query".'<br >';;
			$result = mysql_query($query) or die("Can't select breakpoints coordinates, check the sql query\n: Query fail: " . mysql_error());
			while($row = mysql_fetch_array($result)){
				$val_id= $row['val_id'];
				$new_inv_BP1e= $row['bp1_end'];
			}

			mysql_free_result($result);
			mysql_close($con);
			#echo "$new_inv_BP1e".'<br >';
	
		}

		else{echo "You must select thre breakpoint 1 end";die;}

		#BP2s
		if(isset($_POST["id_bp2s_invs_to_merge"])){
	
			include('db_conexion.php');

			$query = "select i.name, b.id, b.inv_id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b where i.id=b.inv_id AND b.inv_id=$inv_bp2s_id AND b.id= (SELECT id FROM breakpoints b2 WHERE inv_id=i.id AND b2.inv_id=$inv_bp2s_id ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC LIMIT 1);";
			#echo "$query".'<br >';;
			$result = mysql_query($query) or die("Can't select breakpoints coordinates, check the sql query\n: Query fail: " . mysql_error());
			while($row = mysql_fetch_array($result)){
				$val_id= $row['val_id'];
				$new_inv_BP2s= $row['bp2_start'];
			}

			mysql_free_result($result);
			mysql_close($con);
			#echo "$new_inv_BP2s".'<br >';
	
		}

		else{echo "You must select the breakpoint 2 start";die;}


		#BP2e
		if(isset($_POST["id_bp2e_invs_to_merge"])){
	
			include('db_conexion.php');

			$query = "select i.name, b.id, b.inv_id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b where i.id=b.inv_id AND b.inv_id=$inv_bp2e_id AND b.id= (SELECT id FROM breakpoints b2 WHERE inv_id=i.id AND b2.inv_id=$inv_bp2e_id ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC LIMIT 1);";
			#echo "$query".'<br >';;
			$result = mysql_query($query) or die("Can't select breakpoints coordinates, check the sql query\n: Query fail: " . mysql_error());
			while($row = mysql_fetch_array($result)){
				$val_id= $row['val_id'];
				$new_inv_BP2e= $row['bp2_end'];
			}

			mysql_free_result($result);
			mysql_close($con);
			#echo "$new_inv_BP2e".'<br >';
	
		}

		#Update bp query with these new bps!!
		include('db_conexion.php');

		#$query_last_bp_id="SELECT MAX(id) FROM breakpoints where inv_id=$inv1;";
		$query_last_bp_id="SELECT MAX(id) FROM breakpoints;";
		$result = mysql_query($query_last_bp_id) or die("Query fail when selecting last bp id: " . mysql_error());
		while($row = mysql_fetch_array($result)){
			$last_bp_id=$row[0];
			#echo $last_bp_id.'<br >';
		}
		mysql_free_result($result);
		mysql_close($con);
	
		include('db_conexion.php');
	
		$query_update_bp="UPDATE breakpoints SET bp1_start = $new_inv_BP1s, bp1_end = $new_inv_BP1e, bp2_start = $new_inv_BP2s, bp2_end = $new_inv_BP2e WHERE id=$last_bp_id;";
		$result = mysql_query($query_update_bp) or die("Query fail when updating new bps: " . mysql_error());
		#echo $query_update_bp.'<br >';
		mysql_free_result($result);
		mysql_close($con);
	}

	//echo "Current breakpoints method done".'<br >';
}
##############################
# OVERLAP BREAKPOINTS METHOD #
##############################
if($overlap == 1){

	#Foreach inv
	include('db_conexion.php');
	$first_id="0";
	foreach($inv_to_merge_ids_array as $inv_id_overlap) {
		#if inv have curated bp take it
		$query = "select i.name, b.id, b.val_id, b.inv_id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b where i.id=b.inv_id AND b.inv_id =$inv_id_overlap ORDER BY FIELD (b.definition_method, 'manual curation', 'default informatic definition'), b.id DESC LIMIT 1;";
	
		$result_overlap_curated = mysql_query($query);
		if (mysql_num_rows($result_overlap_curated) > 0){
			#echo "Old $inv_id_overlap bp checking".'<br >';
			while($row = mysql_fetch_array($result_overlap_curated)){
				$val_id= $row['val_id'];
				$current_inv_name= $row['name'];
				$new_inv_BP1s= $row['bp1_start'];
				$new_inv_BP1e= $row['bp1_end'];
				$new_inv_BP2s= $row['bp2_start'];
				$new_inv_BP2e= $row['bp2_end'];
				#echo $new_inv_BP1s.'<br >';

				if($first_id == "0"){
					$BP1_start_old = $new_inv_BP1s;
					$BP1_end_old = $new_inv_BP1e;
					$BP2_start_old = $new_inv_BP2s;
					$BP2_end_old = $new_inv_BP2e;
					$first_id="1";
				}
				
				if($first_id == "1"){
					#echo "Old $inv_id_overlap bp checking 2".'<br >';
					#echo "new start $new_inv_BP2s, old start $BP2_start_old, old end $BP2_end_old".'<br >';
					#BP1_START
					#echo "new start $new_inv_BP1s, old start $BP1_start_old, old end $BP1_end_old".'<br >';
					if ($new_inv_BP1s >= $BP1_start_old && $new_inv_BP1s <= $BP1_end_old){
						$BP1_start_old = $new_inv_BP1s;
						$name_bp1s= $current_inv_name;
					}
					#If bp1 does not overlap take the min bp1s value
					if ($new_inv_BP1s <= $BP1_start_old && $new_inv_BP1e <= $BP1_end_old){
						$BP1_start_old = $new_inv_BP1s;
						$name_bp1s= $current_inv_name;					
					}
					#else{continue;}
					#BP1_END
					if($new_inv_BP1e <= $BP1_end_old){
						$BP1_end_old = $new_inv_BP1e;
						$name_bp1e= $current_inv_name;
					}
					#else{continue;}
					#BP2_START
					
					if ($new_inv_BP2s >= $BP2_start_old && $new_inv_BP2s <= $BP2_end_old){
						$BP2_start_old = $new_inv_BP2s;
						$name_bp2s= $current_inv_name;
					}
					if ($new_inv_BP2s >= $BP2_start_old && $new_inv_BP2e >= $BP2_end_old){
						$BP2_start_old = $new_inv_BP2s;
						$name_bp2s= $current_inv_name;					
					}
					#else{continue;}
					#BP2_END
					if($new_inv_BP2e <= $BP2_end_old){
						$BP2_end_old = $new_inv_BP2e;
						$name_bp2e= $current_inv_name;
					}
					#else{continue;}
				}

			}
		
		}
	
	#$first_id == "0";	
	}

	#echo "BP1_s= ".$BP1_start_old."        $name_bp1s".'<br >';
	#echo "BP1_e= ".$BP1_end_old."        $name_bp1e".'<br >';
	#echo "BP2_s= ".$BP2_start_old."        $name_bp2s".'<br >';
	#echo "BP2_e= ".$BP2_end_old."        $name_bp2e".'<br >';
	
	mysql_free_result($result_overlap_curated );
	mysql_close($con);

	#Update bp query with these new bps!!
	include('db_conexion.php');

	#$query_last_bp_id="SELECT MAX(id) FROM breakpoints where inv_id=$inv1;";
	$query_last_bp_id="SELECT MAX(id) FROM breakpoints;";
	
	$result = mysql_query($query_last_bp_id) or die("Query fail: " . mysql_error());
	while($row = mysql_fetch_array($result)){
		$last_bp_id=$row[0];
		#echo $last_bp_id.'<br >';
	}
	mysql_free_result($result);
	mysql_close($con);
	
	include('db_conexion.php');
	
	$query_update_bp="UPDATE breakpoints SET bp1_start = $BP1_start_old, bp1_end = $BP1_end_old, bp2_start = $BP2_start_old, bp2_end = $BP2_end_old WHERE id=$last_bp_id;";
	$result = mysql_query($query_update_bp) or die("Query fails when updating new bps: " . mysql_error());
	#echo $query_update_bp.'<br >';
	mysql_free_result($result);
	mysql_close($con);
//echo "Overlap breakpoints method done".'<br >';
	
}
	
#######################################
# UPDATE STATUS FOR THE NEW INVERSION #
#######################################
include('db_conexion.php');
$query3 = "UPDATE inversions SET status = '$new_status' WHERE id=$new_inv_id;";
//echo $query3.'<br >';
$result3 = mysql_query($query3) or die("Query fail: " . mysql_error());
$row3 = mysql_fetch_array($result3);
mysql_free_result($result3);
mysql_close($con);

#######################################
# UPDATE MECH FOR THE NEW INVERSION #
#######################################
include('db_conexion.php');
$query3 = "UPDATE inversions SET origin = '$new_origin' WHERE id=$new_inv_id;";
//echo "Update mechanism for the new inv: ".$query3;
$result3 = mysql_query($query3) or die("Query fail: " . mysql_error());
$row3 = mysql_fetch_array($result3);
mysql_free_result($result3);
mysql_close($con);


########################################################################
# UPDATE STUDY, DEFINITION METHOD AND DESCRIPTION FOR THE NEW INVERSION #
########################################################################
include('db_conexion.php');

#Current bps
if($m == 1){
	#echo "CURRENT STUDY, DEFINITION METHOD AND DESCRIPTION!".'<br >';


	$sql_additional_info="SELECT b.definition_method, b.description, b.val_id, v.research_name as v_research_name FROM  breakpoints b LEFT JOIN validation v ON (v.bp_id = b.id) LEFT JOIN researchs r ON r.name=v.research_name WHERE b.inv_id =$inv_bp1s_id ORDER BY FIELD (b.definition_method, 'manual curation', 'default informatic definition'), b.date DESC LIMIT 1";
	#echo $sql_additional_info.'<br >';
	$result = mysql_query($sql_additional_info) or die("Select breakpoints study query fail: " . mysql_error());
	
	$array_manual=array();

	while($row = mysql_fetch_array($result)){

		#echo $row['val_id'].'<br >';
		
		if(is_null($row['val_id'])){$val_id='NULL';}else{$val_id=$row['val_id'];}
		#echo "$val_id".'<br >';
		#echo "$m".'<br >';

		$new_study = $row['v_research_name'];
		$new_description = $row['description'];
		$new_definition_method = $row['definition_method'];

		if ($new_definition_method == "manual curation") {
				array_push($array_manual, $val_id);
			}
	}
	mysql_free_result($result);
	mysql_close($con);
	
	#Get last bp id
	include('db_conexion.php');
	$query_last_bp_id="SELECT MAX(id) FROM breakpoints;";
	$result = mysql_query($query_last_bp_id) or die("Query fails when getting last bp id: " . mysql_error());
	while($row = mysql_fetch_array($result)){
		$last_bp_id=$row[0];
	}
	mysql_free_result($result);
	mysql_close($con);
	
	#update definition,description
	if(!empty($array_manual)){
		include('db_conexion.php');

		$new_val_id = $array_manual[0];

		$new_definition_method = "manual curation";

		$query="UPDATE breakpoints SET description=\"$new_description\", definition_method=\"$new_definition_method\", val_id= $new_val_id WHERE id=$last_bp_id;";
		#echo $query;
		$result = mysql_query($query) or die("Query fail when updating bp definition,description: " . mysql_error());			
		mysql_free_result($result);
		mysql_close($con);

	} else{

		include('db_conexion.php');

		$new_val_id = 'NULL';
		$new_definition_method = "default informatic definition";

		$query="UPDATE breakpoints SET description=\"$new_description\", definition_method='$new_definition_method' WHERE id=$last_bp_id;";
		$result = mysql_query($query) or die("Update breakpoints description query fail: " . mysql_error());
		mysql_free_result($result);
		mysql_close($con);
	}

	
	#update study
	if($val_id != 'NULL'){

		include('db_conexion.php');
		$query_upd_val_id="UPDATE breakpoints SET val_id = '$val_id' WHERE id='$last_bp_id';";
		#echo $query_upd_val_id;
		$result = mysql_query($query_upd_val_id) or die("Query fail when updating bp study validation id: " . mysql_error());
		mysql_free_result($result);
		mysql_close($con);

		include('db_conexion.php');
		$query= "UPDATE validation SET bp_id=$last_bp_id, research_name='$new_study' WHERE id=$val_id ORDER BY id DESC LIMIT 1;";
		#echo $query.'<br >';
		#$query= "UPDATE validation SET research_name='$study_bp' WHERE bp_id=$last_bp_id;";
		$result = mysql_query($query) or die("Query fails when updating bp study " . mysql_error());
		
		mysql_free_result($result);
		mysql_close($con);
		
		
	}
}

#Custom bps
/*
if($m == 3){
	
	foreach ($bps_array1 as $id_inv_from_bp){ #Foreach breakpoint
		
		$sql_additional_info="SELECT b.definition_method, b.description, b.val_id, v.research_name as v_research_name FROM  breakpoints b LEFT JOIN validation v ON (v.bp_id = b.id) LEFT JOIN researchs r ON r.name=v.research_name WHERE b.inv_id =$id_inv_from_bp ORDER BY FIELD (b.definition_method, 'manual curation', 'default informatic definition'), b.date DESC LIMIT 1;";

		$result = mysql_query($sql_additional_info) or die("Select breakpoints study query fail: " . mysql_error());
		while($row = mysql_fetch_array($result)){
		
			if(is_null($row['val_id'])){$val_id='NULL';}else{$val_id=$row['val_id'];}
			echo $val_id;
			$new_study.= $row['v_research_name'];
			$new_description.= $row['description'];
			$new_definition_method.= $row['definition_method'];
			
		}
		mysql_free_result($result);
		mysql_close($con);
	}
}
*/

if($m == 3 || $m == 2){
	#Update definition method
	$array_manual=array();

	foreach ($bps_array1 as $id_inv_from_bp){ #Foreach breakpoint

		include('db_conexion.php');
		
		$sql_additional_info="SELECT b.definition_method, b.description, b.val_id, v.research_name as v_research_name FROM  breakpoints b LEFT JOIN validation v ON (v.bp_id = b.id) LEFT JOIN researchs r ON r.name=v.research_name WHERE b.inv_id =$id_inv_from_bp ORDER BY FIELD (b.definition_method, 'manual curation', 'default informatic definition'), b.date DESC LIMIT 1;";

		$result = mysql_query($sql_additional_info) or die("Select breakpoints study query fail: " . mysql_error());
		while($row = mysql_fetch_array($result)){
		
			if(is_null($row['val_id'])){$val_id='NULL';}else{$val_id=$row['val_id'];}
			
			$new_study= $row['v_research_name'];
			$new_description= $row['description'];
			$definition_method= $row['definition_method'];
			if ($definition_method == "manual curation") {
				array_push($array_manual, $val_id);
			}
		}
		mysql_free_result($result);
		mysql_close($con);
	}

	if(!empty($array_manual)){
		include('db_conexion.php');

		$new_val_id = $array_manual[0];
		$new_definition_method = "manual curation";

		$query="UPDATE breakpoints SET definition_method='$new_definition_method', val_id= $new_val_id WHERE id=$last_bp_id;";
		#echo $query;
		$result = mysql_query($query) or die("Query fail when updating bp definition,description: " . mysql_error());			
		mysql_free_result($result);
		mysql_close($con);

	} else{

		include('db_conexion.php');

		$new_val_id = '';
		$new_definition_method = "default informatic definition";

		#Update description
		if($m == 3){

			$new_description='Breakpoints not refined due to lack of overlap of predictions in at least one breakpoint'; 
		}

		if($m == 2){
			$new_description='Breakpoints not refined. Product of the overlap between '.$all_inv_merged_names;
 
		}

		$query="UPDATE breakpoints SET description=\"$new_description\", definition_method=\"$new_definition_method\" WHERE id=$last_bp_id;";
		$result = mysql_query($query) or die("Update breakpoints description query fail: " . mysql_error());
		mysql_free_result($result);
		mysql_close($con);
	}
}


#####################
# Evolutionary info #
#####################

#EVOLUTINARY HISTORY UPDATE
include('db_conexion.php');

$query = "SELECT ancestral_orientation, age, evo_origin FROM inversions WHERE id=$inv_evo_id;";
#echo $query;
$result = mysql_query($query) or die("Can't select Evolutionary history, check the sql query\n: Query fail: " . mysql_error());
while($row = mysql_fetch_array($result)){
		#echo $row['ancestral_orientation'].'<br >';
		if (is_null($row['ancestral_orientation'])){$new_ancestral_ori='';}
		else{$new_ancestral_ori= $row['ancestral_orientation'];}
		#echo $new_ancestral_ori.'<br >';
		if (is_null($row['age'])){$new_age='';}
		else{$new_age= $row['age'];}
		if (is_null($row['evo_origin'])){$new_evo_origin='';}
		else{$new_evo_origin= $row['evo_origin'];}
}

mysql_free_result($result);
mysql_close($con);

	
include('db_conexion.php');

#Update query	
$query_update_evo_hist="UPDATE inversions SET ancestral_orientation = '$new_ancestral_ori', age = '$new_age', evo_origin = '$new_evo_origin' WHERE id=$new_inv_id;";
#echo $query_update_evo_hist.'<br >';
$result = mysql_query($query_update_evo_hist) or die("Query fail: " . mysql_error());
#echo $query_update_bp.'<br >';
mysql_free_result($result);
mysql_close($con);




#ORIENTATION IN OTHER SPECIES
include('db_conexion.php');

$query = "SELECT species_id, orientation, method, source, num_ind, result_value FROM inversions_in_species WHERE inversions_id=$inv_evo_id;";
#echo $query.'<br >';
$result = mysql_query($query) or die("Can't select Evolutionary orientation in other species, check the sql query\n: Query fail: " . mysql_error());

while ($row = mysql_fetch_assoc($result)) {

	#echo $row['species_id'].'<br >';
	if (is_null($row['species_id'])){$new_species_id="''";}
	else{$new_species_id= $row['species_id'];}
	if (is_null($row['orientation'])){$new_orientation="''";}
	else{$new_orientation= $row['orientation'];}
	if (is_null($row['method'])){$new_method="''";}
	else{$new_method= $row['method'];}
	if (is_null($row['source'])){$new_source="''";}
	else{$new_source= $row['source'];}
	if (is_null($row['num_ind'])){$new_num_ind="''";}
	else{$new_num_ind= $row['num_ind'];}
	if (is_null($row['result_value'])){$new_result_value="''";}
	else{$new_result_value= $row['result_value'];}

	#Insert query
	$query_insert_ori_inotherspp="INSERT INTO inversions_in_species (species_id, inversions_id, orientation, method, source, num_ind, result_value) VALUES ($new_species_id,$new_inv_id,\"$new_orientation\",\"$new_method\",\"$new_source\",$new_num_ind,$new_result_value);";

	#echo $query_insert_ori_inotherspp.'<br >';
	$result2 = mysql_query($query_insert_ori_inotherspp) or die("Query fail: " . mysql_error());
}
mysql_free_result($result);
mysql_free_result($result2);
mysql_close($con);


#INV ORIGIN
include('db_conexion.php');

$query = "SELECT origin, method, source FROM inv_origin WHERE inv_id=$inv_evo_id;";
#echo $query.'<br >';
$result = mysql_query($query) or die("Can't select Evolutionary origin, check the sql query\n: Query fail: " . mysql_error());

#$no_row=0;
while ($row = mysql_fetch_assoc($result)) {
	#$no_row = 1;
	#echo $row['species_id'].'<br >';
	if (is_null($row['origin'])){$new_origin='';}
	else{$new_origin= $row['origin'];}

	if (is_null($row['method'])){$new_method='';}
	else{$new_method= $row['method'];}
	if (is_null($row['source'])){$new_source='';}
	else{$new_source= $row['source'];}
	

	#Insert query
	$query_insert_origin="INSERT INTO inv_origin (inv_id,origin, method, source) VALUES ($new_inv_id,\"$new_origin\",\"$new_method\",\"$new_source\");";

	#echo $query_insert_origin.'<br >';
	$result2 = mysql_query($query_insert_origin) or die("INV ORIGIN query fail: " . mysql_error());
}

mysql_free_result($result);
mysql_free_result($result2);
mysql_close($con);

/*if (mysql_num_rows($result)==0) {
	include('db_conexion.php');

	$query_insert_origin="SET FOREIGN_KEY_CHECKS=0;INSERT INTO inv_origin (inv_id,origin, method, source) VALUES ($new_inv_id,'','','');SET FOREIGN_KEY_CHECKS=1;";
	$result3 = mysql_query($query_insert_origin) or die("INV ORIGIN WITHOUT DATA query fail: " . mysql_error());

	mysql_free_result($result3);
	mysql_close($con);
}*/

#INV AGE
include('db_conexion.php');

$query = "SELECT GROUP_CONCAT(a.age ORDER BY a.age ASC SEPARATOR '-') AS age, GROUP_CONCAT(DISTINCT a.method) AS method, a.source, r.year, r.pubMedID FROM inv_age a LEFT JOIN researchs r ON r.name=a.source WHERE a.inv_id='$inv_evo_id' GROUP BY a.source ORDER BY r.year, a.source;";

#echo $query.'<br >';
$result = mysql_query($query) or die("Can't select inversion age info, check the sql query\n: Query fail: " . mysql_error());

while ($row = mysql_fetch_assoc($result)) {

	#echo $row['species_id'].'<br >';
	if (is_null($row['age'])){$new_age='';}
	else{$new_age= $row['age'];}
	if (is_null($row['method'])){$new_method='';}
	else{$new_method= $row['method'];}
	if (is_null($row['source'])){$new_source='';}
	else{$new_source= $row['source'];}
	if (is_null($row['year'])){$new_year='';}
	else{$new_year= $row['year'];}
	if (is_null($row['pubMedID'])){$new_pubmedid='';}
	else{$new_pubmedid= $row['pubMedID'];}
	

	#Insert query
	$query_insert_age="INSERT INTO inv_age (inv_id,age, method, source) VALUES ($new_inv_id,\"$new_age\",\"$new_method\",\"$new_source\");";

	#echo $query_insert_age.'<br >';
	$result2 = mysql_query($query_insert_age) or die("Insert age query fail: " . mysql_error());
}
mysql_free_result($result);
mysql_free_result($result2);
mysql_close($con);





############
# Comments #
############

include('db_conexion.php');
foreach ($inv_com_id_array as $id_inv_comment){

	
	#Last inversion comment
	$last_com_query= "select comment_id, inversion_com from comments where inv_id = $id_inv_comment ORDER BY comment_id DESC LIMIT 1;";
	$result = mysql_query($last_com_query);
	while($thisrow = mysql_fetch_array($result)){
		if($thisrow['inversion_com'] == 'NULL'){$last_com = '';}
		else{$last_com_inv.=$thisrow['inversion_com'];}
	}
	
	#Last bp comment
	$last_bp_com_query= "select comment_id, bp_com from comments where inv_id = $id_inv_comment ORDER BY comment_id DESC LIMIT 1;";
	$result = mysql_query($last_bp_com_query);
	
	while($thisrow = mysql_fetch_array($result)){
	if($thisrow['bp_com'] == 'NULL'){$last_com_bp = '';}
	else{$last_com_bp_inv.=$thisrow['bp_com'];}
	}

	#Last eh comment
	$last_eh_com_query= "select comment_id, evolutionary_history_com from comments where inv_id = $id_inv_comment ORDER BY comment_id DESC LIMIT 1;";
	$result = mysql_query($last_eh_com_query);
	$last_com_eh = '';
	while($thisrow = mysql_fetch_array($result)){
	if($thisrow['evolutionary_history_com'] == 'NULL'){$last_com_eh = '';}
	else{$last_com_eh_inv.=$thisrow['evolutionary_history_com'];}
	}
	
}

mysql_free_result($result);
mysql_close($con);

//UPDATE COMMENTS QUERY
include('db_conexion.php');
$query3 = "INSERT INTO comments (inv_id, user, date, inversion_com, bp_com, evolutionary_history_com) VALUES ($new_inv_id, '".$_SESSION["userID"]."', CURDATE(), '$last_com_inv', '$last_com_bp_inv', '$last_com_eh_inv');";	
//$query3 = "UPDATE comments SET  inversion_com= '$last_com_inv', bp_com = '$last_com_bp_inv', evolutionary_history_com = '$last_com_eh_inv' WHERE id=;";	
$result = mysql_query($query3)  or die("Query update comments fail: " . mysql_error());;

mysql_free_result($result);
mysql_close($con);
##################################################
# Set the automatic BP relation with gene and SD #
##################################################
#Get last bp id
/*include('db_conexion.php');

	$query_last_bp_id="SELECT MAX(id) FROM breakpoints;";
	
	$result = mysql_query($query_last_bp_id) or die("Query fail: " . mysql_error());
	while($row = mysql_fetch_array($result)){
		$last_bp_id=$row[0];
	}
	mysql_free_result($result);
	mysql_close($con);
#update relation
include('db_conexion.php');
$query="CALL get_inv_gene_realtion($last_bp_id);";
$result = mysql_query($query) or die("Wrong update gene and SD relation; Query fail: " . mysql_error());
$roww = mysql_fetch_array($result);
mysql_free_result($result);
mysql_close($con);*/

include('db_conexion.php');
$query2="CALL get_SD_in_BP($last_bp_id);";
$result2= mysql_query($query2) or die("Wrong update gene and SD relation; Query fail: " . mysql_error());
$roww2 = mysql_fetch_array($result2);
mysql_free_result($result2);
mysql_close($con);

echo "Automatic BP relation with gene and SD updated".'<br >';



#####################
# FUNCTIONAL EFFECT #
#####################

include('db_conexion.php');

#Look if it has been automatically annotated
$query_gene = "SELECT DISTINCT(gene_id),id from genomic_effect where inv_id=$new_inv_id AND gene_id is not null;";
$result_gene = mysql_query($query_gene);
#print $query_gene."<br >";

if (mysql_num_rows($result_gene) > 0) {

	#If the inv has been automatically annotated...
	while($row = mysql_fetch_array($result_gene)){
		$new_inv_geneid = $row['gene_id'];
		#Select current info
		$query = "SELECT source, functional_effect, functional_consequence FROM genomic_effect WHERE inv_id=$inv_fun_id and id=(SELECT max(id) from genomic_effect where inv_id=$inv_fun_id AND gene_id is not null);";
		$result = mysql_query($query) or die("FUNCTIONAL GENE query fail: " . mysql_error());;
		#mysql_free_result($result);
		while($row = mysql_fetch_array($result)){

			if(is_null($row['source'])){$new_funct_source='NULL';}else{$new_funct_source= $row['source'];}
			if(is_null($row['functional_effect'])){$new_funct_effect='NULL';}else{$new_funct_effect= $row['functional_effect'];}
			if(is_null($row['functional_consequence'])){$new_funct_conseq='NULL';}else{$new_funct_conseq=$row['functional_consequence'];}

			#Update info with the merged inv
			$query_update_funct_info="UPDATE genomic_effect SET source='$new_funct_source', functional_effect='$new_funct_effect', functional_consequence='$new_funct_conseq' WHERE inv_id='$new_inv_id';";
			#echo "$query_update_funct_info\n";

			$result2 = mysql_query($query_update_funct_info) or die("FUNCTIONAL query fail: $query_update_funct_info " . mysql_error());
			#mysql_free_result($result2);

		}	
	}

}else{ 

	#Select current info
	
	$query = "SELECT source, functional_effect, functional_consequence FROM genomic_effect WHERE inv_id=$inv_fun_id and id=(SELECT max(id) from genomic_effect where inv_id=$inv_fun_id AND gene_id is not null);";
	#echo "$query\n";
	$result = mysql_query($query);

	while($row = mysql_fetch_array($result)){
		$new_funct_geneid = $row['gene_id'];
		$new_funct_bpid = $row['bp_id'];
		$new_funct_gene_relation = $row['gene_realtion'];
		$new_funct_comment = $row['comment'];
		$new_funct_source = $row['source'];
		$new_funct_effect = $row['functional_effect'];
		$new_funct_conseq = $row['functional_consequence'];
	}

	#Insert query
	#echo "$query\n";
	$query="INSERT INTO genomic_effect (inv_id, gene_id, bp_id, gene_relation, comment, source, functional_effect, functional_consequence) VALUES ('$new_inv_id','$new_funct_geneid', '$new_funct_bpid', '$new_funct_gene_relation', '$new_funct_comment',\"$new_funct_source\",'$new_funct_effect','$new_funct_conseq');";
	echo "$query\n";

	#echo $query.'<br >';
	$result = mysql_query($query) or die("MANUAL FUNCTIONAL query fail: " . mysql_error());
	mysql_free_result($result);
}


mysql_close($con);

##############################################
# Print redirection to new inversion message #
##############################################
echo "<br /><input type='submit' value='Go to the new inversion' name='gsubmit'  onclick=\"location.href='../report.php?q=".$new_inv_id."'\" />";	


#######################
# BREAKSEQ annotation #
#######################
//Breakseq gff input file generation
//----------------------------------------------------------------------------
include('db_conexion.php');
exec("kill $(ps aux | grep 'breakseq-1.3' | awk '{print $2}') > /dev/null 2>&1"); #If there is already a breakseq process running, kill it
$gff_file = fopen("/home/invfest/BPSeq/breakseq_annotated_gff/input.gff", "w") or die("Unable to create gff file!");
//Select inversions
$sql_bp="SELECT i.name, b.id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, i.status, b.GC FROM inversions i, breakpoints b  WHERE i.id=b.inv_id AND b.chr NOT IN ('chrM') AND b.GC is null;"; #We will annotate ALL inversions that are not annotated
#print "$sql_bp".'<br/>';

$result_bp=mysql_query($sql_bp);
while($bprow = mysql_fetch_array($result_bp))
{
	$midpoint_BP1=round(($bprow['bp1_end']+$bprow['bp1_start'])/2);
	$bp2_end =$bprow['bp2_end'];
	$bp2_start =$bprow['bp2_start'];
	#print "$bp2_end\t$bp2_start\n";
    	$midpoint_BP2=round(($bp2_start+$bp2_end)/2);
	#print "$midpoint_BP2\n";
    	$chr=$bprow['chr'];
	$name=$bprow['name'];
	$id_bp= $bprow['id'];
	//$gene_id= $bprow['gene_id'];
    $inverion_gff_line= "$chr\t$name\tInversion\t$midpoint_BP1\t$midpoint_BP2\t.\t.\t.\n";
    
    fwrite($gff_file, $inverion_gff_line);
}

fclose($gff_file);

//BreakSeq execution
//---------------------------------------------------------------------------
exec("nohup ./run_breakseq.sh > /dev/null 2>&1 &");
//---------------------------------------------------------------------------
?>