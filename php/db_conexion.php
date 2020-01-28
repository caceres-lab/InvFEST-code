<?php
/******************************************************************************
	DB_CONEXION.PHP

	Decides how to connect to the database
*******************************************************************************/

	# Login
	if ($_SESSION["autentificado"]=='SI') {
		
		# Admin user
		if ($_SESSION['MySQLuser']=="invfest") {
			$user = "invfest";
			$password = "pwdInvFEST";
			$db = "INVFEST-DB";

		# Lab user
		} elseif ($_SESSION['MySQLuser']=="invfestdb-lab") {
			$user = "invfestdb-lab";
			$password = "InvFESTLab";
			$db = "INVFEST-DB";
			
		# I don't know who you are...
		} else {
			$user = "invfest";
			$password = "pwdInvFEST";
			$db = "INVFEST-DB-PUBLIC";
		}

	# No login
	} else {
		$user = "invfest";
		$password = "pwdInvFEST";
		$db = "INVFEST-DB-PUBLIC";
	}
	
	$con = mysql_connect('localhost', $user, $password);
	//$con = mysql_connect('localhost', 'root', 'pwdroot');
	if (!$con) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db($db, $con);

?>