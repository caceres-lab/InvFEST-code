<? 
session_start(); //Inicio la sesión
?>
<?php include_once('php/select_index.php');?>
<?php include_once('php/structure_page.php');?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php 
#error_reporting(E_ALL);
#ini_set('display_errors', TRUE);
#ini_set('display_startup_errors', TRUE);
echo $creator;
echo $head;

?>
<script type="text/javascript" src="js/header.js"></script>
<script src="js/Chart.js"></script>
<!-- ................................................................................................................................. -->
<body>


<?php include('php/echo_menu.php');?>
<br/>
  <div id="welcome" class="section-content">
   <p>Number of unique visits since last week</p></div>

<?php
 #load files
 $handle = fopen("stats/hits.txt", "r");
 $data=array();
 $ip=array();
 if ($handle) {
    while (($line = fgets($handle)) !== false) {
        // process the line read.
	$cols=explode(" ",chop($line));
 	if (! array_key_exists($cols[0],$ip)){
	 $data["a$cols[3]"]["m$cols[1]"]["d$cols[2]"]+=1;
	}
    	$ip[$cols[0]]=0;
    }
 } else {
    // error opening the file.
    echo "error oppening the hits file";
 }
 #var_dump($data);
 
 $values=array();
 $labs=array();
 for ($i=0;$i<=7;$i++){
  $d=date('m d Y',strtotime("-$i days"));
  $df=date('M,d',strtotime("-$i days"));
  $ad = explode(" ",$d);
 
  #echo $d."<br>";
  array_push($labs,$df);
  if ( array_key_exists("d$ad[1]",$data["a$ad[2]"]["m$ad[0]"])){
   #echo $data["a$ad[2]"]["m$ad[0]"]["d$ad[1]"]."<br>";
   array_push($values,$data["a$ad[2]"]["m$ad[0]"]["d$ad[1]"]);
  }
 }
#echo round(max($values)/5);
?>
 
   <canvas id="canvas" height="450" width="600"></canvas>
 
<script>

                var barChartData = {
                        labels : <?php echo json_encode($labs)?>,
                        datasets : [
                                {
                                        fillColor : "rgba(220,220,220,0.5)",
                                        strokeColor : "rgba(220,220,220,1)",
                                        data : <?php echo json_encode($values)?>
                                }
                        ]
                        
                }
	var opts={
		scaleOverride : true,
		scaleSteps : 5,
		scaleStepWidth : <?php echo round(max($values)/5); ?>,
		scaleStartValue : 0
	}
        var myLine = new Chart(document.getElementById("canvas").getContext("2d")).Bar(barChartData,opts);
        
</script>

 <br />
  <div id="foot"><?php include('php/footer.php');?>
  </div>

 </div><!--end Wrapper-->
</body>
</html>
