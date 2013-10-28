<?php
$id=$_GET["id"];

include_once('db_conexion.php');

// Historial de breakpoints (E)
$sql_bp="SELECT b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, b.definition_method, b.SD_relation
	FROM  breakpoints b WHERE b.inv_id ='$id'
	ORDER BY FIELD (b.definition_method, 'HuRef_HG18 cross-comparison', 'informatic delimited'), b.date DESC ;";
$result_bp=mysql_query($sql_bp);
while($bprow = mysql_fetch_array($result_bp)){
	$bp_history.="<tr>
				<td>".$bprow['bp1_start']."</td>
				<td>".$bprow['bp1_end']."</td>
				<td>".$bprow['bp2_start']."</td>
				<td>".$bprow['bp2_end']."</td>
				<td>".$bprow['definition_method']."</td>
				<td>".$bprow['SD_relation']."</td>
			</tr>";

}



echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<link rel="stylesheet" type="text/css" href="../css/style.css" />
</head>
<body>
			<div id="breakpoints_history" class="report-section" >
				<h3>Breakpoints History:  <!---------- BREAKPOINTS HISTORY ------>
				</h3>
				<div class="grlsection-content">
					<table id="validation_table">
					<thead>
					<tr>
						<td>BP1 start</td>	<td>BP1 end</td>
						<td>BP2 start</td>	<td>BP2 end</td>
						<td>Definition method</td>	<td>Segmental duplication</td>
					</tr>
					</thead>
					<tbody>
						'.$bp_history.'
					</tbody>
					</table>
				</div>	
				</div>	
			</div>
			<br />


</body></html>';

?>
