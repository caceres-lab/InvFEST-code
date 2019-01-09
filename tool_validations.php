<?php
/******************************************************************************
	TOOL_VALIDATIONS.PHP

	Applies the function contained in php/add_validation.php to the values in the file with validation information and, if present, the file with genotypes information added by the "Add inversion validation" section from the "Search" menu from the website to include them in the database, and retrieves the modified inversions.
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

    // Select specific data into variables which are retrieved in other php pages
  include_once('php/select_report.php');

  // Includes HTML <head> and other settings for the page
  include_once('php/structure_page.php');

  // Includes global variables
  include_once('php/php_global_variables.php');

?>
<?php

	if (isset( $_POST['submit_table'])){

	    if( !$_FILES['fileToUpload3']['tmp_name'] != "" ){die("No validations file specified!");
	    } else {
	     	$fh = fopen($_FILES['fileToUpload3']['tmp_name'], 'rb') or die ("Could not upload validations file");

	    }
	}

	$individuals_array = "";
	if (isset( $_POST['submit_table'])){

	    if( !$_FILES['fileToUpload4']['tmp_name'] != "" ){
	    } else {
	     	$fh_i = fopen($_FILES['fileToUpload4']['tmp_name'], 'rb') or die ("Could not upload individuals file");
	     	$individuals_array = array();
	    }
	}

	while ( ($query_ind = fgets($fh_i)) !== false) {   
		$individuals_array[] = $query_ind;
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
    <div class="section-title TitleA">- <?php echo "Validations result";?></div>
	<div class='section-content'>
	     <div id="results_table">
		    <table id="sort_table2" width="100%">
				<thead>
					<tr>
				  	    <th class='title' width='10%'>Name <img src='css/img/sort.gif'></th>
					    <th class='title' width='23%'>Position (hg18) <img src='css/img/sort.gif'></th>
					    <th class='title' width='10%'>Estimated Inversion size (bp) <img src='css/img/sort.gif'></th>
					    <th class='title' width='18%'>Status <img src='css/img/sort.gif'></th>
					    <th class='title' width='10%'>Global frequency <img src='css/img/sort.gif'></th>
				    	<th class='title' width='29%'>Functional effect <img src='css/img/sort.gif'></th>
				    </tr>
				</thead>
		    	<tbody>
					<?php

						$including = 'TRUE'; # this is used in add_validation to differentiate between single or massive validation 
						include_once("php/add_validation.php");

						while ( ($query = fgets($fh)) !== false) {
						

							$query  = rtrim($query );
							$cols = explode(":", $query );

							// VALIDATION //
							
							if (count($cols) == 12){
								$id_validation=$cols[0];
								$inv_name=$cols[1];
		  						$_POST["research_name"]=$cols[2];
		    					$_POST["method"]=$cols[3];
		    					$_POST["status"]=$cols[4];
		    					$_POST["checked"]=$cols[5]; # checked is the previos name of 'Force Validation'
		      					$_POST["commentE"]=$cols[6];

							    //Frequency without genotypes
							    $_POST["fng_population"]=$cols[7];
							    $_POST["fng_individuals"]=$cols[8];
							    $_POST["fng_invalleles"]=$cols[9];
							    $_POST["fng_stdfreq"]=$cols[10];
							    $_POST["fng_invfreq"]=$cols[11];

							} else {

								echo "Missing values in your query: ". $query . "<br>";
								continue;
							}
				

							//Checkpoint //

							if ($id_validation == "" )  
								{ echo "Validation ID is not defined in your query: ". $query. "<br>";
								continue; }
					        if ($inv_name == "" )  
								{ echo "Inversion name is not defined in your query: ". $query. "<br>";
								continue; }
							elseif ( !preg_match("/^HsInv\d{4}$/", $inv_name))
								{ echo "Inversion name does not have the right format in your query: ".$query. "<br>";
								continue; }
							
							elseif ( $_POST["research_name"] == "")
								{ echo "Research name  is not defined in your query: ".$query. "<br>";
								continue; }
							elseif (!in_array($_POST["research_name"], $checkpoint_research, true))
								{ echo "Research name not available in your query: ". $query."<br>";
								continue; }
							
							elseif ( $_POST["method"] == "")
								{ echo "Method is not defined in your query: ".$query. "<br>";
								continue; }
							elseif (!in_array($_POST["method"], $checkpoint_method, true))
								{ echo "Method not available in your query: ". $query."<br>";
								continue; }

							elseif ( $_POST["status"] == "")
								{ echo "Status is not defined in your query: ".$query. "<br>";
								continue; }
							elseif (!in_array($_POST["status"], $checkpoint_status, true))
								{ echo "Status not available in your query: ". $query."<br>";
								continue; }
							
							elseif ($_POST["checked"] == "")
								{ echo "'Force status' field  is not defined in your query: ".$query. "<br>";
								continue; }
					        elseif ( $_POST["checked"] != "yes" && $_POST["checked"] != "not")
								{ echo "'Force status' field does not have the right format in your query: ". $query. "<br>";
								continue; }
							
							elseif (  ($_POST["fng_population"]!= "" || $_POST["fng_individuals"]!= "" || $_POST["fng_invalleles"]!= "" || $_POST["fng_stdfreq"]!= "" || $_POST["fng_invfreq"]!= "" ) 
								&&  ($_POST["fng_population"]== "" || $_POST["fng_individuals"]== "" || $_POST["fng_invalleles"]== "" || $_POST["fng_stdfreq"]== "" || $_POST["fng_invfreq"]== "" ))
								{echo "To include frequencies without genotypes all fields must be defined in your query: ". $query."<br>";
								continue;}

							elseif( $_POST["fng_population"] != "" && !in_array($_POST["fng_population"], $checkpoint_fngpopulation, true ))
								{echo "Population not available in your query: ". $query."<br>";
								continue;}
						
							elseif ($_POST["fng_individuals"] != "" && !preg_match("/^\d+$/", $_POST["fng_individuals"]))
								{ echo "Analyzed individuals is not a number or has decimals in your query: ".$query. "<br>";
								continue; }

							elseif ($_POST["fng_invalleles"] != "" && !preg_match("/^\d+$/", $_POST["fng_invalleles"]))
								{ echo "Inverted alleles is not a number or has decimals in your query: ".$query. "<br>";
								continue; }

							elseif ($_POST["fng_stdfreq"] != "" && !preg_match("/^\d+.?\d+$/", $_POST["fng_stdfreq"]))
								{echo "Standard allele frequency is not a number in your query: ".$query. "<br>";
								continue; }

							elseif ($_POST["fng_invfreq"] != "" && !preg_match("/^\d+.?\d+$/", $_POST["fng_invfreq"]))
								{ echo "Inverted  allele frequency is not a number in your query: ".$query. "<br>";
								continue; }

							
							//END Checkpoint //


							$sq_query1= "SELECT  i.id, i.chr FROM inversions i WHERE i.name='".$inv_name."';";
					 				
							$result=mysql_query($sq_query1);if (!$result) {echo('Invalid query: $sq_query1 '. mysql_error());}
				
							while($row = mysql_fetch_array($result)){

								$_POST["inv_id"] = $row['id'];
								$_POST["chr"]=$row['chr'];
								
							}

							// END VALIDATION //

							// INDIVIDUALS //

							if ($individuals_array != ""){
								$indiv_array = array();
								$indiv_array[] = $individuals_array[0];


								foreach ($individuals_array as $ind_a) {

									# Mira las que tienen el ID
									
									$ind_a = rtrim($ind_a);
									$cols = explode("\t", $ind_a );
									
									# Se guarda el ID
									$id_ind=$cols[0];

									if ($id_ind == $id_validation){
										
										# Si lo tienen, se las guarda en el archivo temporal
									 	
									 	$cols_net = array_shift($cols);
									 	$ind_output = implode ("\t", $cols);
									 	$ind_output = $ind_output."\n";
										$indiv_array[] = $ind_output;

									}else {
										continue;
									}
								}
								
								$_POST["indiv"] = $indiv_array;
							}

							// END INDIVIDUALS //


							addvalidation();
							echo $message;
							$sq_query= "SELECT  i.frequency_distribution, i.name, i.chr, i.status, b.inv_id,  b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, b.definition_method, b.genomic_effect 
											 FROM inversions i 
											INNER JOIN 
											breakpoints b ON b.id = (SELECT id 
																	FROM breakpoints b2 
																	WHERE b2.inv_id=i.id
																	ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC
																	LIMIT 1) 
										
											WHERE i.name= '".$inv_name."';";

							
							$resultss=mysql_query($sq_query);if (!$resultss) {echo('Invalid query: $sq_query ' . mysql_error());}
				
							while($row = mysql_fetch_array($resultss)){
					
								if ($row['status'] != 'FALSE') {
						
									if ($_SESSION["autentificado"]=='SI') {
							
										$r_freq = mysql_query("SELECT inv_frequency('".$row['inv_id']."','all','all','all') AS res_freq");
										$r_freq = mysql_fetch_array($r_freq);
										$d_freq = explode(";", $r_freq['res_freq']);
										$r_inv_freq=$d_freq[2];
								
										// Si no s'ha determinat la freqüència amb genotips, calcular-la sense genotips
							
										if (($r_inv_freq == '') or ($r_inv_freq == 'NA')) {
							
											$r_freq2 = mysql_query("SELECT SUM(individuals) individuals, SUM(individuals*inv_frequency)/SUM(individuals) inv_frequency
													FROM
													(SELECT region, SUM(individuals) individuals, SUM(individuals*inv_frequency)/SUM(individuals) inv_frequency
													FROM 
													(SELECT r.region, pd.population_name, IFNULL(pd.individuals,1) individuals, AVG(pd.inv_frequency) inv_frequency
													FROM population_distribution pd
													INNER JOIN(
													    SELECT inv_id, population_name, MAX(individuals) individuals
													    FROM population_distribution
													    GROUP BY inv_id, population_name
													) invres ON pd.inv_id = invres.inv_id 
														AND pd.population_name = invres.population_name 
														AND pd.individuals = invres.individuals
													INNER JOIN population r ON r.name=pd.population_name 
													WHERE pd.inv_id = '".$row['inv_id']."'
													GROUP BY pd.population_name) allpopulations
													GROUP BY region) allregions;");
												$r_freq2 = mysql_fetch_array($r_freq2);
												$r_inv_freq=$r_freq2[1];
										}

										if (($r_inv_freq != '') and ($r_inv_freq != 'NA')) {
								
											$r_inv_freq = number_format($r_inv_freq,4);
							
										}	
										
									} else {
					
										$d_freq = explode(";", $row['frequency_distribution']);
										$r_inv_freq=$d_freq[2];
								
										if (($r_inv_freq != '') and ($r_inv_freq != 'NA')) {
								
											$r_inv_freq = number_format($r_inv_freq,4);
											
										}
									}
						
									if (($r_inv_freq == '') or ($r_inv_freq == 'NA')) {
							
										$r_inv_freq = "<font color='grey'>ND</font>";
									
									}
									
								} else {

									$r_inv_freq = "<font color='grey'>NA</font>";
									$row['genomic_effect'] = 'NA';
					
								}
					
								$name=$row[name];
								$chr = $row[chr];
								$bp1_start=$row[bp1_start];
								$bp2_end=$row[bp2_end];
								$position_hg18=$chr.":".$bp1_start."-".$bp2_end;
								#Recalculate size & range
							    $middle_bp1 = number_format(($row[bp1_start]+$row[bp1_end])/2, 0, '.', '');
							    $middle_bp2 = number_format(($row[bp2_start]+$row[bp2_end])/2, 0, '.', '');
							    $size = number_format(round($middle_bp2-$middle_bp1+1));

								echo "<tr>";
								echo "<td><a href=\"report.php?q=".$row['inv_id']."\" target=\"_blank\" >".$name."</a></td>";
								echo "<td>$position_hg18</td>";
								echo "<td>$size</td>";
								echo "<td>".$array_status[$row['status']]."</td>";
								echo "<td>".$r_inv_freq."</td>";
								echo "<td>".$array_effects[$row['genomic_effect']]."</td>";
								echo "</tr>";
								
							}

						}	
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