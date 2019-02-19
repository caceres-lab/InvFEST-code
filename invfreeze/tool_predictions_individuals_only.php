<?php
/******************************************************************************
	TOOL_PREDICTIONS.PHP

	Adds a new inversion prediction to the database. It is executed when new inversion prediction values ("Single prediction" subsection) or a predictions file ("Multiple predictions" subsection) are added by the "Add inversion prediction" section in the "Search" menu from the website.
	After adding the new inversion validation to the database, it executes automatically run_breakseq.sh for the inversion's BreakSeq annotation and retrieves the modified or added inversions.
*******************************************************************************/
?>


<?php 
    #error_reporting(E_ERROR );
    #ini_set('display_errors',1);
    #ini_set('display_startup_errors',1);
    #error_reporting(-1);
    session_start(); //Inicio la sesión
?>

<?php
  
  // Select specific data into variables which are retrieved in other php pages
  include_once('php/select_index.php');

  // Includes HTML <head> and other settings for the page
  include_once('php/structure_page.php');

  // Includes global variables
  include_once('php/php_global_variables.php');

?>

<?php

		$individuals_array = "";
	if (isset( $_POST['submit_table'])){
	    if( $_FILES['fileToUpload5']['tmp_name'] != "" ){	 

	     	$fh_i = fopen($_FILES['fileToUpload5']['tmp_name'], 'rb') or die ("Could not upload individuals file2");
	     	$individuals_array = array();
	    }
	}

	while ( ($query_ind = fgets($fh_i)) !== false) {   
		$individuals_array[] = $query_ind;
		
	}

	// Check the input file or create temporary file with the form data //
	if (isset( $_POST['submit_table'])){

     	if( !$_FILES['fileToUpload2']['tmp_name'] != "" ){die("No file specified!");
     	} else {
     		$fh = fopen($_FILES['fileToUpload2']['tmp_name'], 'rb') or die ("Could not upload file");
      	}
      	
	}else if (isset( $_POST['submit'])){
  
    	if (isset($_POST['between_bp1'])) { $betweenbp1="TRUE"; } else {$betweenbp1="FALSE";}
    	if (isset($_POST['between_bp2'])) { $betweenbp2="TRUE"; } else {$betweenbp2="FALSE";}
    	$query = "null:".$_POST["pred_chr"].":".$_POST["pred_bp1s"].":".$_POST["pred_bp1e"].":".$betweenbp1.":".$_POST["pred_bp2s"].":".$_POST["pred_bp2e"].":".$betweenbp2.":".$_POST["pred_study_name"].":".$POST["pred_name"];
    	
    	$outputpath = "/var/www/html/invdb/tmp_files/tmp";
		$output = fopen("$outputpath", 'w') or die("Unable to create tmp file in"."$outputpath");
		fwrite($output, $query);
		$fh = fopen($outputpath, 'rb') or die ("Could not upload tmp file"); 
	}


?>

<!DOCTYPE html>
<html>

<?php 
    echo $creator;
    
    $head .= $head_search;  // Attach the 'search form' scripts within the HTML header
    $head .= "</head>";     // Head end
    echo $head;             // 'Print' head code
?>


<!-- **************************************************************************** -->
<!-- BODY -->
<!-- **************************************************************************** -->
<body>


<!-- **************************************************************************** -->
<!-- PAGE MENU: Print the header banner of InvFEST -->
<!-- **************************************************************************** --> 
<?php include('php/echo_menu.php'); ?>
<br />
<?php echo $search_inv; ?>  


