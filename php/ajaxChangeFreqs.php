<?php
// ajaxChangeFreqs.php

// obtiene el numero de la inversion, region, poblacion y estudio seleccionado (solo cuando hay >1)
/*
Obtiene las frecuencias y los valores para el estudio en cuestion con la funcion inv_frequency
Tambien se conecta a la bbdd para ver el nombre de los otros estudios (para poder volver a crear el select)
imprime todo el contenido de la tabla con los nuevos datos, manteniendo el select y cambiando el que esta seleccionado por defecto
*/

session_start(); //Inicio la sesión

include_once('db_conexion.php');
include_once('php/php_global_variables.php');

// Retrieve the query and generate the URL.
$inv_id = $_GET["q"];
$population = $_GET["pop"];
$region = $_GET["reg"];
$study = $_GET["stud"];


$result_freq = mysql_query("SELECT inv_frequency('$inv_id','$population','$region','$study') AS res_freq");
$results_freq = mysql_fetch_array($result_freq);

// Studies without genotypes if ($result_freq == '')
$download_genotypes='';
if ($results_freq['res_freq'] == '') {
	
	if ($study == 'all') {
	
		$result_freq = mysql_query("SELECT CONCAT(IFNULL(individuals,'NA'), ';', IFNULL(inverted_alleles,'NA'), ';', IFNULL(inv_frequency,'NA'), ';NA;NA') AS res_freq FROM population_distribution WHERE inv_id='$inv_id' AND population_name='$population' ORDER BY individuals DESC LIMIT 1;");
	
	} else {
	
		$result_freq = mysql_query("SELECT CONCAT(IFNULL(individuals,'NA'), ';', IFNULL(inverted_alleles,'NA'), ';', IFNULL(inv_frequency,'NA'), ';NA;NA') AS res_freq FROM population_distribution WHERE inv_id='$inv_id' AND population_name='$population' AND validation_research_name='$study' ORDER BY individuals DESC LIMIT 1;");
	
	}	
	
	$results_freq = mysql_fetch_array($result_freq);

	} else {
	
		$download_genotypes="<a href='php/echo_individualsVal.php?id=".$inv_id."&pop=".$population."' ><img src='img/download.png' alt='Download' width='23' height='23'></a>";
	}
///

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
	
		// Vull saber chromosoma
		$r_sql = mysql_query("SELECT chr FROM inversions WHERE id='$inv_id';");
		$r = mysql_fetch_array($r_sql);
		
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

$sql_freq_study="SELECT distinct v.research_name
	FROM validation v 
		INNER JOIN individuals_detection ind2 ON ind2.validation_id=v.id 
		INNER JOIN individuals ind ON ind2.individuals_id=ind.id 
		INNER JOIN population p ON ind.population=p.name 
	WHERE v.inv_id='$inv_id' AND ind.population='$population' AND p.region='$region'
	ORDER BY p.region, ind.population, v.research_name;";
$result_freq_study=mysql_query($sql_freq_study);


echo "<tr><th colspan='4' class='title' width='20%'>$population $download_genotypes
<!-- <input type='checkbox' name='graph[]' value='$std_freq;$inv_freq;$population' class='right' />  -->
</th></tr>
<tr>
	<td class='title' width='20%'>Study</td><td width='30%'>
		<select id='selectStudy' onchange=\"changeFreqs(this,'tableStudy$population','$inv_id','$population','$region')\">";
if ($study == 'all'){echo "			<option value='all' selected='selected'>All studies</option>";
}
else  {echo "			<option value='all' >All studies</option>";}
$selected='';
while ($thisrow = mysql_fetch_array($result_freq_study)){
	if ($thisrow['research_name']==$study){$selected=" selected='selected'";}
	else{$selected='';}
	echo "<option value='".$thisrow['research_name']."' $selected>".$thisrow['research_name']."</option>";
	$selected='';
}

// Add studies without genotypes
$sql_freq_study2="SELECT distinct validation_research_name FROM population_distribution  
	WHERE inv_id='$inv_id' AND population_name='$population' ORDER BY validation_research_name;";
$result_freq_study2=mysql_query($sql_freq_study2);
while ($thisrow = mysql_fetch_array($result_freq_study2)){
	if ($thisrow['validation_research_name']==$study){$selected=" selected='selected'";}
	else{$selected='';}
	echo "<option value='".$thisrow['validation_research_name']."' $selected>".$thisrow['validation_research_name']."</option>";
	$selected='';
} 
///
			

echo "		</select>
	</td>
	<td class='title' width='20%'>Standard frequency</td><td>".number_format($std_freq,4)."</td>
</tr>
<tr>
	<td class='title' width='20%'>Analyzed individuals</td><td width='20%'>".$anal_ind."</td>
	<td class='title' width='20%'>Inverted frequency</td><td>".number_format($inv_freq,4)."</td>
</tr>
<tr>
	<td class='title' width='20%'>Inverted alleles</td><td width='20%'>".$indep_alleles."</td>
	<td class='title' width='20%'>Hardy-Weinberg eq.</td><td>".$hwe."</td>
</tr>
";

mysql_close($con);

?>

