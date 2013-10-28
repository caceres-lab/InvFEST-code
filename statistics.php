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
   <p>Here some statistics...</p>
  </div>

  <br />
  <div id="foot"><?php include('php/footer.php');?>
  </div>

 </div><!--end Wrapper-->
</body>
</html>
