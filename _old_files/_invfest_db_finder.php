<?php

session_start(); //Inicio la sesión
include_once('php/select_index.php');
include_once('php/structure_page.php');;?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php 
echo $creator;
echo $head;
?>
<script type="text/javascript" src="js/header.js"></script>
<!-- ................................................................................................................................. -->
<body>
<?php 

#write in file $IP visit
$myFile = "stats/hits.txt";
$ip=$_SERVER['REMOTE_ADDR'];
$date = date('m d Y', time());
$stringData = "$ip $date\n";
#echo $stringData;
file_put_contents($myFile, $stringData, FILE_APPEND | LOCK_EX);
?>
<?php include('php/echo_menu.php');?>
<br/>

<!-- CSS style -->
<style type="text/css">
#parent_div_1, #parent_div_2, #parent_div_3{
    border:0px;
    margin-right:10px;
    float:left;
}
.section-content{
    float:left;
    margin-right:5px;
    width:700px;
    border: none;
}
.content{
    float:right;
    margin-right:5px;
    width:300px;
    height:100px;
    	
}

table.sample {
	width:30%;
	height:25%;
	border-width: 0px;
	border-spacing: 0px;
	border-style: hidden;
	border-color: white;
	border-collapse: collapse;
}
table.sample th {
	border-width: 0px;
	padding: 2px;
	border-style: none;
	border-color: none;
}
table.sample td {
	border-width: 0px;
	padding: 2px;
	border-style: none;
	border-color: none;
	white-space:nowrap;
	}
table.sample tr {
	border-bottom:hidden;
}
input.deletebox{
	background: url("./img/delete.png");
	background-size: 20px 15px;
 	background-color: transparent;
	background-repeat: no-repeat;
 	border: none;
	 width: 35px;
    height: 25px;
    font-size: 0.1px;
}

</style>
<?php
include_once('php/select_index.php');
include_once('php/structure_page.php');
include_once('php/db_conexion.php');
#include('php/echo_menu.php');

$image_url='http://upload.wikimedia.org/wikipedia/commons/b/ba/Farm-Fresh_database_connect.png';

?>
<script type="text/javascript" src="js/header.js"></script>

<div style="text-align:center;">
<img src="<?php echo $image_url;?>"><b>INVFEST-DB-Finder</b></img>
</div>
<br>
<div id="input_data">





<!--FORMS//-->
		<form action="php/invfestdb_finder_query_to_db.php" method="post" enctype="multipart/form-data">
		Query input file<font color="red"><b><sup>*</sup></b></font> <input type="file" name="fileToUpload" id="fileToUpload">
		<br /><p style="text-indent: 25px;"><font size="2">► hg18 coordinates</p><p style="text-indent: 25px;">► 3 tab delimited columns file: chrn	start	end</font></p>
		
				
				Breakpoint start bias (bp): <input type="text" name="start_bp"><br>
				Breakpoint end bias (bp): <input type="text" name="end_bp"><br>
		Output filename (Ex: results.txt): <input type="text" name="nameoutputfile" id="nameoutputfile""><br>

		<input type="submit"class='right' value="GO!" name="submit"/>
		<br><br><br><font color="red" size="0.5"><b><sup>*</sup></b>Compulsory</font>
		</form>
				
</div>
