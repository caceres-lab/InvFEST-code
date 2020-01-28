<?php
/******************************************************************************
	SEARCH_INVDB2.PHP

	Retrieve the InvFEST matched inversions from the search.php query.
    When the query is done, the search website page is reloaded but now using the search_invdb2.php page instead of the search.php
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

    /***** Added by: RAQUEL *****

    $search_field=$_POST["search_field"];
    $inv_status=$_POST["inversion_status"]; 
    //strlen(implode('', $inv_status)) // --> Puede comprobar la longitud de todos los elementos del array. Si es 0, esta completamente vacío

    $inv_status=$_POST["research"]; 
    $inv_status=$_POST["validation_method"]; 
    $inv_status=$_POST["validation_status"]; 
    $inv_status=$_POST["individual"]; 
    $inv_status=$_POST["search_field2"]; // --> Temporal!!

    */
    /*

    //$inv=$_POST["name"];
    //$chr=$_POST["chr"]; //multiple
    //$range_start=$_POST["range_start"];
    //$range_end=$_POST["range_end"];

    $size=$_POST["size"];
    $size_value=$_POST["size_value"];
    $size_valueB=$_POST["size_valueB"];
    //$score=$_POST["score"];
    $inv_status=$_POST["inversion_status"]; // --> Múltiple

    $research=$_POST["research"]; //multiple
    $validation_method=$_POST["validation_method"]; // --> Múltiple
    $validation_status=$_POST["validation_status"]; // --> Múltiple
    $fosmids=$_POST["fosmids"]; 
    $individual=$_POST["individual"]; // --> Múltiple

    $population=$_POST["population"];
    $freq_distr=$_POST["freq_distr"];
    $fred_distr_value=$_POST["freq_distr_value"];
    $fred_distr_valueB=$_POST["freq_distr_valueB"];

    $seg_dup=$_POST["seg_dup"];
    $gene_symbol=$_POST["gene_symbol"];

    $species=$_POST["species"];
    $orientation=$_POST["orientation"];

    //$effect=$_POST["effect"];

    */

    /***** Added by: Sònia *****/

    $field=$_POST["field"];
    $count_field = count($field);
    // echo "field ($count_field): ";
    // print_r($field);
    // echo "<br><br>";

    $boolean=$_POST["boolean"];
    $count_boolean = count($boolean);
    //echo "boolean ($count_boolean): ";
    //print_r($boolean);
    //echo "<br><br>";

    $field_value=$_POST["field_value"];
    $count_field_value = count($field_value);
    // echo "field_value ($count_field_value): ";
    // print_r($field_value);
    // echo "<br><br>";

    $field_value2=$_POST["field_value2"];
    $count_field_value2 = count($field_value2);
    // echo "field_value2 ($count_field_value2): ";
    // print_r($field_value2);
    // echo "<br><br>";

    //Start arrays for SQL
    $size = array();
    $inv_status = array();
    $pred_name = array();
    $pred_study = array();
    $pred_method = array();
    $pred_ind = array();
    $val_study = array();
    $val_method = array();
    $val_status = array();
    $val_fosmids = array();
    $val_ind = array();
    $freq_pop = array();
    $seg_dup = array();
    $aff_gene = array();
    $inv_sp = array();

    $from = array("inversions","breakpoints");
    $where = array("(inversions.id=breakpoints.inv_id AND breakpoints.id = (
	    SELECT b2.id FROM breakpoints b2
	    WHERE b2.inv_id = inversions.id
	    ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.`id` DESC
	    LIMIT 1))");

   

    // Read and process the search field
    $search_field=$_POST["search_field"];
    if ($search_field == '') {
    $search_field=$_GET["search_field"];
    }

    $direct_query = FALSE;
    // Read and process the assembly --> If hg19, lift over coordinates and change $search_field
    $assembly=$_POST["assembly"];

    if (($assembly == "hg19") and preg_match("/^(chr\w+)\W+(\d+)\W+(\d+)$/i", $search_field, $matches)) {

	    $query_hg19_position = $matches[1].':'.$matches[2].'-'.$matches[3];
	    $TEMPFILE=uniqid('liftover_');
	    file_put_contents(dirname(__FILE__).'/liftOver/'.$TEMPFILE.'.in', $query_hg19_position);

	    $cmd=dirname(__FILE__).'/liftOver/liftOver.sh '.dirname(__FILE__).'/liftOver '.$TEMPFILE;
	    exec($cmd, $output, $errmsg);
	    #var_dump($output); echo "  ---  Error: ".$errmsg;echo $cmd;

	    $search_field = file_get_contents(dirname(__FILE__).'/liftOver/'.$TEMPFILE.'.out');
	    #rm tempfile
	    unlink(dirname(__FILE__).'/liftOver/'.$TEMPFILE.'.out');
	    unlink(dirname(__FILE__).'/liftOver/'.$TEMPFILE.'.in');

    }

    //echo "search field: $search_field <br><br>";

    if (($search_field == "enter position, inversion ID or gene symbol") or ($search_field == "")) {    // ignore field if left blank

	    // ignore $search_field
	
	    $where[] = "(LOWER(inversions.`status`) != 'withdrawn')";

    } else if (preg_match("/^(chr\w+):?(\d*)\W*(\d*)$/i", $search_field, $matches)) {   //chrX:1-200

    //	echo "cromosoma: $matches[1] <br>";
    //	echo "start_coord: $matches[2] <br>";
    //	echo "end_coord: $matches[3] <br><br>";
	
	    if (($matches[2] != "") and ($matches[3] != "")) {  //chrX:1-200
	
		    $where[] = "(inversions.chr='$matches[1]' AND ((inversions.range_start BETWEEN $matches[2] AND $matches[3]) OR (inversions.range_end BETWEEN $matches[2] AND $matches[3])))";
		
	    } else if ($matches[2] != "") {  //chrX:1-
	
		    $where[] = "(inversions.chr='$matches[1]' AND inversions.range_end >= $matches[2])";	
	
	    } else if ($matches[3] != "") {  //chrX:-200
	
		    $where[] = "(inversions.chr='$matches[1]' AND inversions.range_start <= $matches[3])";
	
	    } else {  //chrX
	
		    $where[] = "(inversions.chr='$matches[1]')";
	
	    }
	
	    $where[] = "(LOWER(inversions.`status`) != 'withdrawn')";

    } else if (preg_match("/^HsInv\d{4}$/i", $search_field, $matches)) {   //HsInv0001

    //	echo "Inversion id: $matches[0] <br><br>";

	    $where[] = "(inversions.name='$matches[0]')";
        $direct_query = TRUE;

    } else if (preg_match("/^\w+[pq]\d*\.?\d*$/i", $search_field, $matches)) {   //2p25.1

    //	echo "cyto_band: $matches[0] <br><br>";
	
	    // search coordinates associated to this band
	    $sql_cytoband=" SELECT chrom, MIN(chromStart) as chromStart, MAX(chromEnd) as chromEnd FROM cytoBand WHERE bandID LIKE '$matches[0]%';";
	
    //	echo $sql_cytoband;
    //	echo "cyto_band search: $sql_cytoband <br><br>";
	    $result_cytoband = mysql_query($sql_cytoband);
	
	    while ($row_cytoband = mysql_fetch_assoc($result_cytoband)) {
		    $cytoband_chrom = $row_cytoband['chrom'];
		    $cytoband_chromStart = $row_cytoband['chromStart'];
		    $cytoband_chromEnd = $row_cytoband['chromEnd'];
	    }
	
	    mysql_free_result($result_cytoband);	
	
	    if ($cytoband_chrom != "") {
    //		echo "cytoband cromosoma: $cytoband_chrom <br>";
    //		echo "cytoband start_coord: $cytoband_chromStart <br>";
    //		echo "cytoband end_coord: $cytoband_chromEnd <br><br>";	
	
		    $where[] = "(inversions.chr='$cytoband_chrom' AND ((inversions.range_start BETWEEN $cytoband_chromStart AND $cytoband_chromEnd) OR (inversions.range_end BETWEEN $cytoband_chromStart AND $cytoband_chromEnd)))";
	    }
	
	    $where[] = "(LOWER(inversions.`status`) != 'withdrawn')";

    } else {     // Gene 

	    $aff_gene[] = "HsRefSeqGenes.symbol='".$search_field."'";
	
	    $where[] = "(LOWER(inversions.`status`) != 'withdrawn')";

    }   

    // Add filters to SQL query
    foreach ($field as $i => $value) {
    
        if ($value == "size") {
    
    	    $size[] = "inversions.size".$boolean[$i].$field_value[$i];
    
        } else if ($value == "inv_status") {
    
	       $inv_status[] = "inversions.status".$boolean[$i]."'".$field_value[$i]."'";    
            
        } else if ($value == "pred_name") {

            $pred_name[] = "predictions.prediction_name".$boolean[$i]."'".$field_value[$i]."'";

        } else if ($value == "pred_study") {
    
            $pred_study[] = "predictions.research_name".$boolean[$i]."'".$field_value[$i]."'";
    
        } else if ($value == "pred_method") {

            $pred_method[] = "researchs.prediction_method".$boolean[$i]."'%".$field_value[$i]."%'";

        } else if ($value == "pred_ind") {

            $pred_ind[] = "individuals_detection.individuals_id".$boolean[$i].$field_value[$i];

        } else if ($value == "val_study") {
    
    	    $val_study[] = "validation.research_name".$boolean[$i]."'".$field_value[$i]."'";
    
        } else if ($value == "val_method") {
    
    	    $val_method[] = "validation.method".$boolean[$i]."'".$field_value[$i]."'";
    
        } else if ($value == "val_status") {
    
    	    $val_status[] = "validation.status".$boolean[$i]."'".$field_value[$i]."'";
    
        } else if (($value == "val_fosmids") && ($boolean[$i] == "yes")) {
    
    	    $val_fosmids[] = "yes";
    
        } else if ($value == "val_ind") {
    
    	    $val_ind[] = "individuals_detection.individuals_id".$boolean[$i].$field_value[$i];
    
        } else if ($value == "freq_pop") {
    
    	    $freq_pop[] = "(population_distribution.population_name='".$field_value2[$i]."'".
    			    " AND population_distribution.frequency".$boolean[$i].$field_value[$i].")";
    
        } else if (($value == "seg_dup") && ($boolean[$i] == "yes")) {
    
    	    $seg_dup[] = "yes";
    
        } else if ($value == "aff_gene") {
    
    	    $aff_gene[] = "HsRefSeqGenes.symbol".$boolean[$i]."'".$field_value[$i]."'";
    
        } else if ($value == "inv_sp") {
    
    	    $inv_sp[] = "inversions.ancestral_orientation='".$boolean[$i]."'";
    
        } else {  
    
        }
    
    }

    if (count($size)>0) {

	    $size_string = "(" . implode(" AND ", $size) . ")";
	    $where[] = $size_string;

    }

    if (count($inv_status)>0) {

	    $inv_status_string = "(" . implode(" OR ", $inv_status) . ")";
	    $where[] = $inv_status_string;

    }



    if ((count($pred_study)>0) || (count($pred_method)>0) || (count($pred_name)>0))  {

        $from[] = "predictions";
        $prediction_array = array();
        $prediction_array[] = "inversions.id=predictions.inv_id";
    
        if (count($pred_study)>0) {
            $pred_study_string = "(" . implode(" OR ", $pred_study) . ")";
            $prediction_array[] = $pred_study_string;
        }

    
        if (count($pred_method)>0) {
            $from[] = "researchs";
            $pred_method_string = "(researchs.name = predictions.research_name AND " . implode(" OR ", $pred_method) . ")";
            $prediction_array[] = $pred_method_string;
        }

        if (count($pred_name)>0) {
            $pred_name_string = "(" . implode(" OR ", $pred_name) . ")";
            $prediction_array[] = $pred_name_string;
        }

        $prediction_string =  " (" . implode(" AND ", $prediction_array) . ")";
        $where[] = $prediction_string;

    }

    if (count($pred_ind)>0) {

        $pred_ind_string = "(inversions.id=individuals_detection.inversions_id AND (" . implode(" OR ", $pred_ind) . ")  AND  (prediction_id != '' OR prediction_id IS NOT NULL) )";
        $where[] = $pred_ind_string;
        $from[] = "individuals_detection";

    }

    if ((count($val_study)>0) || (count($val_method)>0) || (count($val_status)>0)) {

	    $validation_array = array();
	
	    if (count($val_study)>0) {
		    $val_study_string = "(" . implode(" OR ", $val_study) . ")";
		    $validation_array[] = $val_study_string;
	    }
	    if (count($val_method)>0) {
		    $val_method_string = "(" . implode(" OR ", $val_method) . ")";
		    $validation_array[] = $val_method_string;
	    }
	    if (count($val_status)>0) {
		    $val_status_string = "(" . implode(" OR ", $val_status) . ")";
		    $validation_array[] = $val_status_string;
	    }
	
	    $validation_string = "(inversions.id=validation.inv_id AND (" . implode(" AND ", $validation_array) . "))";
	    $where[] = $validation_string;
	    $from[] = "validation";

    }

    if (count($val_fosmids)>0) {
	
	    $val_fosmids_string = "(inversions.id=fosmids_validation.inv_id)";
	    $where[] = $val_fosmids_string;
	    $from[] = "fosmids_validation";

    }

    if (count($val_ind)>0) {

        $val_ind_string = "(inversions.id=individuals_detection.inversions_id AND (" . implode(" OR ", $val_ind) . ")  AND  (validation_id != '' OR validation_id IS NOT NULL))";
        $where[] = $val_ind_string;
        $from[] = "individuals_detection";

    }

    if (count($freq_pop)>0) {

	    $freq_pop_string = "(inversions.id=population_distribution.inv_id AND (" . implode(" AND ", $freq_pop) . "))";
	    $where[] = $freq_pop_string;
	    $from[] = "population_distribution";

    }

    if ((count($seg_dup)>0) || (count($aff_gene)>0)) {    /*  FALTA  */

	    $breakpoints_array = array();
	
	    if (count($seg_dup)>0) {   
	
		    $seg_dup_string = "(breakpoints.id=SD_in_BP.BP_id)";
		    $breakpoints_array[] = $seg_dup_string;
		    $from[] = "SD_in_BP";

	    }

	    if (count($aff_gene)>0) {

		    $aff_gene_string = "(breakpoints.id=genomic_effect.bp_id AND genomic_effect.gene_id = HsRefSeqGenes.idHsRefSeqGenes AND (" . implode(" OR ", $aff_gene) . "))";
		    $breakpoints_array[] = $aff_gene_string;
		    $from[] = "genomic_effect";
		    $from[] = "HsRefSeqGenes";

	    }

    //	$breakpoints_string = "(inversions.id=breakpoints.inv_id AND breakpoints.id = (
    //	SELECT b2.id FROM breakpoints b2
    //	WHERE b2.inv_id = inversions.id
    //	ORDER BY FIELD(b2.definition_method, 'manual delimited', 'informatic delimited'), b2.`date` DESC
    //	LIMIT 1) AND (" . implode(" AND ", $breakpoints_array) . "))";
	    $breakpoints_string = "(" . implode(" AND ", $breakpoints_array) . ")";
	    $where[] = $breakpoints_string;
    //	$from[] = "breakpoints";

    }

    if (count($inv_sp)>0) {

	    $inv_sp_string = "(" . implode(" OR ", $inv_sp) . ")";
	    $where[] = $inv_sp_string;

    }

    $count_from = count($from);
    //echo "from ($count_from): ";
    //print_r($from);
    //echo "<br><br>";

    $count_where = count($where);
    //echo "where ($count_where): ";
    //print_r($where);
    //echo "<br><br>";


    $from_string = implode(", ", $from);
    $where_string = implode(" AND ", $where);


    // Connection to the database
    include_once('php/db_conexion.php');

    $select = "inversions.id,
	    inversions.name,
	    inversions.chr,
	    inversions.range_start,
	    inversions.range_end,
	    inversions.size ,
        inversions.status,
	    inversions.frequency_distribution,
	    breakpoints.genomic_effect,
	    breakpoints.bp1_start, 
	    breakpoints.bp1_end, 
	    breakpoints.bp2_start, 
	    breakpoints.bp2_end
	    ";
    // 	h.symbol
  $order = " ORDER BY FIELD (inversions.`status`, 'TRUE', 'ND', 'FILTERED OUT', 'FALSE'), inversions.chr, inversions.range_start";
  

   
    $sql=" SELECT DISTINCT $select FROM $from_string";
    if (count($where)>0) {
	    $sql .= " WHERE $where_string";
    }

    $sql .= $order;

    $sql .= ";";//LIMIT 0,10;";

    // echo $sql."<br /><br />";

    $result = mysql_query($sql);
    $count_result = mysql_num_rows($result);
    //---------------------------------------------------------------------------------------------

    sleep(1);
    $i = 1;

