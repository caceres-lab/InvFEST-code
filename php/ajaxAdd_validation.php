<?php
/******************************************************************************
	AJAXADD_VALIDATION.PHP

	Apparently It is not used anymore. It has been replaced for Add_validation.php
*******************************************************************************/
	session_start();
    include('security_layer.php');
    

    $inv_id=$_POST["inv_id"];
    $chr=$_POST["chr"];
    $research_name=$_POST["research_name"];
    $method=$_POST["method"];
    $status=$_POST["status"];
    $checked=$_POST["checked"];

    $validation=$_POST["validation"]; //Puede ser: experimental o bioinformatics
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

    $message=""; $warning_ind='';

    //Comprobaciones
    if ($checked != 'yes') { $checked='not'; }
    $order_bp='ko';
    if ($bp2e > $bp2s && $bp2s > $bp1e && $bp1e > $bp1s) { $order_bp='ok'; }
    if ($bp1s == "" && $bp1e == "" && $bp2s == "" && $bp2e == "" ) {$order_bp='ok'; }

    if ($research_name == "" || $research_name == null )
        { echo "Error: Study Name is not defined";}
    elseif ($method == "" || $method == null)
        { echo "Error: Method is not defined";}
    elseif ($status == "" || $status == null)
        { echo "Error: Status is not defined";}
    /*elseif (!preg_match('/PCR/',$method) && !preg_match('/FISH/',$method) && !preg_match('/MLPA/',$method) && $method != "" && $fosmids == "")
	    {echo "Error: Fosmids information from Validation details is not defined";}
    elseif (!preg_match('/PCR/',$method) && !preg_match('/FISH/',$method) && !preg_match('/MLPA/',$method) && $method != "" && $results == "")
	    {echo "Error: Results from Validation details is not defined";}
    */
    //elseif ($fosmids != "" && $results == "") { echo "Results from Bioinformatic validation is not defined<br>";}
    elseif (($bp1s != "" || $bp1e!="" || $bp2s!="" || $bp2e!="") && ($bp1s=="" || $bp1e=="" || $bp2s=="" || $bp2e==""))
        { echo "Error: All fields from Add Breakpoints must be defined"; }
    elseif ($bp1s != "" && !preg_match('/^[0-9]+$/', $bp1s) && !preg_match('/[1-9]/', $bp1s))
        { echo"Error: Breakpoint 1 start is not a number"; }
    elseif ($bp1e != "" && !preg_match('/^[0-9]*$/', $bp1e) && !preg_match('/[1-9]/', $bp1e))
        { echo"Error: Breakpoint 1 end is not a number"; }
    elseif ($bp2s != "" && !preg_match('/^[0-9]*$/', $bp2s) && !preg_match('/[1-9]/', $bp2s))
        { echo"Error: Breakpoint 2 start is not a number"; }
    elseif ($bp2e != "" && !preg_match('/^[0-9]*$/', $bp2e) && !preg_match('/[1-9]/', $bp2e))
        { echo"Error: Breakpoint 2 end is not a number"; } 
    elseif ($order_bp != 'ok')
        {echo "Error: Positions of the breakpoints are not correct"; }
    elseif ($_FILES["individuals"]["name"]!="" && $_FILES["individuals"]["type"]!="text/plain")
        { echo "Error: File type not valid"; }
    elseif ($_FILES["individuals"]["name"]!="" && $_FILES["individuals"]["error"]>0)
        { echo "Error: ".$_FILES["individuals"]["error"]; }
    else {
	    //Todo es correcto, por lo tantos conectamos a la BBDD:
	    include_once('db_conexion.php');

	    /* function add_validation
	    IN `inv_id_val` VARCHAR(255), 
	    IN `research_name_val` VARCHAR(255), 
	    IN `validation_val` VARCHAR(255), 
	    IN `valiadtion_method_val` VARCHAR(255), 
	    IN `PCRconditions_val` VARCHAR(255), 
	    IN `primer_val` VARCHAR(255),
	    IN `validation_comment_val` VARCHAR(255) ,
	    IN `checked_val` VARCHAR(255))
	    */

	    //mysql_query('CALL miProcedure()');
	    //mysql_query('SELECT miFunction()');

	    //Llamamos a la funcion add_validation:
        #$f="SELECT add_validation('$inv_id', '$research_name', '$status', '$method', '$experimental_conditions', '$primers','$commentE','$checked', '".$_SESSION["userID"]."') AS id";
	   

	    $sql_validation_id = mysql_query("SELECT update_validation('ADD', '$inv_id', '$research_name', NULL, '$status', '$method', '$experimental_conditions', '$primers','$commentE','$checked', '".$_SESSION["userID"]."') AS id;");


	    
        #echo $f;
	    $r= mysql_fetch_array($sql_validation_id);
	    $validation_id = $r['id'];
        //echo "validacion: ".$r['id']."<br />"; 
        //echo "SELECT add_validation('$inv_id', '$research_name', '$status', '$method', '$experimental_conditions', 			'$primers','$commentE','$checked') AS id<br>";
	    if ($fosmids != '' || $fosmids != NULL) {
		    /*add_fosmid_validation`(
		    IN validation_id_val INT, 
		    IN inv_id_val INT, 
		    IN fosmid_id_val INT, 
		    IN research_val  VARCHAR(255), 
		    IN result_val  VARCHAR(255), 
		    IN comment_val  VARCHAR(255))
		    */

		    //Despues de la funcion add_validation, si hay fosmidos, llamamos al procedure add_fosmid_validation:
		    mysql_query("CALL add_fosmid_validation('$validation_id','$inv_id','$fosmids','$research_name','$results','$commentB', '".$_SESSION["userID"]."');");
            //echo "CALL add_fosmid_validation('$validation_id','$inv_id','$fosmids','$research_name','$results','$commentB');";
	    }

	    if ($_FILES["individuals"]["name"]!="") {
		    //CUANDO PASAN INFORMACION DE INDIVIDUALS, ABRIMOS EL FICHERO, LO LEEMOS LINEA A LINEA
            // OJO!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            // SI HAY ERRORES, SE CREARA LA VALIDACION
            // HABRIA QUE CHEQUEAR PREVIAMENTE!!!!!!!!!!!!!!!!!

		    $file = fopen($_FILES["individuals"]["tmp_name"], "r") or exit("Unable to open file!");
		    while(!feof($file)) {
			    $line=fgets($file);
                if( !strncmp($line, '#', 1) )continue;
			    //Eliminamos los saltos de linea del final
			    $line=trim($line, "\n");
			    $line=trim($line, "\r");
			    //PARA CADA LINEA LLAMAMOS ADD_INDIVIDUAL_GENOTIPY
			    /*
                add_individual_genotipy`(
			        IN `code_val` VARCHAR(255), -> del fichero
			        IN `inv_name_val` VARCHAR(255), -> hidden --------------------->>>>>>>>>>>> lo cambiamos por inv_id
			        IN `genotype_val` VARCHAR(255), -> fichero
			        IN `valid_id_val` INT(11))-> devuelto por add_validation
			        --- Nuevo fromato del fichero
                    IN code_val VARCHAR(255),              -> del fichero
                    IN gender_val VARCHAR(255),            -> del fichero
                    IN population_val VARCHAR(255),        -> del fichero
                    IN region_val VARCHAR(255),            -> del fichero
                    IN family_val VARCHAR(255),            -> del fichero
                    IN relationship_val VARCHAR(255),      -> del fichero
                    IN `genotype_val` VARCHAR(255),        -> del fichero
                    IN allele_comment_val VARCHAR(255),    -> del fichero
                    IN allele_level_val INT,               -> del fichero
                    IN panel_val VARCHAR(255),             -> del fichero
                    IN other_code_val VARCHAR(255),        -> del fichero
                    IN `inv_id_val` INT(11),               -> hidden
                    IN `valid_id_val` INT(11),             -> hidden
                    IN user_id_val INT                     -> hidden
                */
			    $code=""; $geder=""; $population=""; $region=""; $family=""; $panel=""; $relationship=""; $allele_level=""; $genotype=""; 
			    $allele_comment=""; $other_code="";
                if ($line != "") {
				    //PARSEAMOS EL FICHERO
				    //El fichero tiene 'codigo individuo' - 'genotipo' -> puede estar separado por ; \t o espacio
				    $separator="";
				    $semicolon=strpos($line,';');
				    if ($semicolon!==false) {           //; es el separador
					    $separator=';';
				    } else {
					    $tab=strpos($line,"\t");
					    if ($tab!==false) {             // \t es el separador
						    $separator="\t";
					    } else  {
						    $space=strpos($line," ");
						    if ($space!==false) {       // El separador es el espacio
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
                        $family= ($info[4] == "" ? 'none' : $info[4]); 
                        $relationship= ($info[5] == "" ? 'unrelated' : $info[5]);
					    $genotype= $info[6];
                        $allele_comment= ($info[6] == "" ? NULL : $info[6]);
                        $allele_level= ($info[7] == "" ? 0 : $info[7]);
                        $panel= ($info[8] == "" ? 'NA' : $info[8]);
                        $other_code= ($info[9] == "" ? NULL : $info[9]);
                     
					    if ($code !="" && ($genotype=="INV/INV" || $genotype=="STD/INV" || $genotype=="STD/STD" || $genotype=="STD" || $genotype=="INV" || $genotype=="NA" || $genotype=="ND")) {
 
					    mysql_query("CALL add_individual_genotype('$code' , '$geder', '$population', '$region', '$family', '$relationship', '$genotype', '$allele_comment', '$allele_level', '$panel', '$other_code', '$inv_id', '$validation_id', ".$_SESSION["userID"]."');");
                        // mysql_query("CALL add_individual_genotype('$code','$inv_id','$genotype','$validation_id', '".$_SESSION["userID"]."');");
                        //echo "CALL add_individual_genotipy('$code','$inv_id','$genotype','$validation_id');";
					    } else {
						    $warning_ind="yes";
					    }
				    } else {
					    $warning_ind='yes';
				    }
			    }
		    }
		    fclose($file);
	    }

	    if ($bp1s != "" || $bp1e!="" || $bp2s!="" || $bp2e!="") {
		    //CUANDO AÑADIMOS BP
		    /*
            add_BP`(
		        IN validation_id_val INT,
		        IN `inversion_id_val` int, 
		        IN chr_val  VARCHAR(255), -> FALTA PASARLO COMO HIDDEN
		        IN bp1s_val INT, 
		        IN bp1e_val INT, 
		        IN bp2s_val INT, 
		        IN bp2e_val INT, 
		        IN description_val  VARCHAR(255) 
		    */
		    //DEVOLVERA LAS PREDICCIONES QUE HAN QUEDADO ANULADAS Y YO LO MUESTRO EN MENSAJE DE SALIDA
		    //INSERCION OK, PERO HAY CAMBIOS
		    //Llamamos al procedure add_BP (PASARA A SER UNA FUNCION Y NO UN PROCEDIMIENTO PARA ASI DEVOLVER LAS PREDICCIONES QUE QUEDAN ANULADAS
		    $changed=mysql_query("SELECT add_BP('$validation_id','$inv_id','$chr','$bp1s','$bp1e','$bp2s','$bp2e','$description', '".$_SESSION["userID"]."') AS chang");
		    $r= mysql_fetch_array($changed);
            //echo "SELECT add_BP('$inv_id','$chr','$bp1s','$bp1e','$bp2s','$bp2e','$description') AS chang";
		    //Mostramos las predicciones que han quedado anuladas: 
		    if ($r['chang']=='YES') { 
			    $message="Some predictions status have changed<br />";
		    }
	    }

	    if ($warning_ind != '') { $message.="Some individuals have not been correctly introduced <br />"; }
	    //if ($validation_id) HAY Q FORZAR A QUE SALGA MAL PARA SABER Q DEVUELVE!!
        //echo "Validation $validation_id added succesfully<br />".$message;

	    //CON EL SIGUIENTE BOTON SE REFRESCA LA PAGINA PRINCIPAL Y POR LO TANTO TAMBIEN SE CIERRA EL IFRAME -->
	    //echo "<br /><input type='submit' value='Close' name='gsubmit'  onclick='parent.location.reload();' />";

	    //Para mostrar la tabla con Ajax, necesitamos consultar el soporte que presenta. El resto se imprime directamente del formulario
	    //para el support se cuentan todos los individuos de la validacion y se separan en funcion del genotipo
	    $sql_valSupport="SELECT count(id.individuals_id) as count, id.genotype 
		    FROM individuals_detection id
		    WHERE id.inversions_id='$inv_id' and id.validation_id='$validation_id'
		    GROUP BY id.genotype;";
	    $result_valSupport=mysql_query($sql_valSupport);
	    $valSupport='';$totalSupport=0;
	    //En funcion del genotipo, cambia el texto. REVISAR LOS POSIBLES GENOTIPOS!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    while($supportrow = mysql_fetch_array($result_valSupport)) {
		    if ($supportrow['genotype'] == 'INV/INV'){$valSupport.=$supportrow['count'].' homozygote inverted individuals<br />';}
		    else if ($supportrow['genotype'] == 'STD/STD'){$valSupport.=$supportrow['count'].' homozygote standard individuals<br />';}
		    else if ($supportrow['genotype'] == 'STD/INV'||$supportrow['genotype'] == 'INV/STD'){$valSupport.=$supportrow['count'].' heterozygote individuals<br />';}
		    else if ($supportrow['genotype'] == 'INV'){$valSupport.=$supportrow['count'].' inverted individuals<br />';}
		    else if ($supportrow['genotype'] == 'STD'){$valSupport.=$supportrow['count'].' standard individuals<br />';}
		    else {$valSupport.=$supportrow['count'].' '.$supportrow['genotype'].' individuals<br />';}
		    $totalSupport+=$supportrow['count'];
	    }
        //if ($valSupport==''){$valSupport='0 individuals';}
	    if ($totalSupport!=''||$totalSupport!=0) { $totalSupport.=' individuals'; }
	    else { $totalSupport=''; }

	    //Tambien debemos conocer pubMedID y description
	    $sql_val="SELECT r.pubMedID, r.description
		    FROM validation v INNER JOIN researchs r ON v.research_name=r.name
		    WHERE v.inv_id='$inv_id' and v.id='$validation_id';";
	    $result_val=mysql_query($sql_val);
	    $echo_validations='';
	    $r= mysql_fetch_array($result_val);

	    //Imprimimos la tabla para que se añada con Ajax: 
	    echo "<div class='section-title TitleB'>- ";
	    if ($r['pubMedID'] != "") { echo "<a href='http://www.ncbi.nlm.nih.gov/pubmed/".$r['pubMedID']."' target='_blank'>$research_name</a>"; }
	    else { echo $research_name; }
	    echo "</div>";
	    echo "<div class='grlsection-content'>
		        <table width='100%'>
		        <tr><td class='title'>Description</td><td>".$r['description']."</td></tr>
		        <tr><td class='title'>Method</td><td>$method</td></tr>
		        <tr><td class='title'>Status</td><td>$status</td></tr>
		        <tr><td class='title'>Support</td><td>".$valSupport."</td></tr>
		        <tr><td class='title'>Genotypes</td><td>".$totalSupport;
	    if ($totalSupport>0) {
		    echo "<div class='right'><a href='php/echo_individualsVal.php?id=$inv_id&val=$validation_id' >Download</a></div>";
	    }
	    echo   "</td></tr>
                <tr><td class='title'>Comment</td><td>$commentE</td></tr>
                </table>
              </div><br />";

	    mysql_close($con);

        //header('Location: ../report.php?q='.$inv_id.'&o=add_val#validations');	
    }

?>


