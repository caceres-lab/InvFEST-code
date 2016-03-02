<?php

if ($_SESSION["autentificado"]=='SI') {

	if ($_SESSION['MySQLuser']=="invfest") {
	
		// Admin user
		$user = "invfest";
		$password = "pwdInvFEST";
		$db = "INVFEST-DB";
	
	} elseif ($_SESSION['MySQLuser']=="invfestdb-lab") {
	
		// Lab user
		$user = "invfestdb-lab";
		$password = "InvFESTLab";
		$db = "INVFEST-DB";
	
	} else {
	
		// I don't know who you are...
		$user = "invfest";
		$password = "pwdInvFEST";
		$db = "INVFEST-DB-PUBLIC";
	
	}

} else {

		// No login
		$user = "invfest";
		$password = "pwdInvFEST";
		$db = "INVFEST-DB-PUBLIC";

}


$con = mysql_connect('localhost', $user, $password);
//$con = mysql_connect('localhost', 'root', 'pwdroot');
if (!$con) { die('Could not connect: ' . mysql_error()); }
mysql_select_db($db, $con);
$auth_temp=$_SESSION["autentificado"];
#echo "<h3>$db $user -$auth_temp- </h3>"

?>
