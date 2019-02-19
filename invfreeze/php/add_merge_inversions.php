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
# Inversions (array including NAME)
	$inv_to_merge_ids_array=$_POST["id_invs_to_merge"];
	if(sizeof($inv_to_merge_ids_array) < 2){echo "You must select at least 2 inversions to merge";die;}
 
# Name
	if (!isset($_POST['new_name_inv'])){
		$inv_to_merge_name_array = 'NULL';
	}else{ 
		$inv_to_merge_name_array=$_POST['new_name_inv']; #name id
	}

#Bps (unique ids)
	$do_manual_bps = 'FALSE';

	if (!isset($_POST["id_bp1s_invs_to_merge"])){
		$inv_bp1s_id = 'NULL';
	}else{ 
		$inv_bp1s_id=$_POST["id_bp1s_invs_to_merge"]; 
		$do_manual_bps = 'TRUE'; #bp1s id 
	}
	if (!isset($_POST["id_bp1e_invs_to_merge"])){
		$inv_bp1e_id = 'NULL';
	}else{ 
		$inv_bp1e_id=$_POST["id_bp1e_invs_to_merge"];
		$do_manual_bps = 'TRUE';  #bp1e id 
	}
	if (!isset($_POST["id_bp2s_invs_to_merge"])){
		$inv_bp2s_id = 'NULL';
	}else{ 
		$inv_bp2s_id=$_POST["id_bp2s_invs_to_merge"]; 
		$do_manual_bps = 'TRUE'; #bp2s id 
	}
	if (!isset($_POST["id_bp2e_invs_to_merge"])){
		$inv_bp2e_id = 'NULL';
	}else{ 
		$inv_bp2e_id=$_POST["id_bp2e_invs_to_merge"];
		$do_manual_bps = 'TRUE';  #bp2e id 
	}
	

	if (  ($inv_bp1s_id!= 'NULL' || $inv_bp1e_id != 'NULL' ||$inv_bp2s_id != 'NULL' ||$inv_bp2e_id != 'NULL'  ) 
			&&  ($inv_bp1s_id== 'NULL' || $inv_bp1e_id  == 'NULL' ||$inv_bp2s_id == 'NULL' ||$inv_bp2e_id == 'NULL' )){
		echo "To manually curate breakpoints you must select all 4 coordinates";die;
	}

// #Mech
	if (!isset($_POST['new_origin_inv'])){echo "You must specify a mechanism of origin for the new inversion";die;}
	$inv_to_merge_mech_array=$_POST["new_origin_inv"]; #mech id
	
// #Evo
	$inv_evo_id=$_POST["id_evo_invs_to_merge"]; #evolutinary id
	if(empty($inv_evo_id)){echo "You must select the 'Evolutionary field' for the new inversion";die;}

// #Functional
	$inv_fun_id=$_POST["id_fun_invs_to_merge"]; #functional id

// #Comments
	$inv_com_id_array=$_POST["id_com_invs_to_merge"]; #comments id

// #Status
	$new_status=$_POST["status"]; #new_inv_status


echo "All parameters were successfully imported".'<br >';



// ################################
// # CLEAN VALUES FROM report.php #
// ################################
include('db_conexion.php');

# Inversions to merge
	if ($inv_to_merge_name_array != 'NULL'){
		$inv_to_merge_ids_array = array_diff( $inv_to_merge_ids_array, [ $inv_to_merge_name_array]);
	}
	$inv_ids_list=implode(",", $inv_to_merge_ids_array);

# Name is ok already
# Bps
	if ($do_manual_bps == 'TRUE'){
		$query = "SELECT bp1_start FROM breakpoints WHERE id = (SELECT max(id)FROM breakpoints WHERE inv_id = $inv_bp1s_id);";
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)) {
			$inv_bp1s_value=$row[0];
		}
		$query = "SELECT bp1_end FROM breakpoints WHERE id = (SELECT max(id)FROM breakpoints WHERE inv_id = $inv_bp1e_id);";
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)) {
			$inv_bp1e_value=$row[0];
		}
		$query = "SELECT bp2_start FROM breakpoints WHERE id = (SELECT max(id)FROM breakpoints WHERE inv_id = $inv_bp2s_id);";
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)) {
			$inv_bp2s_value=$row[0];
		}
		$query = "SELECT bp2_end FROM breakpoints WHERE id = (SELECT max(id)FROM breakpoints WHERE inv_id = $inv_bp2e_id);";
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)) {
			$inv_bp2e_value=$row[0];
		}
	} else{
		$inv_bp1s_value='NULL';
		$inv_bp1e_value='NULL';
		$inv_bp2s_value='NULL';
		$inv_bp2e_value='NULL';
	}
#Mech
	$inv_to_merge_mech_array = array_diff( $inv_to_merge_mech_array, [ $inv_to_merge_name_array]);
	if(sizeof($inv_to_merge_mech_array) < 1){
		$inv_to_merge_mech_array = "NULL";
	}else{$invs_mech_list=implode(",", $inv_to_merge_mech_array); }#String with all invs ids
#Comments
	$inv_com_id_array = array_diff( $inv_com_id_array, [ $inv_to_merge_name_array]);
	$invs_comm_list=implode(",", $inv_com_id_array); #String with all invs ids
	
#Evo
	if ($inv_evo_id==$inv_to_merge_name_array){$inv_evo_id = 'NULL';}

#Functional
	if ($inv_fun_id==$inv_to_merge_name_array){$inv_fun_id = 'NULL';}



// ##############
// # MERGE CALL #
// ##############

$query = "CALL merge_inversions('$inv_ids_list',".$inv_to_merge_name_array.",'$invs_mech_list', ". $inv_bp1s_value.",".$inv_bp1e_value.",".$inv_bp2s_value.",".$inv_bp2e_value.",".$inv_evo_id.",".$inv_fun_id.",'$invs_comm_list', '$new_status', '".$_SESSION["userID"]."', @newinv);";
print "Your query: $query".'<br>';
$result = mysql_query($query) or die("Query fails when performing merge procedure: " . mysql_error());
while($row = mysql_fetch_array($result)) {
	$new_inv_id=$row[0];
}
echo "Merge done succesfully in ". $new_inv_id.'<br >';
// mysql_free_result($result);
mysql_close($con);

##############################################
# Print redirection to new inversion message #
##############################################
echo "<br /><input type='submit' value='Go to the new inversion' name='gsubmit'  onclick=\"location.href='../report.php?q=".$new_inv_id."'\" />";	


#######################
# BREAKSEQ annotation #
#######################
// Breakseq gff input file generation
// ----------------------------------------------------------------------------
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

// BreakSeq execution
// ---------------------------------------------------------------------------
exec("nohup ./run_breakseq.sh > /dev/null 2>&1 &");
// ---------------------------------------------------------------------------
?>