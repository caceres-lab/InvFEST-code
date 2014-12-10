<?php include('security_layer.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php
$inv1=$_POST["inv1"];
$inv2=$_POST["inv2"];
<<<<<<< HEAD
$separado_por_comas = implode(",", $inv2);
=======
>>>>>>> acd4aaae4e2c8ead252a6100be78eb1591753f57
$new_status=$_POST["status"];

//comprobaciones

	include('db_conexion.php');
	//llamamos a la funcion add_validation:
	$query = "SELECT merge_inv('$inv1,$separado_por_comas','".$_SESSION["userID"]."') AS new_inv_id;";
	print $query.'<br>';
	$result = mysql_query($query) or die("Query fail: " . mysql_error());
	if($result){print "Merge done succesfully".'<br >';}
	$row = mysql_fetch_array($result);
	if ($row){print $row[0].'<br >';}
	mysql_free_result($result);
	mysql_close($con);

	//if ($validation_id) HAY Q FORZAR A QUE SALGA MAL PARA SABER Q DEVUELVE!!
	print "<br><br>BreakSeq is now performing the breakpoints annotation, results will be automatically updated on the inversion report page in a few minutes.".'<br >';
	// CON EL SIGUIENTE BOTON SE REFRESCA LA PAGINA PRINCIPAL Y POR LO TANTO TAMBIEN SE CIERRA EL IFRAME-->
	echo "<br /><input type='submit' value='Go to the new inversion' name='gsubmit'  onclick=\"location.href='../report.php?q=".$row[0]."'\" />";
//header("location: search.php"
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
//---------------------------------------------------------------------------
$query3 = "UPDATE inversions SET status = '$new_status' WHERE id=$row[0];";
	$result3 = mysql_query($query3) or die("Query fail: " . mysql_error());
	$row3 = mysql_fetch_array($result3);
?>
