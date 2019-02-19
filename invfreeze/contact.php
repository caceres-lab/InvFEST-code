<?php
/******************************************************************************
    CONTACT.PHP

    InvFEST public contact information ("Contact" menu from the website)
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
            We welcome your feedback and suggestions about the InvFEST database.<br/>
            &nbsp;
        </p>
        <p style="text-align: justify;">
            <b>Mailing Address</b>
        </p>
        <p style="text-align: justify;">
            Comparative and Functional Genomics group <br/>
            Institut de Biotecnologia i de Biomedicina <br/>
            Universitat Autònoma de Barcelona <br/>
            08193 Bellaterra, Barcelona, Spain <br/> <br/>

            Phone: +34 935868726 <br/>
            Fax: +34 935812011 <br/>
            &nbsp; 
        </p>
        <p style="text-align: justify;">
            <b>Email</b>
        </p>
        <p style="text-align: justify;">
            Email us at 
            <a href="mailto:invfestdb@uab.cat?subject=Questions/Comments">invfestdb@uab.cat</a>
        </p>
    </div>

    <br/>


<!-- **************************************************************************** -->
<!-- FOOT OF THE PAGE -->
<!-- **************************************************************************** -->
    <div id="foot">
        <?php include('php/footer.php'); ?>
    </div>

</div> <!-- Closes the Wrapper's divison opened at 'echo_menu.php' -->

</body>
</html>
