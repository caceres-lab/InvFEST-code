<?php
/******************************************************************************
	ADD_STUDY.PHP

	Adds a new study to the database.
	It automatically retrieves the information obtained from the parameters specified at the "Add new study" form which is opened by php/new_study
*******************************************************************************/
?>


<?php
	session_start();
	include('security_layer.php');
?>

<!DOCTYPE html>
<html>


    <head>
	    <script src="../js/jquery.js"></script>
    </head>
    <?php
    $study=$_POST["study"]; //*

	// Solucionar definitivament caràcters estranys al nom de l'estudi
	setlocale(LC_CTYPE, 'en_US.utf8');
	$study = iconv('UTF-8', 'ASCII//TRANSLIT',$study);
	
    $pubmedID=$_POST["pubmedID"];
    $year=$_POST["year"];
    $journal=$_POST["journal"];
    $author=$_POST["author"];
    $resolution=$_POST["resolution"]; //0 si esta vacía (en predicciones)
    $aim=$_POST["origin"];
    $method=$_POST["searchValMethod"];//*
    $description=$_POST["description"];//*
    #$origin=$_POST['origin'];

    /*
    prediction
    validation
    evolOrigin
    evolOrient
    evolAge
    effPhenotypic
    effGenomic
    */
    $divToChange='';
    if ($aim=='prediction'){ $divToChange='pred_study_name'; }
    elseif ($aim=='validation'){ $divToChange='research_name';$div2ToChange='method'; }
    elseif ($aim=='species'){ $divToChange='source_orientation'; }
    elseif ($aim=='age'){ $divToChange='source_age'; }
    elseif ($aim=='origin'){ $divToChange='source_origin'; }
    elseif ($aim=='genomicEffect'){ $divToChange='source_genomic_func'; }
    elseif ($aim=='phenotypicEffect'){ $divToChange='source_phenotypic_func'; }


    //Comprobaciones
    if ($study == "" || $study == null) { echo "Study is not defined<br>"; }
    elseif ($method == "" || $method == null) { echo "Method is not defined<br>"; }
    elseif ($description == "" || $description == null) { echo "Description is not defined<br>"; }
    //Resolution solo esta en predicciones
    elseif ($origin=="prediction") {
	    if ($resolution == "" || $resolution == null) { $resolution=0; }
	    elseif ($resolution != "" && !preg_match('/^[0-9]+$/', $resolution) && !preg_match('/[1-9]/', $resolution)) {
            echo"Resolution must be a number<br>";
        } 
    }

    else {
	    //Todo es correcto, por lo tantos conectamos a la BBDD:
	    include_once('db_conexion.php');

	    /*Procedure add_study
	    IN `study_name_val` varchar(255), 
	    IN `PMID_val` int(11), 
	    IN `year_val`int(11), 
	    IN `journal_val` varchar(255), 
	    IN `author_val` varchar(255), 
	    IN `pred_error_val` int(11), 
	    IN `aim_val` varchar(255), 
	    IN `method_val` varchar(255), 
	    IN `description_val` varchar(255), 
	    IN user_id_val INT)
	    */

	    //mysql_query('CALL miProcedure()');
	    //mysql_query('SELECT miFunction()');

	    //Hacemos una query y vemos si $method es nuevo en la bbdd (no debe existir antes de la funcion) !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		    $is_new_method =mysql_query("SELECT count(id) as count FROM methods WHERE name ='$method'");
		    $is = mysql_fetch_array($is_new_method);
	    //Si no existia:
	    if ($is['count'] == 0) {
		    $new_method='$(parent.document.getElementById("'.$div2ToChange.'"'.")).append(\"<option value='$method'>$method</option>\");";
	    } else {
		    $new_method='';
	    }
	    //Llamamos a la funcion add_validation:
	    mysql_query("CALL add_study('$study', '$pubmedID', '$year', '$journal', '$author', '$resolution', '$aim', '$method', '$description', '".$_SESSION["userID"]."')");


	    mysql_close($con);

	    echo "Study added succesfully<br />".$message;

	    ?>
	    <br>
	    <input type='button' onclick='appendOption()' value ='close'>
	    <script>
		    function appendOption() {
			    $(parent.document.getElementById("<?php echo $divToChange;?>")).append("<option value='<?php echo $study;?>'><?php echo $study;?></option>");		
			    <?php echo $new_method;?>
			    parent.window.hs.close();
		    }
	    </script>
	    <?php
	    // Con el siguiente botón se refresca la página principal y por lo tanto, también se cierra el iframe -->
	    //echo "<br /><input type='submit' value='Close' name='gsubmit'  onclick='parent.location.reload();' />";
    }

    ?>

</html>
