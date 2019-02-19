<?php
/******************************************************************************
	ECHO_MENU.PHP

	Prints the header banner from InvFEST
*******************************************************************************/
	session_start();
	// Connection to the database
	include('db_conexion.php');
?>

<!-- Add an image to distinguish when the developer is working on DEV-platform -->
<?php if($db == 'INVFEST-DB-dev') { ?>
	<div class="classDiv_Centered">
	    <img class="classImg_centered" src="img/invfest_dev.png" width="200">
	</div>
<?php } ?>
	<div class="classDiv_Centered">
	    <img class="classImg_centered" src="img/invfest_freeze.png" width="200">
	</div>
<!-- Define InvFEST header -->
<div id="wrapper">

<!-- Define a static header -->
	<div id="floatingbar">
	<table class="classTable_menu">
	<tr>

		<!-- InvFEST logo -->
  		<td class="classTd_logo">
  			<a href="index.php"><img img class="classImg_centered" alt="InvFEST" src="img/InvFEST.png" width="200"></a>
  		</td>
  		
  		<!-- InvFEST menu -->
  		<td class="classTd_menu">
            <a href="index.php"><img class="classImg_centered" src="img/longname.png" width="500"></a>
			<div class="classDiv_menu">
				<ul>
					<li><a href="index.php"><button class="default">About the Project</button></a></li>
					<li><a href="search.php"><button class="default">Search Inversions</button></a></li>
					<li><a href="download.php"><button class="default">Downloads</button></a></li>
					<li><a href="help.php"><button class="default">Help</button></a></li>
					<li><a href="submissions.php"><button class="default">Data Submissions</button></a></li>
					<li><a href="contact.php"><button class="default">Contact Us</button></a></li>
				</ul>
			</div>
		</td>
		
		<!-- 'Button' to login/logout from InvFEST -->
<!-- 		<td class="classTd_menuIcon">
			<div id="login" class="classDiv_menuIcon">
				<?php
			#		if ($id=='') {$origin='index';} 
			#		else         {$origin='report';}
		     #       
			#		if ($_SESSION["autentificado"]=='SI') { 
			#			echo'<a href="php/logout.php?origin='.$origin.'&q='.$id.
			#				'"><img src="img/logout.png" title="Logout" width="23"></a>';
			#		} else {
			#			echo'<a id="login2" href="php/login.php?origin='.$origin.'&q='.$id.
			#				'" onclick="return hs.htmlExpand(this, {objectType: \'iframe\', width: 300, height: 161, 
			#				preserverContent: false })" ><img src="img/login.png" title="Login" width="23"></a>';
			#		}
				?>
			</div> 		
  		</td> -->
  	</tr>
  	</table>	
	</div>

<!-- Define a floating header for the deep pages -->
	<div id="minibar">	
	<table class="classTable_menu">
   	<tr>

   		<!-- InvFEST logo -->
   		<td class="classTd_floatMenuLogo">
   			<a href="index.php"><img class="classImg_centered" alt="InvFEST" src="img/InvFEST.png" width="130"></a>
   		</td>
   		<!-- InvFEST menu -->
   		<td class="classTd_floatMenu">
   			<div id="haedtitle">&nbsp;&nbsp;Human Polymorphic Inversion DataBase</div>

			<div class="classDiv_menu"><ul>

			<?php if ($r['name']=='') { ?>
					<li><a href="index.php"><button class="default">About the Project</button></a></li>
					<li><a href="search.php"><button class="default">Search Inversions</button></a></li>
					<li><a href="download.php"><button class="default">Downloads</button></a></li>
					<li><a href="help.php"><button class="default">Help</button></a></li>
					<li><a href="submissions.php"><button class="default">Data Submissions</button></a></li>
					<li><a href="contact.php"><button class="default">Contact Us</button></a></li>
			<?php } else { ?>
					<li>&nbsp;&nbsp;Inversion Report: <b><?php echo $r['name'];?></b></li>
			<?php } ?>

			</ul></div>

   		</td>

   		<!-- 'Button' to scrolldown/scrollup for the deep pages -->
   		<td class="classTd_menuIcon">
			<div id="login" class="classDiv_menuIcon">
				<a href="javascript:#">
					<button title="Scroll" id="dirbutton" class="default">
						<img src="img/bottomarrow.png"></img>
					</button>
				</a>
			</div>
		</td>
	</tr>
	</table>
	</div>

<!-- NOTE: Wrapper's division is closed at the end of each page's content </div> -->
