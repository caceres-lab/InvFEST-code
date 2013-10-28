<? 
//Inicio la sesión
session_start();

//COMPRUEBA QUE EL USUARIO ESTA AUTENTIFICADO
if ($_SESSION["autentificado"] != "SI") {
	//si no existe el usuario, enviamos a la pagina inicial
	header("Location: ../search.php");
	//ademas salgo de este script
	exit(); 
} 
?> 
