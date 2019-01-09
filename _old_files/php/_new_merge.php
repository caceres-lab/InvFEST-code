<?php include('security_layer.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php
#################################
# IMPORT VALUES FROM report.php #
#################################
$inv1=$_POST["inv1"]; #inv1_id
$inv2=$_POST["inv2"]; #inv2_ids
$inv1_name=$_POST["inv1_name"]; #inv1_name
$inv2_ids_string_comma_del = implode(",", $inv2); #Joins array elements into one string #inv2_ids into a comma delimited string
$new_status=$_POST["status"]; #new_inv_status
if(!empty($_POST["mechanism_bp"])){$new_origin=$_POST["mechanism_bp"];} #new_inv_mech
#Logic to follow to update breakpoints of the new inversion
$m='0';
if(isset($_POST['current'])){echo "CURRENT breakpoints method selected".'<br >';$method="current_inv_bp";$m='1';} 
if($m == 0 && isset($_POST['overlap_bps_checkbox'])){echo "OVERLAPING breakpoints method selected".'<br >';$method="overlap_inv_bp";$m='2';}
if($m == 0 && isset($_POST['custom_inv_bp'])){echo "CUSTOM breakpoints method selected".'<br >';$method="custom_inv_bp";$m='3';}
if($m == 0){echo "Please, select an option in order to update the breakpoints of the new inversion!"; die;}

#Print a "Merging status" message
$merging="Proceding to merge $inv1_name with ";

$inv2_ids_array = explode(",", $inv2_ids_string_comma_del);
foreach($inv2_ids_array as $inv2) {
	include('db_conexion.php');
  	$query = "SELECT name from inversions where id=\"$inv2\";";
	$result = mysql_query($query) or die("Query fail: " . mysql_error());
	$row = mysql_fetch_array($result);
	if ($row){$merging.=$row[0].',';}
	mysql_free_result($result);
	mysql_close($con);
}
$merging=substr_replace($merging, "", -1);
print "$merging".'<br>';

##############################################################
# SET ALL INFORMATION (predictions,validations, support,...) #
##############################################################
if($m != "0"){
	
	include('db_conexion.php');
	
	$query = "SELECT merge_inv_ISAAC('$inv1,$inv2_ids_string_comma_del','".$_SESSION["userID"]."') AS new_inv_id;";
	print "Your query: $query".'<br>';
	$result = mysql_query($query) or die("Query fail: " . mysql_error());
	$row = mysql_fetch_array($result);
	if ($row){
		$new_inv_id=$row[0];
		print "Merge done succesfully\n";
		
	}
	else{print "Something went wrong, check \"merge_inv_ISAAC\" sql procedure..."; die;}

	mysql_free_result($result);
	mysql_close($con);
}

######################
# UPDATE BREAKPOINTS #
######################
$current_has_manual_curation_bp="0";
################################################
# Current inversion bps as new inv bps  OPTION #
################################################
if($method == "current_inv_bp"){
	include('db_conexion.php');

	$query = "select i.name, b.id, b.val_id, b.inv_id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b where i.id=b.inv_id AND b.inv_id =$inv1 AND b.definition_method ='manual curation';";
	
	$result_current1 = mysql_query($query);
	if (mysql_num_rows($result_current1) > 0){
		$current_has_manual_curation_bp="1";
		#echo "Old inv1 had manual curated bp results\n";
		while($row = mysql_fetch_array($result_current1)){
			$val_id= $row['val_id'];
			$new_inv_BP1s= $row['bp1_start'];
			$new_inv_BP1e= $row['bp1_end'];
			$new_inv_BP2s= $row['bp2_start'];
			$new_inv_BP2e= $row['bp2_end'];
		}
	}
	else{
		#echo "Old inv1 had not manual bp results";
		$query = "select i.name, b.id, b.inv_id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b where i.id=b.inv_id AND b.inv_id=$inv1 AND b.id= (SELECT id FROM breakpoints b2 WHERE inv_id=i.id AND b2.inv_id=$inv1 ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC LIMIT 1);";
		$result_current = mysql_query($query) or die("Can't select breakpoints coordinates, check the sql query\n: Query fail: " . mysql_error());
		while($row = mysql_fetch_array($result_current)){
			$val_id= $row['val_id'];
			$new_inv_BP1s= $row['bp1_start'];
			$new_inv_BP1e= $row['bp1_end'];
			$new_inv_BP2s= $row['bp2_start'];
			$new_inv_BP2e= $row['bp2_end'];
		}
	}
		mysql_free_result($result_current);
		mysql_close($con);
	
	#Update bp query with these new bps!!
	include('db_conexion.php');

	#$query_last_bp_id="SELECT MAX(id) FROM breakpoints where inv_id=$inv1;";
	$query_last_bp_id="SELECT MAX(id) FROM breakpoints;";
	
	$result = mysql_query($query_last_bp_id) or die("Query fail: " . mysql_error());
	while($row = mysql_fetch_array($result)){
		$last_bp_id=$row[0];
		echo $last_bp_id.'<br >';
	}
	mysql_free_result($result);
	mysql_close($con);
	
	include('db_conexion.php');
	
	$query_update_bp="UPDATE breakpoints SET bp1_start = $new_inv_BP1s, bp1_end = $new_inv_BP1e, bp2_start = $new_inv_BP2s, bp2_end = $new_inv_BP2e WHERE id=$last_bp_id;";
	$result = mysql_query($query_update_bp) or die("Query fail: " . mysql_error());
	#echo $query_update_bp.'<br >';
	mysql_free_result($result);
	mysql_close($con);
}

##############################
# OVERLAP breakpoints OPTION #
##############################

if($method="overlap_inv_bp" && $m == "2"){
	
	#Get breakpoints for each inversion
	$inv2_overlap_ids_array=$_POST["overlap_bps_array"]; #inv2_ids
	if(empty($inv2_overlap_ids_array)){echo "You must select at least one inversion to overlap".'<br >';die;}
	array_push($inv2_overlap_ids_array,$inv1); #push inv1 into inv2s ids array
	
	#Foreach inv
	include('db_conexion.php');
	$first_id="0";
	foreach($inv2_overlap_ids_array as $inv_id_overlap) {
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
	$result = mysql_query($query_update_bp) or die("Query fail: " . mysql_error());
	#echo $query_update_bp.'<br >';
	mysql_free_result($result);
	mysql_close($con);
	
}

##############################
# CUSTOM breakpoints OPTION #
##############################

if($method="custom_inv_bp" && $m == "3"){
	
	$custom_bp1s= $_POST["custom_bp1s"]; #inv_id
	$custom_bp1e= $_POST["custom_bp1e"];
	$custom_bp2s= $_POST["custom_bp2s"];
	$custom_bp2e= $_POST["custom_bp2e"];
	
	#BP1 START
	include('db_conexion.php');

	$query = "select i.name, b.id, b.inv_id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b where i.id=b.inv_id AND b.inv_id =$custom_bp1s ORDER BY FIELD (b.definition_method, 'manual curation', 'default informatic definition'), b.id DESC LIMIT 1;";
	
	$result_custom = mysql_query($query);
	if (mysql_num_rows($result_custom) > 0){
		while($row = mysql_fetch_array($result_custom)){
		$current_inv_name= $row['name'];
		$new_inv_BP1s= $row['bp1_start'];
		}
	}
	else{echo"no bp1s found";die;}

	mysql_free_result($result_custom);
	mysql_close($con);
	
	#BP1 END
	include('db_conexion.php');

	$query = "select i.name, b.id, b.inv_id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b where i.id=b.inv_id AND b.inv_id =$custom_bp1e ORDER BY FIELD (b.definition_method, 'manual curation', 'default informatic definition'), b.id DESC LIMIT 1;";
	
	$result_custom = mysql_query($query);
	if (mysql_num_rows($result_custom) > 0){
		while($row = mysql_fetch_array($result_custom)){
		$current_inv_name= $row['name'];
		$new_inv_BP1e= $row['bp1_end'];
		}
	}
	else{echo"no bp1e found";die;}

	mysql_free_result($result_custom);
	mysql_close($con);

	#BP2 START
	include('db_conexion.php');

	$query = "select i.name, b.id, b.inv_id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b where i.id=b.inv_id AND b.inv_id =$custom_bp2s ORDER BY FIELD (b.definition_method, 'manual curation', 'default informatic definition'), b.id DESC LIMIT 1;";
	
	$result_custom = mysql_query($query);
	if (mysql_num_rows($result_custom) > 0){
		while($row = mysql_fetch_array($result_custom)){
		$current_inv_name= $row['name'];
		$new_inv_BP2s= $row['bp2_start'];
		}
	}
	else{echo"no bp2s found";die;}

	mysql_free_result($result_custom);
	mysql_close($con);
	
	#BP2 END
	include('db_conexion.php');

	$query = "select i.name, b.id, b.inv_id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b where i.id=b.inv_id AND b.inv_id =$custom_bp2e ORDER BY FIELD (b.definition_method, 'manual curation', 'default informatic definition'), b.id DESC LIMIT 1;";
	
	$result_custom = mysql_query($query);
	if (mysql_num_rows($result_custom) > 0){
		while($row = mysql_fetch_array($result_custom)){
		$current_inv_name= $row['name'];
		$new_inv_BP2e= $row['bp2_end'];
		}
	}
	else{echo"no bp2e found";die;}

	mysql_free_result($result_custom);
	mysql_close($con);
	
	#echo $new_inv_BP1s.'<br >';
	#echo $new_inv_BP1e.'<br >';
	#echo $new_inv_BP2s.'<br >';
	#echo $new_inv_BP2e.'<br >';
	
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
	
	$query_update_bp="UPDATE breakpoints SET bp1_start = $new_inv_BP1s, bp1_end = $new_inv_BP1e, bp2_start = $new_inv_BP2s, bp2_end = $new_inv_BP2e WHERE id=$last_bp_id;";
	$result = mysql_query($query_update_bp) or die("Query fail: " . mysql_error());
	#echo $query_update_bp.'<br >';
	mysql_free_result($result);
	mysql_close($con);
	
}

#Logic to follow to update breakpoints of the new inversion
$m='0';
if(isset($_POST['current'])){echo "CURRENT breakpoints method selected".'<br >';$method="current_inv_bp";$m='1';} 
if($m == 0 && isset($_POST['overlap_bps_checkbox'])){echo "OVERLAPING breakpoints method selected".'<br >';$method="overlap_inv_bp";$m='2';}
if($m == 0 && isset($_POST['custom_inv_bp'])){echo "CUSTOM breakpoints method selected".'<br >';$method="custom_inv_bp";$m='3';}
if($m == 0){echo "Please, select an option in order to update the breakpoints of the new inversion!"; die;}

########################################################################
# UPDATE STUDY, DEFINITION METHOD AND DESCRIPTION FOR THE NEW INVERSION #
########################################################################
if($method == "current_inv_bp"){
	echo "current!";
	#if(!empty($_POST["studyname"])){$study_bp= $_POST["studyname"];} #study name
	#echo "Study name: $study_bp".'<br >';
	if(!empty($_POST["description_bp"])){$description_bp= $_POST["description_bp"];} #description
	echo "Description: $description_bp".'<br >';
	if(!empty($_POST["definition_method_bp"])){$definitionmethod_bp= $_POST["definition_method_bp"];} #definition
	echo "Definition: $definition_method_bp".'<br >';
	#if(!empty($_POST["mechanism_bp"])){$mechanism_bp= $_POST["mechanism_bp"];} #mechanism
	
	#Get last bp id
	include('db_conexion.php');
	$query_last_bp_id="SELECT MAX(id) FROM breakpoints;";
	$result = mysql_query($query_last_bp_id) or die("Query fail: " . mysql_error());
	while($row = mysql_fetch_array($result)){
		$last_bp_id=$row[0];
	}
	mysql_free_result($result);
	mysql_close($con);
		
	#update definition,description and mech
	include('db_conexion.php');
	#if ($current_has_manual_curation_bp == "1"){#IF IT HAS MANUAL CURATED BREAKPOINTS ANNOTATION
		
	$query="UPDATE breakpoints SET description='$description_bp', definition_method='$definitionmethod_bp' WHERE id=$last_bp_id;";
		$result = mysql_query($query) or die("Query fail when updating bp definition,description and mech: " . mysql_error());			
	mysql_free_result($result);
	mysql_close($con);

	#update study
	if(!empty($val_id)){
		include('db_conexion.php');
	
		$query= "UPDATE validation SET bp_id=$last_bp_id WHERE id=$val_id ORDER BY id DESC LIMIT 1;;";
		#$query= "UPDATE validation SET research_name='$study_bp' WHERE bp_id=$last_bp_id;";
		$result = mysql_query($query) or die("Query fail when updating bp study: " . mysql_error());

		#$query= "UPDATE breakpoints SET val_id='$val_id' WHERE id=$last_bp_id;";
		#$query= "UPDATE validation SET research_name='$study_bp' WHERE bp_id=$last_bp_id;";
		#$result = mysql_query($query) or die("Query fail when updating bp study: " . mysql_error());			
		mysql_free_result($result);
		mysql_close($con);
	}
}


		
	
	
	
#########################################################
# UPDATE EVOLUTIONARY HISTORY AND FUNCTIONAL EFFECTS??? #
#########################################################

##################################################
# set the automatic BP relation with gene and SD #
##################################################
#Get last bp id
include('db_conexion.php');

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
mysql_close($con);

include('db_conexion.php');
$query2="CALL get_SD_in_BP($last_bp_id);";
$result2= mysql_query($query2) or die("Wrong update gene and SD relation; Query fail: " . mysql_error());
$roww2 = mysql_fetch_array($result2);
mysql_free_result($result2);
mysql_close($con);

#######################################
# UPDATE STATUS FOR THE NEW INVERSION #
#######################################
include('db_conexion.php');
$query3 = "UPDATE inversions SET status = '$new_status' WHERE id=$new_inv_id;";
	echo $query3.'<br >';
	$result3 = mysql_query($query3) or die("Query fail: " . mysql_error());
	$row3 = mysql_fetch_array($result3);
	mysql_free_result($result3);
	mysql_close($con);

#######################################
# UPDATE MECH FOR THE NEW INVERSION #
#######################################
include('db_conexion.php');
$query3 = "UPDATE inversions SET origin = '$new_origin' WHERE id=$new_inv_id;";
echo "Update mechanism for the new inv: ".$query3;
$result3 = mysql_query($query3) or die("Query fail: " . mysql_error());
$row3 = mysql_fetch_array($result3);
mysql_free_result($result3);
mysql_close($con);
##############################################
# Print redirection to new inversion message #
##############################################
echo "<br /><input type='submit' value='Go to the new inversion' name='gsubmit'  onclick=\"location.href='../report.php?q=".$new_inv_id."'\" />";	

#######################
# BREAKSEQ annotation #
#######################


?>	
