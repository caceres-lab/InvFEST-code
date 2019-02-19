<?php
/******************************************************************************
	INSERT_NEWS.PHP

	Inserts or deletes the specified news listed at the InvFEST home webpage.
	It is executed from the index.php script
*******************************************************************************/

	// Connection to the database
	include('db_conexion.php');


	/*// Add new to the news table
	if($_POST[Title] != ''){
		$query2= "INSERT INTO News (Title, Comment, Date) VALUES('$_POST[Title]','$_POST[Comment]',now());";
		$result2 = mysql_query($query2);
		if (!$result2) {die('Invalid query: ' . mysql_error());}
		echo"News added successfully!";
	}

	// Delete new from the news table
	if($_POST["id"] != ''){
		$query3 = "DELETE FROM News where id = $_POST[id];";
		$result3 = mysql_query($query3);
		if (!$result3) {die('Invalid query: ' . mysql_error());}
			echo"News successfully deleted!";
	}*/

	$title = $_POST["Title"];
	$id = $_POST["id"];
	$comment = $_POST["Comment"];
	if ((!$title) && (!$id)){
			echo('You must specify a news Title');
	}

	if (preg_match("/nospam/",$comment)) {
		
		if ($title) {

			$spam1="FyLit";
			$spam = strpos($title,$spam1);

			if(($title != '') and ($spam === false)){
				$pattern = '/nospam/';
				$comment = preg_replace($pattern, '', $comment); #Spam filter
				$query2= "CALL add_news('$_POST[Title]','$comment');";
				#$query2= "INSERT INTO News (Title, Comment, Date) VALUES('$_POST[Title]','$_POST[Comment]',now());";
				$result2 = mysql_query($query2);
				if (!$result2) {
					die('Invalid query: ' . mysql_error());
				}
				echo"News added successfully!";
			}
		}
	}

	if (!preg_match("/nospam/",$comment)){
		"Write 'nospam' in the comment section to avoid malware and spam!";
	}

	if($_POST["id"] != '') {
		$query3 = "CALL delete_news('$_POST[id]');";
		#$query3 = "DELETE FROM News where id = $_POST[id];";
		$result3 = mysql_query($query3);
		if (!$result3) {
			die('Invalid query: ' . mysql_error());
		}
		echo"News successfully deleted!";
	}
	
?>
