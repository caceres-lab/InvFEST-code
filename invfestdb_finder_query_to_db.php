<? 
session_start(); //Inicio la sesión
?>

<?php include('php/select_index.php');?>
<?php include('php/structure_page.php');?>
<?php include('php/php_global_variables.php');?>
<?php 

echo $creator;
echo $head;

?>
<script type="text/javascript" src="js/header.js"></script>
<!-- ................................................................................................................................. -->
<body>


<?php include('php/echo_menu.php');?>

 <?php echo $search_inv;?>  

<?php
#$fp = fopen($_FILES['fileToUpload']['tmp_name'], 'rb');
    #while ( ($line = fgets($fp)) !== false) {
    #echo "$line<br>";
    #}

#Comprovaciones
if( !$_FILES['fileToUpload']['tmp_name'] != "" )
{
    die("No file specified!");
}
if (isset( $_POST['nameoutputfile'])){$outputname = $_POST['nameoutputfile'];}
if(!isset($outputname) || trim($outputname) == ''){$outputname="output.txt";}

if (isset( $_POST['start_bp'])){$start_bias = $_POST['start_bp'];}
if(!isset($start_bias) || trim($start_bias) == ''){$start_bias="0";}

if (isset( $_POST['end_bp'])){$end_bias = $_POST['end_bp'];}
if(!isset($end_bias) || trim($end_bias) == ''){$end_bias="0";}

//Input file parsing
#$fh = fopen('/home/inoguera/Regions_with_high_selection/Data/High/high_degree_coordinates_hg18.txt','r') or die ("Could not open file");
$fh = fopen($_FILES['fileToUpload']['tmp_name'], 'rb') or die ("Could not upload file");
$outputpath = "/home/inoguera/".$outputname;
#echo "$outputpath<br>";

//DB connection
include('php/db_conexion.php');
#$user = "invfest";$password = "pwdInvFEST";$db = "INVFEST-DB";
#$con = mysql_connect('localhost', $user, $password);
if (!$con) { die('Could not connect: ' . mysql_error()); }
#mysql_select_db($db, $con);

//Output
$output = fopen("$outputpath", 'w') or die("Unable to create output file!"."$outputname");

