<?php
//$q=$_GET["q"];

//$inv=$_POST["name"];
//$chr=$_POST["chr"]; //multiple
//$range_start=$_POST["range_start"];
//$range_end=$_POST["range_end"];
$search_field=$_POST["search_field"];
$size=$_POST["size"];
$size_value=$_POST["size_value"];
$size_valueB=$_POST["size_valueB"];
//$score=$_POST["score"];
$inv_status=$_POST["inversion_status"]; //multiple

$research=$_POST["research"]; //multiple
$validation_method=$_POST["validation_method"]; //multiple
$validation_status=$_POST["validation_status"]; //multiple
$fosmids=$_POST["fosmids"]; 
$individual=$_POST["individual"]; //multiple

$population=$_POST["population"];
$freq_distr=$_POST["freq_distr"];
$fred_distr_value=$_POST["freq_distr_value"];
$fred_distr_valueB=$_POST["freq_distr_valueB"];

$seg_dup=$_POST["seg_dup"];
$gene_symbol=$_POST["gene_symbol"];

$species=$_POST["species"];
$orientation=$_POST["orientation"];

//$effect=$_POST["effect"];

// --- CONSULTA BBDD ----------------------------------------------------------------------
include_once('db_conexion.php');

$select = "i.id,
	i.name,
	i.chr,
	i.range_start,
	i.range_end,
	i.size,
	i.status
	";
// 	h.symbol

$from = " inversions i ";
//$from = " inversions i LEFT JOIN genomic_effect g ON i.id=g.inv_id LEFT JOIN HsRefSeqGenes h ON g.gene_id=h.idHsRefSeqGenes";
/*
Se pueden combinar left join con varias tablas en el from pero se debe ir con cuidado con el orden de las tablas. La tabla1 del left join debe estar al final del from
*/

//FILTROS:

$where=array();
$from_array=array();
//de la tabla 'inversions'
//if ($inv != "") {array_push($where," i.name='$inv' ");}
/*if ($chr != "") {
	$chr_array= strpos ($chr,',');
	if ($chr_array !== false) {
		//hay varios
		$where_chr=array();
		$chrs =  explode(",",$chr);
		foreach ($chrs as $value) {
			array_push($where_chr," i.chr='$value' ");
			}
		$where_chr2 = implode(" OR ", $where_chr);
		array_push ($where, " ($where_chr2)");		
	} else { array_push($where," i.chr='$chr' ");}
}
*/
//if ($range_start != "") {array_push($where," i.range_start>'$range_start' ");} 
//if ($range_end != "") {array_push($where," i.range_end<'$range_end' ");} 

if ($search_field != "") {
/*		- coordenades a l estil chr1:22222222-777777777 (permet fer copy-paste del UCSC), no separat en tres camps com està ara
                - inversion name (camp "name" de la taula "inversions")
                - gene name (camp "symbol" de la taula "HsRefSeqGenes")
                - fosmid name (camp "code" de la taula "fosmids")
*/

	$chr_pos= strpos ($search_field,':'); 
	if ($chr_pos !== false) {
		//formato coordenades a l estil chr1:22222222-777777777
		$data = explode(":",$search_field);
		$chr = strpos ($data[0], 'chr');
		if ($chr !== false) {
			//chr debe ser un numero de 1 a 22, X, Y, M o MT
			$chr_tmp=str_replace('chr','',$data[0]);
			echo 'data:'.$chr_tmp.'<br>';
			if ((preg_match('/^[0-9]{1,2}$/',$chr_tmp) && $chr_tmp>0 && $chr_tmp<23)|| preg_match('/^[XYMxym]$/',$chr_tmp)|| preg_match('/^mt$/',$chr_tmp) || preg_match('/^MT$/',$chr_tmp)){
				array_push($where, " i.chr='$data[0]' ");
			}
			else {echo 'error in chr range<br>'; }
		}
		else {echo 'error in chr range<br>';
		}
		$pos = strpos ($data[1], '-');
		if ($pos !== false) {
			$positions=explode('-',$data[1]);
			//posicion inicio y fin deben ser un numero positivo y menor de 246.795.301 (300.000.000)
			//posicion inicio debe ser < posicion fin
			if ((preg_match('/^[0-9]+$/',$positions[0]) && $positions[0]>0 && $positions[0]<300000000 && ($positions[1]>$positions[0]))){
				array_push($where, " i.range_start>'$positions[0]' ");
			}
			else {echo 'error in positions range<br>';}
			if ((preg_match('/^[0-9]+$/',$positions[1]) && $positions[1]>0 && ($positions[1]>$positions[0]))){
				array_push($where, " i.range_end<'$positions[1]' ");
			}
			else {echo 'error in positions range<br>';}
		} else { // en USCS cuando solo hay un numero, la busqueda la hacen solo de esa posicion ! 
			// en inversiones no tiene sentido, se da error
			echo 'error in positions range (only one number introduced)<br>';
		}
	} else {
		if (preg_match('/^[0-9]+$/',$search_field)) {
			$where_search=array();
			array_push($where_search, "i.chr='$search_field' ");
			array_push($where_search, " i.name='$search_field' ");
			array_push($where_search, " h.symbol='$search_field' ");
			array_push($where_search, " f.code='$search_field' ");
			$where_search2 = implode (" OR ", $where_search);
			array_push ($where, " ($where_search2) ");
			array_push ($where, " i.id=g.inv_id "); // a partir de aqui no funciona si los campos estan vacios
			array_push ($where, " g.gene=h.idHsRefSeqGenes ");
			array_push ($where, " i.id=fv.inv_id ");
			array_push ($where, " fv.fosmids_id=f.id ");
			array_push($from_array, " genomic_effect g ");
			array_push($from_array, " HsRefSeqGenes h ");
			array_push($from_array, " fosmids f ");
			array_push($from_array, " fosmids_validations fv ");
		}
		else { echo 'error in Search Field<br>';}
	}

}

