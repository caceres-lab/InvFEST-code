<?php

if ($_SESSION["autentificado"]=='SI') {

	if ($_SESSION['MySQLuser']=="invfest") {
	
		// Admin user
		$user = "inoguera";
		$password = "inoguera";
		$db = "inoguera2";
	
	} elseif ($_SESSION['MySQLuser']=="invfestdb-lab") {
	
		// Lab user
		$user = "inoguera";
		$password = "inoguera";
		$db = "inoguera2";
	
	} else {
	
		// I don't know who you are...
		$user = "inoguera";
		$password = "inoguera";
		$db = "inoguera2";
	
	}

} else {

		// No login
		$user = "inoguera";
		$password = "inoguera";
		$db = "inoguera2";

}


$con = mysql_connect('localhost', $user, $password);
//$con = mysql_connect('localhost', 'root', 'pwdroot');
if (!$con) { die('Could not connect: ' . mysql_error()); }
mysql_select_db($db, $con);
$auth_temp=$_SESSION["autentificado"];
#echo "<h3>$db $user -$auth_temp- </h3>"

?>