$header = "Query_ID\tName\tQuery_position\tPosition_hg18\tSize\tStatus\tGlobal_freq\tFunctional_effect\n";
fwrite($output, $header);
$rowCount="0";
while ( ($line = fgets($fh)) !== false) {
	#echo "$line\n";

	if(preg_match("/:chr\d{1,2}:/", $line) or preg_match("/:chr[M,X,Y]:/",$line) or preg_match("/^chr\d{1,2}:/", $line) or preg_match("/^chr[M,X,Y]:/",$line)){ #if position (so no header), query it

					$cols = explode(":", $line);
					if (count($cols) == 3){
					$ID = $cols[0];
					$chr = $cols[1];
					$positions = $cols[2];
					$position = explode("-", $positions);
					$start = $position[0];$end = $position[1];
					}
					if (count($cols) == 2){
					    $ID++;
					    $chr = $cols[0];
					    $positions = $cols[1];
					    $position = explode("-", $positions);
					    $start = $position[0];$end = $position[1];
					}
	
		
	$sq_query= "Select frequency_distribution, name, chr, status, inv_id, bp1_start, bp1_end, bp2_start, bp2_end, definition_method, genomic_effect from (select i.frequency_distribution, i.name, i.chr, i.status, b.inv_id,  b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, b.definition_method, b.genomic_effect from breakpoints b, inversions i where i.id = b.inv_id ORDER BY FIELD (b.definition_method, 'manual curation', 'default informatic definition'), b.id DESC) as my_table_temp WHERE chr = '$chr' AND (($start BETWEEN (bp1_start-$start_bias) AND (bp1_start+$start_bias)) AND ($end BETWEEN (bp2_end-$end_bias) AND (bp2_end+$end_bias))) AND status NOT IN ('WITHDRAWN','Withdrawn','withdrawn') GROUP BY inv_id ORDER BY FIELD (status, 'TRUE','possible_TRUE', 'possible_FALSE','FALSE','Ambiguous/FALSE','FILTERED OUT','ND','AMBIGUOUS','Ambiguous','ambiguous');";

	#echo "$sq_query<br>";
	$result=mysql_query($sq_query);if (!$result) {echo('Invalid query: ' . mysql_error());}
	
	
	while($row = mysql_fetch_array($result)){
if ($row['status'] != 'FALSE') {
				
						if ($_SESSION["autentificado"]=='SI') {
					
							$r_freq = mysql_query("SELECT inv_frequency('".$row['inv_id']."','all','all','all') AS res_freq");
							$r_freq = mysql_fetch_array($r_freq);
							$d_freq = explode(";", $r_freq['res_freq']);
							$r_inv_freq=$d_freq[2];
						
							// Si no s'ha determinat la freqüència amb genotips, calcular-la sense genotips
							if (($r_inv_freq == '') or ($r_inv_freq == 'NA')) {

								$r_freq2 = mysql_query("SELECT SUM(individuals) individuals, SUM(individuals*inv_frequency)/SUM(individuals) inv_frequency
							FROM
							(SELECT region, SUM(individuals) individuals, SUM(individuals*inv_frequency)/SUM(individuals) inv_frequency
							FROM 
							(SELECT r.region, pd.population_name, IFNULL(pd.individuals,1) individuals, AVG(pd.inv_frequency) inv_frequency
							FROM population_distribution pd
							INNER JOIN(
							    SELECT inv_id, population_name, MAX(individuals) individuals
							    FROM population_distribution
							    GROUP BY inv_id, population_name
							) invres ON pd.inv_id = invres.inv_id 
								AND pd.population_name = invres.population_name 
								AND pd.individuals = invres.individuals
							INNER JOIN population r ON r.name=pd.population_name 
							WHERE pd.inv_id = '".$row['inv_id']."'
							GROUP BY pd.population_name) allpopulations
							GROUP BY region) allregions;");
								$r_freq2 = mysql_fetch_array($r_freq2);
								$r_inv_freq=$r_freq2[1];
	
							}

							if (($r_inv_freq != '') and ($r_inv_freq != 'NA')) {
							$r_inv_freq = number_format($r_inv_freq,4);
							}						
								
						} else {
					
						$d_freq = explode(";", $row['frequency_distribution']);
						$r_inv_freq=$d_freq[2];
						
						if (($r_inv_freq != '') and ($r_inv_freq != 'NA')) {
							$r_inv_freq = number_format($r_inv_freq,4);
						}
									
					}
				
				if (($r_inv_freq == '') or ($r_inv_freq == 'NA')) {
					$r_inv_freq = "<font color='grey'>ND</font>";
				}
				
				} else {
				
				$r_inv_freq = "<font color='grey'>NA</font>";
				$row['genomic_effect'] = 'NA';
				
				}
		$rowCount++;
		$name=$row[name];
						$chr=$row[chr];
						$status=$row[status];
						$query_pos = $chr.":".$start."-".$end;
						$query_pos = str_replace("\n", "", $query_pos);

						$bp1_start=$row[bp1_start];
						$bp1_end=$row[bp1_end];
						$bp2_start=$row[bp2_start];
						$bp2_end=$row[bp2_end];

						$position_hg18=$chr.":".$bp1_start."-".$bp2_end;
						$size = number_format(round(($bp2_end-$bp1_start)+1));
						preg_match("/>(.*?)</", $array_status[$row['status']], $output_array);
						$status = $output_array[1];
						if(preg_match("/>(.*?)</",$array_effects[$row['genomic_effect']], $output_array)){$effect = $output_array[1];}else{$effect = $array_effects[$row['genomic_effect']];}
						preg_match("/>(.*?)</", $r_inv_freq, $output_array);
						$freq = $output_array[1];
						
		$inversion= "$ID\t$name\t$query_pos\t$position_hg18\t$size\t$status\t$freq\t$effect\n";
		#echo "$inversion<br>";
		fwrite($output, $inversion);
	}
}
	else{die("Wrong position syntax for entry:\t$line\n");}
}
#echo "$rowCount\n"; Number of inversions matched
fclose($fh);?>



<script type="text/javascript" src="js/header.js"></script>
<!-- ................................................................................................................................. -->
<body>
<style 'type=text/css'>

form {
display: inline;
}
#report {
/*	margin: 1em 6% 1.5em 6%;*/
	padding: 0;
	background-color:#f5f5f9;/*#444444*/
}

.report-section {
	margin: 0.5em 0.0em 0.5em 0.0em;
	/*width: 90%;*/
	/*border: solid gray 2px;*/
	/*margin-bottom: 0.5em;*/
	background-color: rgba(255, 255, 255, 0.6);
	/*border:1px solid #F1F1F1;*/
}
.section-content,
.grlsection-content{
		padding: 0.5em 3em ;
		border:1px solid #F1F1F1;
		font-family:Ubuntu;
		font-size: 14px;
}

.floating {
	width: 48%;
	float: left;
	margin: 0.4em;
	font-size: 0.8em;
}

.TitleA,.TitleB,.TitleOther {
	padding: 0 0.2em 0 0.4em;
	color:white;
}

