<?php
// conectamos con la bbdd

#$con = mysql_connect('host', 'user', 'pwd');
#if (!$con) { die('Could not connect: ' . mysql_error()); }
include_once('db_conexion.php'); //conecta a 158.109.215.162 como localhost
mysql_select_db("INVFEST-DB", $con);

// Comprobamos que el usuario es correcto
$user = $_POST["usuario"];
$key = $_POST["contrasena"];
$selectUser = "SELECT * FROM user WHERE user='$user' and password ='$key'";

$rs = mysql_query($selectUser,$con);

if (mysql_num_rows($rs)!=0) {
	//usuario y contraseña validos
	//definimos sesion y guardamos los datos
	session_start();
	//session_register("autentificado"); 
	$_SESSION["autentificado"]="SI";
	$autentificado="SI";
	$_SESSION['user']=$user; //guardar el nombre de usuario!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	$thisrow = mysql_fetch_array($rs);
	$_SESSION['userID']= $thisrow['id'];
	$_SESSION['MySQLuser']=$thisrow['MySQLuser'];
	//redirigimos a la misma pagina pero con la sesion iniciada
	// FALTARA SALIR DEL IFRAME PARA QUE QUEDE BIEN!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//if ($_POST["origin"]=="index"){
	//	header("Location: ../index.html");
	//} elseif ($_POST["origin"]=="report"){
	//	$inv=$_POST["q"];
	//	header("Location: ../report.php?q=$inv"); 
	//} else {
	//	header("Location: ../index.html");
	//}
	header ("Location: ../logindone.html");
} else {
	//damos error
	header ("Location: login.php?errorusuario=si");
}


mysql_free_result($rs);
mysql_close($con);
?>
