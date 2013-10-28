<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">


<head>
	<title>Autentification</title>
	<link rel="stylesheet" type="text/css" href="../css/style.css" />
</head>

<body>
<form action="logincontrol.php" method="POST">
<!--guardamos la pagina de origen -->
<?if ($_GET["origin"]=="index"){?>
	<input type="hidden" id="origin" name="origin" value="index" />

<?} elseif ($_GET["origin"]=="report"){?>
	<input type="hidden" id="origin" name="origin" value="report" />
<?} else {?>
	<input type="hidden" id="origin" name="origin" value="" />
<?}?>

<!--guardamos la inversion de origen-->
<?
if ($_GET["q"] != ""){$q=$_GET["q"];?>
	<input type="hidden" id="q" name="q" value="<?echo $q?>" />
<?}?>

<table align="center" width="225" cellspacing="2" cellpadding="2" border="0">
	<tr>
		<td colspan="2" align="center"
			<?if ($_GET["errorusuario"]=="si"){?>
			bgcolor=red><span style="color:ffffff"><b>The user/password is not correct</b></span>
			<?}else{?>
			bgcolor=#cccccc>Enter your access key
		<?}?></td>
	</tr>
	<tr>
		<td align="right">
			USER:
		</td>
		<td>
			<input type="Text" name="usuario" size="8" maxlength="50">
		</td>
	</tr>
	<tr>
		<td align="right">
			PASSWD:
		</td>
		<td>
			<input type="password" name="contrasena" size="8" maxlength="50">
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<input type="Submit" value="SUBMIT">
		</td>
	</tr>
</table>
</form>
</body>
</html>

