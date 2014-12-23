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

<?php include('php/echo_menu.php');?>
<br/>

  <div id="welcome" class="section-content">
   <p style="text-align: justify;">This page lists all downloadable files of InvFEST, and instructions for direct access to our database server: <br/>&nbsp;</p>
   <p style="text-align: justify;"><b>The complete InvFEST database can be downloaded as a compressed SQL file</b></p>
   <p style="text-align: justify;"> Please download the file from <a href="sql/InvFESTdb.sql.gz">InvFESTdb.sql.gz</a><br/>&nbsp;</p>
   <p style="text-align: justify;"><b>Also available is a file with the genotypes for all individuals and inversions in InvFEST</b></p>
   <p style="text-align: justify;"> Please download the file from <a href="sql/genotypesTable.csv">genotypesTable.csv</a><br/>&nbsp;</p>
   <p style="text-align: justify;"><b>Direct access to the MySQL database at the InvFEST database server</b></p>
   <p style="text-align: justify;">Server: invfestdb.uab.cat <br/>
   User: invfestdb-user <br/>
   Database: INVFEST-DB-PUBLIC <br/>
   Password: invfestdb-user <br/>&nbsp;</p>
   <p style="text-align: justify;"><b>Database structure</b></p>
   <p style="text-align: justify;"><img src="img/InvFESTdb-model.png" alt="InvFESTdb-model.png" width="1060"><br/>&nbsp;</p>

  </div>

  <br />
  <div id="foot"><?php include('php/footer.php');?>
  </div>

 </div><!--end Wrapper-->
</body>
</html>
