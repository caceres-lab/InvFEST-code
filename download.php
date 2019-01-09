<?php
/******************************************************************************
  DOWNLOAD.PHP

  List all downloadable files of InvFEST, in addition to information for the direct access to the database server ("Downloads" menu from the website)
*******************************************************************************/
?>


<?php
  // Session start for the PHP
  session_start();
?>

<!DOCTYPE html>
<html>

<?php
  
  // Select specific data into variables which are retrieved in other php pages
  include_once('php/select_index.php');

  // Includes HTML <head> and other settings for the page
  include_once('php/structure_page.php');

?>

<?php 
  echo $creator;
  
  $head .= "</head>";  // Head end
  echo $head;               // 'Print' head code
?>


<!-- **************************************************************************** -->
<!-- BODY -->
<!-- **************************************************************************** -->
<body>

<!-- **************************************************************************** -->
<!-- PAGE MENU: Print the header banner of InvFEST -->
<!-- **************************************************************************** --> 
<?php include('php/echo_menu.php'); ?>


<!-- **************************************************************************** -->
<!-- DIVISIONS -->
<!-- **************************************************************************** -->
    <br/>

    <div id="welcome" class="section-content">

        <p style="text-align: justify;">
          This page lists all downloadable files of InvFEST, and instructions for direct access to our database server: <br/>&nbsp;
        </p>
        <p style="text-align: justify;"><b>
            The complete InvFEST database can be downloaded as a compressed SQL file
        </b></p>
        <p style="text-align: justify;">
            Please download the file from
        <a href="sql/InvFESTdb.sql.gz">
            InvFESTdb.sql.gz</a><br/>&nbsp;
        </p>
        <p style="text-align: justify;"><b>
            Also available is a file with the genotypes for all individuals and inversions in InvFEST
        </b></p>
        <p style="text-align: justify;">
            Please download the file from 
            <a href="sql/genotypesTable.csv">genotypesTable.csv</a><br/>&nbsp;
        </p>
        <p style="text-align: justify;"><b>
            Direct access to the MySQL database at the InvFEST database server
        </b></p>
        <p style="text-align: justify;">
            Server: invfestdb.uab.cat <br/>
            User: invfestdb-user <br/>
            Database: INVFEST-DB-PUBLIC <br/>	
            Password: invfestdb-user <br/>&nbsp;
        </p>
        <p style="text-align: justify;"><b>
            Database structure
        </b></p>
        <p style="text-align: justify;">
            <img src="img/InvFESTdb-model.png" alt="InvFESTdb-model.png" width="1060"><br/>&nbsp;
        </p>

    </div>

    <br />


<!-- **************************************************************************** -->
<!-- FOOT OF THE PAGE -->
<!-- **************************************************************************** -->
  <div id="foot">
    <?php include('php/footer.php'); ?>
  </div>

</div> <!-- Closes the Wrapper's divison opened at 'echo_menu.php' -->

</body>
</html>
