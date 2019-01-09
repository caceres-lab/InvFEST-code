<?php
/******************************************************************************
	_SPLIT_INVERSIONS.PHP

	Apparently it is not used anymore. Replaced by add_split_inversions.php
*******************************************************************************/
?>


<?php include('security_layer.php');?>

<!DOCTYPE html>
<html>

    <head>
	    <title>New Validation</title>
	    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
	    <link rel="stylesheet" type="text/css" href="../css/style.css" />
	    <link rel="stylesheet" type="text/css" href="../css/report.css" />

    </head>

    <?php include_once('select_split_inv.php');?> 

    <body>
        <?php $id=$_GET['q'];?>
        <h3>Split the inversion INV_<?echo $id?> into two new inversions</h3>
        <form name="split_validation" method="post" action="add_split_inversions.php">
	        <table>
		        <tr>
			        <td>Predictions</td>
			        <td>New Inversion1</td>
			        <td>New Inversion 2</td>
		        </tr>
		        <?php
                    if ($predictions == "" || $predictions == NULL) {
                        echo "<tr><td colspan=\"3\">No predictions found</td></tr>";
		            } else { echo $predictions; }
		        ?>
		        <tr></tr>
		        <tr>
			        <td>Validations</td>
			        <td>New Inversion 1</td>
			        <td>New Inversion 2</td>
		        <?php
                    if ($validations == "" || $validations == NULL) {
                        echo "<tr><td colspan=\"3\">No validations found</td></tr>";
                    } else { echo $validations; }
		        ?>
	        </table>
	        <input type="submit" value="Split" />
	        <input type="hidden" name="inv_id" value="<?php echo $id ?>" />
	        <input type="reset" value="Clear" /><br><br>
        </form>
    </body>
</html>
