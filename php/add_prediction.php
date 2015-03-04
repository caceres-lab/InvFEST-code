<?php include('security_layer.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php
$chr=$_POST["pred_chr"]; //*
$bp1s=$_POST["pred_bp1s"]; //numerico *
$bp1e=$_POST["pred_bp1e"]; //numerico *
if (isset($_POST['between_bp1'])) {$betweenbp1="TRUE";}else{$betweenbp1="FALSE";}
$bp2s=$_POST["pred_bp2s"]; //numerico *
$bp2e=$_POST["pred_bp2e"]; //numerico *
if (isset($_POST['between_bp2'])) {$betweenbp2="TRUE";}else{$betweenbp2="FALSE";}
$study_name=$_POST["pred_study_name"]; //*

//echo '<head></head><body>PREDICTIONS<br>';
echo "<br>";
echo "<div class=\"section-title2\">Inversions:</div>";
echo '<div id="results_table">';


//comprobaciones
$order_bp='ko';
if ($bp2e > $bp2s && $bp2s > $bp1e && $bp1e > $bp1s) {$order_bp='ok';}

if ($chr == "" ) { die ("Chromosome is not defined");}
elseif (($bp1s != "" || $bp1e!="" || $bp2s!="" || $bp2e!="") 
	&& ($bp1s=="" || $bp1e=="" || $bp2s=="" || $bp2e=="")) {die("All breakpoint fields must be defined");
}
elseif ($bp1s != "" && !preg_match('/^[0-9]+$/', $bp1s) && !preg_match('/[1-9]/', $bp1s)) { die("Breakpoint 1 start is not a number");} 
elseif ($bp1e != "" && !preg_match('/^[0-9]*$/', $bp1e) && !preg_match('/[1-9]/', $bp1e)) { die("Breakpoint 1 end is not a number");} 
elseif ($bp2s != "" && !preg_match('/^[0-9]*$/', $bp2s) && !preg_match('/[1-9]/', $bp2s)) { die("Breakpoint 2 start is not a number");} 
elseif ($bp2e != "" && !preg_match('/^[0-9]*$/', $bp2e) && !preg_match('/[1-9]/', $bp2e)) { die("Breakpoint 2 end is not a number");}
elseif ($order_bp != 'ok') {die ("Positions of the breakpoints are not correct");}
elseif ($study_name == "" ) {die ("Study name is not defined");}
else {
	//todo es correcto, por lo tantos conectamos a la bbdd:
	include('db_conexion.php');

	/* PROCEDURE `setup_pred_to_inv_merge`
	IN `newInv_name_val` varchar(255),
	IN `newInv_chr_val` varchar(255),
	IN `newInv_bp1s_val` int,
	IN `newInv_bp1e_val` int,
	IN `newInv_bp2s_val` int,
	IN `newInv_bp2e_val` int,
	IN `newInv_studyName_val` varchar(255)
	user_id_val INT)
	*/
//$f="CALL setup_pred_to_inv_merge('$chr', '$bp1s', '$bp1e', '$bp2s', '$bp2e','$study_name', '".$_SESSION["userID"]."')";
	mysql_query("CALL setup_pred_to_inv_merge('$chr', '$bp1s', '$bp1e', '$bp2s', '$bp2e','$study_name', '".$_SESSION["userID"]."')");
//echo $f;

	$sql_get_inv = "SELECT i.id, i.name, i.chr, i.range_start, i.range_end, i.size, i.status
		 FROM inversions AS i JOIN predictions AS p ON i.id = p.inv_id where  p.research_name = '$study_name' and p.research_id = 
		(SELECT max(research_id) FROM predictions WHERE research_name = '$study_name');";
	$result_get_inv = mysql_query($sql_get_inv);
	sleep(1);

	echo '<table id="sort_table">';
	//echo "inversion: ".$inv."<br>";
	echo '<thead>
		  <tr>
			<th>Name <img src=\'css/img/sort.gif\'></th>
			<th>Chromosome <img src=\'css/img/sort.gif\'></th>
			<th>Range start <img src=\'css/img/sort.gif\'></th>
			<th>Range end <img src=\'css/img/sort.gif\'></th>
			<th>Inversion size <img src=\'css/img/sort.gif\'></th>
			<th>Status <img src=\'css/img/sort.gif\'></th>
		  </tr>
		  </thead>
		  <tbody>';

		while($row = mysql_fetch_array($result_get_inv)){
			echo "<tr>";
			echo "<td>".$row['name']."</td>";
			echo "<td>".$row['chr']."</td>";
			echo "<td>".$row['range_start']."</td>";
			echo "<td>".$row['range_end']."</td>";
			echo "<td>".$row['size']."</td>";
			echo "<td>".$row['status']."</td>";
			$inversion_name=$row['name'];
			echo "</tr>";
			echo "<br /><input type='submit' value='Go to the new inversion' name='gsubmit'  onclick=\"location.href='../report.php?q=".$row['id']."'\" />";
			$inv_id = $row['id'];
		}
			#INSERT THE "BETWEEN BREAKPOINTS" INFORMATION TO THE DB		
			$sql_between = "UPDATE breakpoints SET bp1_between='$betweenbp1', bp2_between = '$betweenbp2' WHERE inv_id = $inv_id;";
			$result_between = mysql_query($sql_between);
			if (!$result_between) {
			    die('Error when inserting BETWEEN BREAKPOINTS from the checkbox input to the db: ' . mysql_error());
			}
			
			sleep(1);

	echo "</tbody></table>";
//Breakseq gff input file generation
//----------------------------------------------------------------------------
exec("kill $(ps aux | grep 'breakseq-1.3' | awk '{print $2}') > /dev/null 2>&1");
$gff_file = fopen("/home/shareddata/Bioinformatics/BPSeq/breakseq_annotated_gff/input.gff", "w") or die("Unable to create gff file!");
//Select inversions
$sql_bp="SELECT i.name, b.id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, i.status, b.GC FROM inversions i, breakpoints b  WHERE i.id=b.inv_id AND b.chr NOT IN ('chrM') AND b.GC is null;";
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

	mysql_close($con);
}
print "<br ><br >BreakSeq is now performing the breakpoints annotation, results will be automatically updated on the inversion report page in a few minutes.".'<br >';
echo '</div>';
//echo '</html>'
?>
