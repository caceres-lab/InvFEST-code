<?php
/******************************************************************************
	UPDATE_VALIDATION.PHP

	Modifies an inversion validation in the database.
	It is executed when editing inversion validations 
	After adding the new inversion validation to the database, it executes automatically run_breakseq.sh for its BreakSeq annotation
*******************************************************************************/
?>


<?php
	session_start(); //Inicio la sesión
	include('security_layer.php');

?>

<!DOCTYPE html>
<html>

<?php

function updatevalidation(){ 

	$validation_id=$_POST["val_id"];
    $inv_id=$_POST["inv_id"];
    $chr=$_POST["chr"];
    $research_name=$_POST["research_name"];
    // $method=$_POST["method"];
    $status=$_POST["status"];
    $checked=$_POST["checked"];
    if (isset($_POST['between_bp1'])) { $betweenbp1="TRUE"; }
    else { $betweenbp1="FALSE"; }
    if (isset($_POST['between_bp2'])) { $betweenbp2="TRUE"; }
    else { $betweenbp2="FALSE"; }
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
    $indiv_array = $_POST["indiv"];
	// }

    $message=""; $warning_ind='';

     //Comprobaciones
    if ($checked != 'yes') { $checked='not'; }

    $order_bp='ko';
    if ($bp2e >= $bp2s && $bp2s > $bp1e && $bp1e >= $bp1s) { $order_bp='ok'; }
    if ($bp1s == "" && $bp1e == "" && $bp2s == "" && $bp2e == "" ) { $order_bp='ok'; }

    if ($status == "" || $status == null)
    	{ echo "Status is not defined<br>"; }
    elseif (($bp1s != "" || $bp1e!="" || $bp2s!="" || $bp2e!="") && ($bp1s=="" || $bp1e=="" || $bp2s=="" || $bp2e==""))
        { echo "All fields from Add Breakpoints must be defined<br>"; }
    elseif ($bp1s != "" && !preg_match('/^[0-9]+$/', $bp1s) && !preg_match('/[1-9]/', $bp1s))
        { echo"Breakpoint 1 start is not a number<br>"; }
    elseif ($bp1e != "" && !preg_match('/^[0-9]*$/', $bp1e) && !preg_match('/[1-9]/', $bp1e))
        { echo"Breakpoint 1 end is not a number<br>"; }
    elseif ($bp2s != "" && !preg_match('/^[0-9]*$/', $bp2s) && !preg_match('/[1-9]/', $bp2s))
        { echo"Breakpoint 2 start is not a number<br>"; }
    elseif ($bp2e != "" && !preg_match('/^[0-9]*$/', $bp2e) && !preg_match('/[1-9]/', $bp2e))
        { echo"Breakpoint 2 end is not a number<br>"; } 
    elseif ($order_bp != 'ok')
        {echo "Positions of the breakpoints are not correct<br>";}
    elseif ($_FILES["individuals"]["name"]!="" && $_FILES["individuals"]["type"]!="text/plain")
        { echo "File type not valid <br />"; }
    elseif ($_FILES["individuals"]["name"]!="" && $_FILES["individuals"]["error"]>0)
        { echo "Error: ".$_FILES["individuals"]["error"]."<br />"; }
    else { 

    	include_once('db_conexion.php');
	   
		mysql_query("CALL update_validation('$validation_id', '$status',  '$experimental_conditions', '$primers','$commentE','$checked', '".$_SESSION["userID"]."');");
		
		# FOSMIDS EDITION
		
		if ($fosmids != '' || $fosmids != NULL) {
	       	# PREVIOUS FOSMIDS ARE DELETED
	       	mysql_query("DELETE FROM fosmids_validation WHERE validation_id=$validation_id ;");

				    /*
	                add_fosmid_validation`(
				    IN validation_id_val INT, 
				    IN inv_id_val INT, 
				    IN fosmid_id_val INT, 
				    IN research_val  VARCHAR(255), 
				    IN result_val  VARCHAR(255), 
				    IN comment_val  VARCHAR(255))
				    */

			 //Despues de la funcion edit_validation, si hay fosmidos, llamamos al procedure add_fosmid_validation:
			mysql_query("CALL add_fosmid_validation('$validation_id','$inv_id','$fosmids','$research_name','$results','$commentB', '".$_SESSION["userID"]."');");
		}

		# INDIVIDUALS EDITION 

		if ($_FILES["individuals"]["name"]!="") {
			$fileind = fopen($_FILES["individuals"]["tmp_name"], "r") or exit("Unable to open file!");
			$indiv_array = array();
				    
			while(!feof($fileind)) {
			   	$line=fgets($fileind);
			   	$indiv_array[] = $line;
			}
			
			fclose($fileind);		    
		}

		
		if ($indiv_array != "" || $indiv_array != NULL){ 
			  
			# INDIVIDUALS IN THIS VALIDATION ARE DELETED!!!

	    	$sql_delete = "DELETE FROM individuals_detection WHERE validation_id = $validation_id ;";
			$result_delete= mysql_query($sql_delete);

		    foreach ($indiv_array as $line) {
					    	
		    	if( !strncmp($line, '#', 1) ){continue;}
						    
			        //Eliminamos los saltos de linea del final
					$line=trim($line, "\n");
					$line=trim($line, "\r");

					$code=""; $geder=""; $population=""; $region=""; $family=""; $panel=""; $relationship=""; $allele_level=""; $genotype=""; 
					$allele_comment=""; $other_code="";
							    
		    		if ($line != "") {
				
					    //PARSEAMOS EL FICHERO
					    //El fichero tiene 'codigo individuo' - 'genotipo' -> puede estar separado por ; \t o espacio
					    $separator="";
					    $semicolon=strpos($line,';');
					    if ($semicolon!==false) {           //; es el separador
						    $separator=';';
					    }
					    else {
						    $tab=strpos($line,"\t");
						    if ($tab!==false) {             //\t es el separador
							    $separator="\t";
						    } else {
							    $space=strpos($line," ");
							    if ($space!==false) {       //El separador es el espacio
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

					    // if ($code !="" && ($genotype=="INV/INV" || $genotype=="STD/INV" || $genotype=="STD/STD" || $genotype=="STD" || $genotype=="INV" || $genotype=="NA" || $genotype=="ND")) {
					    if($code != "" &&  (   preg_match('/^[A-Z]{3}\/[A-Z]{3}$/', $genotype) ||  preg_match('/^[A-Z]{3}$/', $genotype) || $genotype=="NA" || $genotype=="ND"  )){
					        mysql_query("CALL add_individual_genotype('$code' , '$geder', '$population', '$region', '$family', '$relationship', '$genotype', '$allele_comment', '$allele_level', '$panel', '$other_code', '$inv_id', '$validation_id', '".$_SESSION["userID"]."');");
		                	// $a="CALL add_individual_genotype('$code' , '$geder', '$population', '$region', '$family', '$relationship', '$genotype', '$allele_comment', '$allele_level', '$panel', '$other_code', '$inv_id', '$validation_id', '".$_SESSION["userID"]."');";
		                    // echo "CALL add_individual_genotipy('$code','$inv_id','$genotype','$validation_id');";
					    } else {
						    $warning_ind="yes";
					    }
			   		} else {
				   	 $warning_ind='yes';
			    	}	
				}
			}
		}

		# BREAKPOINTS [WORK THE SAME, BECAUSE IT IS PREPARED TO BE EDITED]
		
		if ($bp1s != "" || $bp1e!="" || $bp2s!="" || $bp2e!="") {
		    $changed=mysql_query("SELECT add_BP('$validation_id','$inv_id','$chr','$bp1s','$bp1e','$bp2s','$bp2e','$description', '".$_SESSION["userID"]."') AS chang");
		    $r= mysql_fetch_array($changed);
	
		    if ($r['chang']=='YES') { 
			    $message="Some predictions status have changed<br />";
		    }

		    #INSERT THE "BETWEEN BREAKPOINTS" INFORMATION TO THE DB		
			$sql_between = "UPDATE breakpoints SET bp1_between='$betweenbp1', bp2_between = '$betweenbp2' WHERE inv_id = $inv_id;";
			$result_between = mysql_query($sql_between);
			if (!$result_between) {
				die('Error when passing the checkbox input to the db: ' . mysql_error());
			}
		
			sleep(1);

		    //BREAKSEQ ANNOTATION
		    exec("kill $(ps aux | grep 'breakseq-1.3' | awk '{print $2}') > /dev/null 2>&1");
		    $gff_file = fopen("/home/invfest/BPSeq/breakseq_annotated_gff/input.gff", "w") or die("Unable to create gff file!");
            //Select inversions
            $query2="SELECT i.name, b.id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, i.status, b.GC FROM inversions i, breakpoints b  WHERE i.id=b.inv_id AND b.GC is null AND b.chr NOT IN ('chrM');";
            print "$sql_bp".'<br/>';
            $result_bp = mysql_query($query2) or die("Query fail: " . mysql_error());
            while($bprow = mysql_fetch_array($result_bp)) {
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

	    # FREQUENCIES WITHOUT GENOTYPES EDITION	
        if (($fng_population != 'null') && ($fng_invfreq != '')) {
	
		    if ($fng_individuals == '') { $fng_individuals='NULL'; }
		    if ($fng_invalleles == '') { $fng_invalleles='NULL'; }
	
		    $exists=mysql_query("SELECT  population_distribution WHERE inv_id= $inv_id AND population_name= '$fng_population'AND validation_id=$validation_id AND validation_research_name='$research_name'; ");
		    $e= mysql_num_rows($exists);


			if ($e != 0){
		    
		    	$add_freq_nogenotypes = mysql_query("UPDATE population_distribution SET individuals=$fng_individuals, inverted_alleles=$fng_invalleles,  inv_frequency= $fng_invfreq WHERE inv_id= $inv_id AND population_name= '$fng_population'AND validation_id=$validation_id AND validation_research_name='$research_name';");

			}else {

		    	$add_freq_nogenotypes = mysql_query("INSERT INTO population_distribution (inv_id, population_name, validation_id, validation_research_name, individuals, inverted_alleles, inv_frequency) VALUES ($inv_id, '$fng_population', $validation_id, '$research_name', $fng_individuals, $fng_invalleles, $fng_invfreq);");
			}


	    }

	    # END
		mysql_close($con);

	    if ($warning_ind != '') {

		    header('Location: ../report.php?q='.$inv_id.'&o=add_val_inderror#validations'.$validation_id);	
  				 // $message.="Some individuals have not been correctly introduced <br />";
		}else{
		    header('Location: ../report.php?q='.$inv_id.'&o=add_val#validations'.$validation_id);	
		}

		
    }

}

function deletevalidation(){ 

	$validation_id=$_POST["val_id"];
    $inv_id=$_POST["inv_id"];
    $chr=$_POST["chr"];
    $research_name=$_POST["research_name"];
 
   	include_once('db_conexion.php');

   	# Delete validation
	   
	mysql_query("CALL DELETE_validation('$validation_id', '".$_SESSION["userID"]."');");
		
	# Delete fosmids
		
	mysql_query("DELETE FROM fosmids_validation WHERE validation_id=$validation_id ;");

	# Delete individuals

	mysql_query("DELETE FROM individuals_detection WHERE validation_id = $validation_id ;");
	
	# BREAKPOINTS [I DON'T KNOW HOW TO DELETE IT]
		
	// $changed=mysql_query("SELECT add_BP('$validation_id','$inv_id','$chr','$bp1s','$bp1e','$bp2s','$bp2e','$description', '".$_SESSION["userID"]."') AS chang");
	// $r= mysql_fetch_array($changed);
	
	// sleep(1);

    //BREAKSEQ ANNOTATION [I DON'T KNOW IF IT'S NECESSARY]
    // exec("kill $(ps aux | grep 'breakseq-1.3' | awk '{print $2}') > /dev/null 2>&1");
    // $gff_file = fopen("/home/invfest/BPSeq/breakseq_annotated_gff/input.gff", "w") or die("Unable to create gff file!");
    // //Select inversions
    // $query2="SELECT i.name, b.id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, i.status, b.GC FROM inversions i, breakpoints b  WHERE i.id=b.inv_id AND b.GC is null AND b.chr NOT IN ('chrM');";
    // print "$sql_bp".'<br/>';
    // $result_bp = mysql_query($query2) or die("Query fail: " . mysql_error());
    // while($bprow = mysql_fetch_array($result_bp)) {
    //     $midpoint_BP1=round(($bprow['bp1_end']-$bprow['bp1_start'])/2+$bprow['bp1_start']);
    //         $midpoint_BP2=round(($bprow['bp2_end']-$bprow['bp2_end'])/2+$bprow['bp2_start']);
    //         $chr=$bprow['chr'];
    //     $name=$bprow['name'];
    //     $id_bp= $bprow['id'];
    //     //$gene_id= $bprow['gene_id'];
    //     $inverion_gff_line= "$chr\t$name\tInversion\t$midpoint_BP1\t$midpoint_BP2\t.\t.\t.\t$id_bp\n";

    //     fwrite($gff_file, $inverion_gff_line);
    // }

    // fclose($gff_file);

    //BreakSeq execution
    //---------------------------------------------------------------------------
    exec("nohup ./run_breakseq.sh > /dev/null 2>&1 &");


	# Delete fng
    
	mysql_query("DELETE FROM population_distribution WHERE inv_id= $inv_id AND validation_id=$validation_id AND validation_research_name='$research_name'; ");

	mysql_close($con);

	header('Location: ../report.php?q='.$inv_id.'&o=delete_val#validations'.$validation_id);
		

}

if (isset($_POST["Update"])){
	updatevalidation();
}elseif(isset($_POST["Delete"])){
	deletevalidation();
}

?>

