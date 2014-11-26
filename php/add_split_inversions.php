<?php include('security_layer.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php
$inv_id=$_POST["inv_id"];
$chr=$_POST["chr"];
$research_name=$_POST["research_name"];
$pinv1_arr=$_POST["pinv1"];
$pinv2_arr=$_POST["pinv2"];
$vinv1_arr=$_POST["vinv1"];
$vinv2_arr=$_POST["vinv2"];

$pinv1 = implode(",", $pinv1_arr);
$pinv2 = implode(",", $pinv2_arr);
$vinv1 = implode(",", $vinv1_arr);
$vinv2 = implode(",", $vinv2_arr);
$user_id = $_SESSION["userID"];
if($pinv1 == null){$pinv1 = "NA";}
if($pinv2 == null){$pinv2 = "NA";}
if($vinv1 == null){$vinv1 = "NA";}
if($vinv2 == null){$vinv2 = "NA";}

include_once('db_conexion.php');

//Split
$query="CALL split_inv('$inv_id','$pinv1','$pinv2','$vinv1','$vinv2','NA','NA','$user_id');";
print $query.'<br >';
$result = mysql_query($query) or die("Query fail: " . mysql_error());
if($result){print "Split done succesfully".'<br >';}

$query1="select id, name from inversions ORDER BY `id` DESC LIMIT 2;";
$result1 = mysql_query($query1) or die("Query fail: " . mysql_error());
while($row1 = mysql_fetch_array($result1)){
echo "<br /><input type='submit' value=\"Go to the new inversion " .$row1['name']."\" name='gsubmit'  onclick=\"window.open('../report.php?q=".$row1['id']."')\" />";
}
$row = mysql_fetch_array($result);
if ($row){print $row.'<br >';}
mysql_free_result($result);
mysql_close($con);

/*$con=mysqli_connect("localhost","inoguera","inoguera","inoguera2");
// Check connection
if (mysqli_connect_errno()){echo "Failed to connect to MySQL: " . mysqli_connect_error();}
// Perform queries
if(!mysqli_query($con,$query)){echo "Wrong query: $query";}
else{echo "Split done succesfully: $query".'<br >';}
mysqli_close($con);*/

//Breakseq gff input file generation
//----------------------------------------------------------------------------
include('db_conexion.php');
$gff_file = fopen("/home/shareddata/Bioinformatics/BPSeq/breakseq_annotated_gff/input.gff", "w") or die("Unable to create gff file!");
//Select inversions
$query2="SELECT i.name, b.id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, i.status, b.GC FROM inversions i, breakpoints b  WHERE i.id=b.inv_id AND b.GC is null AND b.chr NOT IN ('chrM');";
print "$sql_bp".'<br/>';
$result_bp = mysql_query($query2) or die("Query fail: " . mysql_error());
while($bprow = mysql_fetch_array($result_bp))
{
	$midpoint_BP1=round(($bprow['bp1_end']-$bprow['bp1_start'])/2+$bprow['bp1_start']);
    	$midpoint_BP2=round(($bprow['bp2_end']-$bprow['bp2_end'])/2+$bprow['bp2_start']);
    	$chr=$bprow['chr'];
	$name=$bprow['name'];
	$id_bp= $bprow['id'];
	//$gene_id= $bprow['gene_id'];
	$inverion_gff_line= "$chr\t$name\tInversion\t$midpoint_BP1\t$midpoint_BP2\t.\t.\t.\t$id_bp\n";
	    
    	fwrite($gff_file, $inverion_gff_line);
}

fclose($gff_file);

//BreakSeq execution
//---------------------------------------------------------------------------
exec("nohup ./run_breakseq.sh > /dev/null 2>&1 &");
print "<br ><br >BreakSeq is now performing the breakpoints annotation, results will be automatically updated on the inversion report page in a few minutes.".'<br >';
?>
