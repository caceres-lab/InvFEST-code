<?php
/******************************************************************************
	LOGOUT.PHP

	Logout form
*******************************************************************************/

	// When logout, delete all the data stored during the session
	session_start();
	session_destroy();

	if ($_GET['origin']=="index"){
		header("Location: ../index.php");
	}
	elseif ($_GET['origin']=="report") {
		$inv=$_GET["q"];
		header("Location: ../report.php?q=$inv"); 
	} else {
		header("Location: ../index.php?origen=$HTTP_REFERER");
	}

?>