<!-- **************************************************************************** -->
<!-- DIVISIONS -->
<!-- **************************************************************************** -->
<br />
  <div id="search_results">
    <div class="section-title TitleA">- <?php echo "Predictions result";?></div>
	<div class='section-content'>
	     <div id="results_table">
		    <table id="sort_table2" width="100%">
				<thead>
					<tr>
					    <th class='title' width='7%'>Name <img src='css/img/sort.gif'></th>			
						<th class='title' width='7%'>Chromosome <img src='css/img/sort.gif'></th>
						<th class='title' width='10%'>Range start <img src='css/img/sort.gif'></th>
						<th class='title' width='18%'>Range end <img src='css/img/sort.gif'></th>
						<th class='title' width='10%'>Estimated Inversion size  <img src='css/img/sort.gif'></th>
						<th class='title' width='10%'>Status <img src='css/img/sort.gif'></th>
						<th class='title' width='10%'>Prediction ID <img src='css/img/sort.gif'></th>
					
					</tr>
				</thead>
		    	<tbody>
		   			<?php

						while ( ($line = fgets($fh)) !== false) {
					
							// echo "$line <br>";
							$line = rtrim($line);
							$cols = explode(":", $line);
							
							if (count($cols) == 10){

								$id_pred = $cols[0];
								$chr=$cols[1]; //*
					   			$bp1s=$cols[2]; //numerico *
					    		$bp1e=$cols[3]; //numerico *
					    		$betweenbp1=$cols[4];  // boolean TRUE FALSE *
					    	    $bp2s=$cols[5]; //numerico *
					    		$bp2e=$cols[6]; //numerico *
					    		$betweenbp2=$cols[7];  // boolean TRUE FALSE *
					    	    $study_name=$cols[8]; // *
					    	    $prediction_name=$cols[9];

							} else {

								echo("Incorrect number of values in your query: ". $line. "<br>");
								continue;
							}
		

							//Checkpoint //

					        $order_bp='ko';
		        			if ($bp2e >= $bp2s && $bp2s > $bp1e && $bp1e >= $bp1s) { $order_bp='ok'; }
					        if ($chr == "" )
					        	{ echo ("Chromosome is not defined in your query: ". $line. "<br>"); 
								continue;}
							elseif ( !preg_match("/^chr\d{1,2}$/", $chr) &&  !preg_match("/^chr[M,X,Y]$/", $chr) )
								{ echo ("Chromosome does not have the right format in your query: ". $line. "<br>"); 
								continue; }
					        elseif (($bp1s != "" || $bp1e!="" || $bp2s!="" || $bp2e!="") && ($bp1s=="" || $bp1e=="" || $bp2s=="" || $bp2e=="")) 
					        	{ echo ("All breakpoint fields must be defined in your query: ". $line. "<br>"); 
					    		continue;}
					        elseif ($bp1s != "" && !preg_match('/^[0-9]+$/', $bp1s) && !preg_match('/[1-9]/', $bp1s)) 
					            { echo ("Breakpoint 1 start is not a number in your query: ". $line. "<br>"); 
					        	continue;}
					        elseif ($bp1e != "" && !preg_match('/^[0-9]*$/', $bp1e) && !preg_match('/[1-9]/', $bp1e)) 
					        	{ echo("Breakpoint 1 end is not a number in your query: ". $line. "<br>"); 
					        	continue;}
					        elseif ($bp2s != "" && !preg_match('/^[0-9]*$/', $bp2s) && !preg_match('/[1-9]/', $bp2s)) 
					        	{ echo ("Breakpoint 2 start is not a number in your query: ". $line. "<br>"); 
					        	continue;}
					        elseif ($bp2e != "" && !preg_match('/^[0-9]*$/', $bp2e) && !preg_match('/[1-9]/', $bp2e)) 
					        	{ echo ("Breakpoint 2 end is not a number in your query: ". $line. "<br>"); 
					    		continue;}
					        elseif ($order_bp != 'ok') 
					        	{ echo ("Positions of the breakpoints are not correct in your query: ". $line. "<br>"); 
					    		continue;}
							elseif ($study_name == "" ) 
								{ echo ("Study name is not defined in your query: ". $line. "<br>"); 
								continue;}
							elseif (!in_array($study_name, $checkpoint_research, true))
								{ echo "Research name not available in your query: ". $line."<br>";
								continue; }
							elseif( ($betweenbp1 != "TRUE" && $betweenbp1 != "FALSE") || ($betweenbp2 != "TRUE" && $betweenbp2 != "FALSE"))
								{ echo ("Some of your 'Breakpoint N between start-end' are not defined as TRUE nor FALSE in your query: ". $line. "<br> ");
								continue;}
					        else {
						        //All correct, connect to database: //
						        include('db_conexion.php');
						    }	


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

				            // $f="CALL setup_pred_to_inv_merge('$chr', '$bp1s', '$bp1e', '$bp2s', '$bp2e','$study_name', '".$_SESSION["userID"]."')";
				            // echo $f;
$getinfo = "SELECT id, inv_id FROM predictions WHERE prediction_name = '$id_pred';";
$infogot=mysql_query($getinfo);
while ($info = mysql_fetch_array($infogot)) {
	
	$pred_id = $info['id'];
	$inv_id = $info['inv_id'];
}
echo "test: ".$pred_id." ".$inv_id;

###################################################
			    //     		$add_prediction = mysql_query("CALL setup_pred_to_inv_merge('$chr', '$bp1s', '$bp1e', '$bp2s', '$bp2e','$study_name', '".$_SESSION["userID"]."')");
		          		
							// if (!$add_prediction) {
				           	
				   //         		die('Error when adding prediction: ' . mysql_error());
				   //      	}

					  //       $sql_get_inv = "SELECT i.id
						 //          FROM inversions AS i JOIN predictions AS p ON i.id = p.inv_id where  p.research_name = '$study_name' and p.research_id = 
						 //         (SELECT max(research_id) FROM predictions WHERE research_name = '$study_name');";
					       
			    //    			 // echo "$sql_get_inv<br>";

			    //     		$result_get_inv = mysql_query($sql_get_inv);
			    //     		sleep(1);

			    //   		    while($row = mysql_fetch_array($result_get_inv)){
					        
					  //       	// echo "$row[id], $row[name] <br>";
														
						 //       	$inv_id = $row['id'];	
						 //    }
							
							// //  Add "between breakpoints" information to the database //		
					       
					  //       $sql_between = "UPDATE breakpoints SET bp1_between='$betweenbp1', bp2_between = '$betweenbp2' WHERE inv_id = $inv_id;";
					  //       $result_between = mysql_query($sql_between);
					        
					  //        // echo "$sql_between <br>";
				        
				   //      	if (!$result_between) {
				           	
				   //         		die('Error when inserting BETWEEN BREAKPOINTS from the checkbox input to the db: ' . mysql_error());
				   //      	}
				
				   //      	//  Add "prediction name" information to the database //	
				   //      	if ($prediction_name != ""){

						 //        $sql_get_id = "SELECT id FROM predictions ORDER BY id DESC LIMIT 1;";
						 //        $result_get_id = mysql_query($sql_get_id);
						 //        while ($id = mysql_fetch_array($result_get_id)){
						 //        	$pred_id = $id['id'];
						 //        }	

						 //        $sql_name = "UPDATE predictions SET prediction_name='$prediction_name' WHERE id = $pred_id;";
						 //        $result_name = mysql_query($sql_name);
						 //        if (!$result_name) {		           	
					  //          		die('Error when inserting PREDICTION NAME from the input box to the db: ' . mysql_error());
					  //       	}
				   //     		}


				       		// Add individuals associated to the prediction			
##################################################	       		

							if ($individuals_array != ""){
								$indiv_array = array();
								
								foreach ($individuals_array as $ind_a) {

									
									# Mira las que tienen el ID
									
									$ind_a = rtrim($ind_a);
									$cols = explode("\t", $ind_a );
									
									# Se guarda el ID
									$id_ind=$cols[0];

									if ($id_ind == $id_pred){
										
										# Si lo tienen, se las guarda en el archivo temporal
									 	
									 	$cols_net = array_shift($cols);
									 	$ind_output = implode ("\t", $cols);
									 	$ind_output = $ind_output."\n";

										$indiv_array[] = $ind_output;

									}else {

										continue;
									}
								}
							}

							if ($indiv_array != "" || $indiv_array != NULL){ 
		    					foreach ($indiv_array as $line) {
			    		
				    				if( !strncmp($line, '#', 1) ){continue;} # Si no es un comentario
								    
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

										    if ($code !="" ) {
										        mysql_query("CALL add_individual_prediction('$code' , '$geder', '$population', '$region', '$family', '$relationship', '$genotype', '$allele_comment', '$allele_level', '$panel', '$other_code', '$inv_id', '$pred_id', '".$_SESSION["userID"]."');");
				                             } else {
											    $warning_ind="yes";
										    }
									    } else {
										    $warning_ind='yes';
									    }
							    	}
								}
							}

					       	$sql_row="SELECT  i.name, i.chr, i.range_start, i.range_end, i.size, i.status ,
								    b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end
							    FROM inversions i INNER JOIN breakpoints b ON b.id = (SELECT id FROM breakpoints b2 WHERE b2.inv_id=i.id
								    ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC
								    LIMIT 1) LEFT JOIN validation val ON b.id = val.bp_id 
								    LEFT JOIN researchs r ON val.research_name = r.name 
							    WHERE i.id ='$inv_id';";
						    
						    $result_row = mysql_query($sql_row);
						    $row= mysql_fetch_array($result_row);

						    #Recalculate size & range
						    $middle_bp1 = number_format(($row['bp1_start']+$row['bp1_end'])/2, 0, '.', '');
						    $middle_bp2 = number_format(($row['bp2_start']+$row['bp2_end'])/2, 0, '.', '');
						    $row['size'] = number_format(round($middle_bp2-$middle_bp1+1));
						    $row['range_start'] = $row['bp1_start'];
						    $row['range_end'] = $row['bp2_end'];


					        echo "<tr>";
								echo "<td><a href=\"report.php?q=".$inv_id."\" target=\"_blank\" >".$row['name']."</a></td>";
								echo "<td>".$row['chr']."</td>";
					            echo "<td>".$row['range_start']."</td>";
					            echo "<td>".$row['range_end']."</td>";
					            echo "<td>".$row['size']."</td>";
					            echo "<td>".$array_status[$row['status']]."</td>";
					            echo "<td>".$prediction_name."</td>";
					        echo "<tr>"; 

					        	// sleep(1); 
				   		}
            			
        //     			fclose($fh);

		       		 	// Breakseq gff input file generation
		        		// ----------------------------------------------------------------------------
