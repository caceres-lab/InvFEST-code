<?php
/******************************************************************************
	SECURITY_LAYER.PHP

	Login control
	If the user is not logged in, it redirects him to the InvFEST home webpage
*******************************************************************************/

    //Inicio la sesión
    session_start();

    //COMPRUEBA QUE EL USUARIO ESTA AUTENTIFICADO
    if ($_SESSION["autentificado"] != "SI") {
	    //Si no existe el usuario, enviamos a la pagina inicial
	    header("Location: ../search.php");
	    //Ademas salgo de este script
	    exit(); 
    } 
?> 
