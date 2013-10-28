<?php

session_start(); //Inicio la sesión

$id=$_GET["q"];

include_once('db_conexion.php');
include_once('php/php_global_variables.php');

#### Query Inversion Data  (A) 
$sql_inv="SELECT i.name, i.chr, i.range_start, i.range_end, i.size, i.frequency_distribution, i.evo_origin, i.origin, i.status, i.comment, i.ancestral_orientation, i.age, i.comments_eh, 
		(SELECT count(p.id) FROM predictions p WHERE p.inv_id='$id') as num_pred,
		(SELECT count(distinct research_name) FROM validation v WHERE v.inv_id='$id') as num_val,
		b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end,b.genomic_effect, b.definition_method, b.description, b.id as breakpoint_id, b.comments as breakpoint_comments, val.research_name AS studyname, r.year, r.pubMedID  
	FROM inversions i INNER JOIN breakpoints b ON b.id = (SELECT id FROM breakpoints b2 WHERE b2.inv_id=i.id
		ORDER BY FIELD (b2.definition_method, 'manual curation', 'default informatic definition'), b2.id DESC
		LIMIT 1) LEFT JOIN validation val ON b.id = val.bp_id 
		LEFT JOIN researchs r ON val.research_name = r.name 
	WHERE i.id ='$id';";
#b2.date,

$result_inv = mysql_query($sql_inv);
$r= mysql_fetch_array($result_inv);

# Recalculate size & range
$middle_bp1 = number_format(($r['bp1_start']+$r['bp1_end'])/2, 0, '.', '');
$middle_bp2 = number_format(($r['bp2_start']+$r['bp2_end'])/2, 0, '.', '');
$r['size'] = $middle_bp2-$middle_bp1+1;
$r['range_start'] = $r['bp1_start'];
$r['range_end'] = $r['bp2_end'];

#Este codigo se podria eliminar cunado se actualice el campo ancestral_orientation en la tabla inversions
$sql_inv_ancestral_orientation = "SELECT 1 n, orientation FROM inversions_in_species WHERE inversions_id ='$id' and result_value = 1;";
$result_inv_ancestral_orientation = mysql_query($sql_inv_ancestral_orientation);
$inv_ancestral_orientation= mysql_fetch_array($result_inv_ancestral_orientation);

if ($inv_ancestral_orientation['n'] != 1) {

$sql_inv_ancestral_orientation = "SELECT COUNT(DISTINCT orientation) n, orientation FROM inversions_in_species WHERE inversions_id = '$id' and orientation NOT IN ('polymorphic', 'deleted allele');";
$result_inv_ancestral_orientation = mysql_query($sql_inv_ancestral_orientation);
$inv_ancestral_orientation= mysql_fetch_array($result_inv_ancestral_orientation);
}

if ($inv_ancestral_orientation['n'] == 0) {$r['ancestral_orientation'] = "NA";}
elseif ($inv_ancestral_orientation['n'] == 1) { $r['ancestral_orientation']= ucfirst($inv_ancestral_orientation['orientation']);}
else { $r['ancestral_orientation'] = "ND"; }

//$r['ancestral_orientation']=str_replace('+','',$r['ancestral_orientation']);

