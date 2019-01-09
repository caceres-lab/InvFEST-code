<?php
/******************************************************************************
	TOOL_BATCH_QUERY.PHP

	Retrieves the InvFEST inversions that match with an input file that comes from  structure_page.php. 
    When the query is done, the search website page is reloaded and the matches are listed in a downloadable table. 
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


  // Includes global variables
  include_once('php/php_global_variables.php');

?>

<?php

	// $fp = fopen($_FILES['fileToUpload']['tmp_name'], 'rb');
	// while ( ($line = fgets($fp)) !== false) {
	// 	echo "$line<br>";
	// }

	// Checkpoint //

	if (isset($_POST['fileAlready'])){ # Si esta checkbox para el archivo de antes
			$query_inv = $_POST['result']; # This is an array
		// }
	}else {	# Si no teniamos archivo de antes
		if( !$_FILES['fileToUpload']['tmp_name'] != "" ){die("No file specified!");} # Si no hay archivo die
		$query_inv= array();
		$fh = fopen($_FILES['fileToUpload']['tmp_name'], 'rb') or die ("Could not upload file2"); # Si no se puede subir die
		while ( ($query_file = fgets($fh)) !== false){   
			$query_inv[] = $query_file;
		}
	}

	if (isset( $_POST['nameoutputfile'])){$outputname = $_POST['nameoutputfile'];}
	if(!isset($outputname) || trim($outputname) == ''){$outputname="output.txt";}


	if (isset( $_POST['add_bp'])){$Err1 = $_POST['add_bp'];}
	if(!isset($Err1) || trim($Err1) == ''){$Err1="0";}

	// if (isset ($_POST['accutare_filter_value']) || isset($_POST['submit']) ) {
	// 	if (isset( $_POST['add_bp'])){$Err1 = $_POST['add_bp'];}
	// 	if(!isset($Err1) || trim($Err1) == ''){$Err1="0";}
	// } else if isset($_POST['overlap_search']){
	// 	if (isset( $_POST['add_bp3'])){$Err1 = $_POST['add_bp3'];}
	// 	if(!isset($Err1) || trim($Err1) == ''){$Err1="0";}
	// 	if (isset($_POST['overlap_percent'])){
	// 		$overlap_percent = 	$_POST['overlap_percent'];	
	// 	}
	// 	if (isset($_POST['internal'])){$internal == TRUE; }
	// }


	//Input file parsing//
	
	$outputpath = "/var/www/html/invdb/tmp_files/".$outputname;
	// echo "$outputpath<br>";

    // Includes HTML <head> and other settings for the page
    include_once('php/structure_page.php');

	//DB connection//
	include('db_conexion.php');
	//$user = "invfest";$password = "pwdInvFEST";$db = "INVFEST-DB";
	//$con = mysql_connect('localhost', $user, $password);
	if (!$con) { die('Could not connect: ' . mysql_error()); }
	//mysql_select_db($db, $con);

	//Output//
	$output = fopen("$outputpath", 'w') or die("Unable to create output file!"."$outputname");
	
	$header = "Query_ID\tName\tQuery_position\tPosition_hg18\tSize\tStatus\tGlobal_freq\tFunctional_effect\n";
	fwrite($output, $header);

	$array_results= array();
	$rowCount="0";
	$echoarray = array();
	
	foreach ($query_inv as $line) {
		// echo "$line\n";

		// if(preg_match("/:chr\d{1,2}:/", $line) or preg_match("/:chr[M,X,Y]:/",$line) or preg_match("/^chr\d{1,2}:/", $line) or preg_match("/^chr[M,X,Y]:/",$line)){ #if position (so no header), query it

		$line = rtrim($line);
		$cols = explode(":", $line);
		
		if (count($cols) == 3){
		
			$ID = $cols[0];
			$chr = $cols[1];
			$positions = $cols[2];
			$positions = explode ("-", $positions);
			$bp1 = $positions[0];
			$bp2 = $positions[1];

			if(preg_match("/,/", $bp1)){

				$position_bp1 = explode(",", $bp1);
				$q_bp1_start = $position_bp1[0];
				$q_bp1_end = $position_bp1[1];

			} else {

				$q_bp1_start = $bp1;
				$q_bp1_end = $bp1;
			}

			if(preg_match("/,/", $bp2)){

				$position_bp2 = explode(",", $bp2);
				$q_bp2_start = $position_bp2[0];
				$q_bp2_end = $position_bp2[1];

			}else {

				$q_bp2_start = $bp2;
				$q_bp2_end = $bp2;
			}
		} elseif (count($cols) == 2){
		    
		    $ID++;
		    $chr = $cols[0];
		    $positions = $cols[1];
			$positions = explode ("-", $positions);
			$bp1 = $positions[0];
			$bp2 = $positions[1];
		    
			if(preg_match("/,/", $bp1)){
				
				$position_bp1 = explode(",", $bp1);
				$q_bp1_start = $position_bp1[0];
				$q_bp1_end = $position_bp1[1];
			
			} else {
			
				$q_bp1_start = $bp1;
				$q_bp1_end = $bp1;
			
			}
			
			if(preg_match("/,/", $bp2)){
			
				$position_bp2 = explode(",", $bp2);
				$q_bp2_start = $position_bp2[0];
				$q_bp2_end = $position_bp2[1];
			
			}else {
			
				$q_bp2_start = $bp2;
				$q_bp2_end = $bp2;
			
			}
		} else {
			$echoarray[] = "Missing values in your query: ". $line . "<br>";
			continue;
		}			


		//Checkpoint //
        $order_bp='ko';
		if ($q_bp2_end >= $q_bp2_start && $q_bp2_start > $q_bp1_end && $q_bp1_end >= $q_bp1_start) { $order_bp='ok'; }
        if ($chr == "" )  
			{ $echoarray[] = "Chromosome is not defined in your query: ". $line. "<br>";
			continue; }
		elseif ( !preg_match("/^chr\d{1,2}$/", $chr) &&  !preg_match("/^chr[M,X,Y]$/", $chr) )
			{ $echoarray[] = "Chromosome does not have the right format in your query: ". $line. "<br>";
			continue; }
        elseif (($q_bp1_start != "" || $q_bp1_end!="" || $q_bp2_start!="" || $q_bp2_end!="") && ($q_bp1_start=="" || $q_bp1_end=="" || $q_bp2_start=="" || $q_bp2_end=="")) 
        	{ $echoarray[] = "All breakpoint fields must be defined in your query: ". $line. "<br>";
        	continue; }
        elseif ($q_bp1_start != "" && !preg_match('/^[0-9]+$/', $q_bp1_start) ) 
            { $echoarray[] = "Breakpoint 1 start is not a number in your query: ". $line. "<br>";
            continue; }
        elseif ($q_bp1_end != "" && !preg_match('/^[0-9]+$/', $q_bp1_end) ) 
        	{ $echoarray[] = "Breakpoint 1 end is not a number in your query: ". $line. "<br>";
        	continue; }
        elseif ($q_bp2_start != "" && !preg_match('/^[0-9]+$/', $q_bp2_start) ) 
        	{ $echoarray[] = "Breakpoint 2 start is not a number in your query: ". $line. "<br>";
        	continue; }
        elseif ($q_bp2_end != "" && !preg_match('/^[0-9]+$/', $q_bp2_end)) 
        	{ $echoarray[] = "Breakpoint 2 end is not a number in your query: ". $line. "<br>"; 
    		continue;}
        elseif ($order_bp != 'ok') 
        	{ $echoarray[] = "Positions of the breakpoints are not correct in your query: ". $line. "<br>";
        	continue; }

				
		if (isset( $_POST['accutare_filter_value'])){ 
			
			$accurate_filer = "YES";
		}else{
		
			$accurate_filer = "NO";
		}
		
		if ($accurate_filer == "NO"){
			
			$match_condition = "(($q_bp1_start - $Err1 BETWEEN b.bp1_start AND b.bp2_end) OR ($q_bp2_end + $Err1 BETWEEN b.bp1_start AND b.bp2_end) OR ( $q_bp1_start - $Err1 <= b.bp1_start AND $q_bp2_end + $Err1 >= b.bp2_end))";
		} else if ($accurate_filer == "YES"){

			$match_condition = "(( ($q_bp1_start - $Err1 <= b.bp1_start OR $q_bp1_start - $Err1 BETWEEN b.bp1_start AND b.bp1_end ) 
				AND ( $q_bp1_end + $Err1 >= b.bp1_end OR $q_bp1_end + $Err1 BETWEEN b.bp1_start AND b.bp1_end) )
				AND  (($q_bp2_start - $Err1 <= b.bp2_start OR $q_bp2_start - $Err1 BETWEEN b.bp2_start AND b.bp2_end ) 
				AND ( $q_bp2_end + $Err1 >= b.bp2_end OR $q_bp2_end + $Err1 BETWEEN b.bp2_start AND b.bp2_end)))";
		}



		$sq_query= "SELECT  i.frequency_distribution, i.name, i.chr, i.status, b.inv_id,  b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, b.definition_method, b.genomic_effect 
					 FROM inversions i 
					INNER JOIN 
					breakpoints b ON b.id = (SELECT id 
											FROM breakpoints b2 
											WHERE b2.inv_id=i.id
											ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC
											LIMIT 1) 
					AND i.chr = '$chr' AND $match_condition AND i.status NOT IN ('WITHDRAWN','Withdrawn','withdrawn')
					ORDER BY FIELD (i.status, 'TRUE','possible_TRUE', 'possible_FALSE','FALSE','Ambiguous/FALSE','FILTERED OUT','ND','AMBIGUOUS','Ambiguous','ambiguous');";
			 				
		$result=mysql_query($sq_query);if (!$result) {echo('Invalid query: ' . mysql_error());}
	
		while($row = mysql_fetch_array($result)){

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
			
			

			$rowCount++;
			$name=$row[name];
			$chr=$row[chr];
			$bp1_start=$row[bp1_start];
			$bp1_end=$row[bp1_end];
			$bp2_start=$row[bp2_start];
			$bp2_end=$row[bp2_end];
			$query_position = $chr.":".$q_bp1_start."-".$q_bp2_end;
			$position_hg18=$chr.":".$bp1_start."-".$bp2_end;
			#Recalculate size 
   			$middle_bp1 = number_format(($row['bp1_start']+$row['bp1_end'])/2, 0, '.', '');
    		$middle_bp2 = number_format(($row['bp2_start']+$row['bp2_end'])/2, 0, '.', '');
    		$size = number_format(round($middle_bp2-$middle_bp1+1));


			preg_match("/>(.*?)</", $array_status[$row['status']], $output_array);
			$status = $output_array[1];

			if(preg_match("/>(.*?)</",$array_effects[$row['genomic_effect']], $output_array)){
				
				$effect = $output_array[1]; }else{$effect = $array_effects[$row['genomic_effect']];	
			
			}


			if (preg_match("/>(.*?)</", $r_inv_freq, $output_array)){
			
				$freq = $output_array[1]; } else{$freq = $r_inv_freq;
			
			}


		
			$inversion= "$ID\t$name\t$query_position\t$position_hg18\t$size\t$status\t$freq\t$effect\n";
			$inversion2 =  $ID."|".$row[name]."|".$row[inv_id]."|".$query_position."|".$position_hg18."|".$size."|".$row[status]."|".$freq."|".$row[genomic_effect];
			// echo $inversion2;
			$array_results[] = $inversion2;
			// $array_results[] = $ID.",".$row[name].",".$row[inv_id].",".$query_position.",".$position_hg18.",".$size.",".$row[status].",".$freq.",".$row[genomic_effect];
			fwrite($output, $inversion);
		}
	}

	fclose($fh);

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
	<div class="section-title TitleA">-  <b> <?php echo $rowCount; ?></b> inversions found <form style="display: inline;" method="post" action="php/invfest_finder_download_matched_inversions.php"><input type="image" class='download'  src="img/download.png" name='pathoutput' title='Download table' alt="Submit Form" width='14' height='14' > <input  type='hidden'  name='pathoutput' value="<?php echo $outputpath;?>"></div>
	<div class='section-content'>
	    <div id="results_table">
		    <table id="sort_table2" width="100%">
			    <thead>
				    <tr>
						<th class='title' width='2%'>Query ID<img src='css/img/sort.gif'></th>
						<th class='title' width='2%'>Name <img src='css/img/sort.gif'></th>
						<th class='title' width='8%'>Query position <img src='css/img/sort.gif'></th>
						<th class='title' width='8%'>Position (hg18) <img src='css/img/sort.gif'></th>
						<th style='text-align:right' class='title' width='2%'>Estimated Inversion size (bp) <img src='css/img/sort.gif'></th>
						<th style='text-align:center' class='title' width='2%'>Status <img src='css/img/sort.gif'></th>
						<th class='title' width='2%'> Global frequency <img src='css/img/sort.gif'></th>
						<th class='title' width='12%'> Functional effect <img src='css/img/sort.gif'></th>
					</tr>
			    </thead>
		    	<tbody>
			    <?php
			    	
			    	foreach ($echoarray as $warning){
			    		echo $warning;
			    	}

			    	foreach ($array_results as $line){
			    
				    	$cols = explode ("|", $line);
				    	
				    	$ID= $cols[0];
				    	$name=$cols[1];
				    	$inv_id = $cols[2];
				    	$query_position = $cols[3];
				    	$position_hg18 = $cols[4];
				    	$size = $cols[5];
				    	$status =  $cols[6];
				    	$frequency = $cols[7];
				    	$genomic_effect =  $cols[8];
					
						// echo $internal;
						// echo $Err1;
						// echo $overlap_percent;

						echo "<tr>";
							echo "<td>$ID</td>";
							echo "<td><a href=\"report.php?q=".$inv_id."\" target=\"_blank\" >".$name."</a></td>";
							echo "<td>$query_position</td>";
							echo "<td>$position_hg18</td>";
							echo "<td style='text-align:right'>$size</td>";
							echo "<td style='text-align:center' >".$array_status[$status]."</td>";
							echo "<td>".$freq."</td>";
							echo "<td>".$array_effects[$genomic_effect]."</td>";
						echo "</tr>";
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
  mysql_close($con);
?>
