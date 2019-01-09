 <?php
/******************************************************************************
	INDEX.PHP

	InvFEST's home page ("About the project" menu from the website)
*******************************************************************************/
?>


<!-- Session start for the PHP -->
<?php session_start(); ?>

<!DOCTYPE html>
<html>

<?php

	// Select specific data into variables which are retrieved in other php pages
	include_once('php/select_index.php');

	// Includes HTML <head> and other settings for the page
	include_once('php/structure_page.php');

	echo $creator;

    $head .= "
        <!-- **************************************************************************** -->
        <!-- STYLES -->
        <!-- **************************************************************************** -->
        <style>

	        #parent_div_1, #parent_div_2, #parent_div_3 {
		        border: 0px;
		        margin-right: 10px;
		        float: left;
	        } .section-content {
		        float: left;
		        margin-right: 5px;
		        width: 700px;
		        border: none;
	        } .content {
		        float: right;
		        margin-right: 5px;
		        width: 300px;
		        height: auto;
	        }
            
            table.sample {
		        width: 30%;
		        height: 25%;
		        border-width: 0px;
		        border-spacing: 0px;
		        border-style: hidden;
		        border-color: white;
		        border-collapse: collapse;
	        } table.sample th {
		        border-width: 0px;
		        padding: 2px;
		        border-style: none;
		        border-color: none;
	        } table.sample td {
		        border-width: 0px;
		        padding: 2px;
		        border-style: none;
		        border-color: none;
		        white-space: nowrap;
	        } table.sample tr {
		        border-bottom:hidden;
	        }

	        input.deletebox {
		        background: url('./img/delete.png');
		        background-size: 20px 15px;
	 	        background-color: transparent;
		        background-repeat: no-repeat;
	 	        border: none;
		        width: 35px;
		        height: 25px;
		        font-size: 0.1px;
	        }

        </style>


        <!-- **************************************************************************** -->
        <!-- SCRIPT: Show ADD NEWS -->
        <!-- **************************************************************************** -->
        <script>
                function Show_Div(Div_id) {
                    if (false == $(Div_id).is(':visible')) {
                        $(Div_id).show(250);
                    } else {
                        $(Div_id).hide(250);
                    }
                }
        </script>
    ";

	$head .= "</head>";  // Head end
    echo $head;               // 'Print' head code

?>


