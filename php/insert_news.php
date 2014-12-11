<?php
include('db_conexion.php');
if($_POST[Title] != ''){
	$query2= "INSERT INTO News (Title, Comment, Date) VALUES('$_POST[Title]','$_POST[Comment]',now());";
	$result2 = mysql_query($query2);
	if (!$result2) {die('Invalid query: ' . mysql_error());}
	echo"News added successfully!";
}

if($_POST["id"] != ''){
$query3 = "DELETE FROM News where id = $_POST[id];";
$result3 = mysql_query($query3);
if (!$result3) {die('Invalid query: ' . mysql_error());}
	echo"News successfully deleted!";
}
?>
