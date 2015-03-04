#!/usr/bin/php5
<?php

$user = "invfest";
$password = "pwdInvFEST";
$db = "INVFEST-DB-dev";

$con = mysql_connect('localhost', $user, $password);
if (!$con) { die('Could not connect: ' . mysql_error()); }
mysql_select_db($db, $con);

//Add BreakSeq annotation to the DB
//----------------------------------------------------------------------------
//BreakSeq output parsing
$BS_output = @fopen("/home/shareddata/Bioinformatics/BPSeq/breakseq_annotated_gff/Results/input.gff", "r");

$array = array();
if ($BS_output) {
    
    while (($line = fgets($BS_output, 4096)) !== false) {
    	$cols = explode("\t", $line);
		$breakseq_annot= explode(";", $cols[8]);
		foreach ($breakseq_annot as $key) {
			preg_match('/"([^"]+)"/', $key, $value);
			$array[] = $value[1];
		}
		$name= $cols[1]; 
		$flex=($array[0]);$GC=($array[1]);$Gene=($array[2]);$Helix=($array[3]);$Mech=($array[4]);
		//print $id_bp.'<br/>'.$gene_id.'<br/>'.$id.'<br/>'.$flex.'<br/>'.$GC.'<br/>'.$Gene.'<br/>'.$Helix.'<br/>'.$Mech.'<br/>'.'<br/>';
		//Add results to breakpoints table
		$sql_query="UPDATE breakpoints SET Flexibility = $flex, GC = $GC, Stability = $Helix, Mech = '$Mech' WHERE inv_id = (SELECT id from inversions where name = \"$name\");";
		$result_query=mysql_query($sql_query);
		if (!$result_query) {
    		print('Invalid query: ' . mysql_error().'<br/>');
		}
		//Add results to Bseq_genes table
		/*$annotated_genes= explode(",", $Gene);
		foreach ($annotated_genes as $gene) {
			$sql_query2="REPLACE into BSeq_genes(Breakpoint_id, Gene_id_genomic_effect, BSeq_gene) values($id_bp,$gene_id,'$gene');";
			$result_query2=mysql_query($sql_query2);
			if (!$result_query2) {
    			print('Invalid query nova: ' . mysql_error().'<br/>');
			}
		}*/

		$array = null;	
	}

	if (!feof($BS_output)) {echo "Error: unexpected fgets() fail\n";}
 
 	fclose($BS_output);
}
//exec("nohup rm -R /home/shareddata/Bioinformatics/BPSeq/breakseq_annotated_gff > /dev/null 2>&1 &");
?>
