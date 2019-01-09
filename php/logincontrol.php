<?php
/******************************************************************************
    LOGINCONTROL.PHP

	Private users login control
*******************************************************************************/

	// $con = mysql_connect('host', 'user', 'pwd');
	/* if (!$con) {
		die('Could not connect: ' . mysql_error());
	} */
	session_start();
	// Connection to the database: connect 158.109.215.162 as a localhost
	include_once('db_conexion.php');
	mysql_select_db("INVFEST-DB", $con);

	// Check if the user is correct
	$user = $_POST["usuario"];
	$key = $_POST["contrasena"];
	$selectUser = "SELECT * FROM user WHERE user='$user' and password ='$key'";

	$rs = mysql_query($selectUser,$con);

	if (mysql_num_rows($rs)!=0) {
		// Valid user and password
		// Define the session and save the data
		//session_start();
		//session_register("autentificado"); 
		$_SESSION["autentificado"]="SI";
		$autentificado="SI";
		// Save the username!!!
		$_SESSION['user']=$user;
		$thisrow = mysql_fetch_array($rs);
		$_SESSION['userID']= $thisrow['id'];
		$_SESSION['MySQLuser']=$thisrow['MySQLuser'];
		
		/*
		// Redirect to the same page with the session started
		// FALTARA SALIR DEL IFRAME PARA QUE QUEDE BIEN!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			if ($_POST["origin"]=="index") {
				header("Location: ../index.html");
			} elseif ($_POST["origin"]=="report") {
				$inv=$_POST["q"];
				header("Location: ../report.php?q=$inv"); 
			} else {
				header("Location: ../index.html");
			}
		*/
		header ("Location: ../html/logindone.html");
	
	} else {
		// Show error
		header ("Location: login.php?errorusuario=si");
	}

	mysql_free_result($rs);
	mysql_close($con);

?>