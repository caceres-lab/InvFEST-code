<? 
session_start(); //Inicio la sesión
?>
<?php include('php/db_conexion.php');?>
<?php include_once('php/select_index.php');?>
<?php include_once('php/structure_page.php');?>
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
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Ubuntu:regular,bold&subset=Latin">

<style type="text/css">
body {
    font-family: Ubuntu, "times new roman", times, roman, serif;
}
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
	border:1px solid #ffffff ;
	border: 0;
	border-width: 0px;
	border-spacing: 0px;
	border-style: hidden;
	border-collapse: collapse;
	
}
table.sample th {
	border-width: 0px;
	border: 0; 
	padding: 0.5px;
	border-style: none;
	border-color: none;
}
table.sample td {
	border-width: 0px;
	border: 0; 
	padding: 0.5px;
	border-style: none;
	border-color: none;
	white-space:nowrap;
	border:1px solid #ffffff
	}

table.sample col {
	border-width: 0px;
	border: 0; 
	padding: 0.5px;
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
news{
}
</style>

<!-- Show Add news -->
<script type="text/javascript" src="http://code.jquery.com/jquery-1.4.2.js"></script>
<script type="text/javascript">
        function Show_Div(Div_id) {
            if (false == $(Div_id).is(':visible')) {
                $(Div_id).show(250);
            }
            else {
                $(Div_id).hide(250);
            }
        }
    </script>



<!-- DIVISIONS -->
<!-- WELCOME -->
<div id='parent_div_1'>
	
	<div class ='section-content'>
		<p style="text-align: justify;"><b>Welcome to the InvFEST database!</b></p>
		<p style="text-align: justify;">InvFEST aims to become a reference site to share information and collaborate towards the complete characterization of human polymorphic inversions. It is a data-warehouse implementation that integrates several data of interest related to inversions with an online analytical processing engine (OLAP) to gather information and compute a report of each inversion. </p>
		<p style="text-align: justify;">The InvFEST database stores and merges inversion predictions from healthy individuals into a non-redundant dataset by overlapping the position of the breakpoints of each prediction and taking into account the resolution of each study. Most predictions come from mapping information of paired-end sequences (PEM) obtained by different studies of the literature, which in some cases have been reanalyzed by GRIAL, a program specifically designed to detect inversions from PEM data. Moreover, it stores information of validations and genotyping assays, frequency in different populations, association with genes and segmental duplications, and the evolutionary history of the inversions. </p>
		<p style="text-align: justify;">The database will keep on updating information by incorporating new predictions, validations, genotyping data, and any other information, either extracted from peer reviewed research studies or generated in our lab. We always welcome your suggestions and comments.</p>
		<p style="text-align: justify;">The InvFEST database is an outcome of the INVFEST project, supported by the European Research Council (ERC) Starting Grant 243212 under the European Union Seventh Research Framework Programme (FP7).</p>

	</div>

<!-- END WELLCOME -->

<!-- NEWS -->
	<div class="content">
		<!-- Retrieve news query -->
		<?php
		$query="SELECT id, Title, Comment, DATE_FORMAT(Date,'%d-%m-%Y') as Date FROM News ORDER BY DATE(Date) DESC LIMIT 6;";
		$result = mysql_query($query); 
		if (!$result) {die('Invalid query: ' . mysql_error());}


		#News table
		echo '<table class="sample">';
		echo '<tr>
		<br><news style="color: #006666; font-family: Ubuntu; font-size: 0.9em;"><b>News</b></news><br><br></tr>';
		while($news= mysql_fetch_array($result)){
			echo '<tr>
			<td><news style="text-align: justify; font-size:0.985em; font-family: Ubuntu">'.$news['Date'].'</news></td></tr><tr><td><news style="text-align: justify; font-size:0.985em; font-family: Ubuntu">'.'<i>'.$news['Title'].'</i></news></td>';
			#Delete news button
			if ($_SESSION["autentificado"]=='SI'){?>
				<form name="myForm2" action="php/insert_news.php" method='post'>
				<?php echo "<td><input type=\"submit\" name='id' value=".$news['id']." class=\"deletebox\" ></td></tr>";?>
				</form> <?php
			}
			#Row space between news
			echo '<tr><td height="10">'."".'</td></tr>';
		}
		echo "</table>";

		#Add news button
		if ($_SESSION["autentificado"]=='SI') {
					echo "<div class='content'>";
					echo"<input type=\"button\" value=\"Add\" onclick=\"Show_Div(Div_1)\" />";

		}
		?>
	
		<!-- Insert news  -->

		<div id="Div_1" class="content" style="display: none; padding-bottom: 0.95cm">
			
			<form name="myForm" action="php/insert_news.php" method='post'>
				
				<small>Title</small><br><textarea rows="1" cols="40" name='Title' class='left' id='title'></textarea><br>
				<small>Comment</small><br><textarea rows="2" cols="40" name='Comment' placeholder="Please, in order to avoid malware and spam, write 'nospam'" class='left' id='comment'></textarea>
				<input type='submit' class='right' value="Submit" name="submit">
				
				
				
			</form>
			
		</div>
	
	
		<?php if ($_SESSION["autentificado"]=='SI') {

			echo "</div";
			echo "</div";}?>
		
		<?php if ($_SESSION["autentificado"]=='NO') {
			echo"</div>";}?>
	</div>
	
<!-- END NEWS -->
<!-- HOW TO CITE -->

	

	<div class="content">
		<table class="sample">
		<br>
		<br><tr><p style="color: #006666; font-family: Ubuntu; font-size: 0.9em;"><b>How to cite</b></p></tr>

		<tr><col width="80"><p style="text-align: justify; font-size:0.857em; font-family: Ubuntu">If you use InvFEST, please cite:</p></col></tr>
		<tr><col width="80"><p style="text-align: justify; font-size:0.857em; font-family: Ubuntu"><i>Martínez-Fundichely, A. et al. InvFEST, a database integrating information of polymorphic inversions in the human genome. Nucleic Acids Research 42, (2014). </i><a href="http://dx.doi.org/10.1093/nar/gkt1122">doi:10.1093/nar/gkt1122</a></p></col></tr>

		</table>

<?php if ($_SESSION["autentificado"]=='SI') {
			echo "</div";
			echo "</div";
			echo "</div";}?>
	</div>
	
<!-- END HOW TO CITE -->

<?php if ($_SESSION["autentificado"]=='SI') {
	echo "</div>";
	echo "</div>";
	echo "</div>";}?>
</div>


<div id="Sponsors">

<p style="text-align: center;">
	
	<img src="img/logo-uab.gif" alt="UAB" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<img src="img/logoibb.gif" alt="IBB" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<img src="img/LOGO-ERC.gif" alt="ERC" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<img src="img/flag_yellow_low.jpg" alt="EU" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<img src="img/FP7-ide-RGB.gif" alt="FP7" height="50">
</p>
<p style="text-align: center;">&nbsp;<br/><a href="search.php"><button class="default">Start querying InvFEST</button></a></p>
  </div>


<div id="foot">
<?php include('php/footer.php');?>
  </div>

 </div><!--end Wrapper-->
</body>
</html>
