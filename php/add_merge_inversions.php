<?php include('security_layer.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php
$inv1=$_POST["inv1"];
$inv2=$_POST["inv2"];

//comprobaciones
if (!preg_match('/^[0-9]+$/', $inv1) || !preg_match('/^[0-9]+$/', $inv2)) {
	echo "Inversion IDs introduced are not valid<br>";
	
}
else {
	/*CREATE DEFINER=`amartinez`@`158.109.212.95` FUNCTION `merge_inv`(
		`old_inv1_id_val` INT,
		`old_inv2_id_val` INT
		 user_id_val INT)
		RETURNS int(11)
	    SQL SECURITY INVOKER
	*/

	//mysql_query('CALL miProcedure()');
	//mysql_query('SELECT miFunction()');

	//llamamos a la funcion add_validation:
	$a="SELECT merge_inv('$inv1', '$inv2', '".$_SESSION["userID"]."') AS new_inv_id";
	$sql_merge = mysql_query("SELECT merge_inv('$inv1', '$inv2', '".$_SESSION["userID"]."') AS new_inv_id");
	$res = mysql_fetch_array($sql_merge);
	//result in $res['new_inv_id']

	//if ($validation_id) HAY Q FORZAR A QUE SALGA MAL PARA SABER Q DEVUELVE!!
	echo "Merge done succesfully<br />".$a;

	// CON EL SIGUIENTE BOTON SE REFRESCA LA PAGINA PRINCIPAL Y POR LO TANTO TAMBIEN SE CIERRA EL IFRAME-->
	echo "<br /><input type='submit' value='Go to the new inversion' name='gsubmit'  onclick=\"location.href='../report.php?q=".$res['new_inv_id']."'\" />";
//header("location: search.php"
}

?>
