<?php
/******************************************************************************
	SELECT_REPORT.PHP

	Queries and retrieves the data for the specified inversion that is required for the report.php
*******************************************************************************/
?>


<?php

    session_start(); //Inicio la sesión

    $id=$_GET["q"];

    include_once('db_conexion.php');
    include_once('php/php_global_variables.php');

    #### Query Inversion Data   
        $sql_inv="SELECT i.name, i.chr, i.range_start, i.range_end, i.size, i.frequency_distribution, i.evo_origin, i.origin, i.status, i.comment, i.ancestral_orientation, i.age, i.comments_eh, i.complexity,
                (SELECT count(p.id) FROM predictions p WHERE p.inv_id='$id') as num_pred,
                (SELECT count(distinct research_name) FROM validation v WHERE v.inv_id='$id') as num_val,
                b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, b.GC, b.Stability, b.Mech, b.Flexibility, b.bp1_between, b.bp2_between, b.genomic_effect, b.definition_method, b.description, b.id as breakpoint_id, b.comments as breakpoint_comments, val.research_name AS studyname, r.year, r.pubMedID  
            FROM inversions i INNER JOIN breakpoints b ON b.id = (SELECT id FROM breakpoints b2 WHERE b2.inv_id=i.id
                ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC
                LIMIT 1) LEFT JOIN validation val ON b.id = val.bp_id 
                LEFT JOIN researchs r ON val.research_name = r.name 
            WHERE i.id ='$id';";
  
    

    $result_inv = mysql_query($sql_inv);
    $r= mysql_fetch_array($result_inv);

    // #Recalculate size & range
    $middle_bp1 = number_format(($r['bp1_start']+$r['bp1_end'])/2, 0, '.', '');
    $middle_bp2 = number_format(($r['bp2_start']+$r['bp2_end'])/2, 0, '.', '');
    $r['size'] = $middle_bp2-$middle_bp1+1;
    // $r['range_start'] = $r['bp1_start'];
    // $r['range_end'] = $r['bp2_end'];

    #Este codigo se podria eliminar cunado se actualice el campo ancestral_orientation en la tabla inversions
   $sql_inv_ancestral_orientation = "SELECT 1 n, orientation FROM inversions_in_species WHERE inversions_id ='$id' and result_value = 1;";
    $result_inv_ancestral_orientation = mysql_query($sql_inv_ancestral_orientation);
    $inv_ancestral_orientation= mysql_fetch_array($result_inv_ancestral_orientation);

    if ($inv_ancestral_orientation['n'] != 1) {
        $sql_inv_ancestral_orientation = "SELECT COUNT(DISTINCT orientation) n, orientation FROM inversions_in_species WHERE inversions_id = '$id' and orientation NOT IN ('polymorphic', 'deleted allele');";
        $result_inv_ancestral_orientation = mysql_query($sql_inv_ancestral_orientation);
        $inv_ancestral_orientation= mysql_fetch_array($result_inv_ancestral_orientation);
    }

    if ($inv_ancestral_orientation['n'] == 0) { $r['ancestral_orientation'] = "NA"; }
    elseif ($inv_ancestral_orientation['n'] == 1) { $r['ancestral_orientation']= ucfirst($inv_ancestral_orientation['orientation']); }
    else { $r['ancestral_orientation'] = "ND"; }

    //$r['ancestral_orientation']=str_replace('+','',$r['ancestral_orientation']);

    $inv_ancestral_orientation = $r['ancestral_orientation'];

    if ($r['pubMedID'] != "") {
        $r['studyname']="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$r['pubMedID']."' target='_blank'>".ucfirst($r['studyname'])."</a>";
    } else {
        $r['studyname']=ucfirst($r['studyname']);
    }

    $r_freq = mysql_query("SELECT inv_frequency('$id','all','all','all','all') AS res_freq");
	    $r_freq = mysql_fetch_array($r_freq);
	    $d_freq = explode(";", $r_freq['res_freq']); //->analyzed individuals;independent alleles; inverted freq; HWE
	    $r_inv_freq=$d_freq[2];
	    //$r_std_freq=1-$inv_freq;
	
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
	    WHERE pd.inv_id = '$id'
	    GROUP BY pd.population_name) allpopulations
	    GROUP BY region) allregions;");
	    $r_freq2 = mysql_fetch_array($r_freq2);
	    $r_inv_freq=$r_freq2[1];
    }

    if (($r_inv_freq != '') and ($r_inv_freq != 'NA')) {
	    $r_inv_freq = number_format($r_inv_freq,4);
    }


    ### predicciones (B) y (C)
    // Construccion de la imagen con las evidencias y la tabla de predicciones (B) y (C)
    $sql_img="SELECT (SELECT BP1s FROM predictions WHERE inv_id='$id' ORDER BY BP1s LIMIT 1) as inicio, 
		    (SELECT BP2e FROM predictions WHERE inv_id='$id' ORDER BY BP2e DESC LIMIT 1) as fin 
	    FROM predictions LIMIT 1;";
    // echo 'image '. $sql_img;
    $result_img=mysql_query($sql_img);
    $pos= mysql_fetch_array($result_img);
    $all_bp1s = ""; $all_bp1e = ""; $all_bp2s = ""; $all_bp2e = ""; $all_id  = ""; $all_name = "";
    $smallest_bp = 99999999999999999999;
    $greatest_bp = 0;

    //Se buscan los distintos estudios que predicen la inversion
    $sql_pred_study = "SELECT DISTINCT p.research_name, r.prediction_method, r.pubMedID, r.description
                       FROM predictions p INNER JOIN researchs r ON p.research_name=r.name
                         WHERE p.inv_id='$id' AND (p.status != 'FILTERED_liftover' OR p.status IS NULL) ORDER BY r.pubMedID DESC;"; 
    $result_pred_study = mysql_query($sql_pred_study);

    $sql_out_pred = "SELECT COUNT(*) FROM (
                            SELECT  p.research_name, r.prediction_method, r.pubMedID, r.description
                            FROM predictions p INNER JOIN researchs r ON p.research_name=r.name
                            WHERE p.inv_id='$id' AND (p.status = 'FILTERED_liftover' ) ORDER BY r.pubMedID DESC) AS badpred;";
 
    $result_out_pred = mysql_query($sql_out_pred);
    $bad_predictions = (int)mysql_fetch_array($result_out_pred)[0];
    if ($bad_predictions > 1) {
        $echo_predictions .= $bad_predictions." predictions associated to this inversion could not be converted from hg18 to hg38. Find more details at <a href=\"invfreeze/report.php?q=".$id."\" target=\"_blank\" >InvFEST legacy</a>.";
    } elseif ($bad_predictions == 1){
        $echo_predictions .= $bad_predictions." prediction associated to this inversion could not be converted from hg18 to hg38. Find more details at <a href=\"invfreeze/report.php?q=".$id."\" target=\"_blank\" >InvFEST legacy</a>.";
    }

    while($thisstudy = mysql_fetch_array($result_pred_study)) {
    
        //Creo la seccion
         # if ($db == "INVFEST-DB") {
            $sql_pred="SELECT p.id, concat(p.research_id,';',p.research_name) as p_id, p.chr, p.BP1s, p.BP1e, p.BP2s, p.BP2e, p.comments, p.support, p.research_name, p.accuracy, p.score1, p.score2, 
    	        r.prediction_method, r.pubMedID, r.description, r.individuals,p.prediction_name
    	        FROM predictions p INNER JOIN researchs r ON p.research_name=r.name
    	        WHERE p.inv_id='$id' AND p.research_name='".$thisstudy["research_name"]."';"; //ID y name van juntos!
         // }else{
         //    $sql_pred="SELECT p.id, concat(p.research_id,';',p.research_name) as p_id, p.chr, p.BP1s, p.BP1e, p.BP2s, p.BP2e, p.comments, p.support, p.research_name, p.accuracy, p.score1, p.score2, 
         //        r.prediction_method, r.pubMedID, r.description, r.individuals
         //        FROM predictions p INNER JOIN researchs r ON p.research_name=r.name
         //        WHERE p.inv_id='$id' AND p.research_name='".$thisstudy["research_name"]."';";
         //  }
        //$sql_pred="SELECT p.id, p.chr, p.BP1s, p.BP1e, p.BP2s, p.BP2e, p.comments, p.support_bp1, p.support_bp2, p.pred_name, p.accuracy FROM predictions p WHERE p.inv_id='$id' ORDER BY p.pred_name;";
        $result_pred = mysql_query($sql_pred);
    // echo 'prediction each'. $sql_pred;
        $echo_predictions.="<div class='report-section'>
			        <div class='section-title TitleA'>- ";
	    if ($thisstudy['pubMedID'] != "") {
            $echo_predictions.="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$thisstudy['pubMedID']."' target='_blank'>".$thisstudy['research_name']."</a>";
        } else {
            $echo_predictions.= $thisstudy['research_name'];
        }
	    $echo_predictions.="</div>
			    <div class='grlsection-content'>
			    <table width='100%'>";
			    if (($thisstudy['description'] != '') or ($_SESSION["autentificado"]=='SI')) {
				    $echo_predictions.="<tr><td class='title' width='18%'>Description</td><td colspan='3'>".ucfirst($thisstudy['description'])."</td></tr>";
			    }
				
		$echo_predictions.="<tr><td class='title' width='18%'>Method</td><td colspan='3'>".ucfirst($thisstudy['prediction_method'])."</td></tr>
                            <tr>";
		$counterPredStud=0;
				
        while($thisrow = mysql_fetch_array($result_pred)) {
            $counterPredStud++;
        	if ($counterPredStud>1) { $echo_predictions.="&nbsp;"; }        
            $sql_predFosmids="SELECT f.name
                                FROM predictions p
                                INNER JOIN fosmids_predictions fp ON (p.research_id=fp.predictions_id and p.research_name = fp.predictions_research_name )
		                        INNER JOIN fosmids f ON f.id=fp.fosmids_id
                                WHERE p.inv_id='$id' and p.id='".$thisrow["id"]."';";
	        $result_predFosmids=mysql_query($sql_predFosmids);
	        $fosmids_ar=array(); $fosmids='';
	        while($fosmidrow = mysql_fetch_array($result_predFosmids)){
		        array_push($fosmids_ar, $fosmidrow['name']);
	        }
	        $fosmids = implode(",", $fosmids_ar);
	        if ($thisrow['support'] == null) { $thisrow['support']=0; }

	        //Se buscan los individuos de la inversion y del estudio
	        $sql_predInd="SELECT ind.code
            FROM predictions p
	 	    INNER JOIN individuals_detection ind2 ON ind2.prediction_id=p.id 
            INNER JOIN individuals ind ON ind2.individuals_id=ind.id 
	        WHERE p.inv_id='$id' and p.id='".$thisrow["id"]."';";
	        $result_predInd=mysql_query($sql_predInd);
            $individuals_ar=array(); $individuals='';
	        while($indrow = mysql_fetch_array($result_predInd)){
		        array_push($individuals_ar, $indrow['code']);
	        }
	        $individuals = implode(",", $individuals_ar);

	        $numindividuals = count($individuals_ar);
	        if ($numindividuals === null) { $numindividuals=0; }
	    
            //Se buscan todos los individuos de un estudio para hacer la tabla (con ticks y crosses)
            $sql_resInd= "SELECT i.code 
		    FROM individuals i INNER JOIN individual_research ir ON i.id=ir.individual_id 
		    WHERE ir.research_name='".$thisrow['research_name']."' 
		    ORDER BY i.code;";
            $result_resInd=mysql_query($sql_resInd);
            $indResearch_ar=array();
            while($indResrow = mysql_fetch_array($result_resInd)) {
	            array_push($indResearch_ar, $indResrow['code']);
            }

            $change_format="";
            if ($thisrow['accuracy'] != NULL) { $change_format=" class='invalid_pred' "; }

            $echo_predictions.= "<table width='100%'".$change_format.">";

         
                $echo_predictions.="<tr><td class='title' width='18%'>Name</td><td colspan='3'>".$thisrow['prediction_name']."</td></tr>
                            <tr>";
         

           $echo_predictions.=" <tr><td class='title' width='18%'>Breakpoint 1</td><td>".$thisrow['chr'].":".$thisrow['BP1s']."-".$thisrow['BP1e']."</td>
                <td class='title' width='18%'>Breakpoint 2</td><td>".$thisrow['chr'].":".$thisrow['BP2s']."-".$thisrow['BP2e']."</td></tr>";
                
            if (($thisrow['score1'] != '') or ($thisrow['score2'] != '')) {
                
                if ($thisrow['score1'] == '') {
                    $score1_color='grey';
                    $thisrow['score1'] = 'NA';
                } elseif ($thisrow['score1'] <= -1.96) {
                    $score1_color='red';                
                } else {
                    $score1_color='green';
                }
                
                if ($thisrow['score2'] == '') {
                    $score2_color='grey';
                    $thisrow['score2'] = 'NA';
                } elseif ($thisrow['score2'] < 0.001) {
                    $score2_color='red';                
                } else {
                    $score2_color='green';
                }
                $echo_predictions.= "<tr><td class='title' width='18%'>D/C Score</td><td><font color='$score1_color'>".$thisrow['score1']."</font></td>
                    <td class='title' width='18%'>Disc. Support Prob.</td><td><font color='$score2_color'>".$thisrow['score2']."</font></td></tr>";
                
            }
                
            if (($thisrow['support'] != '') or ($_SESSION["autentificado"]=='SI')) {
                $echo_predictions.="<tr><td class='title' width='18%'>Support</td><td colspan='3'>";
	            if ($thisrow['support']>0) {
		            $echo_predictions.=$thisrow['support']." probes <a href='php/echo_fosmids.php?fos=".$fosmids."' ><img src='img/download.png' alt='Download' width='23' height='23'></a>";
	            }
	            $echo_predictions.="</td></tr>";
	        }
	           
		    $echo_predictions.="<tr><td class='title' width='18%'>Individuals</td><td colspan='3'>";
            //if ($numindividuals>0) {
            //	$echo_predictions.="<div class='right'><a href='php/echo_individuals.php?ind=".$individuals."' >Download ".$numindividuals." individuals</a></div>";
            //}
	        #$echo_predictions.="<br />";

	        //Subtabla con todos los individuos de un estudio (indResearch_ar)
	        //Si el individuo ha sido validado (individuals_ar), tendra un 'tick', si no ha sido validado tendra un 'cross'
            if(!empty($indResearch_ar)) {

            	
	            
	            if (count($indResearch_ar) <= 10 ){

	            	$echo_predictions.="<table ><tr align='center'>";
		            
		            foreach ($indResearch_ar as $value) {
	                    $echo_predictions.="<td class='title'>$value</td>";
	                }
	            	$echo_predictions.="<td style='border-color:#FFFFFF;'></td></tr><tr align='center'>";
		            foreach ($indResearch_ar as $value) {
		                if (in_array($value, $individuals_ar)) {
	                        $echo_predictions.="<td><img src='img/tick2.png' width='23' height='23'/></td>";
		                } else {
	                        $echo_predictions.="<td><img src='img/cross2.png' width='23' height='23'/></td>";
	                    }
		            }
		            $echo_predictions.="<td style='border-color:#FFFFFF;'>";
	                if ($numindividuals>0) {
                    
                       $echo_predictions.="&nbsp;&nbsp;
                     <a href='php/echo_individuals.php?pred=".$thisrow['p_id']."&invid=".$id ."' ><img src='img/download.png' alt='Download' width='23' height='23'></a>";
                      
	                }

	                $echo_predictions.="</td></tr></table>";

            	} else {

	            	// $echo_predictions.="<td style='border-color:#FFFFFF;'>";
	                if ($numindividuals>0) {
                       $echo_predictions.=count($individuals_ar)." out of ".count($indResearch_ar)." individuals &nbsp;&nbsp;
                        <a href='php/echo_individuals.php?pred=".$thisrow['p_id']."&invid=".$id ."' ><img src='img/download.png' alt='Download' width='23' height='23'></a>";
                      
                        }
	            }
            	
            	
	               
            }

	        $echo_predictions.="</td></tr>";
            if (($thisrow['accuracy'] != '') or ($thisrow['comments'] != '') or ($_SESSION["autentificado"]=='SI')) {
			    $echo_predictions.="<tr ><td class='title' width='18'>Comments</td><td id='comments_pred".$thisrow['p_id']."' colspan='3'><div  id='acc".$thisrow['p_id']."'>";
	            if ($thisrow['accuracy'] !=''||$thisrow['accuracy']!=NULL) {
	           	    $echo_predictions.=$thisrow['accuracy']."<br>";
                }
                $echo_predictions.="</div><div  id='DIVcomments_pred".$thisrow['p_id']."'>".$thisrow['comments']."</div>";
                if ($_SESSION["autentificado"]=='SI'){
        		    $echo_predictions.="<input type='button' class='right' value='Edit' onclick=\"updateTD('comments_pred','".$thisrow['p_id']."')\" />";
	                //thisrow['p_id'] ahora sera research_id y research_name juntos separados por ';'
                }
                $echo_predictions.="</td></tr>";
            }
            	
            $echo_predictions.="</table>";

            //Guardamos la informacion para crear la imagen
	        $all_bp1s .= $thisrow['BP1s'].":";		$all_bp1e .= $thisrow['BP1e'].":";
	        $all_bp2s .= $thisrow['BP2s'].":";		$all_bp2e .= $thisrow['BP2e'].":";
	        $all_id  .= $thisrow['id'].":";			$all_name .= $thisrow['research_name'].":";
	
	        if ($all_bp1s < $smallest_bp) { $smallest_bp = $all_bp1s; }
	        if ($all_bp2e > $greatest_bp) { $greatest_bp = $all_bp2e; }
        }
        $echo_predictions.="</tr>
                </table>
			    </div></div>";

        //Pongo lo que esta hasta ahora pero uniendo las tablas   
    
    }


    /*
    $sql_pred="SELECT p.id, concat(p.research_id,';',p.research_name) as p_id, p.chr, p.BP1s, p.BP1e, p.BP2s, p.BP2e, p.comments, p.support, p.research_name, p.accuracy,
	    r.prediction_method, r.pubMedID, r.description, r.individuals
	    FROM predictions p INNER JOIN researchs r ON p.research_name=r.name
	    WHERE p.inv_id='$id' 
	    ORDER BY p.research_name;"; //id y name van juntos!
    //$sql_pred="SELECT p.id, p.chr, p.BP1s, p.BP1e, p.BP2s, p.BP2e, p.comments, p.support_bp1, p.support_bp2, p.pred_name, p.accuracy FROM predictions p WHERE p.inv_id='$id' ORDER BY p.pred_name;";
    $result_pred = mysql_query($sql_pred);

    while($thisrow = mysql_fetch_array($result_pred)){
	    //Se buscan los fosmidos de la inversion y del estudio
	    $sql_predFosmids="SELECT f.name
		    FROM predictions p
			    INNER JOIN fosmids_predictions fp ON (p.research_id=fp.predictions_id and p.research_name = fp.predictions_research_name )
			    INNER JOIN fosmids f ON f.id=fp.fosmids_id
		    WHERE p.inv_id='$id' and p.id='".$thisrow["id"]."';";
	    $result_predFosmids=mysql_query($sql_predFosmids);
	    $fosmids_ar=array(); $fosmids='';
	    while($fosmidrow = mysql_fetch_array($result_predFosmids)){
		    array_push($fosmids_ar, $fosmidrow['name']);
	    }
	    $fosmids = implode(",", $fosmids_ar);
	    if ($thisrow['support'] == null){$thisrow['support']=0;}

	    //Se buscan los individuos de la inversion y del estudio
	    $sql_predInd="SELECT ind.code
		    FROM predictions p
			    INNER JOIN individuals_detection ind2 ON ind2.prediction_id=p.id 
			    INNER JOIN individuals ind ON ind2.individuals_id=ind.id 
		    WHERE p.inv_id='$id' and p.id='".$thisrow["id"]."';";
	    $result_predInd=mysql_query($sql_predInd);
	    $individuals_ar=array(); $individuals='';
	    while($indrow = mysql_fetch_array($result_predInd)){
		    array_push($individuals_ar, $indrow['code']);
	    }
	    $individuals = implode(",", $individuals_ar);
	    $numindividuals = count($individuals_ar);
	    if ($numindividuals === null){$numindividuals=0;}
	    //Se buscan todos los individuos de un estudio para hacer la tabla (con ticks y crosses)
	    $sql_resInd= "SELECT i.code 
			    FROM individuals i INNER JOIN individual_research ir ON i.id=ir.individual_id 
			    WHERE ir.research_name='".$thisrow['research_name']."' 
			    ORDER BY i.code;";
	    $result_resInd=mysql_query($sql_resInd);
	    $indResearch_ar=array();
	    while($indResrow = mysql_fetch_array($result_resInd)){
		    array_push($indResearch_ar, $indResrow['code']);
	    }

	    $change_format="";
	    if ($thisrow['accuracy'] != NULL) {$change_format=" class='invalid_pred' ";}

	    //Se crea la tabla de predicciones
	    $echo_predictions.="<div class='report-section'>
			    <div class='section-title TitleA'>- ";
	    if ($thisrow['pubMedID'] != "") {$echo_predictions.="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$thisrow['pubMedID']."' target='_blank'>".$thisrow['research_name']."</a>";}
	    else {$echo_predictions.= $thisrow['research_name'];}
	    $echo_predictions.="</div>
			    <div class='grlsection-content'>

			    <table width='100%'".$change_format.">
				    <tr><td class='title' width='20%'>Description</td><td>".$thisrow['description']."</td></tr>
				    <tr><td class='title' width='20%'>Method</td><td>".$thisrow['prediction_method']."</td></tr>
				    <tr><td class='title' width='20%'>Breakpoint 1</td><td>".$thisrow['chr'].":".$thisrow['BP1s']."-".$thisrow['BP1e']."</td></tr>
				    <tr><td class='title' width='20%'>Breakpoint 2</td><td>".$thisrow['chr'].":".$thisrow['BP2s']."-".$thisrow['BP2e']."</td></tr>
				    <tr><td class='title' width='20%'>Support</td><td>";
	    if ($thisrow['support']>0){
		    $echo_predictions.=$thisrow['support']." probes <div class='right'><a href='php/echo_fosmids.php?fos=".$fosmids."' >Download</a></div>";
	    }
	    $echo_predictions.="</td></tr>
				    <tr><td class='title' width='20%'>Individuals</td><td>";
        //if ($numindividuals>0){
        //	$echo_predictions.="<div class='right'><a href='php/echo_individuals.php?ind=".$individuals."' >Download ".$numindividuals." individuals</a></div>";
        //}
	    #$echo_predictions.="<br />";

	    //Subtabla con todos los individuos de un estudio (indResearch_ar)
	    //Si el individuo ha sido validado (individuals_ar), tendra un 'tick', si no ha sido validado tendra un 'cross'
	    if(!empty($indResearch_ar)){
		    $echo_predictions.="<table ><tr align='center'>";
		    foreach ($indResearch_ar as $value) {$echo_predictions.="<td class='title'>$value</td>";}
		    $echo_predictions.="<td></td></tr><tr align='center'>";
		    foreach ($indResearch_ar as $value) {
			    if (in_array($value, $individuals_ar)) {$echo_predictions.="<td><img src='img/tick2.png' width='23' height='23'/></td>";}
			    else{$echo_predictions.="<td><img src='img/cross2.png' width='23' height='23'/></td>";}
		    }
		    $echo_predictions.="<td>";
            if ($numindividuals>0){
		    $echo_predictions.="<div class='right'><a href='php/echo_individuals.php?ind=".$individuals."' > Download </a></div>";//".$numindividuals." individuals
            }
            $echo_predictions.="</td></tr></table>";
	    }
	    $echo_predictions.="</td></tr>
				    <tr ><td class='title' width='20'>Comments</td><td id='".$thisrow['p_id']."'><div  id='acc".$thisrow['p_id']."'>";
	    if ($thisrow['accuracy'] !=''||$thisrow['accuracy']!=NULL) {
		    $echo_predictions.=$thisrow['accuracy']."<br>";
	    }
	    $echo_predictions.="</div><div  id='comments_pred".$thisrow['p_id']."'>".$thisrow['comments']."</div>";
	    if ($_SESSION["autentificado"]=='SI'){
		    $echo_predictions.="<input type='button' class='right' value='Update' onclick=\"updateTD('comments_pred','".$thisrow['p_id']."')\" />";
	    //thisrow['p_id'] ahora sera research_id y research_name juntos separados por ';'
	    }
	    $echo_predictions.="</td></tr>
			    </table>
			    </div></div>";

	    //guardamos la informacion para crear la imagen
	    $all_bp1s .= $thisrow['BP1s'].":";		$all_bp1e .= $thisrow['BP1e'].":";
	    $all_bp2s .= $thisrow['BP2s'].":";		$all_bp2e .= $thisrow['BP2e'].":";
	    $all_id  .= $thisrow['id'].":";			$all_name .= $thisrow['research_name'].":";
    }
    */

    //Guardamos la informacion para crear la imagen
    $bp_bp1s = $r['bp1_start'].":"; 	$bp_bp1e = $r['bp1_end'].":"; 
    $bp_bp2s = $r['bp2_start'].":"; 	$bp_bp2e = $r['bp2_end'].":"; 
    $bp_id = "0".":";

    $all_bp1s = preg_replace('(:$)','',$all_bp1s);	$all_bp1e = preg_replace('(:$)','',$all_bp1e);
    $all_bp2s = preg_replace('(:$)','',$all_bp2s);	$all_bp2e = preg_replace('(:$)','',$all_bp2e);
    $all_id  = preg_replace('(:$)','',$all_id);	$all_name = preg_replace('(:$)','',$all_name);	

    $bp_bp1s = preg_replace('(:$)','',$bp_bp1s);	$bp_bp1e = preg_replace('(:$)','',$bp_bp1e);
    $bp_bp2s = preg_replace('(:$)','',$bp_bp2s);	$bp_bp2e = preg_replace('(:$)','',$bp_bp2e);
    $bp_id  = preg_replace('(:$)','',$bp_id);	

    $perlurl = "?id=".$id."&chr=".$r['chr'].
		        "&bp_bp1s=".$bp_bp1s."&bp_bp1e=".$bp_bp1e."&bp_bp2s=".$bp_bp2s."&bp_bp2e=".$bp_bp2e."&bp_id=".$bp_id.
		        "&all_bp1s=".$all_bp1s."&all_bp1e=".$all_bp1e."&all_bp2s=".$all_bp2s."&all_bp2e=".$all_bp2e."&all_id=".$all_id."&all_name=".$all_name;
		  
    if ($r['bp1_start'] < $smallest_bp) { $smallest_bp = $r['bp1_start']; }
    if ($r['bp2_end'] > $greatest_bp) { $greatest_bp = $r['bp2_end']; }

    $length_image = $greatest_bp - $smallest_bp;
    $start_image = number_format(($smallest_bp-($length_image*0.3)), 0, '.', '');
    $end_image = number_format(($greatest_bp+($length_image*0.3)), 0, '.', '');


    //### Apartado validaciones (D) field change r.validation_method

    // #$sql_val="SELECT v.id, GROUP_CONCAT(DISTINCT v.status SEPARATOR ' ;') status, GROUP_CONCAT(DISTINCT v.comment SEPARATOR ' ;') comment,
    // #	r.name, GROUP_CONCAT(DISTINCT v.method SEPARATOR ' ;') validation_method, r.pubMedID, r.description
    // #	FROM validation v INNER JOIN researchs r ON v.research_name=r.name
    // #	WHERE v.inv_id='$id'
    // #   GROUP BY r.name ORDER BY r.pubMedID DESC;";
    
    // $sql_val="SELECT DISTINCT r.name, r.validation_method, r.pubMedID, r.description, v.id, v.status, v.comment
			 //    FROM validation v INNER JOIN researchs r ON v.research_name=r.name
			 //    WHERE v.inv_id='$id' GROUP BY r.name ORDER BY r.pubMedID DESC;";

    // $result_val=mysql_query($sql_val);
    // $echo_validations='';

    // while($thisrow = mysql_fetch_array($result_val)) {
    
	   //  $echo_validations.="<div class='report-section'>
			 //    <div class='section-title TitleA'>- ";
	   //  if ($thisrow['pubMedID'] != "") {
    //         $echo_validations.="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$thisrow['pubMedID']."' target='_blank'>".$thisrow['name']."</a>";
    //     } else {
    //         $echo_validations.= $thisrow['name'];
    //     }
	   //  $echo_validations.="</div>
			 //    <div class='grlsection-content'>
			 //    <table width='100%'>";
			
			 //    if (($thisrow['description'] != '') or ($_SESSION["autentificado"]=='SI')) {
			 //        $echo_validations.="<tr><td class='title' width='20%'>Description</td><td>".ucfirst($thisrow['description'])."</td></tr><tr>";
			 //    }

    //     $sql_val_study ="SELECT DISTINCT r.name, r.pubMedID, r.description, v.id, v.method, v.status, v.comment
			 //        FROM validation v INNER JOIN researchs r ON v.research_name=r.name
			 //        WHERE v.inv_id='$id' AND r.name ='".$thisrow["name"]."' ORDER BY r.pubMedID DESC;";
    //     $result_val_study =mysql_query($sql_val_study);

    //         while($thisrow_study = mysql_fetch_array($result_val_study)) {

	   //          //Para el support se cuentan todos los individuos de la validacion y se separan en funcion del genotipo
    //             //$sql_valSupport="SELECT count(distinct id.individuals_id) as count, id.genotype 
    //                 //FROM individuals_detection id
    //                 //WHERE id.inversions_id='$id' and id.validation_id='".$thisrow_study["id"]."' and id.validation_research_name='".$thisrow_study["name"]."'
    //                 //GROUP BY id.genotype
    //                 //ORDER BY FIELD(id.genotype,'STD/STD','STD/INV','INV/STD','INV/INV','STD','INV','NA','ND');";
   
	   //          $sql_valSupport="SELECT count(distinct id.individuals_id) as count, IF(id.genotype = 'INV/STD', 'STD/INV', id.genotype) AS genotype2 
		  //           FROM individuals_detection id
		  //           WHERE id.inversions_id='$id' and id.validation_id='".$thisrow_study["id"]."' and id.validation_research_name='".$thisrow_study["name"]."'
		  //           GROUP BY genotype2
		  //           ORDER BY FIELD(genotype2,'STD/STD','STD/INV','INV/STD','INV/INV','STD','INV','NA','ND');";

	   //          $result_valSupport=mysql_query($sql_valSupport);
	   //          $valSupport='';$totalSupport=0;
	   //          //En funcion del genotipo, cambia el texto. REVISAR LOS POSIBLES GENOTIPOS!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	   //          //El fichero php/ajaxAdd_validation.php tiene este mismo bloque de codigo, revisar tambien!!!!!!!!!!!!!
	   //          while($supportrow = mysql_fetch_array($result_valSupport)) {
		  //           if ($totalSupport>0) { $valSupport.= '&nbsp;&nbsp;<b>-</b>&nbsp;&nbsp;'; }
    //                 //if ($supportrow['genotype'] == 'INV/INV'){$valSupport.=$supportrow['count'].' homozygote inverted individuals. ';}
    //                 //else if ($supportrow['genotype'] == 'STD/STD'){$valSupport.=$supportrow['count'].' homozygote standard individuals. ';}
    //                 //else if ($supportrow['genotype'] == 'STD/INV'||$supportrow['genotype'] == 'INV/STD'){$valSupport.=$supportrow['count'].' heterozygote individuals. ';}
    //                 //else if ($supportrow['genotype'] == 'INV'){$valSupport.=$supportrow['count'].' hemizygous inverted individuals. ';}
    //                 //else if ($supportrow['genotype'] == 'STD'){$valSupport.=$supportrow['count'].' hemizygous standard individuals. ';}
    //                 //else if ($supportrow['genotype'] == 'NA'){$valSupport.=$supportrow['count'].' not applicable individuals. ';}
    //                 //else if ($supportrow['genotype'] == 'ND'){$valSupport.=$supportrow['count'].' not determined individuals. ';}
    //                 //else {$valSupport.=$supportrow['count'].' '.$supportrow['genotype'].' individuals. ';}
		  //           $valSupport.=$supportrow['count'].' '.$supportrow['genotype2'];
		  //           $totalSupport+=$supportrow['count'];
	   //          }
    //             //if ($valSupport==''){$valSupport='0 individuals';}
	   //          if ($totalSupport!=''||$totalSupport!=0) { $totalSupport.=' individuals'; }
	   //          else { $totalSupport=''; }

    //             // Mirar quants són de HapMap, etc.
    //             if ($totalSupport != '') {
    //                 $sql_fromHapMap="SELECT bt.panel, COUNT(bt.id) AS countpanel
	   //                  FROM 
	   //                  (
	   //                  SELECT id.id, (
	   //                  CASE
	   //                     WHEN i.panel LIKE '%HapMap%' OR i.panel LIKE '%1000 Genomes Project%' THEN 'HapMap/1000GP'
	   //                     WHEN i.panel LIKE '%HGDP%' THEN 'HGDP'
	   //                     WHEN i.panel LIKE 'unknown' THEN 'unknown'
	   //                     ELSE 'Other'
	   //                  END) AS panel
	   //                  FROM individuals i, individuals_detection id
	   //                  WHERE i.id=id.individuals_id AND id.inversions_id='$id' AND id.validation_id='".$thisrow_study["id"]."' 
	   //                  AND id.validation_research_name='".$thisrow_study["name"]."' 
	   //                  ) bt
	   //                  GROUP BY bt.panel
	   //                  ORDER BY FIELD(bt.panel,'HapMap/1000GP','HGDP','Other','unknown');";
    //                     $result_fromHapMap=mysql_query($sql_fromHapMap);
    //                     $writePanel='';
    //                     while($getresult_fromHapMap = mysql_fetch_array($result_fromHapMap)){
    //  	                    $writePanel.=$getresult_fromHapMap['countpanel'].' '.$getresult_fromHapMap['panel'].', ';
    //                     }
    //                 $writePanel = substr_replace($writePanel ,"",-2);
    //             }

    //             // O el soprte de fosmidos secuenciados en caso de validacion por secuencias
    //             $sql_fosm_valSupport="SELECT GROUP_CONCAT( DISTINCT CONCAT_WS(': ',fv.fosmids_name, fv.result) SEPARATOR '. ') fosm_resul , GROUP_CONCAT( DISTINCT CONCAT_WS('; ',v.comment, CONCAT_WS(': ', fv.fosmids_name, fv.comments)) SEPARATOR '. ') fosm_commet
    //                 FROM inversions i 
    //                     JOIN  validation v ON (i.id = v.inv_id and v.method = 'Fosmid sequence analysis***') 
    //                     JOIN fosmids_validation fv ON (v.id = fv.validation_id)
    //                 WHERE v.inv_id='$id' and id.validation_id='".$thisrow_study["id"]."' and v.research_name = '".$thisrow_study["name"]."'
    //                 GROUP BY v.research_name";
    //              $result_fosm_valSupport=mysql_query($sql_fosm_valSupport);
    //              $fosm_supportrow = mysql_fetch_array($result_fosm_valSupport);
     
    //              // O freqüències sense genotips
    //              $getresult_nogenotypes='';
    //              $sql_nogenotypes = "SELECT SUM(pd.individuals) AS individuals 
    //                 FROM population_distribution pd
    //                 WHERE pd.inv_id='$id' AND pd.validation_id='".$thisrow_study["id"]."' AND pd.validation_research_name='".$thisrow_study["name"]."';";
    //              $result_nogenotypes=mysql_query($sql_nogenotypes);
    //              $getresult_nogenotypes = mysql_fetch_array($result_nogenotypes);

    //  		        #$echo_validations.="<tr><td width='20%'></td><td></td></tr>";
    //  		        $echo_validations.="&nbsp;";

			 //        $echo_validations.="<table width='100%'><tr><td class='title' width='20%'>Method</td><td>".ucfirst($thisrow_study['method'])."</td></tr>
			 //        <tr><td class='title' width='20%'>Status</td><td>".$array_status[$thisrow_study['status']]."</td></tr>";
			
			 //        // Here support/genotypes
			 //        if (($totalSupport != '') or ($valSupport != '') or ($fosm_supportrow['fosm_resul'] != '') or ($getresult_nogenotypes['individuals'] != '') or ($_SESSION["autentificado"]=='SI')) {
		   
			 //            $echo_validations.="<tr><td class='title' width='20%'>Genotyping</td><td>";
			
			 //            if ($totalSupport != '') { $echo_validations.= "$totalSupport:  $writePanel"; } 
			 //            else if ($getresult_nogenotypes['individuals']>0) { $echo_validations.=$getresult_nogenotypes['individuals']." individuals"; }
			 //            else {}
			  
 			//             if ($valSupport != '') { $echo_validations.="<br/>$valSupport &nbsp;<a href='php/echo_individualsVal.php?id=".$id."&val=".$thisrow_study['name']."&valid=".$thisrow_study['id'] ."' ><img src='img/download.png' alt='Download' width='23' height='23'></a>"; }
			 //            else if ($getresult_nogenotypes['individuals']>0) { $echo_validations.= " (No genotypes available)"; }
			 //            else {}
 			
			 //            if ($fosm_supportrow['fosm_resul']) { $echo_validations.= " (Fosmids: ".$fosm_supportrow['fosm_resul'].")"; }
    //       		        $echo_validations .="</td></tr>";
			 //        }
			 //        // End support/genotypes
			
			 //        if (($thisrow_study['comment'] != '') or ($fosm_supportrow['fosm_commet'] != '') or ($_SESSION["autentificado"]=='SI')) {
	   //                  $echo_validations .="<tr><td class='title' width='20%'>Comment</td><td>".$thisrow_study['comment'];
    //                     #isset($fosm_supportrow['fosm_commet']) ? $echo_validations.= $fosm_supportrow['fosm_commet']."</td></tr></table></div></div>" : $echo_validations.= "</td></tr></table></div></div>";
    //                     if ($fosm_supportrow['fosm_commet']) {
    //                         $echo_validations.= $fosm_supportrow['fosm_commet']."</td></tr>";
    //                     } else {
    //                         $echo_validations.= "</td></tr>";
    //                     }
 			//         }
    //             $echo_validations.="</table>";
    //         }
    //     $echo_validations.= "</tr></table></div></div>";
    //  }

    //### Frequency
    /*
    //OLD:
    $sql_freq="SELECT p.region, pd.population_name, pd.frequency
	    FROM population p
	    INNER JOIN population_distribution pd ON p.name=pd.population_name
	    WHERE pd.inv_id ='$id';";
    */

 $sql_freq="(SELECT distinct p.region AS region, ind.population AS population_name, CONCAT(p.id, ';', p.sampling, ' (', v.research_name, ')') AS description
        FROM validation v 
            INNER JOIN individuals_detection ind2 ON ind2.validation_id=v.id
            INNER JOIN individuals ind ON ind2.individuals_id=ind.id 
            INNER JOIN population p ON ind.population_id=p.id
        WHERE v.inv_id='$id')
    
        UNION ALL
    
        (SELECT   p.region , pd.population_name, CONCAT( pd.population_id,  ';', p.sampling, ' (', pd.validation_research_name, ')') FROM population_distribution pd, population p 
        WHERE inv_id='$id' AND pd.population_id=p.id)
    
        ORDER BY  FIELD (region, 'Africa (AFR)', 'Middle East/North Africa (MENA) ','Europe (EUR)', 'Central Asia (CAS)', 'South Asia (SAS)', 'East Asia (EAS)', 'Oceania (OCE)', 'America - Admixed (AMR)', 'America - Natives (AMN)', 'Unknown'), population_name, description;";

    $result_freq=mysql_query($sql_freq);
    $info= array();
    
    while ($thisrow = mysql_fetch_array($result_freq)) {
        //if ($thisrow["population"] != 'unknown') {
        $info[$thisrow['region']][$thisrow['population_name']][$thisrow['description']]=$thisrow['description'];
        //}
    }


    ////////////////////////////////////////////////////////////////////////////////////
    /*
    $sql_freq="SELECT distinct ind.population, v.research_name, p.region 
	    FROM validation v 
		    INNER JOIN individuals_detection ind2 ON ind2.validation_id=v.id
		    INNER JOIN individuals ind ON ind2.individuals_id=ind.id 
		    INNER JOIN population p ON ind.population=p.name 
	    WHERE v.inv_id='$id' 
	    ORDER BY p.region, ind.population, v.research_name;";

    $result_freq=mysql_query($sql_freq);
    $info= array();

    while ($thisrow = mysql_fetch_array($result_freq)) {
    */
	    /*
        funcion inv_frequencyindependent in
	        inv_id_val INT, 
	        population_val VARCHAR(255),  -> puede ser all
	        region_val VARCHAR(255),  -> puede ser all NUNCA buscare all
	        study_val VARCHAR(255)) -> puede ser all
	    */
    /* resultados para inv_id=77
    +------------+---------------+---------+
    | population | research_name | region  |
    +------------+---------------+---------+
    | YRI        | genotyping    | Africa  |
    | YRI        | Korbel        | Africa  | //Resultado insertado expresamente para probar que haya >1 estudio
    | CHB        | genotyping    | Asia    |
    | JPT        | genotyping    | Asia    |
    | CEU        | genotyping    | Europe  |
    | unknown    | genotyping    | unknown |
    +------------+---------------+---------+
    */
	    //Buscare cada linea independientemente (excepto unknown)
	    //Tambien buscare all studies para una poblacion y region
	    //Tambien buscare all poblaciones para una region (sera all studies o tiene sentido alguna otra combinacion????????????????????)
    /*
	    if ($thisrow["population"] != 'unknown') {
            $info[$thisrow['region']][$thisrow['population']][$thisrow['research_name']]=$thisrow['research_name'];
            //echo $thisrow['region']." - ".$thisrow['population']." - ".$thisrow['research_name']."<br>";
	    }
    }

    // Other data without genotypes
    $sql_freq2="SELECT pd.*, p.region FROM population_distribution pd, population p 
	    WHERE inv_id='$id' AND pd.population_name=p.name;";

    $result_freq2=mysql_query($sql_freq2);
    while ($thisrow = mysql_fetch_array($result_freq2)){
	    if ($thisrow["population"] != 'unknown') {
            $info[$thisrow['region']][$thisrow['population_name']][$thisrow['validation_research_name']]=$thisrow['validation_research_name'];
	    }
    }
    */
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if ($r['chr'] == 'chrY') { $MultSample=1; }
    else                     { $MultSample=2; }

    // 2016/04 START - Graphs in map modification: frequency section reorganization
    $NewGraphs = "<div class='report-section'>
        
        <!-- Geochart (map) table -->
        <table cellpadding='10' style=\"width:100%; borders:0;\">
            <tr><td>
                &nbsp;&nbsp;
                <select id='typeChart' name='typeChart' onchange=\"isCanvasSupported('divMap')\">
                    <option value='one' selected='selected'>Populations aggregated by origin</option>
                    <option value='all'>Separated charts for each population</option>
				</select>
                            
                &nbsp;&nbsp;
                <input type='button' value='Refresh' onclick=\"isCanvasSupported('divMap')\" />
				<input type='button' value='Clear' onclick=\"clearMap('divMap')\" />
                <input type='button' id='btn_download' value='&#x21D3; PNG' onclick=\"mapDownload()\" disabled />
                            
                <br/><br/>
                <!-- Resolution slidebar: type = 'hidden' >> type = 'range' -->
                <input type='hidden' name='Resolution' id='mapResolution' value='700'
                    max='950' min='450' step='1' onchange=\"isCanvasSupported('divMap')\" disabled>

                <!-- Generate the frequency map -->
                <!-- <div id=\"divMap\" style=\"margin:auto; width: 950px; height: 603px; display: none;\"></div> -->
                <div id=\"divMap\" style=\"margin:auto; width: 950px; height: 603px;\"></div>

            </td></tr>
        </table>
    ";

    foreach ($info as $region => $value) { // Para cada region
 
	    $result_freq = mysql_query("SELECT inv_frequency('$id','all','all','$region','all') AS res_freq");
	    $results_freq = mysql_fetch_array($result_freq);
	    
	    $download_genotypes='';
	    if ($results_freq['res_freq'] == '') {
		    $result_freq = mysql_query("SELECT CONCAT(IFNULL(population_distribution.individuals,'NA'), 
                                                ';', IFNULL(population_distribution.inverted_alleles,'NA'), 
                                                ';', IFNULL(population_distribution.inv_frequency,'NA'), ';NA;NA')
                                                 AS res_freq 
                                                 FROM population_distribution, population 
                                                 WHERE population_distribution.population_name=population.name 
                                                        AND population_distribution.inv_id='$id' 
                                                        AND population.region='$region' 
                                                ORDER BY population_distribution.individuals DESC LIMIT 1;");
		    $results_freq = mysql_fetch_array($result_freq);
	    } else {
		    $download_genotypes="
                <a href='php/echo_individualsVal.php?id=".$id."&region=".$region."' >
                    <img class = 'custom_icon' 
                        src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAABLklEQVRoge3WsY3CQBCF4e3BO3ZABQTUQEAPBAgR7SwpPRAQUAMBPRAQWNqZDQmogYAuCLjIdzodMrbP8mjFfNLk75cs28YopXoFSM+6k973lgZISz5ASUv+EdIAackHKGnJP0IaIE0DpGmAtCQCLIb9u6Ftz2LYDxaQu7IApGOPAcfclcVgAcYYU3ieAIbz/8eHc+F5Muj474h1nIGna+fxnq7FOs5Exles5wUg3TsE3K3nhej4inVxA0iPFuMf1sWN9O5fwIVd4wAXdtJ7/xitygyQDg0CDqNVmUnvfSlzPAYMp5o3zilzPJbeWctinALS5UXAxWKcSu9rBJDnFun285WlGyDPpXe1kruwrAJyF5bSezoB5C0gb6V3KNVFj7/KvZwGSN/nBSj1Ib4Az3i4AP+OviMAAAAASUVORK5CYII=' 
                        title='Download individuals' width='14pt' height='14pt'>
                </a>";
	    }
	
	    $data_freq = explode(";", $results_freq['res_freq']); //->analyzed individuals;independent alleles; inverted freq; HWE

	    $anal_ind=$data_freq[0];
	    $inv_alleles = $data_freq[1];
	    $inv_freq=$data_freq[2];
	    $std_freq=1-$inv_freq;
	    $hwe=$data_freq[3];
	    $noninv_alleles = $data_freq[4];
	
          $hwe= preg_replace('/chi\-square = .*, p\-value/', '',$hwe);

	    if ($inv_alleles=='NA' && $noninv_alleles=='NA') {
	
		    if ($anal_ind=='NA') {
			    //No es pot calcular
		    } else {
                // Vull saber chromosoma
			    if ($r['chr']=='chrY') {
				    $inv_alleles = number_format($inv_freq*$anal_ind, 0, '.', '');
				    $noninv_alleles = number_format($std_freq*$anal_ind, 0, '.', '');
			    } elseif ($r['chr']!='chrX') {
				    $inv_alleles = number_format($inv_freq*$anal_ind*2, 0, '.', '');
				    $noninv_alleles = number_format($std_freq*$anal_ind*2, 0, '.', '');
			    } else {
				    //No es pot calcular
			    }
		    }
		
	    } elseif ($inv_alleles!='NA') {
            
            // 2016/04 START - Graphs in map modification: adds a condition when $inv_alleles = 0
            if ($inv_alleles != 0) {
                // Original line:
		        $noninv_alleles = number_format($std_freq*$inv_alleles/$inv_freq, 0, '.', '');

            } else {
                if ($anal_ind == 'NA') {
			        //No es pot calcular
		        } else {
			        if ($r['chr'] == 'chrY') {
				        $noninv_alleles = number_format($std_freq*$anal_ind, 0, '.', '');
			        } elseif ($r['chr'] != 'chrX') {
				        $noninv_alleles = number_format($std_freq*$anal_ind*2, 0, '.', '');
			        } else {
				        //No es pot calcular
			        }
		        }
            }
            // 2016/04 END - Graphs in map modification: adds a condition when $inv_alleles = 0

	    } else {
		    //No hauria de passar mai
	    }
	    
        $indep_alleles = $inv_alleles;
        //$std_freq='0.5';
        //$inv_freq='0.5';
        
	    // if ($region=='Asia' || $region=='America' || $region=='Oceania' || $region=='Africa')   { $addPrefix = 'n'; }
	    // elseif ($region=='Europe')  { $addPrefix = 'an'; }
	    // else                        { $addPrefix = ''; }
	    
	    $echo_frequency.="
            <div class='report-section'>
		    <div class='section-title TitleA'>+ ".ucfirst($region)."$addPrefix </div>
            <div class='hidden'>
		        <div class='grlsection-content'>
		";

	    // HERE I PREPARE GRAPHS FOR CONTINENTS...
	    $regionSimple = preg_replace('/\W+/', '', $region); 
	    if ($noninv_alleles == "ND") {
		    $noninv_alleles=$std_freq;
		    $inv_alleles=$inv_freq;
	    }

	    
        // Attach the table header for this region's populations
		$echo_frequency.="
			<table class='popTable' id='popTable$regionSimple'>
				<tr>
					<th  >Population&nbsp&nbsp</th>
					<th width='30%' >Sample and Study</th>
					<th width='11%'>Independent individuals</th>
					<th width='10%'>Inverted alleles</th>
					<th width='10%'>Standard frequency</th>
					<th width='10%'>Inverted frequency</th>
					<th width='10%'>HWE p-value</th>
				</tr>
		";

         $echo_frequency_aftertable="
                        <tr id='tableStudy$region'>
                            <th >$region $download_genotypes 
                              <input type='checkbox' class='regChkbox' value='$regionSimple' checked
                            title='Show/Hide this information in the frequency map'>
                        <!-- La següent línia ¿? -->
                        <input type='hidden' name='NewGraphs_".$regionSimple."[]' value='0;0;hidden'  class='$regionSimple' checked>

                            </th>
                            <th width='30%'> $region summary </th>
                            <th width='11%'>".$anal_ind."</th>
                            <th width='10%'>".$indep_alleles."</th>
                            <th width='10%'>".number_format($std_freq,4)."</th>
                            <th width='10%'>".number_format($inv_freq,4)."</th>
                            <th width='10%'>".$hwe."</th>
                        </tr>
            ";




                 // <th width='2%'>Hardy-Weinberg eq.</th>
        foreach ($info[$region] as $population =>$study_ar) {
		    $num_studies=count($study_ar);
		    if ($num_studies>1) { 
			    $result_freq = mysql_query("SELECT inv_frequency('$id','$population','all','$region','all') AS res_freq");
		    } else {

                $data_lines = explode(";", key($study_ar)); // id; description (paper)
                $popid=$data_lines[0]; # population id
                $desc = $data_lines[1]; # description (paper) <- menu unit
                $study_test= preg_replace('/.*\(/', '',$desc);
                $study =  preg_replace('/\)/', '', $study_test); # paper only
                
                if ($study == 'in preparation'){
                    $study = "Martinez-Fundichely et al. 2014 (in preparation)";
                    // $study_query = "Martinez\-Fundichely et al\. 2014 \(in preparation\)";
                }


			    $result_freq = mysql_query("SELECT inv_frequency('$id','$population','$popid','$region','$study') AS res_freq");
		    }
		    $results_freq = mysql_fetch_array($result_freq);
		
		    // Si no he trobat dades amb genotips, busco sense genotips. En aquest cas només mostro l'estudi amb més individus analitzats, ja que no puc sumar-los perquè no sé si són els mateixos individus
		    $download_genotypes='';
		    if (($results_freq['res_freq'] == '') || ($results_freq['res_freq'] == 'NA;NA;NA;NA;NA')) {
			    $result_freq = mysql_query("SELECT CONCAT(IFNULL(individuals,'NA'), 
                                            ';', IFNULL(inverted_alleles,'NA'), 
                                            ';', IFNULL(inv_frequency,'NA'), ';NA;NA') 
                                            AS res_freq 
                                            FROM population_distribution 
                                            WHERE inv_id='$id' AND population_name='$population' 
                                            ORDER BY individuals DESC LIMIT 1;");
			    $results_freq = mysql_fetch_array($result_freq);
		    } else {
			    $download_genotypes="
                    <a href='php/echo_individualsVal.php?id=".$id."&pop=".$population."' >
                        <img class = 'custom_icon' 
                            src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAABLklEQVRoge3WsY3CQBCF4e3BO3ZABQTUQEAPBAgR7SwpPRAQUAMBPRAQWNqZDQmogYAuCLjIdzodMrbP8mjFfNLk75cs28YopXoFSM+6k973lgZISz5ASUv+EdIAackHKGnJP0IaIE0DpGmAtCQCLIb9u6Ftz2LYDxaQu7IApGOPAcfclcVgAcYYU3ieAIbz/8eHc+F5Muj474h1nIGna+fxnq7FOs5Exles5wUg3TsE3K3nhej4inVxA0iPFuMf1sWN9O5fwIVd4wAXdtJ7/xitygyQDg0CDqNVmUnvfSlzPAYMp5o3zilzPJbeWctinALS5UXAxWKcSu9rBJDnFun285WlGyDPpXe1kruwrAJyF5bSezoB5C0gb6V3KNVFj7/KvZwGSN/nBSj1Ib4Az3i4AP+OviMAAAAASUVORK5CYII=' 
                            title='Download individuals' width='12pt' height='12pt'>
                    </a>";
		    }
		    
		    $data_freq = explode(";", $results_freq['res_freq']); //->analyzed individuals;independent alleles; inverted freq; HWE
		    
		    $anal_ind=$data_freq[0];
		    $inv_alleles = $data_freq[1];
		    $inv_freq=$data_freq[2];
		    $std_freq=1-$inv_freq;
		    $hwe=$data_freq[3];
		    $noninv_alleles = $data_freq[4];
	        
            $hwe= preg_replace('/chi\-square = .*, p\-value/', '',$hwe);
            // $hwe =  substr(strrchr($hwe, "e"), 1);
            // echo "$hwe";


		    if ($inv_alleles=='NA' && $noninv_alleles=='NA') {
	
			    if ($anal_ind=='NA') {
				    //No es pot calcular
			    } else {
				    if ($r['chr']=='chrY') {
					    $inv_alleles = number_format($inv_freq*$anal_ind, 0, '.', '');
					    $noninv_alleles = number_format($std_freq*$anal_ind, 0, '.', '');
				    } elseif ($r['chr']!='chrX') {
					    $inv_alleles = number_format($inv_freq*$anal_ind*2, 0, '.', '');
					    $noninv_alleles = number_format($std_freq*$anal_ind*2, 0, '.', '');
				    } else {
					    //No es pot calcular
				    }
			    }

		    } elseif ($inv_alleles!='NA') {
                // 2016/04 START - Graphs in map modification: adds a condition when $inv_alleles = 0
                if ($inv_alleles != 0) {
                    // 2016/04 Original line:
			        $noninv_alleles = number_format($std_freq*$inv_alleles/$inv_freq, 0, '.', '');
                
                } else {
                    if ($anal_ind == 'NA') {
			            //No es pot calcular
		            } else {
			            if ($r['chr'] == 'chrY') {
				            $noninv_alleles = number_format($std_freq*$anal_ind, 0, '.', '');
			            } elseif ($r['chr'] != 'chrX') {
				            $noninv_alleles = number_format($std_freq*$anal_ind*2, 0, '.', '');
			            } else {
				            //No es pot calcular
			            }
		            }

                }
                // 2016/04 END - Graphs in map modification: adds a condition when $inv_alleles = 0

		    } else {
			    //No hauria de passar mai
		    }

		    $indep_alleles=$inv_alleles;
		    
            // Check longitude and latitude in the populations' table
            $query_coord = "SELECT longitude, latitude FROM population WHERE name = '$population' AND region = '$region' LIMIT 1";
            $result_coord = mysql_fetch_object(mysql_query($query_coord));
            $lng = $result_coord->longitude;
            $lat = $result_coord->latitude;
            
		    $echo_frequency.="
						<tr id='tableStudy$population'>
							<td class='popName'>".ucfirst($population)." $download_genotypes
                                <input type='checkbox' name='NewGraphs_".$regionSimple."[]' value='$noninv_alleles;$inv_alleles;$population;$std_freq;$inv_freq;$lng;$lat' class='$regionSimple'
                                    checked title='Show/Hide this information in the frequency map'>
								<!-- Next line is useless ¿? Make it commented by now -->
                                <!-- <div class='grlsection-;$population'></div> -->
							</td>
							<td>";
								if ($num_studies>1) {       //Si hay >1 estudio, creamos un desplegable
                                    $echo_frequency.="All samples and studies"; 
									$echo_frequency.="
									</br><select id='selectStudy' style='width: 180px' onchange=\"changeFreqs(this,'tableStudy$population','$id','$population','$region')\"> 
                                            <option disabled selected value>Select sample/study</option>
                                            <option value='all'>All samples and studies</option>";
										foreach($info[$region][$population] as $description => $value) {
                                            $data_lines = explode(";", $description); // id; description (paper)
                                            $popid=$data_lines[0]; # population id
                                            $desc = $data_lines[1]; # description
                                            $echo_frequency.="
                                            <option value='$desc'>$desc</option>";
										}
										$echo_frequency.="
									</select>";
								} else {                    //Si solo hay 1 estudio, lo mostramos tal cual
									$echo_frequency.=$desc;
								}
			$echo_frequency.="
							</td>
							<td>".$anal_ind."</td>
							<td>".$indep_alleles."</td>
							<td>".number_format($std_freq,4)."</td>
							<td>".number_format($inv_freq,4)."</td>
							<td>".$hwe."</td>
						</tr>
			";
			
		    // HERE I PREPARE GRAPHS FOR POPULATIONS...
		    if ($noninv_alleles == "NA") {
			    $noninv_alleles=$std_freq;
			    $inv_alleles=$inv_freq;
		    }
            
	    }
	
	    // 	HERE I FINISH THE TABLE
	    $echo_frequency .= "</table>
                    </br>
                    <table class='popTable' id='popTable$regionSimple'>
                    ".$echo_frequency_aftertable."
                    </table>
                </div>
            </div>
		    </div>
        ";
    }

    

    // 2016/04 END - Graphs in map modification: frequency section reorganization

    /*
    echo "<PRE>";
    print_r($info);
    echo "</PRE>";
    */

    // Add new frequency without genotypes
    $sql_popul="SELECT DISTINCT name, region FROM population;";
    $result_popul=mysql_query($sql_popul);
    $fng_population='';
    $checkpoint_fngpopulation = array();
    while ($thisrow = mysql_fetch_array($result_popul)) {
	    $fng_population.='<option value="'.$thisrow['name'].'">'.$thisrow['name'].' ('.$thisrow['region'].')</option>';
        $checkpoint_fngpopulation[] = $thisrow['name'].' ('.$thisrow['region'].')';
    }


    //###Breakpoints
    //los campos de esta seccion ya estan incluidos en la busqueda (A)
    //Hay que buscar informacion de las duplicaciones segmentales nada mas
    $sql_seqFeat="SELECT sd.chrom, sd.chromStart, sd.chromEnd, sd.otherChrom, sd.otherStart, sd.otherEnd, sd.fracMatch, sd.strand
	    FROM seg_dups sd
	    INNER JOIN SD_in_BP ON SD_in_BP.SD_id=sd.id
	    INNER JOIN breakpoints b ON b.id=SD_in_BP.BP_id
	    WHERE b.id='".$r['breakpoint_id']."' AND SD_in_BP.type LIKE '2BPs_pair%'
	    ORDER BY sd.chrom, sd.chromStart;";//solo del ultimo breakpoint (es implicito ya que tengo el breakpoint_id guardado)


    $result_seqFeat=mysql_query($sql_seqFeat);
    $bp_seq_features='';
    while ($thisrow = mysql_fetch_array($result_seqFeat)) {
	    $size=$thisrow['chromEnd']-$thisrow['chromStart']+1;
	    $othersize=$thisrow['otherEnd']-$thisrow['otherStart']+1;
	    $orientation='';
	    if ($thisrow['strand']=='-') { $orientation='Inverted'; }
	    elseif($thisrow['strand']=='+') { $orientation='Direct'; }
	    $bp_seq_features.='<tr><td>'.$thisrow['chrom'].':'.$thisrow['chromStart'].'-'.$thisrow['chromEnd'].'</td><td>'.number_format($size).'</td>
		    <td>'.$thisrow['otherChrom'].':'.$thisrow['otherStart'].'-'.$thisrow['otherEnd'].'</td><td>'.number_format($othersize).'</td>
		    <td>'.number_format($thisrow['fracMatch'],3).'</td><td>'.$orientation.'</td></tr>';	
    }

    /*
    // Apartado segmental Duplications (F) ANTIGUO 
    $sql_sd="SELECT sd.chrom, sd.chromStart, sd.chromEnd, sd.strand, sd.otherStart, sd.otherEnd
	    FROM seg_dups sd 
	    INNER JOIN SD_in_BP sdRel ON sd.id=sdRel.SD_id
	    INNER JOIN breakpoints b ON sdRel.BP_id=b.id
	    WHERE b.inv_id='$id'
	    ORDER BY FIELD (b.definition_method, 'manual curation', 'default informatic definition'), b.date DESC ;";
    $result_sd=mysql_query($sql_sd);
    while($sdrow = mysql_fetch_array($result_sd)){
	    $segmental_duplication.="<tr>
				    <td>".$sdrow['chrom']."</td>
				    <td>".$sdrow['chromStart']."</td>
				    <td>".$sdrow['chromEnd']."</td>";
	    if ($sdrow['strand'] == '+') {
		    $segmental_duplication.="
				    <td>same orientation</td>";
	    } elseif ($sdrow['strand'] =='-') {
		    $segmental_duplication.="
				    <td>inverted</td>";
	    }
	    $segmental_duplication.="
				    <td>".$sdrow['otherStart']."</td>
				    <td>".$sdrow['otherEnd']."</td>
			    </tr>";
    }
    */

    // Apartado evolutive information (I)
    $echo_evolution_orientation='';
    $sql_ev="SELECT sp.name, iis.orientation, iis.method, iis.source, r.year, r.pubMedID
	    FROM species sp 
	    INNER JOIN inversions_in_species iis ON sp.id=iis.species_id
	    LEFT JOIN researchs r ON r.name=iis.source
	    WHERE iis.inversions_id ='$id'
	    ORDER BY r.year, iis.source;";
    $result_ev=mysql_query($sql_ev);
    while($evrow = mysql_fetch_array($result_ev)) {

	    if ($evrow['pubMedID'] != "") { 
            $studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$evrow['pubMedID']."' target='_blank'>".ucfirst($evrow['source'])."</a>";
        } else {
            $studyname=ucfirst($evrow['source']);
        }

	    $echo_evolution_orientation.="<tr>
				    <td><em>".ucfirst($evrow['name'])."</em></td>
				    <td>".ucfirst($evrow['orientation'])."</td>
				    <td>".ucfirst($evrow['method'])."</td>
				    <td>".$studyname."</td>
			    </tr>";
    }

    $echo_evolution_age='';
    $sql_ev_age="SELECT GROUP_CONCAT(a.age ORDER BY a.age ASC SEPARATOR '-') AS age, GROUP_CONCAT(DISTINCT a.method) AS method, a.source, r.year, r.pubMedID 
	    FROM inv_age a LEFT JOIN researchs r ON r.name=a.source
	    WHERE a.inv_id='$id'
	    GROUP BY a.source
	    ORDER BY r.year, a.source;";
	
    $result_ev_age=mysql_query($sql_ev_age);
    while($agerow = mysql_fetch_array($result_ev_age)) {

	    if ($agerow['pubMedID'] != "") {
            $studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$agerow['pubMedID']."' target='_blank'>".ucfirst($agerow['source'])."</a>";
        } else {
            $studyname=ucfirst($agerow['source']);
        }

	    $explode_ages = explode("-", $agerow['age']);
	    for($i = 0; $i < count($explode_ages); ++$i) {
	        $explode_ages[$i] = number_format($explode_ages[$i]);
	    }
	    $implode_ages = implode("-", $explode_ages);

	    $echo_evolution_age.="<tr>
				    <td>".$implode_ages." years</td>
				    <td>".ucfirst($agerow['method'])."</td>
				    <td>".$studyname."</td>
			    </tr>";
    }

    $echo_summary_age = '';
    $sql_ev_age2="SELECT MIN(a.age) AS min_age, MAX(a.age) AS max_age 
	    FROM inv_age a
	    WHERE a.inv_id='$id';";
    $result_ev_age2=mysql_query($sql_ev_age2);
    $agerow2 = mysql_fetch_array($result_ev_age2);

    if (($agerow2['min_age'] == '') and ($agerow2['max_age'] == '')) {
	    $echo_summary_age = '<font color="grey">ND</font>';
    } else if ($agerow2['min_age'] == $agerow2['max_age']) {
	    $echo_summary_age = number_format($agerow2['min_age']).' years';
    } else {
	    $echo_summary_age = number_format($agerow2['min_age']).'-'.number_format($agerow2['max_age']).' years';
    }

    $echo_evolution_origin='';
    $sql_ev_origin="SELECT o.origin, o.method, o.source, r.year, r.pubMedID 
	    FROM inv_origin o LEFT JOIN researchs r ON r.name=o.source
	    WHERE o.inv_id='$id'
	    ORDER BY r.year, o.source;";
    $result_ev_origin=mysql_query($sql_ev_origin);
    while($origrow = mysql_fetch_array($result_ev_origin)) {

	    if ($origrow['pubMedID'] != "") {
            $studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$origrow['pubMedID']."' target='_blank'>".ucfirst($origrow['source'])."</a>";
        } else {
            $studyname=ucfirst($origrow['source']);
        }

	    $echo_evolution_origin.="<tr>
				    <td>".ucfirst($origrow['origin'])."</td>
				    <td>".ucfirst($origrow['method'])."</td>
				    <td>".$studyname."</td>
			    </tr>";
    }


    /// Apartado Effect on genes (G)
    $sql_ge="SELECT g.gene_relation, g.functional_effect, g.source, g.functional_consequence, h.symbol, h.refseq, h.chr, h.txStart, h.txEnd, h.idHsRefSeqGenes, r.year, r.pubMedID
	    FROM genomic_effect g
	    INNER JOIN HsRefSeqGenes h ON g.gene_id=h.idHsRefSeqGenes
	    LEFT JOIN researchs r ON r.name=g.source
	    WHERE g.inv_id ='$id' AND (g.bp_id = '".$r['breakpoint_id']."' OR g.bp_id IS NULL OR g.bp_id = '' 
	    OR g.functional_effect IS NOT NULL OR g.functional_consequence IS NOT NULL)
	    ORDER BY h.symbol;"; //Solo del ultimo breakpoint (es implicito ya que tengo el breakpoint_id guardado)

    $result_ge=mysql_query($sql_ge);
    $echo_symbols=''; $this_symbol = '';
    while($thisrow = mysql_fetch_array($result_ge)) {

	    if ($thisrow['symbol'] != $this_symbol) {
	
	        if ($this_symbol != '') {	
			        $echo_functional_effect.="
			        </div>
			        </div>";
	        }	

	        $this_symbol = $thisrow['symbol'];
		
	        $gene_relation = preg_replace("/(\S+)(, NA)/", "$1", $thisrow['gene_relation']);
            //str_ireplace(", NA","",$thisrow['gene_relation']);

	        if ($thisrow['pubMedID'] != "") {
                $studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$thisrow['pubMedID']."' target='_blank'>".ucfirst($thisrow['source'])."</a>";
            } else {
                $studyname=ucfirst($thisrow['source']);
            }
 
            $genomic_effect.="<tr>
				        <td>".$thisrow['gene_relation']."</td>
				        <td>".$thisrow['functional_effect']."</td>
				        <td>".$thisrow['symbol']."</td>
			        </tr>";
	        $echo_functional_effect.="<div class='report-section'>
			        <div class='section-title TitleA'>- <a href='http://www.ncbi.nlm.nih.gov/gene/?term=".$thisrow['refseq']."' target='_blank'>".$thisrow['symbol']."</a></div>
			        <div class='grlsection-content'>

			        <table width='100%' id='".$thisrow['idHsRefSeqGenes']."'>
			        <tr><td class='title' width='20%'>Gene position</td><td>".$thisrow['chr'].':'.$thisrow['txStart'].'-'.$thisrow['txEnd']."</td></tr>";
			
			        if (($gene_relation != '') or ($_SESSION["autentificado"]=='SI')) {
			            $echo_functional_effect.="<tr><td class='title' width='20%'>Mechanism</td><td id='".$thisrow['idHsRefSeqGenes']."_mechanism'>".$array_effects[$gene_relation]."</td></tr>";
			        }
			
			        if (($thisrow['source'] != '') or ($_SESSION["autentificado"]=='SI')) {
			            $echo_functional_effect.="<tr><td class='title' width='20%'>Study</td><td>".$studyname."</td></tr>";
			        }
			
			        if (($thisrow['functional_effect'] != '') or ($_SESSION["autentificado"]=='SI')) {
			            $echo_functional_effect.="<tr><td class='title' width='20%'>Effect</td><td>".ucfirst($thisrow['functional_effect'])."</td></tr>";
			        }
			
			        if (($thisrow['functional_consequence'] != '') or ($_SESSION["autentificado"]=='SI')) {
			            $echo_functional_effect.="<tr><td class='title' width='20%'>Functional consequences</td><td>".ucfirst($thisrow['functional_consequence'])."</td></tr>";
			        }
			
	        $echo_symbols.="<option value='".$thisrow['idHsRefSeqGenes']."'>".$thisrow['symbol']."</option>";
	
	        $echo_functional_effect.="</table>";
	
	    } else {
			if (($thisrow['source'] != '') or ($thisrow['functional_effect'] != '') or ($thisrow['functional_consequence'] != '')) {
			    $echo_functional_effect.="<table width='100%' id='".$thisrow['idHsRefSeqGenes']."'><tr><td class='title' width='20%'>Study</td><td>".$studyname."</td></tr>
						    <tr><td class='title' width='20%'>Effect</td><td>".ucfirst($thisrow['functional_effect'])."</td></tr>
						    <tr><td class='title' width='20%'>Functional consequences</td><td>".ucfirst($thisrow['functional_consequence'])."</td></tr></table>";
			}
	    }
    }

	if ($this_symbol != '') {	
			$echo_functional_effect.="
			</div>
			</div>";
	}	


    //EJEMPLO:
    /*	$echo_functional_effect.="<div class='report-section'>
			    <div class='section-title TitleA'>- <a href='http://www.ncbi.nlm.nih.gov/gene' target='_blank'>ACTB (EJEMPLO)".$thisrow['symbol']."</a></div>
			        <div class='grlsection-content'>
			            <table width='100%'>
			                <tr><td class='title'>Gene position</td><td>chr1:1-100000".$POSITION."</td></tr>
			                <tr><td class='title'>Effect</td><td>Disruption by removal of first exon...".$thisrow['functional_effect']."</td></tr>
			                <tr><td class='title'>Mechanism</td><td>Disrupted by...".$thisrow['gene_relation']."</td></tr>
			                <tr><td class='title'>Study (NEW)</td><td>Caceres et al".$SOURCE."</td></tr>
			                <tr><td class='title'>Functional consequences (NEW)</td><td>Unknown/disease/Metabolic...</td></tr>
			            </table>
			        </div>
			    </div>";
    */

    //Phenotypical effects
    $sql_fe="SELECT f.effect, f.mechanism, f.source, f.comment, r.year, r.pubMedID
	    FROM phenotipic_effect f LEFT JOIN researchs r ON r.name=f.source
	    WHERE f.inv_id ='$id'
	    ORDER BY r.year, f.source;";

    $result_fe=mysql_query($sql_fe);
    while($thisrow = mysql_fetch_array($result_fe)) {
	
	    if ($thisrow['pubMedID'] != "") {
            $studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$thisrow['pubMedID']."' target='_blank'>".ucfirst($thisrow['source'])."</a>";
        } else {
            $studyname=ucfirst($thisrow['source']);
        }

	    $echo_phenotypical_effect.="<tr><td>".$thisrow['effect']."</td><td>".$studyname."</td></tr>";
    }


    // Apartado Report History (J)
    /*
    $sql_history="SELECT previous_inv_id
		    FROM inversion_history
		    WHERE new_inv_id='$id';";
    */
    $sql_history="SELECT * FROM inversion_history
		    WHERE previous_inv_id='$id' or new_inv_id='$id' GROUP BY new_inv_id;";
    $result_history=mysql_query($sql_history);
    $history='';
    while($historyrow = mysql_fetch_array($result_history)) {
	    $history.=$historyrow['cause'].'<br>'; //Crear los enlaces a las inversiones precedentes. SOLO CON EL NOMBRE?????????????
    }
    //if ($history != '') {
    //	$history = '<ul>'.$history;
    //	$history .= '</ul>';
    //}

    // Historial de breakpoints (E)
     $sql_bp="SELECT b.id, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, b.definition_method, b.description, b.date, v.research_name as v_research_name, r.year, r.pubMedID , b.assembly
        FROM  breakpoints b LEFT JOIN validation v ON (v.bp_id = b.id) LEFT JOIN researchs r ON r.name=v.research_name WHERE b.inv_id ='$id' 
        ORDER BY b.date DESC ;"; //FIELD (b.definition_method, 'manual curation', 'default informatic definition'), 
    $result_bp=mysql_query($sql_bp);

    while($bprow = mysql_fetch_array($result_bp)) {
	    //tambien se muestran las duplicaciones segmentales y los efectos genomicos
	    $seq_feat_hist='';$gen_eff_hist='';
	    $sql_seqFeat_Hist="SELECT sd.chrom, sd.chromStart, sd.chromEnd, sd.otherChrom, sd.otherStart, sd.otherEnd, sd.fracMatch, sd.strand
		    FROM seg_dups sd
		    INNER JOIN SD_in_BP ON SD_in_BP.SD_id=sd.id
		    INNER JOIN breakpoints b ON b.id=SD_in_BP.BP_id
		    WHERE b.id='".$bprow['id']."';"; //puede haber >1 SD por cada breakpoint!
	    $result_seqFeat_Hist=mysql_query($sql_seqFeat_Hist);

	    $sql_ge_Hist="SELECT g.gene_relation, g.functional_effect, h.symbol, h.refseq, h.chr, h.txStart, h.txEnd
		    FROM genomic_effect g
		    INNER JOIN HsRefSeqGenes h ON g.gene_id=h.idHsRefSeqGenes
		    WHERE g.inv_id ='$id' AND g.bp_id = '".$bprow['id']."';"; //puede haber >1
	    $result_ge_Hist=mysql_query($sql_ge_Hist);
	
	    // $r['chr'].' bp1:'.$bprow['bp1_start']."-".$bprow['bp1_end'].
	    //		' bp2:'.$bprow['bp2_start']."-".$bprow['bp2_end']

	    $bp_history.= '<div class="report-section">
            <div class="section-title TitleA">- '.$bprow['date'].'</div>
		    <div class="grlsection-content ContentB">
			    <table width="100%">
                <tr><td class="title" width="20%">Breakpoint 1</td><td>'.$r['chr'].':'.$bprow['bp1_start']."-".$bprow['bp1_end'].'</td>
                        <td class="title" width="20%">Breakpoint 2<td>'.$r['chr'].':'.$bprow['bp2_start']."-".$bprow['bp2_end'].'</td></td>
                </tr>';
            
            if (($bprow['v_research_name'] != '') or ($_SESSION["autentificado"]=='SI')) {
		        if ($bprow['pubMedID'] != "") {
                    $studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$bprow['pubMedID']."' target='_blank'>".ucfirst($bprow['v_research_name'])."</a>";
                } else {
                    $studyname=ucfirst($bprow['v_research_name']);
                }
            	$bp_history.= '<tr><td class="title" width="20%">Study</td><td colspan="3">'.$studyname.'</td></tr>';
            }
            if (($bprow['assembly'] != '') or ($_SESSION["autentificado"]=='SI')) {
                 $bp_history.= '<tr><td class="title" width="20%">Assembly</td><td colspan="3">'.$bprow['assembly'].'</td></tr>';
            }
   		    if (($bprow['description'] != '') or ($_SESSION["autentificado"]=='SI')) {
		        $bp_history.= '<tr><td class="title" width="20%">Description</td><td colspan="3">'.ucfirst($bprow['description']).'</td></tr>';
		    }

            if (($bprow['definition_method'] != '') or ($_SESSION["autentificado"]=='SI')) {
   		        $bp_history.= '<tr><td class="title" width="20%">Definition method</td><td colspan="3">'.$array_definitionmethod[$bprow['definition_method']].'</td></tr>  ';       	
   		    }
   		
        $bp_history.= ' <!-- <tr><td class="title" width="20%">Date</td><td>'.$bprow['date'].'</td></tr>
            <tr><td class="title" width="20%">Sequence features</td><td> ';
	    
        while($sfhrow = mysql_fetch_array($result_seqFeat_Hist)) {
		    $size=$sfhrow['chromEnd']-$sfhrow['chromStart']+1;
		    $othersize=$sfhrow['otherEnd']-$sfhrow['otherStart']+1;
		    $orientation='';
		    if ($sfhrow['strand']=='-') { $orientation='Inverted'; }
		    elseif($sfhrow['strand']=='+') { $orientation='Direct'; }
		    $seq_feat_hist.='<tr><td>'.$sfhrow['chrom'].':'.$sfhrow['chromStart'].'-'.$sfhrow['chromEnd'].'</td><td>'.$size.'</td>
			    <td>'.$sfhrow['otherChrom'].':'.$sfhrow['otherStart'].'-'.$sfhrow['otherEnd'].'</td><td>'.$othersize.'</td>
			    <td>'.$sfhrow['fracMatch'].'</td><td>'.$orientation.'</td></tr>';
	    }
	    if ($seq_feat_hist !='') {
		    $bp_history.= 'Segmental duplications<br/>
			    <table width="100%"><tr><td class="title">Position SD1</td><td class="title" >Size (bp)</td>
			    <td class="title" >Position SD2</td><td class="title">Size (bp)</td>
			    <td class="title" >Identity</td><td class="title">Relative orientation</td></tr>';
		    $bp_history.=$seq_feat_hist;
		    $bp_history.= "</table>";
	    }
	    $bp_history.= '</td></tr><tr><td class="title" width="20%">Genomic effect</td><td>';

	    while($gehrow = mysql_fetch_array($result_ge_Hist)) {
		    $gen_eff_hist.="<table width='100%'>
			    <thead><td class='title' width='20%' colspan='2'>Gene <a href='http://www.ncbi.nlm.nih.gov/gene/?term=".$gehrow['refseq']."' target='_blank'>".$gehrow['symbol']."</a></td></thead>
			    <tr><td class='title' width='20%'>Gene position</td><td>".$gehrow['chr'].':'.$gehrow['txStart'].'-'.$gehrow['txEnd']."</td></tr>
			    <tr><td class='title' width='20%'>Study (NEW)</td><td>".$gehrow['source']."</td></tr>
			    <tr><td class='title' width='20%'>Mechanism</td><td>".$gehrow['gene_relation']."</td></tr>
			    <tr><td class='title' width='20%'>Effect</td><td>".$gehrow['functional_effect']."</td></tr>
			    <tr><td class='title' width='20%'>Functional consequences (NEW)</td><td>".$gehrow['functional_consequences']."</td></tr>
			    </table>";
	    }
	    if ($gen_eff_hist !=""){
		    $bp_history.= $gen_eff_hist;
	    }
	    $bp_history.= '</td></tr> -->
            </table></div></div>';
    }


    // APARTADOS DE ADVANCED EDITION

    //////////////////////////
    // Add a new validation //
    //////////////////////////
    $chr=''; $research_name=''; $method_add_val=''; $status_add_val='';

    $sql_chr="select chr from inversions where id='$id';";
    $result_chr = mysql_query($sql_chr);
    $chr = mysql_fetch_array($result_chr);


    $sql_research_name="select distinct name from researchs where name is not null order by name;";
    $result_research_name = mysql_query($sql_research_name);
    while($thisrow = mysql_fetch_array($result_research_name)) {
        $research_name.="<option value='".$thisrow["name"]."'>".$thisrow["name"]."</option>";
    }

    //$sql_method="select distinct method from validation where method is not null order by method;";
    $sql_method="select distinct name as method from methods where name is not null order by name;"; //and aim like '%validation%' 
    //Ponemos todos los metodos o solo los de validacion????????????????????????????????????????????????????
    $result_method = mysql_query($sql_method);
    while($thisrow = mysql_fetch_array($result_method)) {
        $method_add_val.="<option value='".$thisrow["method"]."'>".$thisrow["method"]."</option>";
    }

    $sql_status="select distinct status from validation where status is not null order by status;";
    $result_status = mysql_query($sql_status);
    while($thisrow = mysql_fetch_array($result_status)) {
      $status_add_val.="<option value='".$thisrow["status"]."'>".$thisrow["status"]."</option>\n";
    }
    // $status_add_val.="
    //     <option value='Breakpoint refinement'>Breakpoint refinement</option>\n
    //     <option value='Genotyping'>Genotyping</option>\n
    //     <option value='TRUE'>TRUE</option>\n
    //     <option value='FALSE'>FALSE</option>\n";
        //<option value='possible_TRUE'>possible_TRUE</option>\n

    //Add evolutionary information
    $species=''; $orientation=''; 
    //study|source estan en $research_name
    //method estan en $method_add_val

    $sql_species="select distinct id,name from species where id is not null order by name;";
    //$sql_species="SELECT DISTINCT id,name FROM species WHERE id IS NOT null AND id NOT IN (SELECT DISTINCT sp.id FROM species sp INNER JOIN inversions_in_species iis ON sp.id=iis.species_id WHERE iis.inversions_id='$id') ORDER BY name;";
    $result_species = mysql_query($sql_species);
    while($thisrow = mysql_fetch_array($result_species)) {
        $species.="<option value='".$thisrow["id"]."'>".$thisrow["name"]."</option>";
    }

    $sql_orientation="select distinct orientation from inversions_in_species where orientation is not null order by orientation;";
    $result_orientation = mysql_query($sql_orientation);
    while($thisrow = mysql_fetch_array($result_orientation)) {
        $orientation.="<option value='".$thisrow["orientation"]."'>".$thisrow["orientation"]."</option>";
    }


    //////////////////////////////////
    // Best Merge inversions options//
    //////////////////////////////////

    $best_merge="";
    $number = 0;
    if ($_SESSION["autentificado"]=='SI') {
        $chr=$r['chr'];
        $q_bp1_start = $r['bp1_start'] ;
        $q_bp1_end = $r['bp1_end'];
        $q_bp2_start = $r['bp2_start'];
        $q_bp2_end= $r['bp2_end'];
        $Err1 =  0;
        $match_condition = "(($q_bp1_end + $Err1 BETWEEN b.bp1_end AND b.bp2_start) OR ($q_bp2_start - $Err1 BETWEEN b.bp1_end AND b.bp2_start) OR ( $q_bp1_end + $Err1 <= b.bp1_end AND $q_bp2_start - $Err1 >= b.bp2_start))";
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

            $match_name=$row[name];
            $bp1_start=$row[bp1_start];
            $bp1_end=$row[bp1_end];
            $bp2_start=$row[bp2_start];
            $bp2_end=$row[bp2_end];
        
                        
            $length_overlap = min(($q_bp2_start - $Err1),$bp2_start) - max($bp1_end, ($q_bp1_end + $Err1)) ;
            $length_query = ($q_bp2_start - $Err1) - ($q_bp1_end + $Err1);
            $length_inversion = $bp2_start-$bp1_end;
            $overlap_query = $length_overlap/$length_query*100;
            $overlap_inversion = $length_overlap/$length_inversion*100;

            if ($overlap_query >= 90 && $overlap_inversion >= 90 &&  $match_name != $r['name']){
                $number = $number + 1;
                $best_merge.=" <a href=\"report.php?q=".$row['inv_id']."\" target=\"_blank\" >".$match_name."</a>,";       
                
            }

        }
        $best_merge = substr($best_merge, 0, -1);

        if ($number == 1){                                
            $best_merge = "The best option to merge with this inversion is:". $best_merge;
        }elseif($number > 1){
            $best_merge = "The best options to merge with this inversion are:". $best_merge;
        }
    }

     //////////////////////
    // Merge inversions //
    //////////////////////
    $array_inv_to_merge=array();
    if ($_SESSION["autentificado"]=='SI') {
        $chr_inv1 = $r['chr'];
        $start_inv1 = $r['range_start'];
        $end_inv1 = $r['range_end'];
        //$sql_inv="select distinct id, name from inversions WHERE chr = '$chr_inv1' order by name, id;";
        //$sql_inv="select distinct i.id, i.name from inversions i, breakpoints b WHERE chr = '$chr_inv1' AND (b.bp1_start BETWEEN $start_inv1 AND $end_inv1) OR (b.bp2_end BETWEEN $start_inv1 AND $end_inv1) OR ($start_inv1 BETWEEN b.bp1_start AND b.bp2_end) AND order by name, id;";
        $sql_inv="SELECT DISTINCT  i.id, i.name, i.status, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end 
            FROM inversions i, breakpoints b 
            WHERE i.chr = '$chr_inv1' AND i.id = b.inv_id 
            AND i.status NOT IN ('WITHDRAWN','Withdrawn','withdrawn')
            AND ((b.bp1_start BETWEEN $start_inv1 AND $end_inv1) OR (b.bp2_end BETWEEN $start_inv1 AND $end_inv1) 
                OR ($start_inv1 BETWEEN b.bp1_start AND b.bp2_end) 
                OR ($end_inv1 BETWEEN b.bp1_start AND b.bp2_end)) 
            GROUP BY i.id ORDER BY name, id;";

        $result_inv = mysql_query($sql_inv);

        $inv2='<option value="">-Select-</option>\n';
        while($thisrow = mysql_fetch_array($result_inv)) {
            $namee_inv=$thisrow['name'];
            if ($thisrow['id'] != $id) {
                $inv2.="<option value='".$thisrow["id"]."'>";
                if ($thisrow["name"] != "" || $thisrow["name"] != NULL) {
                    $inv2.=$thisrow["name"]."(".$thisrow["status"].")";
                } else {
                    $inv2.='id '.$thisrow["id"]."(".$thisrow["status"].")";
                }
                $inv2.="</option>\n";
            }
            array_push($array_inv_to_merge,$namee_inv);
            $inv1_with_inv2.="<option value='".$thisrow["id"]."'></option>";
            $inv1_with_inv2.=$thisrow["name"]."(".$thisrow["status"].")";
        }
    }


    ////////////////////////////
    // Merge inversions table //
    ////////////////////////////
    $count_merge_inv=0;
    foreach($array_inv_to_merge as $inv_name_merge) {$count_merge_inv++;}
    //Merge
    $sql_inv1="select distinct i.id, i.name, i.status, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b WHERE i.chr = '$chr_inv1' AND i.id = b.inv_id AND i.status NOT IN ('WITHDRAWN','Withdrawn','withdrawn') AND ((b.bp1_start BETWEEN $start_inv1 AND $end_inv1) OR (b.bp2_end BETWEEN $start_inv1 AND $end_inv1) OR ($start_inv1 BETWEEN b.bp1_start AND b.bp2_end) OR ($end_inv1 BETWEEN b.bp1_start AND b.bp2_end)) group by i.id order by name, id;";
        $result_inv1 = mysql_query($sql_inv1);
    $inversions_merge_checkbox='';
    $inversions_merge_checkbox.="<tr><td class='merge_table'>Merge</td>";
    while($thisrow = mysql_fetch_array($result_inv1)){
        $inversions_merge_checkbox.="
            <td class='merge_table'>
                <input type='checkbox' value='".$thisrow['id']."' name='id_invs_to_merge[]' />
            </td>";
            
    }
    $inversions_merge_checkbox.="</tr>";

    // Name
    $sql_inv1="select distinct i.id, i.name, i.status, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b WHERE i.chr = '$chr_inv1' AND i.id = b.inv_id AND i.status NOT IN ('WITHDRAWN','Withdrawn','withdrawn') AND ((b.bp1_start BETWEEN $start_inv1 AND $end_inv1) OR (b.bp2_end BETWEEN $start_inv1 AND $end_inv1) OR ($start_inv1 BETWEEN b.bp1_start AND b.bp2_end) OR ($end_inv1 BETWEEN b.bp1_start AND b.bp2_end)) group by i.id order by name, id;";
        $result_inv1 = mysql_query($sql_inv1);

    $result_inv1 = mysql_query($sql_inv1);
    $inversions_name_checkbox='';
    $inversions_name_checkbox.="<tr><td class='merge_table'>Name</td>";
    while($thisrow = mysql_fetch_array($result_inv1)){
        $id_inv=$thisrow['id'];
        $name_inv=$thisrow['id'];
        
        $inversions_name_checkbox.="
            <td class='merge_table'>
                <input type='checkbox' value='$name_inv' name='new_name_inv' />
            </td>";
    }
    $inversions_name_checkbox.="</tr>";



    // Mech of origin
    $sql_inv1="select distinct i.id, i.name, i.status, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b WHERE i.chr = '$chr_inv1' AND i.id = b.inv_id AND i.status NOT IN ('WITHDRAWN','Withdrawn','withdrawn') AND ((b.bp1_start BETWEEN $start_inv1 AND $end_inv1) OR (b.bp2_end BETWEEN $start_inv1 AND $end_inv1) OR ($start_inv1 BETWEEN b.bp1_start AND b.bp2_end) OR ($end_inv1 BETWEEN b.bp1_start AND b.bp2_end)) group by i.id order by name, id;";
        $result_inv1 = mysql_query($sql_inv1);

    $result_inv1 = mysql_query($sql_inv1);
    $inversions_mech_checkbox='';
    $inversions_mech_checkbox.="<tr><td class='merge_table'>Mechanism of origin</td>";
    while($thisrow = mysql_fetch_array($result_inv1)){
        $id_inv=$thisrow['id'];
        // $origin_inv=$thisrow['origin'];
        
        $inversions_mech_checkbox.="
            <td class='merge_table'>
                <input type='checkbox' value='$id_inv' name='new_origin_inv[]' />
            </td>";
    }
    $inversions_mech_checkbox.="</tr>";

    // Breakpoints
    $sql_inv1="select distinct i.id, i.name, i.status, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b WHERE i.chr = '$chr_inv1' AND i.id = b.inv_id AND i.status NOT IN ('WITHDRAWN','Withdrawn','withdrawn') AND ((b.bp1_start BETWEEN $start_inv1 AND $end_inv1) OR (b.bp2_end BETWEEN $start_inv1 AND $end_inv1) OR ($start_inv1 BETWEEN b.bp1_start AND b.bp2_end) OR ($end_inv1 BETWEEN b.bp1_start AND b.bp2_end)) group by i.id order by name, id;";
        $result_inv1 = mysql_query($sql_inv1);
    $inversions_breakpoints_checkbox='';
    $inversions_breakpoints_checkbox.="<tr><td class='merge_table'>Breakpoints</td>";
    while($thisrow = mysql_fetch_array($result_inv1)){
        
        $inversions_breakpoints_checkbox.="
            <td class='merge_table'>
                <input type='checkbox' value='".$thisrow['id']."' name='id_bp1s_invs_to_merge' title='bp1s'/>
                <input type='checkbox' value='".$thisrow['id']."' name='id_bp1e_invs_to_merge'  title='bp1e'/>
                <input type='checkbox' value='".$thisrow['id']."' name='id_bp2s_invs_to_merge'  title='bp2s'/>
                <input type='checkbox' value='".$thisrow['id']."' name='id_bp2e_invs_to_merge'  title='bp2e'/>
            </td>";
    }
    $inversions_breakpoints_checkbox.="</tr>";

    // Evolutionary 
    $sql_inv1="select distinct i.id, i.name, i.status, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b WHERE i.chr = '$chr_inv1' AND i.id = b.inv_id AND i.status NOT IN ('WITHDRAWN','Withdrawn','withdrawn') AND ((b.bp1_start BETWEEN $start_inv1 AND $end_inv1) OR (b.bp2_end BETWEEN $start_inv1 AND $end_inv1) OR ($start_inv1 BETWEEN b.bp1_start AND b.bp2_end) OR ($end_inv1 BETWEEN b.bp1_start AND b.bp2_end)) group by i.id order by name, id;";
        $result_inv1 = mysql_query($sql_inv1);
    $inversions_evolutionary_checkbox='';
    $inversions_evolutionary_checkbox.="<tr><td class='merge_table'>Evolutionary</td>";
    while($thisrow = mysql_fetch_array($result_inv1)){
        $inversions_evolutionary_checkbox.="
            <td class='merge_table'>
                <input type='checkbox' value='".$thisrow['id']."' name='id_evo_invs_to_merge' />
            </td>";
    }
    $inversions_evolutionary_checkbox.="</tr>";

    // Functional
    $sql_inv1="select distinct i.id, i.name, i.status, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b WHERE i.chr = '$chr_inv1' AND i.id = b.inv_id AND i.status NOT IN ('WITHDRAWN','Withdrawn','withdrawn') AND ((b.bp1_start BETWEEN $start_inv1 AND $end_inv1) OR (b.bp2_end BETWEEN $start_inv1 AND $end_inv1) OR ($start_inv1 BETWEEN b.bp1_start AND b.bp2_end) OR ($end_inv1 BETWEEN b.bp1_start AND b.bp2_end)) group by i.id order by name, id;";
        $result_inv1 = mysql_query($sql_inv1);
    $inversions_functional_checkbox='';
    $inversions_functional_checkbox.="<tr><td class='merge_table'>Functional</td>";
    while($thisrow = mysql_fetch_array($result_inv1)){
        $inversions_functional_checkbox.="
            <td class='merge_table'>
                <input type='checkbox' value='".$thisrow['id']."' name='id_fun_invs_to_merge' />
            </td>";
    }
    $inversions_functional_checkbox.="</tr>";

    // Comments
   $sql_inv1="select distinct i.id, i.name, i.status, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end from inversions i, breakpoints b WHERE i.chr = '$chr_inv1' AND i.id = b.inv_id AND i.status NOT IN ('WITHDRAWN','Withdrawn','withdrawn') AND ((b.bp1_start BETWEEN $start_inv1 AND $end_inv1) OR (b.bp2_end BETWEEN $start_inv1 AND $end_inv1) OR ($start_inv1 BETWEEN b.bp1_start AND b.bp2_end) OR ($end_inv1 BETWEEN b.bp1_start AND b.bp2_end)) group by i.id order by name, id;";
        $result_inv1 = mysql_query($sql_inv1);
    $inversions_comments_checkbox='';
    $inversions_comments_checkbox.="<tr><td class='merge_table'>Comments</td>";
    while($thisrow = mysql_fetch_array($result_inv1)){
        $inversions_comments_checkbox.="
            <td class='merge_table'>
                <input type='checkbox' value='".$thisrow['id']."' name='id_com_invs_to_merge[]' />
            </td>";
    }
    $inversions_comments_checkbox.="</tr>";

    //////////////////////
    // Split inversions //
    //////////////////////
    $sql_pred="SELECT p.id, p.research_name, p.BP1s, p.BP1e, p.BP2s, p.BP2e
	    FROM predictions p 
	    WHERE p.inv_id='$id' 
	    ORDER BY p.research_name;";
    $result_pred = mysql_query($sql_pred);
    $predictions='';
    while($thisrow = mysql_fetch_array($result_pred)) {
	    $predictions.="
            <tr>
                <td>
                    <a title=\"BP1:".$thisrow['BP1s']."-".$thisrow['BP1e']." BP2:".$thisrow['BP2s']."-".$thisrow['BP2e']."\">".$thisrow['research_name']."
                    </a>
                </td>";
	    $predictions.="
                <td>
                    <input type='checkbox' value='".$thisrow['id']."' name='pinv1[]' />
                </td>
		        <td>
                    <input type='checkbox' value='".$thisrow['id']."' name='pinv2[]' />
                </td>
            </tr>";
    }

    $sql_val="SELECT v.id, v.research_name, v.method, v.status, v.experimental_conditions, v. primers, v.comment
	    FROM validation v
	    WHERE v.inv_id='$id';";
    $result_val=mysql_query($sql_val);
    $validations='';
    while($thisrow = mysql_fetch_array($result_val)) {
	    $validations.="
            <tr>
                <td><a title=\"Method: ".$thisrow['method']."; Status: ".$thisrow['status'];
	    if ($thisrow['experimental_conditions'] != "" || $thisrow['experimental_conditions'] != NULL) {
		    $validations.="; Experimental Conditions: ".$thisrow['experimental_conditions'];
	    }
	    if ($thisrow['primers'] != "" || $thisrow['primers'] != NULL) {
		    $validations.="; Primers: ".$thisrow['primers'];
	    }
	    if ($thisrow['comment'] !="" || $thisrow['comment']!= NULL) {
		    $validations.="; Comments: ".$thisrow['comment'];
	    }
	    $validations.="\">".$thisrow['research_name']."</td>
		      <td><input type='checkbox' value='".$thisrow['id']."' name='vinv1[]' /></td>
		      <td><input type='checkbox'value ='".$thisrow['id']."' name='vinv2[]' /></td>
            </tr>";
    }

    # Comments history
    $sql_com_his="SELECT inversion_com, user, DATE_FORMAT(date,'%d-%m-%Y') as date from comments where inv_id = $id AND inversion_com NOT LIKE 'NULL' GROUP BY inversion_com ORDER BY date;";
    $result_com=mysql_query($sql_com_his);
    $comments_historys='';
    while($comment = mysql_fetch_array($result_com)) {
	    $inversion_com = $comment['inversion_com'];
	    $user = $comment['user'];
	    $date = $comment['date'];
	    if (empty($inversion_com) or $inversion_com == "NULL") { $inversion_com = ''; }
	    if (empty($user) or $user == 'NULL') { $user = 'unknown'; }
	    if (empty($date) or $date == 'NULL') { $date = 'unknown'; }
	    if ($inversion_com != '') {
	        $comments_history_inversion.="<p><font color= 'gray'>".$inversion_com."&nbsp;"."<sub>".$user."&nbsp;".$date."</p></sub>";
	    } else { $comments_history_inversion='Empty history. This entry has not been commented yet!'; }
    }
    if (empty($comments_history_inversion)) { $comments_history_inversion='Empty history. This entry has not been commented yet!'; }

    $sql_com_his="SELECT bp_com, user, DATE_FORMAT(date,'%d-%m-%Y') as date from comments where inv_id = $id AND bp_com NOT LIKE 'NULL' GROUP BY bp_com ORDER BY date;";
    $result_com=mysql_query($sql_com_his);
    while($comment = mysql_fetch_array($result_com)) {
	    $bp_com = $comment['bp_com'];
	    $user = $comment['user'];
	    $date = $comment['date'];
	    if (empty($bp_com) or $bp_com == "NULL") { $bp_com = ''; }
	    if (empty($user) or $user == 'NULL') { $user = 'unknown'; }
	    if (empty($date) or $date == 'NULL') { $date = 'unknown'; }
	    if ($bp_com != '') {
	    $comments_history_bp.="<p><font color= 'gray'>".$bp_com."&nbsp;"."<sub>".$user."&nbsp;".$date."</p></sub>";
	    } else {
	    $comments_history_bp='Empty history. This entry has not been commented yet!';
        }
    }

    if (empty($comments_history_bp)) { $comments_history_bp='Empty history. This entry has not been commented yet!'; }

    $sql_com_his="SELECT evolutionary_history_com, user, DATE_FORMAT(date,'%d-%m-%Y') as date from comments where inv_id = $id AND evolutionary_history_com NOT LIKE 'NULL' GROUP BY evolutionary_history_com ORDER BY date;";
    $result_com=mysql_query($sql_com_his);
    while($comment = mysql_fetch_array($result_com)) {
	    $eh_com = $comment['evolutionary_history_com'];
	    $user = $comment['user'];
	    $date = $comment['date'];
	    if (empty($eh_com)){$eh_com = '';}
	    if (empty($user) or $user == 'NULL'){$user = 'unknown';}
	    if (empty($date) or $date == 'NULL'){$date = 'unknown';}
	    if ($eh_com != ''){
	    $comments_history_eh.="<p><font color= 'gray'>".$eh_com."&nbsp;"."<sub>".$user."&nbsp;".$date."</p></sub>";
	    } else{
	    $comments_history_eh='Empty history. This entry has not been commented yet!';}
    }
    if (empty($comments_history_eh)) { $comments_history_eh='Empty history. This entry has not been commented yet!'; }


    #Last inversion comment
        $last_com_query= "select comment_id, inversion_com from comments where inv_id = $id ORDER BY comment_id DESC LIMIT 1;";
        $result = mysql_query($last_com_query);
        while($thisrow = mysql_fetch_array($result)){
            if($thisrow['inversion_com'] == "NULL"){$last_com = '';}
            else{$last_com=$thisrow['inversion_com'];}
        }
    #Last bp comment
        $last_bp_com_query= "select comment_id, bp_com from comments where inv_id = $id ORDER BY comment_id DESC LIMIT 1;";
        $result_sql = mysql_query($last_bp_com_query);
        $last_com_bp = '';
        while($thisrow = mysql_fetch_array($result_sql)){
            if($thisrow['bp_com'] == "NULL"){$last_com_bp = '';}
            else{$last_com_bp=$thisrow['bp_com'];}
        }
    #Last eh comment
        $last_eh_com_query= "select comment_id, evolutionary_history_com from comments where inv_id = $id ORDER BY comment_id DESC LIMIT 1;";
        $result_sql = mysql_query($last_eh_com_query);
        $last_com_eh = '';
        while($thisrow = mysql_fetch_array($result_sql)){
            if($thisrow['evolutionary_history_com'] == "NULL"){$last_com_eh = '';}
            else{$last_com_eh=$thisrow['evolutionary_history_com'];}
        }
    #Last func effect comment
    $last_funct_effect_com_query= "select comment_id, functional_effect_com from comments where inv_id = $id ORDER BY comment_id DESC LIMIT 1;";
        $result_sql = mysql_query($last_funct_effect_com_query);
        $last_com_funct_effect= '';
        while($thisrow = mysql_fetch_array($result_sql)){
            if($thisrow['functional_effect_com'] == "NULL"){$last_com_funct_effect = '';}
            else{$last_com_funct_effect=$thisrow['functional_effect_com'];}
        }
    #Last func consequences comment
    $last_funct_conseq_com_query= "select comment_id, functional_conseq_com from comments where inv_id = $id ORDER BY comment_id DESC LIMIT 1;";
        $result_sql = mysql_query($last_funct_conseq_com_query);
        $last_com_funct_effect = '';
        while($thisrow = mysql_fetch_array($result_sql)){
            if($thisrow['functional_conseq_com'] == "NULL"){$last_com_conseq_effect = '';}
            else{$last_com_conseq_effect=$thisrow['functional_conseq_com'];}
        }
    #Last pheno effect comment
    $last_pheno_effect_com_query= "select comment_id, phenotypic_effects_com from comments where inv_id = $id ORDER BY comment_id DESC LIMIT 1;";
        $result_sql = mysql_query($last_pheno_effect_com_query);
        $last_com_pheno_effect = '';
        while($thisrow = mysql_fetch_array($result_sql)){
            if($thisrow['phenotypic_effects_com'] == "NULL"){$last_com_pheno_effect = '';}
            else{$last_com_pheno_effect=$thisrow['phenotypic_effects_com'];}
        }


   //### Apartado validaciones (D) field change r.validation_method

    #$sql_val="SELECT v.id, GROUP_CONCAT(DISTINCT v.status SEPARATOR ' ;') status, GROUP_CONCAT(DISTINCT v.comment SEPARATOR ' ;') comment,
    #   r.name, GROUP_CONCAT(DISTINCT v.method SEPARATOR ' ;') validation_method, r.pubMedID, r.description
    #   FROM validation v INNER JOIN researchs r ON v.research_name=r.name
    #   WHERE v.inv_id='$id'
    #   GROUP BY r.name ORDER BY r.pubMedID DESC;";
    
    $sql_val="SELECT DISTINCT r.name, r.validation_method, r.pubMedID, r.description, v.id, v.status, v.comment
                FROM validation v INNER JOIN researchs r ON v.research_name=r.name 
                WHERE v.inv_id='$id' GROUP BY r.name ORDER BY r.pubMedID DESC;";

    $result_val=mysql_query($sql_val);
    $echo_validations='';

    while($thisrow = mysql_fetch_array($result_val)) {
    
        $echo_validations.="<div class='report-section'>
                <div class='section-title TitleA'>-";
        if ($thisrow['pubMedID'] != "") {
            $echo_validations.="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$thisrow['pubMedID']."' target='_blank'>".$thisrow['name']."</a>";
        } else {
            $echo_validations.= $thisrow['name'];
        }

        $echo_validations.="</div>
                <div class='grlsection-content' style= 'padding:1.2em 3em '>
                <table width='100%'>";
            
                if (($thisrow['description'] != '') or ($_SESSION["autentificado"]=='SI')) {
                    $echo_validations.="<tr><td class='title' width='20%'>Description</td><td>".ucfirst($thisrow['description'])."</td></tr><tr>";
                }

        $sql_val_study ="SELECT DISTINCT r.name, r.pubMedID, r.description, v.id, v.method, v.status, v.comment, v.checked, i.chr
                    FROM validation v INNER JOIN researchs r ON v.research_name=r.name INNER JOIN inversions i ON v.inv_id = i.id 
                    WHERE v.inv_id='$id' AND r.name ='".$thisrow["name"]."' ORDER BY r.pubMedID DESC;";
        $result_val_study =mysql_query($sql_val_study);

            while($thisrow_study = mysql_fetch_array($result_val_study)) {

                //Para el support se cuentan todos los individuos de la validacion y se separan en funcion del genotipo
                //$sql_valSupport="SELECT count(distinct id.individuals_id) as count, id.genotype 
                    //FROM individuals_detection id
                    //WHERE id.inversions_id='$id' and id.validation_id='".$thisrow_study["id"]."' and id.validation_research_name='".$thisrow_study["name"]."'
                    //GROUP BY id.genotype
                    //ORDER BY FIELD(id.genotype,'STD/STD','STD/INV','INV/STD','INV/INV','STD','INV','NA','ND');";
   
                $sql_valSupport="SELECT count(distinct id.individuals_id) as count, IF(id.genotype = 'INV/STD', 'STD/INV', id.genotype) AS genotype2 
                    FROM individuals_detection id
                    WHERE id.inversions_id='$id' and id.validation_id='".$thisrow_study["id"]."' and id.validation_research_name='".$thisrow_study["name"]."'
                    GROUP BY genotype2
                    ORDER BY FIELD(genotype2,'STD/STD','STD/INV','INV/STD','INV/INV','STD','INV','NA','ND');";

                $result_valSupport=mysql_query($sql_valSupport);
                $valSupport='';$totalSupport=0;
                //En funcion del genotipo, cambia el texto. REVISAR LOS POSIBLES GENOTIPOS!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                //El fichero php/ajaxAdd_validation.php tiene este mismo bloque de codigo, revisar tambien!!!!!!!!!!!!!
                while($supportrow = mysql_fetch_array($result_valSupport)) {
                    if ($totalSupport>0) { $valSupport.= '&nbsp;&nbsp;<b>-</b>&nbsp;&nbsp;'; }
                    //if ($supportrow['genotype'] == 'INV/INV'){$valSupport.=$supportrow['count'].' homozygote inverted individuals. ';}
                    //else if ($supportrow['genotype'] == 'STD/STD'){$valSupport.=$supportrow['count'].' homozygote standard individuals. ';}
                    //else if ($supportrow['genotype'] == 'STD/INV'||$supportrow['genotype'] == 'INV/STD'){$valSupport.=$supportrow['count'].' heterozygote individuals. ';}
                    //else if ($supportrow['genotype'] == 'INV'){$valSupport.=$supportrow['count'].' hemizygous inverted individuals. ';}
                    //else if ($supportrow['genotype'] == 'STD'){$valSupport.=$supportrow['count'].' hemizygous standard individuals. ';}
                    //else if ($supportrow['genotype'] == 'NA'){$valSupport.=$supportrow['count'].' not applicable individuals. ';}
                    //else if ($supportrow['genotype'] == 'ND'){$valSupport.=$supportrow['count'].' not determined individuals. ';}
                    //else {$valSupport.=$supportrow['count'].' '.$supportrow['genotype'].' individuals. ';}
                    $valSupport.=$supportrow['count'].' '.$supportrow['genotype2'];
                    $totalSupport+=$supportrow['count'];
                }
                //if ($valSupport==''){$valSupport='0 individuals';}
                if ($totalSupport!=''||$totalSupport!=0) { $totalSupport.=' individuals'; }
                else { $totalSupport=''; }

                // Mirar quants són de HapMap, etc.
                if ($totalSupport != '') {
                    $sql_fromHapMap="SELECT bt.panel, COUNT(bt.id) AS countpanel
                        FROM 
                        (
                        SELECT id.id, (
                        CASE
                           WHEN i.panel LIKE '%HapMap%' OR i.panel LIKE '%1000 Genomes Project%' THEN 'HapMap/1000GP'
                           WHEN i.panel LIKE '%HGDP%' THEN 'HGDP'
                           WHEN i.panel LIKE 'unknown' THEN 'unknown'
                           ELSE 'Other'
                        END) AS panel
                        FROM individuals i, individuals_detection id
                        WHERE i.id=id.individuals_id AND id.inversions_id='$id' AND id.validation_id='".$thisrow_study["id"]."' 
                        AND id.validation_research_name='".$thisrow_study["name"]."' 
                        ) bt
                        GROUP BY bt.panel
                        ORDER BY FIELD(bt.panel,'HapMap/1000GP','HGDP','Other','unknown');";
                        $result_fromHapMap=mysql_query($sql_fromHapMap);
                        $writePanel='';
                        while($getresult_fromHapMap = mysql_fetch_array($result_fromHapMap)){
                            $writePanel.=$getresult_fromHapMap['countpanel'].' '.$getresult_fromHapMap['panel'].', ';
                        }
                    $writePanel = substr_replace($writePanel ,"",-2);
                }

                // O el soprte de fosmidos secuenciados en caso de validacion por secuencias
                $sql_fosm_valSupport="SELECT GROUP_CONCAT( DISTINCT CONCAT_WS(': ',fv.fosmids_name, fv.result) SEPARATOR '. ') fosm_resul , GROUP_CONCAT( DISTINCT CONCAT_WS('; ',v.comment, CONCAT_WS(': ', fv.fosmids_name, fv.comments)) SEPARATOR '. ') fosm_commet
                    FROM inversions i 
                        JOIN  validation v ON (i.id = v.inv_id and v.method = 'Fosmid sequence analysis***') 
                        JOIN fosmids_validation fv ON (v.id = fv.validation_id)
                    WHERE v.inv_id='$id' and id.validation_id='".$thisrow_study["id"]."' and v.research_name = '".$thisrow_study["name"]."'
                    GROUP BY v.research_name";
                 $result_fosm_valSupport=mysql_query($sql_fosm_valSupport);
                 $fosm_supportrow = mysql_fetch_array($result_fosm_valSupport);
     
                 // O freqüències sense genotips
                 $getresult_nogenotypes='';
                 $sql_nogenotypes = "SELECT SUM(pd.individuals) AS individuals 
                    FROM population_distribution pd
                    WHERE pd.inv_id='$id' AND pd.validation_id='".$thisrow_study["id"]."' AND pd.validation_research_name='".$thisrow_study["name"]."';";
                 $result_nogenotypes=mysql_query($sql_nogenotypes);
                 $getresult_nogenotypes = mysql_fetch_array($result_nogenotypes);

                    #$echo_validations.="<tr><td width='20%'></td><td></td></tr>";
                    $echo_validations.="&nbsp;";


                    $echo_validations.="<table width='100%'><tr><td class='title' width='20%'>Method</td><td>".ucfirst($thisrow_study['method'])."</td></tr>
                    <tr><td class='title' width='20%'>Status</td><td>".$array_status[$thisrow_study['status']]."</td></tr>";
            
                    // Here support/genotypes
                    if (($totalSupport != '') or ($valSupport != '') or ($fosm_supportrow['fosm_resul'] != '') or ($getresult_nogenotypes['individuals'] != '') or ($_SESSION["autentificado"]=='SI')) {
           
                        $echo_validations.="<tr><td class='title' width='20%'>Genotyping</td><td>";
            
                        if ($totalSupport != '') { $echo_validations.= "$totalSupport:  $writePanel"; } 
                        else if ($getresult_nogenotypes['individuals']>0) { $echo_validations.=$getresult_nogenotypes['individuals']." individuals"; }
                        else {}
              
                        if ($valSupport != '') { $echo_validations.="<br/>$valSupport &nbsp;<a href='php/echo_individualsVal.php?id=".$id."&val=".$thisrow_study['name']."&valid=".$thisrow_study['id']."' ><img src='img/download.png' alt='Download' width='23' height='23'></a>"; }
                        else if ($getresult_nogenotypes['individuals']>0) { $echo_validations.= " (No genotypes available)"; }
                        else {}
            
                        if ($fosm_supportrow['fosm_resul']) { $echo_validations.= " (Fosmids: ".$fosm_supportrow['fosm_resul'].")"; }
                        $echo_validations .="</td></tr>";
                    }
                    // End support/genotypes
            
                    if (($thisrow_study['comment'] != '') or ($fosm_supportrow['fosm_commet'] != '') or ($_SESSION["autentificado"]=='SI')) {
                        $echo_validations .="<tr><td class='title' width='20%'>Comment</td><td>".$thisrow_study['comment'];
                        #isset($fosm_supportrow['fosm_commet']) ? $echo_validations.= $fosm_supportrow['fosm_commet']."</td></tr></table></div></div>" : $echo_validations.= "</td></tr></table></div></div>";
                        if ($fosm_supportrow['fosm_commet']) {
                            $echo_validations.= $fosm_supportrow['fosm_commet']."</td></tr>";
                        } else {
                            $echo_validations.= "</td></tr>";
                        }
                    }

                    

                $echo_validations.="</table>";
                // echo $thisrow_study['method'];
                $status_add_val_option =  preg_replace("/\<option value=\'".$thisrow_study['status']."\'\>".$thisrow_study['status']."\<\/option\>\\n/", "<option selected = selected value='".$thisrow_study['status']."'>".$thisrow_study['status']."</option>\n", $status_add_val );
                

                $echo_validations.=  '<div id="edit_validation'.$thisrow_study['id'].'" class="content" style="display: none;" </div>
                        <div class="grlsection-content ContentA"> 

                            <form name="edit_validation" method="post" action="php/update_validation.php" 
                                onsubmit="return validate()" enctype="multipart/form-data" >
                                
                        
                                Status 
                                <select name="status" id="status" >
                                    <option value="" id="status_null">-Select-</option>'.$status_add_val_option.'
                                </select><br>';
               
                if($thisrow_study['checked'] == "yes"){
                    $echo_validations.=' Force status <input name="checked" id="checked" type="checkbox" value="yes" checked/><br> ';

                }else {
                    $echo_validations.=' Force status <input name="checked" id="checked" type="checkbox" value="yes" /><br> ';}

                $echo_validations.=  'Comment <textarea rows="1" cols="68" name="commentE" id="commentE" type="text" /></textarea><br>

                                <div id="validation" class="report-section" >
                                    <div class="section-title TitleB">
                                        + Validation details 
                                    </div>
                                    <div class="hidden" >
                                    <div class="grlsection-content ContentA">';
                            if(preg_match("/PCR|FISH|MLPA/",$thisrow_study['method'])){ // experimental
                                 $echo_validations.= '<div id="includeExperimental_edit">
                                                            Experimental conditions <input name="experimental_conditions" id="experimental_conditions" type="text" /><br>
                                                            Primers <input name="primers" id="primers" type="text" /><br>
                                                    </div>';
                                         
                            } else  {            // Bioinformatics
                                $echo_validations.= '<div id="includeBioinformatics_edit">
                                                            <p id="auto" style="display: inline-block"> 
                                                                <label>Fosmids <!--<div class="compulsory">*</div>--> </label>
                                                                <input type="text" id="searchFosmids" name="searchFosmids" />
                                                            </p><br>
                                                            Results <!--<div class="compulsory">*</div>--> 
                                                            <input name="fosmids_results" id="fosmids_results" type="text" /><br>
                                                            Comment <textarea rows="1" cols="61" name="commentB" id="commentB" type="text" /></textarea><br>
                                                        </div>';
                           }
                            
                              $echo_validations.=  '</div>
                               <?php if ($_GET["o"]!="add_val"){ ?>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div id="individuals" class="report-section" >
                                    <div class="section-title TitleB">+ Individuals:</div>
                                    <div class="hidden">
                                        <div class="grlsection-content ContentA">
                                            Individuals <input type="file" name="individuals" id="individuals" /><br>
                                        </div>
                                    </div>
                                </div>
                                <div id="nogenotypes" class="report-section" >
                                    <div class="section-title TitleB">+ Frequency without genotypes:</div>
                                    <div class="hidden">
                                        <div class="grlsection-content ContentA">
                                            <font color="red">
                                                Please be aware that this information will be displayed in the Frequency section of the Inversion report, but the following will not be available: Hardy-Weinberg test and genotype file for download. Also, data will not be averaged with other studies of the same population.
                                            </font><br><br>
                                            Population 
                                            <select id="fng_population" name="fng_population">
                                                <option value="null">-Select-</option>
                                                '.$fng_population.'
                                            </select><br>
                                            Analyzed individuals <input name="fng_individuals" id="fng_individuals" type="text" onchange="calculateFng(this)"/><br>
                                            Inverted alleles <input name="fng_invalleles" id="fng_invalleles" type="text" onchange="calculateFng(this)" /><br>
                                            Standard frequency <input name="fng_stdfreq" id="fng_stdfreq" type="text" onchange="calculateFng(this)" /><br>
                                            Inverted frequency <input name="fng_invfreq" id="fng_invfreq" type="text" onchange="calculateFng(this)" /><br>
                                        </div>
                                    </div>
                                </div>
                                <div id="addBreakpoints" class="report-section" >
                                    <div class="section-title TitleB">+ Manually curated breakpoints:</div>
                                    <div class="hidden">
                                        <div class="grlsection-content ContentA">
                                            Breakpoint 1 start <input name="bp1s" id="bp1s" type="text" /><br>
                                            Breakpoint 1 end <input name="bp1e" id="bp1e" type="text" /><br>
                                            Breakpoint 1 between start-end <input type="checkbox" id="between_bp1" name="between_bp1" /><br />
                                            Breakpoint 2 start <input name="bp2s" id="bp2s" type="text" /><br>
                                            Breakpoint 2 end <input name="bp2e" id="bp2e" type="text" /><br>
                                            Breakpoint 2 between start-end <input type="checkbox" id="between_bp2" name="between_bp2" /><br />
                                            Description <textarea rows="1" cols="40" name="description" id="description" type="text" /></textarea><br>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="val_id" id="val_id" value="'.$thisrow_study["id"] .'" />
                                <input type="hidden" name="method" id="method" value="'.$thisrow_study["method"] .'" />
                                <input type="hidden" name="chr" id="chr" value="'.$thisrow_study["chr"] .'" /> 
                                <input type="hidden" name="inv_id" id="inv_id" value="'.$id .'" /> 
                                <input type="hidden" name="research_name" id="research_name" value="'.$thisrow_study["name"] .'"/> 
                                <input type="submit" name="Update"  value="Update" />
                                <input type="reset" value="Clear" />
                                <img class="masterTooltip" src="img/alert.png" title="Only modified sections are submitted. All the corresponding fields must be complete because they will be overwritten. In the Frequency without genotypes section multiple populations can be added if the editing process is repeated for each one of them." width = "18">
                                <input type="submit" onclick="return confirm(\'Are you sure?\')" name="Delete"  value="Delete" style="float: right; color : #bb452c;" />
                            </form>

                        </div>
                    </div>';





                if ($_SESSION["autentificado"]=='SI'){
                    $echo_validations.="&nbsp;<input type='button' class='right' value='Edit' onclick=\"Show_Div(edit_validation".$thisrow_study['id'].")\" />";
                    //thisrow['p_id'] ahora sera research_id y research_name juntos separados por ';'
                }
            }

    $echo_validations.="&nbsp;";
          
        $echo_validations.= "</tr></table></div></div>";

     }

    ###############
    # BP_features #
    ###############
    #TE
    $sql_TE="SELECT te.subtype, te.chrom, te.strand, te.chromStart, te.chromEnd, te.size, te.otherChrom, te.otherStart, te.otherEnd, te.otherSize, te.fracMatch FROM TE_in_BP te where te.inv_id = $id ORDER BY te.subtype,te.chrom, te.chromStart;";


    $result_seqTE=mysql_query($sql_TE);
    $TE_features='';
    while ($thisroww = mysql_fetch_array($result_seqTE)){
        $subtype=$thisroww['subtype'];
        $size1=$thisroww['size'];
        $size2=$thisroww['otherSize'];
        $orientationn='';
        if ($thisroww['strand']=='-'){$orientationn='Inverted';}
        elseif($thisroww['strand']=='+'){$orientationn='Direct';}
        $TE_features.='<tr><td>'.$subtype.'</td><td>'.$thisroww['chrom'].':'.$thisroww['chromStart'].'-'.$thisroww['chromEnd'].'</td><td>'.number_format($size1).'</td>
            <td>'.$thisroww['otherChrom'].':'.$thisroww['otherStart'].'-'.$thisroww['otherEnd'].'</td><td>'.number_format($size2).'</td>
            <td>'.number_format($thisroww['fracMatch'],1).'</td><td>'.$orientationn.'</td></tr>';
    }

    #IR
    $sql_IR="SELECT ir.chrom, ir.strand, ir.chromStart, ir.chromEnd, ir.size, ir.otherChrom, ir.otherStart, ir.otherEnd, ir.otherSize, ir.fracMatch FROM IR_in_BP ir where ir.inv_id = $id ORDER BY ir.chrom, ir.chromStart;";


    $result_seqIR=mysql_query($sql_IR);
    $IR_features='';
    while ($thisroww = mysql_fetch_array($result_seqIR)){
        $size1=$thisroww['size'];
        $size2=$thisroww['otherSize'];
        $orientationn='';
        if ($thisroww['strand']=='-'){$orientationn='Inverted';}
        elseif($thisroww['strand']=='+'){$orientationn='Direct';}
        $IR_features.='<tr><td>'.$thisroww['chrom'].':'.$thisroww['chromStart'].'-'.$thisroww['chromEnd'].'</td><td>'.number_format($size1).'</td>
            <td>'.$thisroww['otherChrom'].':'.$thisroww['otherStart'].'-'.$thisroww['otherEnd'].'</td><td>'.number_format($size2).'</td>
            <td>'.number_format($thisroww['fracMatch'],1).'</td><td>'.$orientationn.'</td></tr>';
    }


    mysql_close($con);

?>
