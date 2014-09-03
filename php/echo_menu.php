 <div id="wrapper">
  <?php // echo $id_head;?> 
  <div  id="floatingbar">
  	
  <table style="border:0px;">
  	<tr style="border:0px;">
  		<td style="border:0px;width:200px;"><a href="index.php"><img border=0 src="img/InvFEST.png" width="200"></a></td>
  		<td style="border:0px;width:1430px;"><a href="index.php"><img border=0 src="img/longname.png" width="500"></a>
  		
     <div style="float: left">
    <ul>

		  <li></li>
		   <li><a href="index.php"><button class="default">About the Project</button></a></li>
		   <li><a href="search.php"><button class="default">Search Inversions</button></a></li>
		<!--   <li><a href="statistics.php"><button class="default">Statistics</button></a></li>	-->	   
		   <li><a href="download.php"><button class="default">Downloads</button></a></li>
		   <li><a href="help.php"><button class="default">Help</button></a></li>		   
		   <li><a href="submissions.php"><button class="default">Data Submissions</button></a></li>
		   <li><a href="contact.php"><button class="default">Contact Us</button></a></li>
		   <li><font color="#759bb1"><b>DB-Version: 1.0</b></font></li>

		 </ul>
    </div>		
  		</td>
  		<td valign="top" align="right" style="border:0px;">

  <div id="login" class='right'>
				<?
				if ($id=='') {$origin='index';} 
				else {$origin='report';}
				
				if ($_SESSION["autentificado"]=='SI'){echo'<a href="php/logout.php?origin='.$origin.'&q='.$id.'"><img src="img/logout.png" alt="Logout" width="23"></a>';}
				else {echo'<a id="login2" href="php/login.php?origin='.$origin.'&q='.$id.'" onclick="return hs.htmlExpand(this, {objectType: \'iframe\', width: 300, preserverContent: false })" ><img src="img/login.png" alt="Login" width="23"></a>';}?>
				
		</div> 		
  		</td>
  	</tr>
  </table>	
</div>
	


<div  id="minibar">
	
   <table style="border:0px;">
   	<tr style="border:0px;">
   		<td style="border:0px;width:130px;"><img border=0 src="img/InvFEST.png" width="130" style="float: left;"></td>
   		<td style="border:0px;width:1500px;"><div  id="haedtitle">&nbsp;&nbsp;Human Polymorphic Inversion DataBase</div>
   		
   <div style="float: left">
    <ul>


<?if ($r['name']==''){ ?>

		  <li></li>
		   <li><a href="index.php"><button class="default">About the Project</button></a></li>
		   <li><a href="search.php"><button class="default">Search Inversions</button></a></li>
	<!--	   <li><a href="statistics.php"><button class="default">Statistics</button></a></li>		-->   
		   <li><a href="download.php"><button class="default">Downloads</button></a></li>
		   <li><a href="help.php"><button class="default">Help</button></a></li>		   
		   <li><a href="submissions.php"><button class="default">Data Submissions</button></a></li>
		   <li><a href="contact.php"><button class="default">Contact Us</button></a></li>
		   <li><font color="#759bb1"><b>DB-Version: 1.0</b></font></li>


<?
} else { ?>

<li>&nbsp;&nbsp;&nbsp;Inversion Report: <b><?php echo $r['name'];?></b></li>

<? } ?>
           <li></li>


		   <li></li>
          </ul>
         </div>   		
   		
   		</td>
   		<td style="border:0px;" valign="top" align="right" >
  
 <div id="login" class='right'>
 
 <a href="javascript:#"><button title="Scroll" id="dirbutton" class="default">
		   <img border=0 src="img/bottomarrow.png"></img>
		   </button></a>
 <!--  		
  
				<?
				if ($id=='') {$origin='index';} 
				else {$origin='report';}
				
				if ($_SESSION["autentificado"]=='SI'){echo'<a href="php/logout.php?origin='.$origin.'&q='.$id.'"><img src="img/logout.png" alt="Logout" width="23"></a>';}
				else {echo'<a id="login2" href="php/login.php?origin='.$origin.'&q='.$id.'" onclick="return hs.htmlExpand(this, {objectType: \'iframe\', width: 300, preserverContent: false })" ><img src="img/login.png" alt="Login" width="23"></a>';}?>
				
				-->
				
		</div>
    </div>
   		
   		</td>
        </tr>
   </table>	
</div>

