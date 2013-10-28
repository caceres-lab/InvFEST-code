<?php include('security_layer.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<script type="text/javascript" src="../js/jquery.js"></script>
</head>
<?php
$inv_id=$_POST["inv_id"]; //*
$id_gene=$_POST["id_gene"]; //*
	list($idHsRefSeqGenes, $symbol) = split(';', $id_gene);

$divToChange='gene_func';

//comprobaciones
if ($idHsRefSeqGenes == "" || $idHsRefSeqGenes == null) {echo "Please select one gene!<br><INPUT Type='button' VALUE='Back' onClick='history.go(-1);return true;'>";}

else {
	//todo es correcto, por lo tantos conectamos a la bbdd:
	include_once('db_conexion.php');

	//llamamos a la funcion add_validation:
	mysql_query("INSERT INTO genomic_effect (inv_id, gene_id, gene_relation) VALUES ('$inv_id', '$idHsRefSeqGenes', 'intergenic, NA');");

	mysql_close($con);

	echo "Gene added succesfully<br />".$message;
	
	?>
	<br>
	<input type='button' onclick='history.go(0)' value ='Close'>
	<script>
		function appendOption() {
			$(parent.document.getElementById("<?php echo $divToChange;?>")).append("<option value='<?php echo $idHsRefSeqGenes;?>'><?php echo $symbol;?></option>");		
			//$(parent.document.getElementById("functional_effectAjax")).append("<?php echo $InfoNewGene;?>");
			parent.window.hs.close();
		}
	</script>
	<?php
	// CON EL SIGUIENTE BOTON SE REFRESCA LA PAGINA PRINCIPAL Y POR LO TANTO TAMBIEN SE CIERRA EL IFRAME-->
	//echo "<br /><input type='submit' value='Close' name='gsubmit'  onclick='parent.location.reload();' />";

}

?>

</html>


