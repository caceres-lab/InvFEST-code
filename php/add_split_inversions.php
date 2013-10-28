<?php include('security_layer.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php
$inv_id=$_POST["inv_id"];
$chr=$_POST["chr"];
$research_name=$_POST["research_name"];
$pinv1_arr=$_POST["pinv1"];
$pinv2_arr=$_POST["pinv2"];
$vinv1_arr=$_POST["vinv1"];
$vinv2_arr=$_POST["vinv2"];

$pinv1 = implode(",", $pinv1_arr);
$pinv2 = implode(",", $pinv2_arr);
$vinv1 = implode(",", $vinv1_arr);
$vinv2 = implode(",", $vinv2_arr);
//echo "p inv1: $pinv1<br>p inv2: $pinv2<br>v inv1: $vinv1<br>v inv2: $vinv2<br>";

// HAY QUE PONER ALGUN CONTROL DE ENTRADA?? UN MINIMO DE PREDICCIONES O VALIDACIONES PARA CADA INVERSION NUEVA??
//DEBEMOS PERMITIR AÑADIR ALGUNA VALIDACION O PREDICCION?? O MEJOR QUE CADA INSERCION SE HAGA DESDE UN SOLO PUNTO??

include_once('db_conexion.php');

/*SPLIT split_inv
 CREATE DEFINER=`amartinez`@`158.109.212.95` PROCEDURE `split_inv`
IN `old_Inv_name_val` varchar(255),  --> SERA EL ID!!
IN new_inv1_pred_list varchar(255), 
IN new_inv2_pred_list varchar(255),  
IN new_inv1_valid_list varchar(255), 
IN new_inv2_valid_list varchar(255), 
IN inv1_status_val varchar(255), 		QUE ES ESTO? LO TIENE QUE DEFINIR EL USUARIO EN EL FORMULARIO DEL SPLIT??
In inv2_status_val varchar(255)) 		QUE ES ESTO? LO TIENE QUE DEFINIR EL USUARIO EN EL FORMULARIO DEL SPLIT?? 
    SQL SECURITY INVOKER 
*/

//mysql_query('CALL miProcedure()');
//mysql_query('SELECT miFunction()');

//llamamos a la funcion add_validation:
mysql_query("CALL split_inv('$inv_id', '$pinv1', '$pinv2', '$vinv1', '$vinv2', '','', '".$_SESSION["userID"]."')");
//$sql_split = mysql_query("CALL split_inv('$inv_id', '$pinv1', '$pinv2', '$vinv1', '$vinv2', '','')");
//mysql_fetch_array($sql_split);

//if ($validation_id) HAY Q FORZAR A QUE SALGA MAL PARA SABER Q DEVUELVE!!
echo "Split done succesfully<br />";

// CON EL SIGUIENTE BOTON SE REFRESCA LA PAGINA PRINCIPAL Y POR LO TANTO TAMBIEN SE CIERRA EL IFRAME-->
echo "<br /><input type='submit' value='Close' name='gsubmit'  onclick='parent.location.reload();' />";

?>
