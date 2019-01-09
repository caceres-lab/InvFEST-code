<?php
/******************************************************************************
	AJAXADD_EVOL_INFO.PHP

	Adds the evolutionary information from an inversion to the database.
	It automatically retrieves the information obtained from the "Evolutionary history" section of the inversion report webpage (which is inserted manually)
*******************************************************************************/
	
	session_start();
    include('security_layer.php');


    $inv_id=$_POST["inv_id"];
    $evol_type=$_POST["evol_type"];
    $orientation_species=$_POST["orientation_species"];
    $orientation_orientation=$_POST["orientation_orientation"];
    $method_orientation=$_POST["method_orientation"];
    $source_orientation=$_POST["source_orientation"];
    $age_age=$_POST["age_age"];
    $method_age=$_POST["method_age"];
    $source_age=$_POST["source_age"];
    $origin_origin=$_POST["origin_origin"];
    $method_origin=$_POST["method_origin"];
    $source_origin=$_POST["source_origin"];

    //Comprobaciones
    if ($evol_type == "") {
	    echo "Error: Type of information is not selected";
    } else {
	    /*
            evolution_orientation		/*evolution_age		/*evolution_orientation
		    orientation_species			age_age				origin_origin
		    orientation_orientation
		    method_orientation			method_age			method_origin
		    source_orientation			source_age			source_origin
        */

	    if ($evol_type == "evolution_orientation") {
		    $data=$orientation_orientation;
		    $table='inversions_in_species';
		    $method=$method_orientation;
		    $source=$source_orientation;
	    } elseif ($evol_type == "evolution_age") {
		    $data=$age_age;
		    $table='inv_age';
		    $method=$method_age;
		    $source=$source_age;
	    }elseif ($evol_type == "evolution_origin") {
		    $data=$origin_origin;
		    $table='inv_origin';
		    $method=$method_origin;
		    $source=$source_origin;
	    }

	    if ($evol_type == "evolution_orientation" && $orientation_species=="") { echo "Error: Species is not defined"; }
	    elseif ($evol_type == "evolution_orientation" && $data=="") { echo "Error: Orientation is not defined"; }
	    elseif ($evol_type == "evolution_age" && $data=="") {echo "Error: Age is not defined";
	    	//echo "\n$orientation_species\n$inv_id\n$table\n$data\n$method\n$source\n";
	    }
	    elseif ($evol_type == "evolution_origin" && $data=="") { echo "Error: Origin is not defined"; }
	    elseif ($method=="") { echo "Error: Method is not defined"; }
	    elseif ($source=="") { echo "Error: Study is not defined"; }
		
	    else {
		    //Todo es correcto, por lo tantos conectamos a la BBDD:
		    include_once('db_conexion.php');

		    //mysql_query('CALL miProcedure()');
		    //mysql_query('SELECT miFunction()');

		    //Llamamos a la funcion add_evolutionary_info:
		    /*
            `add_evolutionary_info`
			    (IN key_val INT, 	//orientation_species o '' para age y origin
			    IN inv_id_val INT, 
			    IN table_val VARCHAR(255),  
			    IN info_val VARCHAR(255), 
			    IN method_val VARCHAR(255), 
			    IN source_val VARCHAR(255), 
			    IN user_id_val INT)
		    */
    //      $f="CALL add_evolutionary_info('$orientation_species','$inv_id','$table','$data','$method','$source','".$_SESSION["userID"]."')";
		    mysql_query("CALL add_evolutionary_info('$orientation_species','$inv_id','$table','$data','$method','$source','".$_SESSION["userID"]."')");


    /*
		    echo "<head>
		    <script src='../js/jquery.js'></script>
		    <script src='../js/highslide_complete.js'></script>
		    <script>
		    function hideEvolInfoResults() {
			    div = document;
			    $(div).empty(''); alert(div);
		    }
		    </script>
		    </head>";
    */

    //		echo "Information added succesfully<br />".$f.$message;

	
		    echo "<tr>";
		    if ($evol_type == "evolution_orientation") {
			    $sql_val="SELECT name
				    FROM species
				    WHERE id='$orientation_species';";
			    $result_val=mysql_query($sql_val);
			    $echo_validations='';
			    $r= mysql_fetch_array($result_val);
			    echo "<td><em>".$r['name']."</em></td>";
		    }
		    echo "<td>$data</td><td>$method</td><td>$source</td></tr>";

		    // CON EL SIGUIENTE BOTON SE REFRESCA LA PAGINA PRINCIPAL Y POR LO TANTO TAMBIEN SE CIERRA EL IFRAME-->
		    //echo "<br /><input type='submit' value='Close' name='gsubmit'  onclick='hideEvolInfoResults()' />";
            //header('Location: ../report.php?q='.$inv_id.'&o=add_evol#evolutionary_history');	

		    mysql_close($con);

	    }
    }
?>