if ($r['pubMedID'] != "") {$r['studyname']="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$r['pubMedID']."' target='_blank'>".ucfirst($r['studyname'])."</a>";}
	else {$r['studyname']=ucfirst($r['studyname']);}

$r_freq = mysql_query("SELECT inv_frequency('$id','all','all','all') AS res_freq");
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
$result_img=mysql_query($sql_img);
$pos= mysql_fetch_array($result_img);
$all_bp1s = ""; $all_bp1e = ""; $all_bp2s = ""; $all_bp2e = ""; $all_id  = ""; $all_name = "";
$smallest_bp = 99999999999999999999;
$greatest_bp = 0;

// se buscan los distintos estudios que predicen la inversion
$sql_pred_study = "SELECT DISTINCT p.research_name, r.prediction_method, r.pubMedID, r.description
                    FROM predictions p INNER JOIN researchs r ON p.research_name=r.name
                     WHERE p.inv_id='$id' ORDER BY r.pubMedID DESC;"; 
$result_pred_study = mysql_query($sql_pred_study);
while($thisstudy = mysql_fetch_array($result_pred_study)){
//creo la seccion
$sql_pred="SELECT p.id, concat(p.research_id,';',p.research_name) as p_id, p.chr, p.BP1s, p.BP1e, p.BP2s, p.BP2e, p.comments, p.support, p.research_name, p.accuracy, p.score1, p.score2, 
	r.prediction_method, r.pubMedID, r.description, r.individuals
	FROM predictions p INNER JOIN researchs r ON p.research_name=r.name
	WHERE p.inv_id='$id' AND p.research_name='".$thisstudy["research_name"]."';"; //id y name van juntos!
//$sql_pred="SELECT p.id, p.chr, p.BP1s, p.BP1e, p.BP2s, p.BP2e, p.comments, p.support_bp1, p.support_bp2, p.pred_name, p.accuracy FROM predictions p WHERE p.inv_id='$id' ORDER BY p.pred_name;";
$result_pred = mysql_query($sql_pred);

$echo_predictions.="<div class='report-section'>
			<div class='section-title TitleA'>- ";
	if ($thisstudy['pubMedID'] != "") {$echo_predictions.="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$thisstudy['pubMedID']."' target='_blank'>".$thisstudy['research_name']."</a>";}
	else {$echo_predictions.= $thisstudy['research_name'];}
	$echo_predictions.="</div>
			<div class='grlsection-content'>

			<table width='100%'>";
			
			if (($thisstudy['description'] != '') or ($_SESSION["autentificado"]=='SI')) {
			
				$echo_predictions.="<tr><td class='title' width='18%'>Description</td><td colspan='3'>".ucfirst($thisstudy['description'])."</td></tr>";
				
			}	
				
				
				$echo_predictions.="<tr><td class='title' width='18%'>Method</td><td colspan='3'>".ucfirst($thisstudy['prediction_method'])."</td></tr>
				<tr>";
				$counterPredStud=0;
				
                while($thisrow = mysql_fetch_array($result_pred)){
        	$counterPredStud++;
        	if ($counterPredStud>1) {
        		$echo_predictions.="&nbsp;";
        	}        
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

	               //se buscan los individuos de la inversion y del estudio
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
	               //se buscan todos los individuos de un estudio para hacer la tabla (con ticks y crosses)
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
                    
                    
               // 
                $echo_predictions.= "<table width='100%'".$change_format."><tr><td class='title' width='18%'>Breakpoint 1</td><td>".$thisrow['chr'].":".$thisrow['BP1s']."-".$thisrow['BP1e']."</td>
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
	           if ($thisrow['support']>0){
		          $echo_predictions.=$thisrow['support']." probes <a href='php/echo_fosmids.php?fos=".$fosmids."' ><img src='img/download.png' alt='Download' width='23' height='23'></a>";
	           }
	           
	           $echo_predictions.="</td></tr>";
	           
	          }
	           
				$echo_predictions.="<tr><td class='title' width='18%'>Individuals</td><td colspan='3'>";
//	if ($numindividuals>0){
//		$echo_predictions.="<div class='right'><a href='php/echo_individuals.php?ind=".$individuals."' >Download ".$numindividuals." individuals</a></div>";
//	}
	#$echo_predictions.="<br />";

	//subtabla con todos los individuos de un estudio (indResearch_ar)
	//si el individuo ha sido validado (individuals_ar), tendra un 'tick', si no ha sido validado tendra un 'cross'
	           if(!empty($indResearch_ar)){
	           	$echo_predictions.="<table ><tr align='center'>";
	           	foreach ($indResearch_ar as $value) {$echo_predictions.="<td class='title'>$value</td>";}
	           	$echo_predictions.="<td style='border-color:#FFFFFF;'></td></tr><tr align='center'>";
	           	foreach ($indResearch_ar as $value) {
	           		if (in_array($value, $individuals_ar)) {$echo_predictions.="<td><img src='img/tick2.png' width='23' height='23'/></td>";}
	           		else{$echo_predictions.="<td><img src='img/cross2.png' width='23' height='23'/></td>";}
	           	}
	           	$echo_predictions.="<td style='border-color:#FFFFFF;'>";
                 if ($numindividuals>0){
	           	   $echo_predictions.="&nbsp;&nbsp;<a href='php/echo_individuals.php?ind=".$individuals."' ><img src='img/download.png' alt='Download' width='23' height='23'></a>";//".$numindividuals." individuals
                  }
               $echo_predictions.="</td></tr></table>";
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
                //guardamos la informacion para crear la imagen
	$all_bp1s .= $thisrow['BP1s'].":";		$all_bp1e .= $thisrow['BP1e'].":";
	$all_bp2s .= $thisrow['BP2s'].":";		$all_bp2e .= $thisrow['BP2e'].":";
	$all_id  .= $thisrow['id'].":";			$all_name .= $thisrow['research_name'].":";
	
	if ($all_bp1s < $smallest_bp) {
		$smallest_bp = $all_bp1s;
	}
	
	if ($all_bp2e > $greatest_bp) {
		$greatest_bp = $all_bp2e;
	}
	
            }
            $echo_predictions.="</tr>
            </table>
			</div></div>"; 
//pongo lo que esta hasta ahora pero uniendo las tablas   
    
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
	//se buscan los fosmidos de la inversion y del estudio
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

	//se buscan los individuos de la inversion y del estudio
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
	//se buscan todos los individuos de un estudio para hacer la tabla (con ticks y crosses)
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

	//se crea la tabla de predicciones
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
//	if ($numindividuals>0){
//		$echo_predictions.="<div class='right'><a href='php/echo_individuals.php?ind=".$individuals."' >Download ".$numindividuals." individuals</a></div>";
//	}
	#$echo_predictions.="<br />";

	//subtabla con todos los individuos de un estudio (indResearch_ar)
	//si el individuo ha sido validado (individuals_ar), tendra un 'tick', si no ha sido validado tendra un 'cross'
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
//guardamos la informacion para crear la imagen
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
		  
if ($r['bp1_start'] < $smallest_bp) {
	$smallest_bp = $r['bp1_start'];
}

if ($r['bp2_end'] > $greatest_bp) {
	$greatest_bp = $r['bp2_end'];
}

$length_image = $greatest_bp - $smallest_bp;
$start_image = number_format(($smallest_bp-($length_image*0.3)), 0, '.', '');
$end_image = number_format(($greatest_bp+($length_image*0.3)), 0, '.', '');


//### Apartado validaciones (D) field change r.validation_method

#$sql_val="SELECT v.id, GROUP_CONCAT(DISTINCT v.status SEPARATOR ' ;') status, GROUP_CONCAT(DISTINCT v.comment SEPARATOR ' ;') comment,
#	r.name, GROUP_CONCAT(DISTINCT v.method SEPARATOR ' ;') validation_method, r.pubMedID, r.description
#	FROM validation v INNER JOIN researchs r ON v.research_name=r.name
#	WHERE v.inv_id='$id'
#   GROUP BY r.name ORDER BY r.pubMedID DESC;";
    
$sql_val="SELECT DISTINCT r.name, r.validation_method, r.pubMedID, r.description, v.id, v.status, v.comment
			FROM validation v INNER JOIN researchs r ON v.research_name=r.name
			WHERE v.inv_id='$id' GROUP BY r.name ORDER BY r.pubMedID DESC;";    



$result_val=mysql_query($sql_val);
$echo_validations='';

while($thisrow = mysql_fetch_array($result_val)){
    
	$echo_validations.="<div class='report-section'>
			<div class='section-title TitleA'>- ";
	if ($thisrow['pubMedID'] != "") {$echo_validations.="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$thisrow['pubMedID']."' target='_blank'>".$thisrow['name']."</a>";}
	else {$echo_validations.= $thisrow['name'];}
	$echo_validations.="</div>
			<div class='grlsection-content'>

			<table width='100%'>";
			
			if (($thisrow['description'] != '') or ($_SESSION["autentificado"]=='SI')) {
			
			$echo_validations.="<tr><td class='title' width='20%'>Description</td><td>".ucfirst($thisrow['description'])."</td></tr><tr>";
			
			}

$sql_val_study ="SELECT DISTINCT r.name, r.pubMedID, r.description, v.id, v.method, v.status, v.comment
			FROM validation v INNER JOIN researchs r ON v.research_name=r.name
			WHERE v.inv_id='$id' AND r.name ='".$thisrow["name"]."' ORDER BY r.pubMedID DESC;";
$result_val_study =mysql_query($sql_val_study);

while($thisrow_study = mysql_fetch_array($result_val_study)){


	//para el support se cuentan todos los individuos de la validacion y se separan en funcion del genotipo
//	$sql_valSupport="SELECT count(distinct id.individuals_id) as count, id.genotype 
//		FROM individuals_detection id
//		WHERE id.inversions_id='$id' and id.validation_id='".$thisrow_study["id"]."' and id.validation_research_name='".$thisrow_study["name"]."'
//		GROUP BY id.genotype
//		ORDER BY FIELD(id.genotype,'STD/STD','STD/INV','INV/STD','INV/INV','STD','INV','NA','ND');";
   
	$sql_valSupport="SELECT count(distinct id.individuals_id) as count, IF(id.genotype = 'INV/STD', 'STD/INV', id.genotype) AS genotype2 
		FROM individuals_detection id
		WHERE id.inversions_id='$id' and id.validation_id='".$thisrow_study["id"]."' and id.validation_research_name='".$thisrow_study["name"]."'
		GROUP BY genotype2
		ORDER BY FIELD(genotype2,'STD/STD','STD/INV','INV/STD','INV/INV','STD','INV','NA','ND');";
   
        
	$result_valSupport=mysql_query($sql_valSupport);
	$valSupport='';$totalSupport=0;
	//en funcion del genotipo, cambia el texto. REVISAR LOS POSIBLES GENOTIPOS!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//el fichero php/ajaxAdd_validation.php tiene este mismo bloque de codigo, revisar tambien!!!!!!!!!!!!!
	while($supportrow = mysql_fetch_array($result_valSupport)){
		if ($totalSupport>0) {$valSupport.= '&nbsp;&nbsp;<b>-</b>&nbsp;&nbsp;';}
//		if ($supportrow['genotype'] == 'INV/INV'){$valSupport.=$supportrow['count'].' homozygote inverted individuals. ';}
//		else if ($supportrow['genotype'] == 'STD/STD'){$valSupport.=$supportrow['count'].' homozygote standard individuals. ';}
//		else if ($supportrow['genotype'] == 'STD/INV'||$supportrow['genotype'] == 'INV/STD'){$valSupport.=$supportrow['count'].' heterozygote individuals. ';}
//		else if ($supportrow['genotype'] == 'INV'){$valSupport.=$supportrow['count'].' hemizygous inverted individuals. ';}
//		else if ($supportrow['genotype'] == 'STD'){$valSupport.=$supportrow['count'].' hemizygous standard individuals. ';}
//        	else if ($supportrow['genotype'] == 'NA'){$valSupport.=$supportrow['count'].' not applicable individuals. ';}
//        	else if ($supportrow['genotype'] == 'ND'){$valSupport.=$supportrow['count'].' not determined individuals. ';}
//		else {$valSupport.=$supportrow['count'].' '.$supportrow['genotype'].' individuals. ';}
		$valSupport.=$supportrow['count'].' '.$supportrow['genotype2'];
		$totalSupport+=$supportrow['count'];
	}
//	if ($valSupport==''){$valSupport='0 individuals';}
	if ($totalSupport!=''||$totalSupport!=0){$totalSupport.=' individuals';}
	else{$totalSupport='';}

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
     
     //O freqüències sense genotips
     $getresult_nogenotypes='';
     $sql_nogenotypes = "SELECT SUM(pd.individuals) AS individuals 
FROM population_distribution pd
WHERE pd.inv_id='$id' AND pd.validation_id='".$thisrow_study["id"]."' AND pd.validation_research_name='".$thisrow_study["name"]."';";
     $result_nogenotypes=mysql_query($sql_nogenotypes);
     $getresult_nogenotypes = mysql_fetch_array($result_nogenotypes);

     		#$echo_validations.="<tr><td width='20%'></td><td></td></tr>";
     		$echo_validations.="&nbsp;";

			$echo_validations.="<table width='100%'><tr><td class='title' width='20%'>Method</td><td>".ucfirst($thisrow_study['method'])."</td></tr>
			<tr><td class='title' width='20%'>Status</td><td>".$thisrow_study['status']."</td></tr>";
			
			// here support/genotypes
			if (($totalSupport != '') or ($valSupport != '') or ($fosm_supportrow['fosm_resul'] != '') or ($getresult_nogenotypes['individuals'] != '') or ($_SESSION["autentificado"]=='SI')) {
		   
			$echo_validations.="<tr><td class='title' width='20%'>Genotyping</td><td>";
			
			if ($totalSupport != '') { $echo_validations.= "$totalSupport:  $writePanel"; } 
			
			else if ($getresult_nogenotypes['individuals']>0) { $echo_validations.=$getresult_nogenotypes['individuals']." individuals"; }
			
			else {}
			  
 			if ($valSupport != '') { $echo_validations.="<br/>$valSupport &nbsp;<a href='php/echo_individualsVal.php?id=".$id."&val=".$thisrow_study['name']."' ><img src='img/download.png' alt='Download' width='23' height='23'></a>"; }
 			
			else if ($getresult_nogenotypes['individuals']>0) { $echo_validations.= " (No genotypes available)"; }
			
			else {}
 			
			if ($fosm_supportrow['fosm_resul']){ $echo_validations.= " (Fosmids: ".$fosm_supportrow['fosm_resul'].")"; }
          		
          		$echo_validations .="</td></tr>";

			   }
			// end support/genotypes
			
			if (($thisrow_study['comment'] != '') or ($fosm_supportrow['fosm_commet'] != '') or ($_SESSION["autentificado"]=='SI')) {
	
	
	$echo_validations .="<tr><td class='title' width='20%'>Comment</td><td>".$thisrow_study['comment'];
     #isset($fosm_supportrow['fosm_commet']) ? $echo_validations.= $fosm_supportrow['fosm_commet']."</td></tr></table></div></div>" : $echo_validations.= "</td></tr></table></div></div>";
     if ($fosm_supportrow['fosm_commet']){ $echo_validations.= $fosm_supportrow['fosm_commet']."</td></tr>";}else {$echo_validations.= "</td></tr>";}
 
 			}
 $echo_validations.="</table>";
 }
 
 $echo_validations.= "</tr></table></div></div>";
 }


//### Frequency
/*//OLD:
$sql_freq="SELECT p.region, pd.population_name, pd.frequency
	FROM population p
	INNER JOIN population_distribution pd ON p.name=pd.population_name
	WHERE pd.inv_id ='$id';";
*/

$sql_freq="(SELECT distinct ind.population AS population, v.research_name AS research_name, p.region AS region 
	FROM validation v 
		INNER JOIN individuals_detection ind2 ON ind2.validation_id=v.id
		INNER JOIN individuals ind ON ind2.individuals_id=ind.id 
		INNER JOIN population p ON ind.population=p.name 
	WHERE v.inv_id='$id')
	
	UNION ALL
	
	(SELECT pd.population_name, pd.validation_research_name, p.region FROM population_distribution pd, population p 
	WHERE inv_id='$id' AND pd.population_name=p.name)
	
	ORDER BY region, population, research_name;";

$result_freq=mysql_query($sql_freq);
$info= array();

while ($thisrow = mysql_fetch_array($result_freq)){

//	if ($thisrow["population"] != 'unknown') {

		$info[$thisrow['region']][$thisrow['population']][$thisrow['research_name']]=$thisrow['research_name'];

//	}
	
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

while ($thisrow = mysql_fetch_array($result_freq)){
*/
	/*funcion inv_frequency
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
| YRI        | Korbel        | Africa  | //resultado insertado expresamente para probar que haya >1 estudio
| CHB        | genotyping    | Asia    |
| JPT        | genotyping    | Asia    |
| CEU        | genotyping    | Europe  |
| unknown    | genotyping    | unknown |
+------------+---------------+---------+
*/
	//buscare cada linea independientemente (excepto unknown)
	//tambien buscare all studies para una poblacion y region
	//tambien buscare all poblaciones para una region (sera all studies o tiene sentido alguna otra combinacion????????????????????)
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
///
*/
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($r['chr'] == 'chrY') {
$MultSample=1;
} else {
$MultSample=2;
}

$NewGraphs = "<div class='report-section'>
	<div class='section-title TitleA'>- Customizable graphs</div>
	<div class='grlsection-content'>
	<form name='frequency_graphNew' id='frequency_graphNew'>
	<table width='100%' id='NewGraphs'>";

foreach ($info as $region => $value){ //para cada region
//	//$results_freq .= mysql_query("SELECT inv_frequency('$id','population','region','research_name') AS res_freq");
	
	$result_freq = mysql_query("SELECT inv_frequency('$id','all','$region','all') AS res_freq");
	$results_freq = mysql_fetch_array($result_freq);
	
	$download_genotypes='';
	if ($results_freq['res_freq'] == '') {
	
		$result_freq = mysql_query("SELECT CONCAT(IFNULL(population_distribution.individuals,'NA'), ';', IFNULL(population_distribution.inverted_alleles,'NA'), ';', IFNULL(population_distribution.inv_frequency,'NA'), ';NA;NA') AS res_freq FROM population_distribution, population WHERE population_distribution.population_name=population.name AND population_distribution.inv_id='$id' AND population.region='$region' ORDER BY population_distribution.individuals DESC LIMIT 1;");
		$results_freq = mysql_fetch_array($result_freq);
	} else {
	
		$download_genotypes="<a href='php/echo_individualsVal.php?id=".$id."&region=".$region."' ><img src='img/download.png' alt='Download' width='23' height='23'></a>";
	}
	
	$data_freq = explode(";", $results_freq['res_freq']); //->analyzed individuals;independent alleles; inverted freq; HWE

	$anal_ind=$data_freq[0];
	$inv_alleles = $data_freq[1];
	$inv_freq=$data_freq[2];
	$std_freq=1-$inv_freq;
	$hwe=$data_freq[3];
	$noninv_alleles = $data_freq[4];
	
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
	
		$noninv_alleles = number_format($std_freq*$inv_alleles/$inv_freq, 0, '.', '');
	
	} else {
	
		//No hauria de passar mai
	
	}
	
	
//$std_freq='0.5';
//$inv_freq='0.5';

	if ($region=='unknown') {
	$addPrefix = '';
	} elseif ($region=='Europe') {
	$addPrefix = 'an';
	} else {
	$addPrefix = 'n';
	}
	
	$echo_frequency.="<div class='report-section'>
		<div class='section-title TitleA'>- ".ucfirst($region)."$addPrefix Population $download_genotypes</div>
		<div class='grlsection-content'>	
		"; // <input type='checkbox' name='graph[]' value='$noninv_alleles;$inv_alleles;$region' class='right' />
		
	// HERE I PREPARE GRAPHS FOR CONTINENTS...
	$regionSimple = preg_replace('/\W+/', '', $region); 
	if ($noninv_alleles == "ND") {
	
		$noninv_alleles=$std_freq;
		$inv_alleles=$inv_freq;
	
	}

	$NewGraphs .= "<tr>
		<td class='title' width='8%'>".ucfirst($region)."$addPrefix Population</td><td width='80%'>
				<select id='typeChart_$regionSimple' name='typeChart_$regionSimple'>
					<option value='all'>Each selected population in a separate graph</option>
					<option value='one'>All selected populations in a single graph</option>
				</select>
				&nbsp;&nbsp;
				<input type='button' value='Graph' onclick=\"drawChartNew('$regionSimple')\" />
				<input type='button' value='Clear' onclick=\"clearChartNew('$regionSimple')\" />
				<br/>
				<input type='checkbox' class='chkbox' value='$regionSimple' checked> <b>All|None</b>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<input type='hidden' name='NewGraphs_".$regionSimple."[]' value='0;0;hidden'  class='$regionSimple' checked>
				"; //<input type='image' src='img/chart_pie.png' alt='Graph' width='23' onClick=\"drawChartNew('$regionSimple'); return false;\">
						
	foreach ($info[$region] as $population =>$study_ar) {
		$num_studies=count($study_ar);
		if ($num_studies>1){ 
			$result_freq = mysql_query("SELECT inv_frequency('$id','$population','$region','all') AS res_freq");
		} else {
			$study=key($study_ar);
			$result_freq = mysql_query("SELECT inv_frequency('$id','$population','$region','$study') AS res_freq");
		}
		$results_freq = mysql_fetch_array($result_freq);
		
		// Si no he trobat dades amb genotips, busco sense genotips. En aquest cas només mostro l'estudi amb més individus analitzats, ja que no puc sumar-los perquè no sé si són els mateixos individus
		$download_genotypes='';
		if (($results_freq['res_freq'] == '') || ($results_freq['res_freq'] == 'NA;NA;NA;NA;NA')) {
		
			$result_freq = mysql_query("SELECT CONCAT(IFNULL(individuals,'NA'), ';', IFNULL(inverted_alleles,'NA'), ';', IFNULL(inv_frequency,'NA'), ';NA;NA') AS res_freq FROM population_distribution WHERE inv_id='$id' AND population_name='$population' ORDER BY individuals DESC LIMIT 1;");
			$results_freq = mysql_fetch_array($result_freq);
		} else {
		
			$download_genotypes="<a href='php/echo_individualsVal.php?id=".$id."&pop=".$population."' ><img src='img/download.png' alt='Download' width='23' height='23'></a>";
		}
		
		$data_freq = explode(";", $results_freq['res_freq']); //->analyzed individuals;independent alleles; inverted freq; HWE
		
		$anal_ind=$data_freq[0];
		$inv_alleles = $data_freq[1];
		$inv_freq=$data_freq[2];
		$std_freq=1-$inv_freq;
		$hwe=$data_freq[3];
		$noninv_alleles = $data_freq[4];
	
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
	
			$noninv_alleles = number_format($std_freq*$inv_alleles/$inv_freq, 0, '.', '');
	
		} else {
	
			//No hauria de passar mai
	
		}
		
		$indep_alleles=$inv_alleles;
		
		$echo_frequency.="
			<table width='100%' id='tableStudy$population'>
			<tr><th colspan='4' class='title' width='20%'>".ucfirst($population)." $download_genotypes</div>
		<div class='grlsection-;$population' class='right' /></th></tr>
			<tr>
				<td class='title' width='20%' id='tdStudy$population'>Study</td><td width=20%>";
		if ($num_studies>1){ //si hay >1 estudio, creamos un desplegable
			$echo_frequency.="<select id='selectStudy' onchange=\"changeFreqs(this,'tableStudy$population','$id','$population','$region')\"><option value='all'>All studies</option>";
			foreach($info[$region][$population] as $study => $value) {
				$echo_frequency.="<option value='$study'>$study</option>";
			}
			$echo_frequency.="</select>";
		} else { //si solo hay 1 estudio, lo mostramos tal cual
			$echo_frequency.=key($study_ar);
	
		}

		$echo_frequency.="
				</td>
				<td class='title' width='20%'>Standard frequency</td><td>".number_format($std_freq,4)."</td>
			</tr>
			<tr>
				<td class='title' width='20%'>Analyzed individuals</td><td width='30%'>".$anal_ind."</td>
				<td class='title'>Inverted frequency</td><td>".number_format($inv_freq,4)."</td>
			</tr>
			<tr>
				<td class='title' width='20%'>Inverted alleles</td><td width='30%'>".$indep_alleles."</td>
				<td class='title' width='20%'>Hardy-Weinberg eq.</td><td>".$hwe."</td>
			</tr>
			</table>
			";
			
		// HERE I PREPARE GRAPHS FOR POPULATIONS...
		if ($noninv_alleles == "NA") {
		
			$noninv_alleles=$std_freq;
			$inv_alleles=$inv_freq;
		
		}
	
		$NewGraphs .= "<input type='checkbox' name='NewGraphs_".$regionSimple."[]' value='$noninv_alleles;$inv_alleles;$population'  class='$regionSimple' checked> $population &nbsp;&nbsp;"; 
		
	}
	
	
	// 	HERE I FINISH THE GRAPH TABLE
	
	$NewGraphs .= "	<br/><div id='chart_graph_$regionSimple' style='display:inline-block'></div>
		</td>
	</tr>
	";
	
	$echo_frequency.="</div>
		</div>
";
}

$NewGraphs .= "</table></form></div></div>";

/*
echo "<PRE>";
print_r($info);
echo "</PRE>";
*/

// Add new frequency without genotypes
$sql_popul="SELECT DISTINCT name, region FROM population;";
$result_popul=mysql_query($sql_popul);
$fng_population='';
while ($thisrow = mysql_fetch_array($result_popul)){
	$fng_population.='<option value="'.$thisrow['name'].'">'.$thisrow['name'].' ('.$thisrow['region'].')</option>';
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
while ($thisrow = mysql_fetch_array($result_seqFeat)){
	$size=$thisrow['chromEnd']-$thisrow['chromStart']+1;
	$othersize=$thisrow['otherEnd']-$thisrow['otherStart']+1;
	$orientation='';
	if ($thisrow['strand']=='-'){$orientation='Inverted';}
	elseif($thisrow['strand']=='+'){$orientation='Direct';}
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
while($evrow = mysql_fetch_array($result_ev)){

	if ($evrow['pubMedID'] != "") {$studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$evrow['pubMedID']."' target='_blank'>".ucfirst($evrow['source'])."</a>";}
	else {$studyname=ucfirst($evrow['source']);}

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
while($agerow = mysql_fetch_array($result_ev_age)){

	if ($agerow['pubMedID'] != "") {$studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$agerow['pubMedID']."' target='_blank'>".ucfirst($agerow['source'])."</a>";}
        else {$studyname=ucfirst($agerow['source']);}

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
while($origrow = mysql_fetch_array($result_ev_origin)){

	if ($origrow['pubMedID'] != "") {$studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$origrow['pubMedID']."' target='_blank'>".ucfirst($origrow['source'])."</a>";}
        else {$studyname=ucfirst($origrow['source']);}

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
	ORDER BY h.symbol;"; //solo del ultimo breakpoint (es implicito ya que tengo el breakpoint_id guardado)

$result_ge=mysql_query($sql_ge);
$echo_symbols=''; $this_symbol = '';
while($thisrow = mysql_fetch_array($result_ge)){

	if ($thisrow['symbol'] != $this_symbol) {
	
	if ($this_symbol != '') {	
			$echo_functional_effect.="
			</div>
			</div>";
	}	

	$this_symbol = $thisrow['symbol'];
		
	$gene_relation = preg_replace("/(\S+)(, NA)/", "$1", $thisrow['gene_relation']);
    //str_ireplace(", NA","",$thisrow['gene_relation']);

	if ($thisrow['pubMedID'] != "") {$studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$thisrow['pubMedID']."' target='_blank'>".ucfirst($thisrow['source'])."</a>";}
        else {$studyname=ucfirst($thisrow['source']);}
 
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
while($thisrow = mysql_fetch_array($result_fe)){
	
	if ($thisrow['pubMedID'] != "") {$studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$thisrow['pubMedID']."' target='_blank'>".ucfirst($thisrow['source'])."</a>";}
        else {$studyname=ucfirst($thisrow['source']);}

	$echo_phenotypical_effect.="<tr><td>".$thisrow['effect']."</td><td>".$studyname."</td></tr>";
}


// Apartado Report History (J)
/*$sql_history="SELECT previous_inv_id
		FROM inversion_history
		WHERE new_inv_id='$id';";
*/
$sql_history="SELECT * FROM inversion_history
		WHERE previous_inv_id='$id' or new_inv_id='$id';";
$result_history=mysql_query($sql_history);
$history='';
while($historyrow = mysql_fetch_array($result_history)){
	$history.=$historyrow['cause'].'<br>'; //crear los enlaces a las inversiones precedentes. SOLO CON EL NOMBRE?????????????
}
//if ($history != '') {
//	$history = '<ul>'.$history;
//	$history .= '</ul>';
//}

// Historial de breakpoints (E)
$sql_bp="SELECT b.id, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, b.definition_method, b.description, b.date, v.research_name as v_research_name, r.year, r.pubMedID 
	FROM  breakpoints b LEFT JOIN validation v ON (v.bp_id = b.id) LEFT JOIN researchs r ON r.name=v.research_name WHERE b.inv_id ='$id' 
	ORDER BY b.date DESC ;"; //FIELD (b.definition_method, 'manual curation', 'default informatic definition'), 
$result_bp=mysql_query($sql_bp);
while($bprow = mysql_fetch_array($result_bp)){
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

	$bp_history.= '<div class="report-section"><div class="section-title TitleA">- '.$bprow['date'].'</div>
		<div class="grlsection-content ContentB">
			<table width="100%">
            <tr><td class="title" width="20%">Breakpoint 1</td><td>'.$r['chr'].':'.$bprow['bp1_start']."-".$bprow['bp1_end'].'</td>
                    <td class="title" width="20%">Breakpoint 2<td>'.$r['chr'].':'.$bprow['bp2_start']."-".$bprow['bp2_end'].'</td></td>
            </tr>';
            
            	if (($bprow['v_research_name'] != '') or ($_SESSION["autentificado"]=='SI')) {

		if ($bprow['pubMedID'] != "") {$studyname="<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$bprow['pubMedID']."' target='_blank'>".ucfirst($bprow['v_research_name'])."</a>";}
        else {$studyname=ucfirst($bprow['v_research_name']);}
		
            	$bp_history.= '<tr><td class="title" width="20%">Study</td><td colspan="3">'.$studyname.'</td></tr>';
            	
            	}
            	
   		if (($bprow['description'] != '') or ($_SESSION["autentificado"]=='SI')) {
   				
		$bp_history.= '<tr><td class="title" width="20%">Description</td><td colspan="3">'.ucfirst($bprow['description']).'</td></tr>';
		
		}

            	if (($bprow['definition_method'] != '') or ($_SESSION["autentificado"]=='SI')) {
            	
   		$bp_history.= '<tr><td class="title" width="20%">Definition method</td><td colspan="3">'.$array_definitionmethod[$bprow['definition_method']].'</td></tr>  ';       	
   		}
   		
	  $bp_history.= ' <!-- <tr><td class="title" width="20%">Date</td><td>'.$bprow['date'].'</td></tr>
			<tr><td class="title" width="20%">Sequence features</td><td> ';
	while($sfhrow = mysql_fetch_array($result_seqFeat_Hist)){
		$size=$sfhrow['chromEnd']-$sfhrow['chromStart']+1;
		$othersize=$sfhrow['otherEnd']-$sfhrow['otherStart']+1;
		$orientation='';
		if ($sfhrow['strand']=='-'){$orientation='Inverted';}
		elseif($sfhrow['strand']=='+'){$orientation='Direct';}
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

	while($gehrow = mysql_fetch_array($result_ge_Hist)){
//
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


// APARTADO ADVANCED EDITION
//merge inversions
if ($_SESSION["autentificado"]=='SI'){
	$sql_inv="select distinct id, name from inversions order by name, id;";
	$result_inv = mysql_query($sql_inv);

	$inv2='<option value="">-Select-</option>\n';
	while($thisrow = mysql_fetch_array($result_inv)){
		if ($thisrow['id'] != $id) {
			$inv2.="<option value='".$thisrow["id"]."'>";
			if ($thisrow["name"] != "" || $thisrow["name"] != NULL) {
				$inv2.=$thisrow["name"];
			} else {
				$inv2.='id '.$thisrow["id"];
			}
			$inv2.="</option>\n";
		}
	}
}

//Add a new validation
$chr=''; $research_name=''; $method_add_val=''; $status_add_val='';

$sql_chr="select chr from inversions where id='$id';";
$result_chr = mysql_query($sql_chr);
$chr = mysql_fetch_array($result_chr);


$sql_research_name="select distinct name from researchs where name is not null order by name;";
$result_research_name = mysql_query($sql_research_name);
while($thisrow = mysql_fetch_array($result_research_name)){
	$research_name.="<option value='".$thisrow["name"]."'>".$thisrow["name"]."</option>";
}

//$sql_method="select distinct method from validation where method is not null order by method;";
$sql_method="select distinct name as method from methods where name is not null order by name;"; //and aim like '%validation%' 
//ponemos todos los metodos o solo los de validacion??????????????????????????????????????????????????????????????????????
$result_method = mysql_query($sql_method);
while($thisrow = mysql_fetch_array($result_method)){
	$method_add_val.="<option value='".$thisrow["method"]."'>".$thisrow["method"]."</option>";
}

#$sql_status="select distinct status from validation where status is not null order by status;";
#$result_status = mysql_query($sql_status);
#while($thisrow = mysql_fetch_array($result_status)){
#	$status_add_val.="<option value='".$thisrow["status"]."'>".$thisrow["status"]."</option>\n";
#}
$status_add_val.="<option value='Breakpoint refinement'>Breakpoint refinement</option>\n
<option value='genotyping'>genotyping</option>\n
<option value='TRUE'>TRUE</option>\n
<option value='FALSE'>FALSE</option>\n";
//<option value='possible_TRUE'>possible_TRUE</option>\n

//Add evolutionary information
$species=''; $orientation=''; 
//study|source estan en $research_name
//method estan en $method_add_val

$sql_species="select distinct id,name from species where id is not null order by name;";
//$sql_species="SELECT DISTINCT id,name FROM species WHERE id IS NOT null AND id NOT IN (SELECT DISTINCT sp.id FROM species sp INNER JOIN inversions_in_species iis ON sp.id=iis.species_id WHERE iis.inversions_id='$id') ORDER BY name;";
$result_species = mysql_query($sql_species);
while($thisrow = mysql_fetch_array($result_species)){
	$species.="<option value='".$thisrow["id"]."'>".$thisrow["name"]."</option>";
}

$sql_orientation="select distinct orientation from inversions_in_species where orientation is not null order by orientation;";
$result_orientation = mysql_query($sql_orientation);
while($thisrow = mysql_fetch_array($result_orientation)){
	$orientation.="<option value='".$thisrow["orientation"]."'>".$thisrow["orientation"]."</option>";
}


//Split inversions
$sql_pred="SELECT p.id, p.research_name, p.BP1s, p.BP1e, p.BP2s, p.BP2e
	FROM predictions p 
	WHERE p.inv_id='$id' 
	ORDER BY p.research_name;";
$result_pred = mysql_query($sql_pred);
$predictions='';
while($thisrow = mysql_fetch_array($result_pred)){
	$predictions.="<tr><td><a title=\"BP1:".$thisrow['BP1s']."-".$thisrow['BP1e']." BP2:".$thisrow['BP2s']."-".$thisrow['BP2e']."\">".$thisrow['research_name']."</a></td>";
	$predictions.="<td><input type='checkbox' value='".$thisrow['id']."' name='pinv1[]' /></td>
		<td><input type='checkbox' value='".$thisrow['id']."' name='pinv2[]' /></td></tr>";
}

$sql_val="SELECT v.id, v.research_name, v.method, v.status, v.experimental_conditions, v. primers, v.comment
	FROM validation v
	WHERE v.inv_id='$id';";
$result_val=mysql_query($sql_val);
$validations='';
while($thisrow = mysql_fetch_array($result_val)){
	$validations.="<tr><td><a title=\"Method: ".$thisrow['method']."; Status: ".$thisrow['status'];
	if ($thisrow['experimental_conditions'] != "" || $thisrow['experimental_conditions'] != NULL) {
		$validations.="; Experimental Conditions: ".$thisrow['experimental_conditions'];
	}
	if ($thisrow['primers'] != "" || $thisrow['primers'] != NULL) {
		$validations.="; Primers: ".$thisrow['primers'];
	}
	if ($thisrow['comment'] !="" || $thisrow['comment']!= NULL){
		$validations.="; Comments: ".$thisrow['comment'];
	}
	$validations.="\">".$thisrow['research_name']."</td>
		<td><input type='checkbox' value='".$thisrow['id']."' name='vinv1[]' /></td>
		<td><input type='checkbox'value ='".$thisrow['id']."' name='vinv2[]' /></td></tr>";

}


mysql_close($con);

/*

*/
?>