<!-- **************************************************************************** -->
<!-- BODY -->
<!-- **************************************************************************** -->
<body>

    <?php /* >> Currently not working. Google Analytics instead.
	    // VISITS' LOG: Write in a TXT file each $IP visit
	    $myFile="stats/hits.txt";
	    $ip=$_SERVER['REMOTE_ADDR'];
	    $date=date('m d Y', time());
	    $stringData="$ip $date\n";
	    #echo $stringData;
	    file_put_contents($myFile, $stringData, FILE_APPEND | LOCK_EX);
    */ ?>

    <!-- **************************************************************************** -->
    <!-- PAGE MENU: Print the header banner of InvFEST -->
    <!-- **************************************************************************** -->	
    <?php include('php/echo_menu.php'); ?>

    <br/>

    <!-- **************************************************************************** -->
    <!-- DIVISIONS -->
    <!-- **************************************************************************** -->
    <div id='parent_div_1'>
	    <!-- WELCOME -->
	    <div class ='section-content'>
		    <p style="text-align: justify;"><b>Welcome to the InvFEST database!</b></p>
		    <p style="text-align: justify;">InvFEST aims to become a reference site to share information and collaborate towards the complete characterization of human polymorphic inversions. It is a data-warehouse implementation that integrates several data of interest related to inversions with an online analytical processing engine (OLAP) to gather information and compute a report of each inversion. </p>
		    <p style="text-align: justify;">The InvFEST database stores and merges inversion predictions from healthy individuals into a non-redundant dataset by overlapping the position of the breakpoints of each prediction and taking into account the resolution of each study. Most predictions come from mapping information of paired-end sequences (PEM) obtained by different studies of the literature, which in some cases have been reanalyzed by GRIAL, a program specifically designed to detect inversions from PEM data. Moreover, it stores information of validations and genotyping assays, frequency in different populations, association with genes and segmental duplications, and the evolutionary history of the inversions. </p>
		    <p style="text-align: justify;">The database will keep on updating information by incorporating new predictions, validations, genotyping data, and any other information, either extracted from peer reviewed research studies or generated in our lab. We always welcome your suggestions and comments.</p>
		    <p style="text-align: justify;">The InvFEST database is an outcome of the INVFEST project, supported by the European Research Council (ERC) Starting Grant 243212 under the European Union Seventh Research Framework Programme (FP7).</p>
	    </div>
	
	    <!-- NEWS -->
	    <div class="content">
		    <?php
			    $query="SELECT id, Title, Comment, DATE_FORMAT(Date,'%d-%m-%Y') as 
				    Date FROM News ORDER BY DATE(Date) DESC LIMIT 6;";
			    $result=mysql_query($query);
			    if (!$result) {
				    die('Invalid query: ' . mysql_error());
			    }
			    echo '<table class="sample">';
			    echo '<tr><br><p style="color: #006666; font-family: Sans-serif; font-size: 0.9em;"><b>News</b></p></tr>';
			    while($news= mysql_fetch_array($result)) {
				    echo '<tr><td>' . $news['Date'] . '</td></tr><tr><td>' . 
					    '<i>&nbsp&nbsp' . $news['Title'] . '</i></td>';
				    #Authorized users have additional controls to DELETE news
				    if ($_SESSION["autentificado"]=='SI') {
		    ?>
					    <form name="myForm2" action="php/insert_news.php" method='post'>
						    <?php echo "<td><input type=\"submit\" name='id' value=" . 
							    $news['id'] . " class=\"deletebox\" ></td></tr>";
						    ?>
					    </form>
		    <?php
				    }
				    echo '<tr><td height="10">' . "" . '</td></tr>'; // Empty row between the news
			    }
			    echo "</table>";
			    // Authorized users have additional controls to ADD news
			    if ($_SESSION["autentificado"]=='SI') {
				    echo"<input type=\"button\" value=\"Add\" onclick=\"Show_Div(Div_1)\" />";
			    }
		    ?>
				<table class="sample">
			    <tr><br><p style="color: #006666; font-family: Sans-serif; font-size: 0.9em;"><b>How to cite</b></p></tr>
			    <p style="text-align: justify; font-size:0.857em; font-family: Ubuntu">If you use InvFEST, please cite:</p>
			    <p style="text-align: justify; font-size:0.857em; font-family: Ubuntu"><i>Martínez-Fundichely, A. et al. InvFEST, a database integrating information of polymorphic inversions in the human genome. Nucleic Acids Research 42, (2014). </i><a href="http://dx.doi.org/10.1093/nar/gkt1122">doi:10.1093/nar/gkt1122</a></p>
			    </table>
			    
			    <div id="Div_1" class="content" style="display:none;">
				    <form name="myForm" action="php/insert_news.php" method='post'>
					    <small>Title</small>
					    <br>
					    <textarea rows="1" cols="40" name='Title' class='left' id='title'></textarea>
					    <br><br>
					    <small>Comment</small>
					    <br>
					    <textarea rows="2" cols="40" name='Comment' class='left' id='comment'></textarea>
					    <input type='submit' class='right' value="Submit" name="submit">
				    </form>
			    </div>
	    </div>
    </div>

    <!-- SPONSORS -->
    <div id="sponsors">
	    <p style="text-align:center;">
		    <img src="img/logo-uab.gif" alt="UAB" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <img src="img/logoibb.gif" alt="IBB" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <img src="img/LOGO-ERC.gif" alt="ERC" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <img src="img/flag_yellow_low.jpg" alt="EU" height="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		    <img src="img/FP7-ide-RGB.gif" alt="FP7" height="50">
	    </p>

	    <p style="text-align:center;">&nbsp;<br/>
		    <a href="search.php">
			    <button class="default">Start querying InvFEST</button>
		    </a>
	    </p>
    </div>

    <!-- **************************************************************************** -->
    <!-- FOOT OF THE PAGE -->
    <!-- **************************************************************************** -->
    <div id="foot">
	    <?php include('php/footer.php'); ?>
    </div>

</div> <!-- Closes the Wrapper's divison opened at 'echo_menu.php' -->

</body>
</html>