if ($size_value != "" || $size_valueB != "") {
	if ($size == 'gt') {
		array_push($where," i.size>'$size_value' ");
	} elseif ($size == 'lt') {
		array_push($where," i.size<'$size_value' ");
	} else {
		array_push($where," i.size>'$size_value' ");
		array_push($where," i.size<'$size_valueB' ");
	}
}
	
//if ($score != "") {array_push($where," i.score='$score' ");} //score <, > o rango
if ($inv_status != "") {
	$inv_status_array= strpos ($inv_status,',');
	if ($inv_status_array !== false) {
		//hay varios
		$where_status=array();
		$statuss =  explode(",",$inv_status);
		foreach ($statuss as $value) {
			array_push($where_status," i.status='$value' ");
			}
		$where_status2 = implode(" OR ", $where_status);
		array_push ($where, " ($where_status2)");		
	} else { array_push($where," i.status='$inv_status' ");}
}

//de la tabla 'validation'
if ($research != "") {
	$research_array= strpos ($research,',');
	if ($research_array !== false) {
		//hay varios
		$where_research=array();
		$researchs =  explode(",",$research);
		foreach ($researchs as $value) {
			array_push($where_research," v.research_name='$value' ");
			}
		$where_research2 = implode(" OR ", $where_research);
		array_push ($where, " ($where_research2)");		
	} else { array_push($where," v.research_name='$research' ");}
}
if ($validation_method != "") {
	$validation_method_array= strpos ($validation_method,',');
	if ($validation_method_array !== false) {
		//hay varios
		$where_validation_method=array();
		$validation_methods =  explode(",",$validation_method);
		foreach ($validation_methods as $value) {
			array_push($where_validation_method," v.method='$value' ");
			}
		$where_validation_method2 = implode(" OR ", $where_validation_method);
		array_push ($where, " ($where_validation_method2)");		
	} else { array_push($where," v.method='$validation_method' ");}
}
if ($validation_status != "") {
	$validation_status_array= strpos ($validation_status,',');
	if ($validation_status_array !== false) {
		//hay varios
		$where_validation_status=array();
		$validation_statuss =  explode(",",$validation_status);
		foreach ($validation_statuss as $value) {
			array_push($where_validation_status," v.status='$value' ");
			}
		$where_validation_status2 = implode(" OR ", $where_validation_status);
		array_push ($where, " ($where_validation_status2)");		
	} else { array_push($where," v.status='$validation_status' ");}
}
//from: validation v
if ($research != "" || $validation_method != "" || $validation_status != "") 
	{
	array_push($where," v.inv_id=i.id ");
	array_push($from_array, " validation v ");
	} 

//de la tabla 'fosmids' *********************************************************
if ($fosmids == "yes") {
//	array_push($w,".."); // es un checkbox, como hago el select?
//from: fosmids_validation f
//	array_push($from_array, " fosmids_validation f ");
//	array_push($where, " f.inv_id=i.id ");
}