.TitleStatic {
    color: #1c4257;
	background: #a3cde3;
	background: -webkit-gradient(linear, left top, left bottom, from(#b9e0f5), to(#85b2cb));
	background: -moz-linear-gradient(top, #b9e0f5, #85b2cb);
	border: 1px solid #759bb1;
	border-top-color: #8ab0c6;
	border-bottom-color: #587e93;
		text-shadow: 0 1px 1px #fff;
		font-size: 14px;
	font-weight: bold;
	font-family:Ubuntu;
}

.TitleA {
	/*font-size: 1.4em;
	border: solid white 1px;
	background-color: #405BA2;*/
    
    color: #1c4257;
	background: #a3cde3;
	background: -webkit-gradient(linear, left top, left bottom, from(#b9e0f5), to(#85b2cb));
	background: -moz-linear-gradient(top, #b9e0f5, #85b2cb);
	border: 1px solid #759bb1;
	border-top-color: #8ab0c6;
	border-bottom-color: #587e93;
		text-shadow: 0 1px 1px #fff;
		font-size: 14px;
	font-weight: bold;
	font-family:Ubuntu;
	
	
}

.TitleA:hover,
.TitleA:focus {
	box-shadow: 0 0 7px #53a6d5;
	
		
		     box-shadow: 0 0 7px #53a6d5, inset 0 1px 0 #fff;
    		-webkit-box-shadow: 0 0 7px #53a6d5, inset 0 1px 0 #fff;
    		-moz-box-shadow: 0 0 7px #53a6d5, inset 0 1px 0 #fff;
    		-o-box-shadow: 0 0 7px #53a6d5, inset 0 1px 0 #fff;
			
		}
.TitleA:active {
		background: #8abcd7;
		background: -webkit-gradient(linear, left top, left bottom, from(#81afc8), to(#b7def4));
		background: -moz-linear-gradient(top, #81afc8, #b7def4);
		border-color: #6e94a9;
		border-top-color: #567c91;
		border-bottom-color: #88aec4;

		box-shadow: inset 0 -1px 1px #fff;
    		-webkit-box-shadow: inset 0 -1px 1px #fff;
    		-moz-box-shadow: inset 0 -1px 1px #fff;
    		-o-box-shadow: inset 0 -1px 1px #fff;
		
		
		}

/*.TitleB {
	font-size: 1.2em;
	border: solid #9A9999 1px;
	background-color: #96A9E4;
}*/

.TitleB {
	/*font-size: 1.4em;
	border: solid white 1px;
	background-color: #405BA2;*/
    
    color: #57261c;  
	background: #e3b4a3;   
	background: -webkit-gradient(linear, left top, left bottom, from(#f5c1b9), to(#cb9185));    
	background: -moz-linear-gradient(top, #f5c1b9, #cb9185);
	border: 1px solid #b17d75;  
	border-top-color: #c6928a;  
	border-bottom-color: #935d58;  
		text-shadow: 0 1px 1px #fff;
		font-size: 14px;
	font-weight: bold;
	font-family:Ubuntu;
}

.TitleB:hover,
.TitleB:focus {
	box-shadow: 0 0 7px #d56a53;   
	
		
		     box-shadow: 0 0 7px #d56a53, inset 0 1px 0 #fff;
    		-webkit-box-shadow: 0 0 7px #d56a53, inset 0 1px 0 #fff;
    		-moz-box-shadow: 0 0 7px #d56a53, inset 0 1px 0 #fff;
    		-o-box-shadow: 0 0 7px #d56a53, inset 0 1px 0 #fff;
			
		}
.TitleB:active {
		background: #d7948a;  
		background: -webkit-gradient(linear, left top, left bottom, from(#c88a81), to(#f4bcb7));    
		background: -moz-linear-gradient(top, #c88a81, #f4bcb7);
		border-color: #a9736e;  
		border-top-color: #915e56;  
		border-bottom-color: #c48d88;  

		box-shadow: inset 0 -1px 1px #fff;
    		-webkit-box-shadow: inset 0 -1px 1px #fff;
    		-moz-box-shadow: inset 0 -1px 1px #fff;
    		-o-box-shadow: inset 0 -1px 1px #fff;
		
		
		}











.TitleOther {
	font-size: 1.4em;
	border: solid white 1px;
	background-color: #B7C6FF;
}
.section-title small {
	float:right;
	font-size: 0.6em;
}
.section-title:hover {
	cursor: s-resize;
}

/*.section-content,*/
.ContentA {
	padding: 0.5em 3em ;
	background-color: white;
    /*border: solid 1px;
    border-collapse: collapse;
    border-color: #405BA2;*/
}

.section-content p:hover,
.ContentA p:hover{
/*	background-color: rgba(175,175,175, 0.4);*/

}

.prova:hover{
	background-color: rgba(175,175,175, 0.4);
	-moz-border-radius: 8px;
}

.ContentA li {
	padding-top: 0.5em;
}

.hidden {
	display: none
}

/*.right, 
.left {
	width : 49%;
	padding: 0;
}
*/
.left {	
	float:left; 
}

.right { 
	float: right; 
}

.bkp {
	font-size: 0.9em;
	border: solid gray 1px;
	margin: 1em;
	padding: 1em;
	width: 40%;
	background-color: rgba(255, 255, 255, 0.7);
}

.bkp h4{
	margin-top: 0;
}

.field {
	margin: 0;
	padding: 0.3em 0.3em;
}	

/*IFRAME*/
iframe {
	display:block;
	margin: auto;
	width: 90%;
}

#region {
	display:block;
	margin: auto;
	padding-bottom: 1em;
}

/**/
.invalid_pred {
	color:#808080;
	font-style:italic;
}

table,tr,td {
	border: 1px solid black;
	border-collapse:collapse;
    border-color: #dcdcea;
    font-family: Ubuntu;
    font-size: 14px;
}
td.title {
	font-weight:bolder;
	background-color:#f5f5f9;
	/*width:23%;*/
}
th.title {
	font-weight:bolder;
	background-color:#bababa;
}


</style>
  <div id="search_results">
	<div class="section-title TitleA">- <?php echo "<b>$rowCount</b> inversions found";?><form method="post" action="php/invfest_finder_download_matched_inversions.php">&nbsp;&nbsp;<?php echo "<input type='image' class='download' src='img/download.png' onFocus='this.form.submit()' name='pathoutput' title='Download table' style='width:14px; height:14px'/>";?><input  type='hidden'  name='pathoutput' value="<?php echo $outputpath;?>">

</form></div>
	<div class='section-content'>
	
	<!-- <?php echo "<b>$count_result</b> inversions found <br/><br/>";?> -->

	<div id="results_table">
		<table id="sort_table" width="100%">
		<thead>
		  <tr>
			<th class='title' width='7%'> Query ID <img src='css/img/sort.gif'></th>			
			<th class='title' width='7%'>Name <img src='css/img/sort.gif'></th>
			<th class='title' width='10%'>Query position <img src='css/img/sort.gif'></th>
			<th class='title' width='18%'>Position (hg18) <img src='css/img/sort.gif'></th>
			<th class='title' width='10%'>Estimated Inversion size (bp) <img src='css/img/sort.gif'></th>
			<th class='title' width='10%'>Status <img src='css/img/sort.gif'></th>
			<th class='title' width='5%'>Global frequency <img src='css/img/sort.gif'></th>
			<th class='title' width='27%'>Functional effect <img src='css/img/sort.gif'></th>
		</tr>
		</thead>
<form method="post" action="php/invfest_finder_download_matched_inversions.php">


<!-- <input  type='hidden'  name='pathoutput' value="<?php echo $outputpath;?>">
&nbsp;&nbsp;<input type="image" name='pathoutput' src="img/download.png" alt="Submit Form" width='23' height='23' align="right"/>

</form> -->
		<?php
$fh = fopen($_FILES['fileToUpload']['tmp_name'], 'rb') or die ("Could not upload file");
$ID = "0";
			while (($line = fgets($fh)) !== false) {
	

				if(preg_match("/:chr\d{1,2}:/", $line) or preg_match("/:chr[M,X,Y]:/",$line) or preg_match("/^chr\d{1,2}:/", $line) or preg_match("/^chr[M,X,Y]:/",$line)){ #if position (so no header), query it

					$cols = explode(":", $line);
					if (count($cols) == 3){
					$ID = $cols[0];
					$chr = $cols[1];
					$positions = $cols[2];
					$position = explode("-", $positions);
					$start = $position[0];$end = $position[1];
					}
					if (count($cols) == 2){
					    $ID++;
					    $chr = $cols[0];
					    $positions = $cols[1];
					    $position = explode("-", $positions);
					    $start = $position[0];$end = $position[1];
					}
		
					$sq_query= "Select frequency_distribution, name, chr, status, inv_id, bp1_start, bp1_end, bp2_start, bp2_end, definition_method, genomic_effect from (select i.frequency_distribution, i.name, i.chr, i.status, b.inv_id,  b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, b.definition_method, b.genomic_effect from breakpoints b, inversions i where i.id = b.inv_id ORDER BY FIELD (b.definition_method, 'manual curation', 'default informatic definition'), b.id DESC) as my_table_temp WHERE chr = '$chr' AND (($start BETWEEN (bp1_start-$start_bias) AND (bp1_start+$start_bias)) AND ($end BETWEEN (bp2_end-$end_bias) AND (bp2_end+$end_bias))) AND status NOT IN ('WITHDRAWN','Withdrawn','withdrawn') GROUP BY inv_id ORDER BY FIELD (status, 'TRUE','possible_TRUE', 'possible_FALSE','FALSE','Ambiguous/FALSE','FILTERED OUT','ND','AMBIGUOUS','Ambiguous','ambiguous');";

					$resultss=mysql_query($sq_query);if (!$resultss) {echo('Invalid query: ' . mysql_error());}
	
	
					while($row = mysql_fetch_array($resultss)){
					if ($row['status'] != 'FALSE') {
				
						if ($_SESSION["autentificado"]=='SI') {
					
							$r_freq = mysql_query("SELECT inv_frequency('".$row['inv_id']."','all','all','all') AS res_freq");
							$r_freq = mysql_fetch_array($r_freq);
							$d_freq = explode(";", $r_freq['res_freq']);
							$r_inv_freq=$d_freq[2];
						
							// Si no s'ha determinat la freqüència amb genotips, calcular-la sense genotips
							if (($r_inv_freq == '') or ($r_inv_freq == 'NA')) {

								$r_freq2 = mysql_query("SELECT SUM(individuals) individuals, SUM(individuals*inv_frequency)/SUM(individuals) inv_frequency
							FROM
							(SELECT region, SUM(individuals) individuals, SUM(individuals*inv_frequency)/SUM(individuals) inv_frequency
							FROM 
							(SELECT r.region, pd.population_name, IFNULL(pd.individuals,1) individuals, AVG(pd.inv_frequency) inv_frequency
							FROM population_distribution pd
							INNER JOIN(
							    SELECT inv_id, population_name, MAX(individuals) individuals
							    FROM population_distribution
							    GROUP BY inv_id, population_name
							) invres ON pd.inv_id = invres.inv_id 
								AND pd.population_name = invres.population_name 
								AND pd.individuals = invres.individuals
							INNER JOIN population r ON r.name=pd.population_name 
							WHERE pd.inv_id = '".$row['inv_id']."'
							GROUP BY pd.population_name) allpopulations
							GROUP BY region) allregions;");
								$r_freq2 = mysql_fetch_array($r_freq2);
								$r_inv_freq=$r_freq2[1];
	
							}

							if (($r_inv_freq != '') and ($r_inv_freq != 'NA')) {
							$r_inv_freq = number_format($r_inv_freq,4);
							}						
								
						} else {
					
						$d_freq = explode(";", $row['frequency_distribution']);
						$r_inv_freq=$d_freq[2];
						
						if (($r_inv_freq != '') and ($r_inv_freq != 'NA')) {
							$r_inv_freq = number_format($r_inv_freq,4);
						}
									
					}
				
				if (($r_inv_freq == '') or ($r_inv_freq == 'NA')) {
					$r_inv_freq = "<font color='grey'>ND</font>";
				}
				
				} else {
				
				$r_inv_freq = "<font color='grey'>NA</font>";
				$row['genomic_effect'] = 'NA';
				
				}
						$name=$row[name];
						$chr=$row[chr];
						$status=$row[status];
						$effect= $row[genomic_effect];

						$bp1_start=$row[bp1_start];
						$bp1_end=$row[bp1_end];
						$bp2_start=$row[bp2_start];
						$bp2_end=$row[bp2_end];

						$query_position = $chr.":".$positions;

						$position_hg18=$chr.":".$bp1_start."-".$bp2_end;
						$size = number_format(round(($bp2_end-$bp1_start)+1));

						echo "<tr>";
						echo "<td>$ID</td>";
						echo "<td><a href=\"report.php?q=".$row['inv_id']."\" target=\"_blank\" >".$name."</a></td>";
						echo "<td>$query_position</td>";
						echo "<td>$position_hg18</td>";
						echo "<td>$size</td>";
						echo "<td>".$array_status[$row['status']]."</td>";
						echo "<td>".$r_inv_freq."</td>";
						echo "<td>".$array_effects[$row['genomic_effect']]."</td>";
						echo "</tr>";
					}
				}
			}
	
?>
</tbody></table>

	</div>
	</div>
  </div>

  <br />
  <div id="foot"><?php include('php/footer.php');?>
  </div>

 </div><!--end Wrapper-->
</body>
</html>



<?php
mysql_close($con);
?>






