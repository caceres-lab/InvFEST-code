<?php include('security_layer.php'); ?>

<?php

$inv_id=$_POST["inv_id"];
$effect_type=$_POST["effect_type"];

$gene=$_POST["gene_func"];
$genomic_effect=$_POST["genomic_eff_func"];
$genomic_source=$_POST["source_genomic_func"];
$consequences=$_POST["conseq_func"];
$comment=$_POST["genomic_conseq_comment"];
$mechanism=$_POST["mechanism"];

$phenotypic_effect=$_POST["phenotypic_eff_func"];
$phenotypic_source=$_POST["source_phenotypic_func"];


//Comprobaciones
if ($effect_type == ""){
	echo "Error: Type of effect is not selected";
}
else {
	/*eff_genomic				/*eff_phenotypic
	gene_func -> gene			phenotypic_eff_func -> effect
	genomic_eff_func -> effect		source_phenotypic_func -> study
	source_genomic_func -> study
	conseq_func -> consequences		*/

	if ($effect_type == "eff_genomic"){
		$effect=$genomic_effect;
		$source=$genomic_source;
	}
	elseif ($effect_type == "eff_phenotypic"){
		$effect=$phenotypic_effect;
		$source=$phenotypic_source;
	}

	if ($effect_type == "eff_genomic" && $gene==""){echo "Error: Gene is not defined";}
	elseif ($effect_type == "eff_genomic" && $consequences==""){echo "Error: Consequences are not defined";}
	elseif ($effect==""){echo "Error: Effect is not defined";}
	elseif ($source==""){echo "Error: Study is not defined";}
		
	else {
		//todo es correcto, por lo tantos conectamos a la bbdd:
		include_once('db_conexion.php');

		//mysql_query('CALL miProcedure()');
		//mysql_query('SELECT miFunction()');

		//llamamos a la funcion add_phenotipic_effect o update_genomic_effect en funcion de $effect_type:
		/*`update_genomic_effect`
			(IN key_val INT, ???
			IN inv_id_val INT, 
			IN effect_val VARCHAR(255), 
			IN funt_cons_val VARCHAR(255),  
			IN source_val VARCHAR(255), 
			IN user_id_val INT)
		*/
		/*`add_phenotipic_effect`
			(IN key_val INT, 			????
			IN inv_id_val INT, 
			IN effect_val VARCHAR(255), 
			IN source_val VARCHAR(255), 
			IN user_id_val INT)
		*/
		if ($effect_type == "eff_genomic"){
			mysql_query("CALL update_genomic_effect('$gene','$inv_id','$effect', '$consequences', '$source','".$_SESSION["userID"]."')");
			mysql_query("UPDATE genomic_effect SET comment='$comment' WHERE inv_id = $inv_id;");
			$sql_gene="select refseq, chr, txStart, txEnd from HsRefSeqGenes where idHsRefSeqGenes='$gene';";
			$result_gene=mysql_query($sql_gene);
			$r= mysql_fetch_array($result_gene);

			echo "<tr><td class='title' width='20%'>Gene position</td><td>".$r['chr'].':'.$r['txStart'].'-'.$r['txEnd']."</td></tr>
			<tr><td class='title'>Effect</td><td>".$effect."</td></tr>
			<tr><td class='title'>Mechanism</td><td id='".$gene."_mechanism'>".$mechanism."</td></tr>
			<tr><td class='title'>Study</td><td>".$source."</td></tr>
			<tr><td class='title'>Functional consequences</td><td>".$consequences."</td></tr>
			<tr><td class='title'>Comment</td><td>".$comment."</td></tr>
			";
		}
		elseif ($effect_type == "eff_phenotypic"){
//$f="CALL add_phenotipic_effect('$inv_id','$effect', '$source','".$_SESSION["userID"]."')";
			mysql_query("CALL add_phenotipic_effect('$inv_id','$effect', '$source','".$_SESSION["userID"]."')");
			echo "<tr><td>$effect</td><td>$source</td></tr>";
//echo $f;
		}
		mysql_close($con);

	}
}
?>


