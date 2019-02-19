<?php
/******************************************************************************
	DB_CONEXION.PHP

	Decides how to connect to the database
*******************************************************************************/

	# Login
	// if ($_SESSION["autentificado"]=='SI') {
		
	// 	# Admin user
	// 	if ($_SESSION['MySQLuser']=="invfest") {
	// 		$user = "invfest";
	// 		$password = "pwdInvFEST";
	// 		$db = "INVFEST-DB-FREEZE";

	// 	# Lab user
	// 	} elseif ($_SESSION['MySQLuser']=="invfestdb-lab") {
	// 	$user = "invfest";
	// 		$password = "pwdInvFEST";
	// 		$db = "INVFEST-DB-FREEZE";
			
	// 	# I don't know who you are...
	// 	} else {
	// 		$user = "invfest";
	// 		$password = "pwdInvFEST";
	// 		$db = "INVFEST-DB-FREEZE";
	// 	}

	// # No login
	// } else {
		$user = "invfest";
			$password = "pwdInvFEST";
			$db = "INVFEST-DB-FREEZE";
	// }
	
	$con = mysql_connect('localhost', $user, $password);
	//$con = mysql_connect('localhost', 'root', 'pwdroot');
	if (!$con) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db($db, $con);

?>