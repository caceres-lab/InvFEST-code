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
   <p style="text-align: justify;">We welcome your feedback and suggestions about the InvFEST database.<br/> &nbsp;
    </p>
   <p style="text-align: justify;"><b>Mailing Address</b></p>
   <p style="text-align: justify;">
Comparative and Functional Genomics group <br/>
Institut de Biotecnologia i de Biomedicina <br/>
Universitat Autònoma de Barcelona <br/>
08193 Bellaterra, Barcelona, Spain <br/> <br/>

Phone: +34 935868726 <br/>
Fax: +34 935812011 <br/>
&nbsp; 
   </p>
   <p style="text-align: justify;"><b>Email</b></p>
   <p style="text-align: justify;">
Email us at <a href="mailto:invfestdb@uab.cat?subject=Questions/Comments">invfestdb@uab.cat</a>
   </p>
  </div>

  <br />
  <div id="foot"><?php include('php/footer.php');?>
  </div>

 </div><!--end Wrapper-->
</body>
</html>
