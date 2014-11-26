<?php include('security_layer.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php
$inv_id=$_POST["inv_id"];
$chr=$_POST["chr"];
$research_name=$_POST["research_name"];
$method=$_POST["method"];
$status=$_POST["status"];
$checked=$_POST["checked"];

$validation=$_POST["validation"]; //puede ser: experimental o bioinformatics
// Si es experimental, recibimos esta informacion:
$experimental_conditions=$_POST["experimental_conditions"];
$primers=$_POST["primers"];
$commentE=$_POST["commentE"];

//Si es bioinformatics, recibimos esta otra informacion
$fosmids=$_POST["searchFosmids"]; 
$commentB=$_POST["commentB"];
$results=$_POST["fosmids_results"]; //obligatorio cuando fosmids esta relleno

//FALTA COMPROBAR ERRORES AL ESCOGER ENTRE LAS DOS OPCIONES
$bp1s=$_POST["bp1s"]; //numerico
$bp1e=$_POST["bp1e"]; //numerico
$bp2s=$_POST["bp2s"]; //numerico
$bp2e=$_POST["bp2e"]; //numerico
$description=$_POST["description"];

//Frequency without genotypes
$fng_population=$_POST["fng_population"]; 
$fng_individuals=$_POST["fng_individuals"]; 
$fng_invalleles=$_POST["fng_invalleles"]; 
$fng_stdfreq=$_POST["fng_stdfreq"]; 
$fng_invfreq=$_POST["fng_invfreq"]; 
///

$message=""; $warning_ind='';


//comprobaciones
if ($checked != 'yes') {
	$checked='not';
}
$order_bp='ko';
if ($bp2e > $bp2s && $bp2s > $bp1e && $bp1e > $bp1s) {$order_bp='ok';}
if ($bp1s == "" && $bp1e == "" && $bp2s == "" && $bp2e == "" ) {$order_bp='ok';}

if ($research_name == "" || $research_name == null ) { echo "Research Name is not defined<br>";}
elseif ($method == "" || $method == null) { echo "Method is not defined<br>";}
elseif ($status == "" || $status == null) { echo "Status is not defined<br>";}
/*elseif (!preg_match('/PCR/',$method) && !preg_match('/FISH/',$method) && !preg_match('/MLPA/',$method) && $method != "" && $fosmids == "")
	{echo "Fosmids information from Validation details is not defined<br>";}
elseif (!preg_match('/PCR/',$method) && !preg_match('/FISH/',$method) && !preg_match('/MLPA/',$method) && $method != "" && $results == "")
	{echo "Results from Validation details is not defined<br>";}
*/
//elseif ($fosmids != "" && $results == "") { echo "Results from Bioinformatic validation is not defined<br>";}
elseif (($bp1s != "" || $bp1e!="" || $bp2s!="" || $bp2e!="") 
	&& ($bp1s=="" || $bp1e=="" || $bp2s=="" || $bp2e=="")) {
	echo "All fields from Add Breakpoints must be defined<br>";
}
elseif ($bp1s != "" && !preg_match('/^[0-9]+$/', $bp1s) && !preg_match('/[1-9]/', $bp1s)) { echo"Breakpoint 1 start is not a number<br>";} 
elseif ($bp1e != "" && !preg_match('/^[0-9]*$/', $bp1e) && !preg_match('/[1-9]/', $bp1e)) { echo"Breakpoint 1 end is not a number<br>";} 
elseif ($bp2s != "" && !preg_match('/^[0-9]*$/', $bp2s) && !preg_match('/[1-9]/', $bp2s)) { echo"Breakpoint 2 start is not a number<br>";} 
elseif ($bp2e != "" && !preg_match('/^[0-9]*$/', $bp2e) && !preg_match('/[1-9]/', $bp2e)) { echo"Breakpoint 2 end is not a number<br>";} 
elseif ($order_bp != 'ok') {echo "Positions of the breakpoints are not correct<br>";}
elseif ($_FILES["individuals"]["name"]!="" && $_FILES["individuals"]["type"]!="text/plain") {
	echo "File type not valid <br />";
}
elseif ($_FILES["individuals"]["name"]!="" && $_FILES["individuals"]["error"]>0) {
	echo "Error: ".$_FILES["individuals"]["error"]."<br />";
} 
else {
	//todo es correcto, por lo tantos conectamos a la bbdd:
	include_once('db_conexion.php');

	/* function add_validation
	IN `inv_id_val` VARCHAR(255), 
	IN `research_name_val` VARCHAR(255), 
	IN `validation_val` VARCHAR(255), 
	IN `valiadtion_method_val` VARCHAR(255), 
	IN `PCRconditions_val` VARCHAR(255), 
	IN `primer_val` VARCHAR(255),
	IN `validation_comment_val` VARCHAR(255) ,
	IN `checked_val` VARCHAR(255))
	*/

	//mysql_query('CALL miProcedure()');
	//mysql_query('SELECT miFunction()');

	//llamamos a la funcion add_validation:
$a="SELECT add_validation('$inv_id', '$research_name', '$status', '$method', '$experimental_conditions', '$primers','$commentE','$checked', '".$_SESSION["userID"]."') AS id;";
	$sql_validation_id = mysql_query("SELECT add_validation('$inv_id', '$research_name', '$status', '$method', '$experimental_conditions', '$primers','$commentE','$checked', '".$_SESSION["userID"]."') AS id;");
	$r= mysql_fetch_array($sql_validation_id);
	$validation_id = $r['id'];

	if ($validation_id != '') {
	//echo "validacion: ".$r['id']."<br />"; 
	//echo "SELECT add_validation('$inv_id', '$research_name', '$status', '$method', '$experimental_conditions', 			'$primers','$commentE','$checked') AS id<br>";
		if ($fosmids != '' || $fosmids != NULL) {
			/*add_fosmid_validation`(
			IN validation_id_val INT, 
			IN inv_id_val INT, 
			IN fosmid_id_val INT, 
			IN research_val  VARCHAR(255), 
			IN result_val  VARCHAR(255), 
			IN comment_val  VARCHAR(255))
			*/

			//Despues de la funcion add_validation, si hay fosmidos, llamamos al procedure add_fosmid_validation:
			mysql_query("CALL add_fosmid_validation('$validation_id','$inv_id','$fosmids','$research_name','$results','$commentB', '".$_SESSION["userID"]."');");
	//echo "CALL add_fosmid_validation('$validation_id','$inv_id','$fosmids','$research_name','$results','$commentB');";
		}

		if ($_FILES["individuals"]["name"]!="") {
			//CUANDO PASAN INFORMACION DE INDIVIDUALS, ABRIMOS EL FICHERO, LO LEEMOS LINEA A LINEA
	// OJO!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// SI HAY ERRORES, SE CREARA LA VALIDACION
	// HABRIA Q CHEQUEAR PREVIAMENTE!!!!!!!!!!!!!!!!!111

			$file = fopen($_FILES["individuals"]["tmp_name"], "r") or exit("Unable to open file!");
			while(!feof($file)) {
				$line=fgets($file);
		    if( !strncmp($line, '#', 1) )continue;
				//eliminamos los saltos de linea del final
				$line=trim($line, "\n");
				$line=trim($line, "\r");
				//PARA CADA LINEA LLAMAMOS ADD_INDIVIDUAL_GENOTIPY
				/* add_individual_genotipy`(
				IN `code_val` VARCHAR(255), -> del fichero
				IN `inv_name_val` VARCHAR(255), -> hidden --------------------->>>>>>>>>>>> lo cambiamos por inv_id
				IN `genotype_val` VARCHAR(255), -> fichero
				IN `valid_id_val` INT(11))-> devuelto por add_validation
				--- Nuevo fromato del fichero
		    IN code_val VARCHAR(255),              -> del fichero
		    IN gender_val VARCHAR(255),            -> del fichero
		    IN population_val VARCHAR(255),        -> del fichero
		    IN region_val VARCHAR(255),            -> del fichero
		    IN family_val VARCHAR(255),            -> del fichero
		    IN relationship_val VARCHAR(255),      -> del fichero
		    IN `genotype_val` VARCHAR(255),        -> del fichero
		    IN allele_comment_val VARCHAR(255),    -> del fichero
		    IN allele_level_val INT,               -> del fichero
		    IN panel_val VARCHAR(255),             -> del fichero
		    IN other_code_val VARCHAR(255),        -> del fichero
		    IN `inv_id_val` INT(11),               -> hidden
		    IN `valid_id_val` INT(11),             -> hidden
		    IN user_id_val INT                     -> hidden
		    */
				$code=""; $geder=""; $population=""; $region=""; $family=""; $panel=""; $relationship=""; $allele_level=""; $genotype=""; 
				$allele_comment=""; $other_code="";
				if ($line != "") {
					//PARSEAMOS EL FICHERO
					//el fichero tiene 'codigo individuo' - 'genotipo' -> puede estar separado por ; \t o espacio
					$separator="";
					$semicolon=strpos($line,';');
					if ($semicolon!==false) {//; es el separador
						$separator=';';
					}
					else {
						$tab=strpos($line,"\t");
						if ($tab!==false) {//\t es el separador
							$separator="\t";
						}
						else  {
							$space=strpos($line," ");
							if ($space!==false) { // el separador es el espacio
								$separator=' ';
							}
						}
					}
					if ($separator!="") {
						$info=explode($separator,$line);
						$code=$info[0];
		            $geder = ($info[1] == "" ? NULL : $info[1]);
		            $population= ($info[2] == "" ? NULL : $info[2]);
		            $region= ($info[3] == "" ? NULL : $info[3]);
		            $family= ($info[4] == "" ? NULL : $info[4]); 
		            $relationship= ($info[5] == "" ? NULL : $info[5]);
						$genotype= $info[6];
		            $allele_comment= ($info[6] == "" ? NULL : $info[6]);
		            $allele_level= ($info[7] == "" ? NULL : $info[7]);
		            $panel= ($info[8] == "" ? NULL : $info[8]);
		            $other_code= ($info[9] == "" ? NULL : $info[9]);

						if ($code !="" && ($genotype=="INV/INV" || $genotype=="STD/INV" || $genotype=="STD/STD" || $genotype=="STD" || $genotype=="INV" || $genotype=="NA" || $genotype=="ND")) {

						mysql_query("CALL add_individual_genotype('$code' , '$geder', '$population', '$region', '$family', '$relationship', '$genotype', '$allele_comment', '$allele_level', '$panel', '$other_code', '$inv_id', '$validation_id', '".$_SESSION["userID"]."');");
	//$a="CALL add_individual_genotype('$code' , '$geder', '$population', '$region', '$family', '$relationship', '$genotype', '$allele_comment', '$allele_level', '$panel', '$other_code', '$inv_id', '$validation_id', '".$_SESSION["userID"]."');";
	//echo "CALL add_individual_genotipy('$code','$inv_id','$genotype','$validation_id');";
						}
						else {
							$warning_ind="yes";
						}
					}
					else {
						$warning_ind='yes';
					}
				}
			}
			fclose($file);
		}

		if ($bp1s != "" || $bp1e!="" || $bp2s!="" || $bp2e!="") {
			$changed=mysql_query("SELECT add_BP('$validation_id','$inv_id','$chr','$bp1s','$bp1e','$bp2s','$bp2e','$description', '".$_SESSION["userID"]."') AS chang");
			$r= mysql_fetch_array($changed);
	
			if ($r['chang']=='YES') { 
				$message="Some predictions status have changed<br />";
			}
			//BREAKSEQ ANNOTATION
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
		}
		
		// Frequencies without genotypes
		if (($fng_population != 'null') && ($fng_invfreq != '')) {
		
			if ($fng_individuals == '') {
				$fng_individuals='NULL';
			}
			if ($fng_invalleles == '') {
				$fng_invalleles='NULL';
			}
		
			$add_freq_nogenotypes = mysql_query("INSERT INTO population_distribution (inv_id, population_name, validation_id, validation_research_name, individuals, inverted_alleles, inv_frequency) VALUES ($inv_id, '$fng_population', $validation_id, '$research_name', $fng_individuals, $fng_invalleles, $fng_invfreq);");
	
		}

		mysql_close($con);

		if ($warning_ind != '') {
			$message.="Some individuals have not been correctly introduced <br />";
		}
		//if ($validation_id) HAY Q FORZAR A QUE SALGA MAL PARA SABER Q DEVUELVE!!
	//	echo "Validation added succesfully<br />".$message;

		// CON EL SIGUIENTE BOTON SE REFRESCA LA PAGINA PRINCIPAL Y POR LO TANTO TAMBIEN SE CIERRA EL IFRAME-->
	//	echo "<br /><input type='submit' value='Close' name='gsubmit'  onclick='parent.location.reload();' />";


		header('Location: ../report.php?q='.$inv_id.'&o=add_val#validations'.$validation_id);	
	} //fin if que ha funcionado bien el add_validation
	else {
		mysql_close($con);
		header('Location: ../report.php?q='.$inv_id.'&o=add_valError#validations');
	}

}

?>


