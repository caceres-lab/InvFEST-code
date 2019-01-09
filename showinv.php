<?php
/******************************************************************************
  SHOWINV.PHP

  The page showinv.php will show a track containing the info of gbk files.
  The files used are in gbk folder, and are *.tab
  These files are converted with the script that are in the same folder gb2bed.py
  It needs as input the gbk files created by lab people and Biopython (so probably cannot run at the server)
    1. get new gbk files from dropbox in a computer that has installed Biopython
    2. run the script python gb2bed.py file.gbk
    3. copy tab files to gbk folder at server
  To add new inversions:
    1. Do previous steps
    2. Add to the variable $filelist the new inversion: name => file_name
  To change colors or add features:
    1. The switch function in the script handle the colors of different features: SD, BP, Genes... Just add a new line of code
    2. To filter out a new feature (like Primers), go to the if line, and add or strpos($feat[0],"NEWFEATURE")
       That will avoid to plot that feature.
*******************************************************************************/
?>


<!-- Session start for the PHP -->
<?php session_start(); ?>

<!DOCTYPE html>
<html>


<!-- **************************************************************************** -->
<!-- HEAD -->
<!-- **************************************************************************** -->
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <!-- **************************************************************************** -->
    <!-- SCRIPTS -->
    <!-- **************************************************************************** -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js"></script>
    <script src="js/Scribl-master/Scribl.1.1.4.min.js"></script>
    <script src="js/Scribl-master/dragscrollable.js"></script>
    <script src="js/header.js"></script>
    

    <!-- **************************************************************************** -->
    <!-- STYLES -->
    <!-- **************************************************************************** -->
    <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
    <style>
        #containerinv {
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }
        #scroll-wrapper #containerinv {
            padding-left: 0;
            padding-right: 0;
            margin-left: auto;
            margin-right: auto;
            display: block;
            width: 900px;
        }
        .slider {
            margin-left: 40px;
            font-size: 8px;
            width: 20px;
            display: inline-block;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="css/style.css" />

    <!-- **************************************************************************** -->
    <!-- DATA -->
    <!-- **************************************************************************** -->
    <?php
        #error_reporting(E_ALL);
        #ini_set('display_errors', '1');
        $listfiles=array(
	        "HsInv0124std" => "HsInv0124_74017bp_HG18_20140129_MP.tab", 
	        "HsInv0124inv" => "HsInv0124_74017bp_HG18inv_20140228_MP.tab", 
	        "HsInv102std"=>"HsInv102_12032bp_HG18_20140309_MC.tab",
            "HsInv102inv"=>"HsInv102_2465bp_ABC14_20140309_MC.tab",
            "HsInv1051std"=>"HsInv1051_542006bp_HG18_20140226_MP.tab",
            "HsInv1051inv"=>"HsInv1051_542006bp_HG18inv_20140228_MP.tab",
            "HsInv105inv"=>"HsInv105_34029bp_ABC7-ABC14_20140309_MC.tab",
            "HsInv105std"=>"HsInv105_72484bp_HG18_20140309_MC.tab",
            "HsInv114std"=>"HsInv114_66945bp_HG18_20140129_MP.tab",
            "HsInv114inv"=>"HsInv114_66945bp_HG18inv_20140228_MP.tab",
            "HsInv124std"=>"HsInv124_74017bp_HG18_20140129_MP.tab",
            "HsInv124inv"=>"HsInv124_74017bp_HG18inv_20140228_MP.tab",
            "HsInv201inv"=>"HsInv201_5510bp_Celera_201400309_MC.tab",
            "HsInv201std"=>"HsInv201_6866bp_HG18_20140309_MC.tab",
            "HsInv209std"=>"HsInv209_28962bp_HG18_20140226_MP.tab",
            "HsInv209inv"=>"HsInv209_28962bp_HG18inv_20140228_MP.tab",
            "HsInv260inv"=>"HsInv260_59933bp_ABC7-G248_20140309_MC.tab",
            "HsInv260std"=>"HsInv260_65507bp_HG18_201400309_MC.tab",
            "HsInv278std"=>"HsInv278_27043bp_HG18_20140226_MP.tab",
            "HsInv278inv"=>"HsInv278_27043bp_HG18inv_20140228_MP.tab",
            "HsInv284inv"=>"HsInv284_58353bp_ABC13_20140227_DIF.tab",
            "HsInv284std"=>"HsInv284_64557bp_HG18_20140207_DIF.tab",
            "HsInv30std"=>"HsInv30_26651bp_HG18_20140227_MP.tab",
            "HsInv30inv"=>"HsInv30_26653bp_HuRef_20140227_MP.tab",
            "HsInv3std"=>"HsInv3_10250bp_HG18_20140227_MP.tab",
            "HsInv3inv"=>"HsInv3_10250bp_HuRef_20140227_MP.tab",
            "HsInv31std"=>"HsInv31_11066bp_HG18_20140227_MP.tab",
            "HsInv31inv"=>"HsInv31_11089bp_HuRef_20140227_MP.tab",
            "HsInv340std"=>"HsInv340_117903bp_HG18_20140226_MP.tab",
            "HsInv340inv"=>"HsInv340_117903bp_HG18inv_20140226_MP.tab",
            "HsInv341std"=>"HsInv341_70754bp_HG18_20140226_MP.tab",
            "HsInv341inv"=>"HsInv341_70754bp_HG18inv_20140226_MP.tab",
            "HsInv344std"=>"HsInv344_80544bp_HG18_20140226_MP.tab",
            "HsInv344inv"=>"HsInv344_80544bp_HG18inv_20140228_MP.tab",
            "HsInv347std"=>"HsInv347_78174bp_HG18_20140226_MP.tab",
            "HsInv347inv"=>"HsInv347_78174bp_HG18inv_20140226_MP.tab",
            "HsInv374std"=>"HsInv374_72497bp_HG18_20140226_MP.tab",
            "HsInv374inv"=>"HsInv374_72497bp_HG18inv_20140228_MP.tab",
            "HsInv389std"=>"HsInv389_90911bp_HG18_20140227_MP.tab",
            "HsInv389inv"=>"HsInv389_90911bp_HG18inv_20140228_MP.tab",
            "HsInv393std"=>"HsInv393_71065bp_HG18_201402026_MP.tab",
            "HsInv393inv"=>"HsInv393_71065bp_HG18inv_201402028_MP.tab",
            "HsInv396std"=>"HsInv396_112301bp_HG18_20140226_MP.tab",
            "HsInv396inv"=>"HsInv396_112301bp_HG18inv_20140226_MP.tab",
            "HsInv397std"=>"HsInv397_66162bp_HG18_20140226_MP.tab",
            "HsInv397inv"=>"HsInv397_66162bp_HG18inv_20140226_MP.tab",
            "HsInv40inv"=>"HsInv40_14209bp_HuRef_20140227_MP.tab",
            "HsInv40std"=>"HsInv40_14255bp_HG18_20140227_MP.tab",
            "HsInv403inv"=>"HsInv403_75453bp_HG19_20140227_MP.tab",
            "HsInv403std"=>"HsInv403_75465bp_HG18_20140227_MP.tab",
            "HsInv409std"=>"HsInv409_10200bp_HG18_20140309_MC.tab",
            "HsInv409inv"=>"HsInv409_2051bp_ABC12_20140309_MC.tab",
            "HsInv4std"=>"HsInv4_10762bp_HG18_20140227_MP.tab",
            "HsInv4inv"=>"HsInv4_10764bp_HuRef_20140227_MP.tab",
            "HsInv41std"=>"HsInv41_10103bp_HG18_20140227_MP.tab",
            "HsInv41inv"=>"HsInv41_9193bp_HuRef_20140227_MP.tab",
            "HsInv45std"=>"HsInv45_10955bp_HG18_20140227_MP.tab",
            "HsInv45inv"=>"HsInv45_10955bp_HuRef_20140227_MP.tab",
            "HsInv55std"=>"HsInv55_68216bp_HG18_20140227_MP.tab",
            "HsInv55inv"=>"HsInv55_68260bp_HuRef_20140227_MP.tab",
            "HsInv58std"=>"HsInv58_10874bp_HG18_20140307_MC.tab",
            "HsInv58std"=>"HsInv58_10877bp_HG18_20140227_MP.tab",
            "HsInv58inv"=>"HsInv58_13599bp_HuRef_20140307_MC.tab",
            "HsInv58inv2"=>"HsInv58_13601bp_HuRef_20140227_MP.tab",
            "HsInv59std"=>"HsInv59_10309bp_HG18_20140305_MP.tab",
            "HsInv59inv"=>"HsInv59_10917bp_HuRef_20140305_MP.tab",
            "HsInv61inv"=>"HsInv61_11636bp_HuRef_20140227_MP.tab",
            "HsInv61std"=>"HsInv61_31676bp_HG18_20140227_MP.tab",
            "HsInv63inv"=>"HsInv63_22449bp_HuRef_20140227_MP.tab",
            "HsInv63std"=>"HsInv63_27695bp_HG18_20140227_MP.tab",
            "HsInv68std"=>"HsInv68_10250bp_HG18_20140227_MP.tab",
            "HsInv68inv"=>"HsInv68_11807bp_HuRef_20140227_MP.tab",
            "HsInv72inv"=>"HsInv72_12106bp_HuRef_20140227_MP.tab",
            "HsInv72std"=>"HsInv72_12267bp_HG18_20140227_MP.tab",
            "HsInv832std"=>"HsInv832_71880bp_HG18_20140226_MP.tab",
            "HsInv832inv"=>"HsInv832_71880bp_HG18inv_20140228_MP.tab",
            "HsInv92std"=>"HsInv92_14040bp_HG18_20140307_MC.tab",
            "HsInv92inv"=>"HsInv92_5078bp_ABC10_20140307_MC.tab",
            "HsInv95inv"=>"HsInv95_62587bp_ABC13-ABC10_20140308_MC.tab",
            "HsInv95std"=>"HsInv95_62608bp_HG18_20140308_MC.tab",
            "HsInv97std"=>"HsInv97_37820bp_HG18_20140308_MC.tab",
            "HsInv97inv"=>"HsInv97_73953bp_G248_20140308_MC.tab",
            "HsInv98std"=>"HsInv98_11818bp_HG18_20140308_MC.tab",
            "HsInv98inv"=>"HsInv98_1434bp_ABC10_20140308_MC.tab",
            "HsInv98inv2"=>"HsInv98_1434bp_ABC10wextraAseq_20140308_MC.tab",
        );

        $inv=$_GET['name'];
        $invfile=$listfiles[$inv];
        if ($invfile!="") {
            $track="";
            $fh = fopen('gbk/'.$invfile,'r');
            $idx = 0;
            while ($line = fgets($fh)) {
                // Do your work with the line
                #echo($line);
                $line=rtrim($line); 
                $feat = preg_split('/\t/', $line);
                #echo $feat[0];echo $feat[4];echo ".<br>";

                $color='rgb(20,150,255)';
                switch ($feat[0]) {
                    case Indel:
                    $color='rgb(120,0,255)';break;
                    case Complex_inversi:
                    $color='rgb(120,120,255)';break;
                    case Inversion:
                    $color='rgb(120,120,255)';break;
                    case SD:
                    $color='rgb(229,183,90)';break;
                    case Gene:
                    $color='rgb(140,208,148)';break;
                }
                if (strpos($feat[0],"Primer") === FALSE) {
                    $idx++;
                    $l=$feat[3]-$feat[2]+1;
                    $track=$track."f".$idx."=new BlockArrow('".$feat[1]."', ".$feat[2].", ".$l." , '".$feat[4]."') ;";
                    $track=$track."f".$idx.".color = '".$color."';";
                    $track=$track."f".$idx.".name = '".$feat[1]."';";
                    $track=$track."f".$idx.".onMouseover = '".$feat[1]."';";
                    #$track=$track."f".$idx.".onClick = function() {alert('".$feat[1]."');};";
                    $track=$track."g".$idx." = chart.addFeature("."f".$idx.");";
                }
            }
            #echo $track;
            #echo '<br><br><br>';
            fclose($fh);
        }
    ?>

    <!-- **************************************************************************** -->
    <!-- SCRIPTS -->
    <!-- **************************************************************************** -->
    <script>
        $(function() {
            $('select').change(function() {
                $sel = $(this).val();
		        alert($sel);
		        window.location.href = "showinv.php?name="+$sel;
            });
        });

        function draw(canvasName) {  	

            // Get Canvas and Create Chart
            var canvas = document.getElementById(canvasName);

            // Create Chart
            chart = new Scribl(canvas, 900);
            chart.scrollable = true;

            // Change laneSizes and buffers
            chart.laneSizes = 18;
            chart.laneBuffer = 2;
            chart.trackBuffer = 40;

            // Change text color
            chart.glyph.text.color = 'white';	

            // Create Track 1
            //track1 = chart.addTrack();	

            // Add Genes to Track 1
            <?php echo $track;?>

            //gene1 = chart.addFeature( new BlockArrow('track1', 30000, 35000 , '-') );

            //chart.track1.name = 'track 1';

            // Create Track 2

            // Add Genes to Track 2

            // Create Lane1 of Track2

            // Draw Chart
            chart.draw();

        }
	</script>

</head>


<!-- **************************************************************************** -->
<!-- BODY -->
<!-- **************************************************************************** -->
<body onload="draw('canvas')">
    
    <?php include('php/echo_menu.php'); ?>
    <br><br>
    <select>
        <?php
            foreach ($listfiles as $k=>$v) {
                echo "<option value=\"".$k."\">".$k."</option>";
            }
        ?>
    </select>
    <br><br>
    <div>
        <?php echo $inv; ?>
        <div>
        <div id="containerinv">
            <canvas id="canvas" width="947" height="400"></canvas>
        </div>
    <!-- Two /div are missed!!! -->
</body>

</html>