//de la tabla 'individuals'
if ($individual != "") {
	$individual_array= strpos ($individual,',');
	if ($individual_array !== false) {
		//hay varios
		$where_individual=array();
		$individuals =  explode(",",$individual);
		foreach ($individuals as $value) {
			array_push($where_individual," ind.individuals_id='$value' ");
			}
		$where_individual2 = implode(" OR ", $where_individual);
		array_push ($where, " ($where_individual2)");		
	} else { array_push($where," ind.individuals_id='$individual' ");}
//from: individuals_detection ind_d
	array_push($where, " ind.inversions_id=i.id");
	array_push($from_array, " individuals_detection ind ");
}

//de la tabla population distribution
if ($population != "") {array_push($where, " p.population_name='$population' ");}
//puede ser un rango, > o <
if ($freq_distr_value != "" && $freq_distr == 'lt') {array_push($where, " p.frequency<'$freq_distr_value' ");}
if ($freq_distr_value != "" && $freq_distr == 'gt') {array_push($where, " p.frequency>'$freq_distr_value' ");}
if ($freq_distr_value != "" && $freq_distr == 'between') {
	array_push($where, " p.frequency>'$freq_distr_value' ");
	array_push($where, " p.frequency<'$freq_distr_value2' ");
	}
if ($population != "" || $freq_distr_value != "")  {
	//from: population_distribution p
	array_push($where, " p.inv_id=i.id ");
	array_push($from_array, " population_distribution p ");
	}

//de la tabla 'seg_dups' ***************************************************************
if ($seg_dup == "yes") {array_push($w,"..");} // es un checkbox, como hago el select?
//y el from: ?? QUE RELACION HAY CON LAS INVERSIONES??


//de la tabla HsRefSeqGenes
if ($gene_symbol != "") {array_push($where," h.symbol='$gene_symbol' ");}


//de la tabla inversons_in_species
if ($species != "") {array_push($where," iis.species_id='$species' ");}
if ($orientation != "") {array_push($where, " iis.orientation='$orientation' ");}
//from: inversions_in_species iis
if ($species != "" || $orientation != "") {
	array_push($from_array, " inversions_in_species iis ");
	array_push($where, " iis.inversions_id=i.id ");
}


$from_sql = implode (",", $from_array);
if ($from_sql != "") {$from = $from_sql.", ". $from;}

$where_sql = implode(" and ", $where);
if ($where_sql !="") {$where_sql = " WHERE ".$where_sql;}

$sql=" SELECT $select FROM $from $where_sql ;";//LIMIT 0,10;";

echo $sql."<br>";

$result = mysql_query($sql);
//---------------------------------------------------------------------------------------------

sleep(1);
$i = 1;

echo "<br>";
echo "<div class=\"section-title2\">Inversions:</div>";
echo '<div id="results_table">
      <table id="sort_table">';
//echo "inversion: ".$inv."<br>";
echo '<thead>
	  <tr>
		<th>Name <img src=\'css/img/sort.gif\'></th>
		<th>Chromosome <img src=\'css/img/sort.gif\'></th>
		<th>Range start <img src=\'css/img/sort.gif\'></th>
		<th>Range end <img src=\'css/img/sort.gif\'></th>
		<th>Inversion size <img src=\'css/img/sort.gif\'></th>
';
//		<th>Score <img src=\'css/img/sort.gif\'></th>
echo'		<th>Status <img src=\'css/img/sort.gif\'></th>
';
//		<th>Affected genes <img src=\'css/img/sort.gif\'></th>
echo'	  </tr>
	  </thead>
	  <tbody>';

	while($row = mysql_fetch_array($result)){
		echo "<tr>";
		echo "<td><a href=\"report.php?q=".$row['id']."\" target=\"_blank\" >".$row['name']."</a></td>";
		//echo "<td><a href=\"report.php?q=".$row['id']."\">".$row['id']."</a></td>";
		echo "<td>".$row['chr']."</td>";
		echo "<td>".$row['range_start']."</td>";
		echo "<td>".$row['range_end']."</td>";
		echo "<td>".$row['size']."</td>";
		//echo "<td>".$row['score']."</td>";
		echo "<td>".$row['status']."</td>";
		//echo "<td>".$row['effect']."</td>";
		echo "</tr>";
	}
echo "</tbody></table>";

mysql_close($con);

?>