############3
				           //  exec("kill $(ps aux | grep 'breakseq-1.3' | awk '{print $2}') > /dev/null 2>&1");
				           //  $gff_file = fopen("/home/invfest/BPSeq/breakseq_annotated_gff/input.gff", "w") or die("Unable to create gff file!");
				           //   //Select inversions
				           //  $sql_bp="SELECT i.name, b.id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, i.status, b.GC FROM inversions i, breakpoints b  WHERE i.id=b.inv_id AND b.chr NOT IN ('chrM') AND b.GC is null;";
				           //  #print "$sql_bp".'<br/>';

				           //  $result_bp=mysql_query($sql_bp);
				           //  while($bprow = mysql_fetch_array($result_bp)) {
					          //   $midpoint_BP1=round(($bprow['bp1_end']+$bprow['bp1_start'])/2);
					          //   $bp2_end =$bprow['bp2_end'];
					          //   $bp2_start =$bprow['bp2_start'];
					          //   #print "$bp2_end\t$bp2_start\n";
				    	      //       $midpoint_BP2=round(($bp2_start+$bp2_end)/2);
					          //   #print "$midpoint_BP2\n";
				    	      //       $chr=$bprow['chr'];
					          //   $name=$bprow['name'];
					          //   $id_bp= $bprow['id'];
					          //   //$gene_id= $bprow['gene_id'];
				           //      $inverion_gff_line= "$chr\t$name\tInversion\t$midpoint_BP1\t$midpoint_BP2\t.\t.\t.\n";
				    
				           //      fwrite($gff_file, $inverion_gff_line);
				           //  }

				           //  fclose($gff_file);

			            // //BreakSeq execution
			            // //---------------------------------------------------------------------------
				           //  exec("nohup ./run_breakseq.sh > /dev/null 2>&1 &");
##############3            			
					?>
		    	</tbody>
		    </table>
	    </div>
	</div>
</div>

<br/>
<!-- **************************************************************************** -->
<!-- FOOT OF THE PAGE -->
<!-- **************************************************************************** -->
<div id="foot">
	<?php include('php/footer.php'); ?>
</div>

</div> <!-- Closes the Wrapper's divison opened at 'echo_menu.php' -->
</body>
</html>

<?php
  // 
  mysql_close($con);
?>
