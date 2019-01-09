<?php
/******************************************************************************
	SUBMISSIONS.PHP

	Data submission webpage ("Data submissions" menu from the website)
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
 		<p style="text-align: justify;">We welcome your participation to add new predictions, validations, or any valuable information regarding human polymorphic inversions that is not currently included in the InvFEST database. <br/><br/>
 		Please <b>email us at 
 		<a href="mailto:invfestdb@uab.cat?subject=Submissions">invfestdb@uab.cat</a></b>
 		 to submit your data.</p>
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
