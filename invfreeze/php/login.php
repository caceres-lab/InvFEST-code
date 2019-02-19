<?php
/******************************************************************************
	LOGIN.PHP

	Login form
*******************************************************************************/
?>


<!DOCTYPE html>
<html>

    <head>
	    <title>Authentication</title>
	    
        <style>
            @import url('http://fonts.googleapis.com/css?family=Ubuntu:regular,italic,bold,bolditalic');
            body.classBody_login {
                text-align: center;
                vertical-align: middle;
                font-family: "Ubuntu";
                font-size: 11pt;
            } table.classTable_login {
                margin: auto;
                text-align: center;
                border-spacing: 2px;
                border-collapse: separate;
                border: 0px;
            } table.classTable_login td {
                vertical-align: top;
                border: 0;
                padding: 2px;
                text-align: center;
            } table.classTable_login td.classTd_right {
                text-align: right;
            } table.classTable_login td.classTd_left {
                text-align: left;
            } p.classP {
                margin: 0;
                padding: 0;
                display: inline-block;
            } .class_italic {
                font-size: 10pt;
                font-style: italic;
                color: black;
            } .class_error {
                font-size: 10pt;
                font-style: italic;
                color: red;
            }
        </style>
    </head>

    <body class="classBody_login">

	    <!-- Show the login control in a pop-up form -->
	    <form action="logincontrol.php" method="POST">
		
		<!-- Get the origin page -->
	    <?php if ($_GET["origin"]=="index") { ?>
			        <input type="hidden" id="origin" name="origin" value="index" />
	    <?php } elseif ($_GET["origin"]=="report") { ?>
			        <input type="hidden" id="origin" name="origin" value="report" />
	    <?php } else { ?>
			        <input type="hidden" id="origin" name="origin" value="" />
	    <?php } ?>
                
        <!-- Save the origin inversion -->
	    <?php if ($_GET["q"] != "") {
                    $q=$_GET["q"];
        ?>
			        <input type="hidden" id="q" name="q" value="<?php echo $q ?>" />
	    <?php } ?>

		    <table class="classTable_login">
			    <tr>
				    <td colspan="2"
				    <?php if ($_GET["errorusuario"]=="si") { ?>
						    class="class_error">
							    The user/password is not correct
				    <?php } else { ?>
						    class="class_italic">
                                Enter your access key
				    <?php } ?>
				    </td>
			    </tr>
			    <tr>
				    <td class = "classTd_right">
					    USER:
				    </td>
				    <td class = "classTd_left">
					    <input type="Text" name="usuario" size="8" maxlength="50">
				    </td>
			    </tr>
			    <tr>
				    <td class = "classTd_right">
					    PASSWD:
				    </td>
				    <td class = "classTd_left">
					    <input type="password" name="contrasena" size="8" maxlength="50">
				    </td>
			    </tr>
			    <tr>
				    <td colspan ="2">
					    <input type="Submit" value="SUBMIT">
				    </td>
			    </tr>

		    </table>
	    </form>

    </body>
</html>