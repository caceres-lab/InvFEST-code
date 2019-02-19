<?php
/******************************************************************************
	AJAXCHANGEFREQS.PHP

	Allows to change between studies to check the frequency of the inversion inside a population at the report webpage

    Obtiene el numero de la inversion, region, poblacion y estudio seleccionado (solo cuando hay >1)
    
    Obtiene las frecuencias y los valores para el estudio en cuestion con la funcion inv_frequency
    Tambien se conecta a la BBDD para ver el nombre de los otros estudios (para poder volver a crear el select)
    imprime todo el contenido de la tabla con los nuevos datos, manteniendo el select y cambiando el que esta seleccionado por defecto
    
*******************************************************************************/

    session_start(); //Inicio la sesión

    include_once('db_conexion.php');
    include_once('php/php_global_variables.php');

    // Retrieve the query and generate the URL.
    $inv_id = $_GET["q"];
    $population = $_GET["pop"];
    $region = $_GET["reg"];
    $study_ln = $_GET["stud"];
    $study_test= preg_replace('/.*\(/', '', $study_ln);
    $study =  preg_replace('/\)/', '', $study_test);
    $study_query = $study;

    if ($study == 'in preparation'){
    	$study = "Martinez-Fundichely et al. 2014 (in preparation)";
    	$study_query = "Martinez\-Fundichely et al\. 2014 \(in preparation\)";
    }

	$pop_description = preg_replace('/ \('.$study_query.'\)/','',$study_ln);		

	if ($study != "all") {
		$q = "SELECT id from population where sampling = '$pop_description';";
		$getid = mysql_query($q);
		$getid = mysql_fetch_array($getid);
		$pop_id = $getid['id']; 
	}else{
		$pop_id = "all";
	}


    $result_freq = mysql_query("SELECT inv_frequency('$inv_id','$population','$pop_id', '$region','$study') AS res_freq"); # only this result exactly
    $results_freq = mysql_fetch_array($result_freq);
    $download_genotypes='';
    if ($results_freq['res_freq'] == '') {
	    if ($study == 'all') {
		    $result_freq = mysql_query("SELECT CONCAT(IFNULL(individuals,'NA'), ';', IFNULL(inverted_alleles,'NA'), ';', IFNULL(inv_frequency,'NA'), ';NA;NA') AS res_freq FROM population_distribution WHERE inv_id='$inv_id' AND population_id='$pop_id' ORDER BY individuals DESC LIMIT 1;");
	    } else {
		    $result_freq = mysql_query("SELECT CONCAT(IFNULL(individuals,'NA'), ';', IFNULL(inverted_alleles,'NA'), ';', IFNULL(inv_frequency,'NA'), ';NA;NA') AS res_freq FROM population_distribution WHERE inv_id='$inv_id' AND population_id='$pop_id' AND validation_research_name='$study' ORDER BY individuals DESC LIMIT 1;");
	    }
	    $results_freq = mysql_fetch_array($result_freq);
	} else {
	    $download_genotypes="
                    <a href='php/echo_individualsVal.php?id=".$inv_id."&pop=".$population."' >
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

    $indep_alleles=$inv_alleles;

    $sql_freq_study="SELECT distinct p.region AS region, ind.population AS population_name, CONCAT(p.id, ';', p.sampling, ' (', v.research_name, ')')  AS research_name
	    FROM validation v 
		    INNER JOIN individuals_detection ind2 ON ind2.validation_id=v.id 
		    INNER JOIN individuals ind ON ind2.individuals_id=ind.id 
		    INNER JOIN population p ON  ind.population_id=p.id
	    WHERE v.inv_id='$inv_id' AND ind.population='$population' AND p.region='$region'
	    ORDER BY p.region, ind.population, p.sampling;";

    $result_freq_study=mysql_query($sql_freq_study);


    echo   "<tr id='tableStudy$population'>
				<td class='popName'>".ucfirst($population)." $download_genotypes
                    <input type='checkbox' name='NewGraphs_".$regionSimple."[]' value='$noninv_alleles;$inv_alleles;$popname;$std_freq;$inv_freq' class='$regionSimple'
                        checked title='Show/Hide this information in the frequency map'>
					<!-- Dubtosa línea ¿? -->
                    <div class='grlsection-;$popname' class='right' />
				</td>

				<td>";
				if ($study_ln == 'all'){
					echo 'All samples and studies';
				}else{
					echo $study_ln;
				}
		         echo   "<br/><select id='selectStudy' style='width: 180px'  onchange=\"changeFreqs(this,'tableStudy$population','$inv_id','$population','$region')\">
		         		 <option disabled selected value>Select sample/study</option>
		         		 <option value='all'>All samples and studies</option>";
    
    $selected='';
    
    while ($thisrow = mysql_fetch_array($result_freq_study)) {
		 	$data_lines = explode(";", $thisrow['research_name']); // id; description (paper)
            $popid_part=$data_lines[0]; # population id
            $desc_part = $data_lines[1]; # description (paper) <- menu unit

	    // if ($desc_part==$study_ln)  { $selected=" selected='selected'"; }
	    // else                                    { 
            $selected=''; // }
	    echo "          <option value='".$desc_part."' $selected>".$desc_part."</option>";
	    $selected='';
    }

    //Add studies without genotypes
    $sql_freq_study2="SELECT   p.region , pd.population_name, CONCAT( pd.population_id,  ';', p.sampling, ' (', pd.validation_research_name, ')') AS research_name 
    				FROM population_distribution pd, population p 
        			WHERE  pd.inv_id='$inv_id' AND pd.population_name='$population' AND pd.population_id=p.id ORDER BY p.sampling;";

    $result_freq_study2=mysql_query($sql_freq_study2);
    while ($thisrow = mysql_fetch_array($result_freq_study2)) {
		 	$data_lines = explode(";", $thisrow['research_name']); // id; description (paper)
            $popid_part=$data_lines[0]; # population id
            $desc_part = $data_lines[1]; # description (paper) <- menu unit

	    // if ($desc_part==$study_ln)  { $selected=" selected='selected'"; }
	    // else                                    {
	     $selected='';// }
	    echo "          <option value='".$desc_part."' $selected>".$desc_part."</option>";
	    $selected='';
    }

    echo "		    </select>
	            </td>
				<td>".$anal_ind."</td>
				<td>".$indep_alleles."</td>
				<td>".number_format($std_freq,4)."</td>
				<td>".number_format($inv_freq,4)."</td>
				<td>".$hwe."</td>
			</tr>
        ";

    mysql_close($con);

?>

