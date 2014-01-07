<? 
session_start(); //Inicio la sesión
?>
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
  <div id="welcome" class="section-content">
<p style="text-align: justify;"><b>Welcome to the InvFEST database!</b></p>
<p style="text-align: justify;">InvFEST aims to become a reference site to share information and collaborate towards the complete characterization of human polymorphic inversions. It is a data-warehouse implementation that integrates several data of interest related to inversions with an online analytical processing engine (OLAP) to gather information and compute a report of each inversion. </p>
<p style="text-align: justify;">The InvFEST database stores and merges inversion predictions from healthy individuals into a non-redundant dataset by overlapping the position of the breakpoints of each prediction and taking into account the resolution of each study. Most predictions come from mapping information of paired-end sequences (PEM) obtained by different studies of the literature, which in some cases have been reanalyzed by GRIAL, a program specifically designed to detect inversions from PEM data. Moreover, it stores information of validations and genotyping assays, frequency in different populations, association with genes and segmental duplications, and the evolutionary history of the inversions. </p>
<p style="text-align: justify;">The database will keep on updating information by incorporating new predictions, validations, genotyping data, and any other information, either extracted from peer reviewed research studies or generated in our lab. We always welcome your suggestions and comments.</p>
<p style="text-align: justify;">The InvFEST database is an outcome of the INVFEST project, supported by the European Research Council (ERC) Starting Grant 243212 under the European Union Seventh Research Framework Programme (FP7).</p>
<p style="text-align: center;">
	<img src="img/logo-uab.gif" alt="UAB" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<img src="img/logoibb.gif" alt="IBB" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<img src="img/LOGO-ERC.gif" alt="ERC" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<img src="img/flag_yellow_low.jpg" alt="EU" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<img src="img/FP7-ide-RGB.gif" alt="FP7" height="50">
</p>
<p style="text-align: center;">&nbsp;<br/><a href="search.php"><button class="default">Start querying InvFEST</button></a></p>
  </div>
  <br />
  <div id="foot"><?php include('php/footer.php');?>
  </div>

 </div><!--end Wrapper-->
</body>
</html>