if ($direct_query == TRUE & $count_result == 1) {

    $row = mysql_fetch_array($result);
    header( "Location: report.php?q=".$row['id'] ); die;

}else{

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
    <div class="section-title TitleA">- <?php echo "<b>$count_result</b> inversions found<br/>"; ?></div>
	<div class='section-content'>
	
	    <!-- <?php echo "<b>$count_result</b> inversions found <br/><br/>";?> -->

	    <div id="results_table">
		    <table id="sort_table" width="100%">
		    <thead>
		      <tr>
			    <th class='title' width='10%'>Name <img src='css/img/sort.gif'></th>
			    <th class='title' width='23%'>Position (hg18) <img src='css/img/sort.gif'></th>
			    <th style='text-align:right' class='title' width='10%'>Estimated Inversion size (bp) <img src='css/img/sort.gif'></th>
                <th style='text-align:center' class='title' width='18%'>Status <img src='css/img/sort.gif'></th>
               <th class='title' width='10%'>Global frequency <img src='css/img/sort.gif'></th>
			    <th class='title' width='29%'>Functional effect <img src='css/img/sort.gif'></th>
		      </tr>
		    </thead>
		    <tbody>
		    <?php
			    while($row = mysql_fetch_array($result)){
			
				    if ($row['status'] != 'FALSE') {
				
					    if ($_SESSION["autentificado"]=='SI') {
					
						    $r_freq = mysql_query("SELECT inv_frequency('".$row['id']."','all','all','all', 'all') AS res_freq");
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
							    WHERE pd.inv_id = '".$row['id']."'
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

				    #Recalculate size & range
				    // $middle_bp1 = number_format(($row['bp1_start']+$row['bp1_end'])/2, 0, '.', '');
				    // $middle_bp2 = number_format(($row['bp2_start']+$row['bp2_end'])/2, 0, '.', '');
				    // $size = number_format(round($middle_bp2-$middle_bp1+1));
			
                     // if ($_SESSION["autentificado"]=='SI') {
                        $middle_bp1 = number_format(($row['bp1_start']+$row['bp1_end'])/2, 0, '.', '');
                        $middle_bp2 = number_format(($row['bp2_start']+$row['bp2_end'])/2, 0, '.', '');
                        $size = number_format(round($middle_bp2-$middle_bp1+1));
                     
                    // }else{
                        // $size = $row['size'];
                    // }


				    echo "<tr>";
				    echo "<td><a href=\"report.php?q=".$row['id']."\" target=\"_blank\" >".$row['name']."</a></td>";
				    //echo "<td><a href=\"report.php?q=".$row['id']."\">".$row['id']."</a></td>";
				    echo "<td>".$row['chr'].":".$row['bp1_start']."-".$row['bp2_end']."</td>";
				    echo "<td style='text-align:right'>".$size."</td>";
                    echo "<td style='text-align:center'>".$array_status[$row['status']]."</td>";
                    echo "<td>".$r_inv_freq."</td>";
				    echo "<td>".$array_effects[$row['genomic_effect']]."</td>";
				    echo "</tr>";
			    }
		    ?>
		    </tbody></table>

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
  }//
  mysql_close($con);
?>
