<?php
include_once('db_conexion.php');

// Retrieve the query and generate the URL.
$refseq = $_GET["refseq"];
$symbol = $_GET["symbol"];

$sql_ge="SELECT idHsRefSeqGenes, refseq, symbol, chr, strand, txStart, txEnd 
	FROM HsRefSeqGenes WHERE refseq = '$refseq' OR symbol = '$symbol';";

$result_ge=mysql_query($sql_ge);

while($thisrow = mysql_fetch_array($result_ge)){

echo "
<input type='radio' name='id_gene' id='id_gene' value='".$thisrow['idHsRefSeqGenes'].";".$thisrow['symbol']."'>&nbsp;
".$thisrow['refseq']."&nbsp;".$thisrow['symbol']."&nbsp;".$thisrow['chr']."(".$thisrow['strand']."):".$thisrow['txStart']."-".$thisrow['txEnd']."<br/>";

}

echo '
<input type="submit" value="Add gene" />
<input type="reset" value="Clear" /><br><br>';

?>

