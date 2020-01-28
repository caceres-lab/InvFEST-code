<?php

 session_start();
	#Save query information
	$inv_id = $_POST['inversionid'];
	$type = $_POST['type'];
	$subtype = $_POST['TE_subtype'];
	$position1 = $_POST['position1'];
	$position2 = $_POST['position2'];
	$identity = $_POST['identity'];
	$relativeorientation = $_POST['relativeorientation'];

	if (!preg_match('/chr.+\:[0-9]+\-[0-9]+/', $position1)){
		echo "Invalid position 1 coordinates format, should be entered as: chrn:start-end\n";
		die;
	}
	if (!preg_match('/chr.+\:[0-9]+\-[0-9]+/', $position2)){
		echo "Invalid position 2 coordinates format, should be entered as: chrn:start-end\n";
		die;
	}

	#Position1
	$position1 = explode(":",$position1); 
	$chr1 = $position1[0];
	$position11 = $position1[1];
	$position111 = explode("-",$position11);
	$position1s = $position111[0];
	$position1e = $position111[1];

	#Position2
	$position2 = explode(":",$position2); 
	$chr2 = $position2[0];
	$position22 = $position2[1];
	$position222 = explode("-",$position22);
	$position2s = $position222[0];
	$position2e = $position222[1];

	#Comprovaciones
	if (($position1s == '') or ($position1e == '') or ($position2s == '') or ($position2e == '')){
		echo "Fill all position coordinates please";
		die;
	}
	if (($identity == '') or ($relativeorientation == '')) {
		echo "Idenity or orientation field is empty";
		die;
	}
	if(($position1e >= $position2s) or ($position1s >= $position2s)){
		echo "Invalid position coordinates, position 2 start is equal or bigger than position 1 end";
		die;
	}

	$size1 = ($position1e-$position1s)+1;
	$size2 = ($position2e-$position2s)+1;

include('db_conexion.php');

	#TE
	if($type == "TE"){
		// $query2= "CALL  `INVFEST-DB`.`add_TE`('$inv_id', '$subtype', '$chr1', '$position1s', '$position1e', '$size1', '$chr2', '$position2s', '$position2e', '$size2', '$identity', '$relativeorientation');";
		$query2="INSERT INTO TE_in_BP (inv_id, subtype, chrom,chromStart,chromEnd,size,otherChrom,otherStart,otherEnd,otherSize,fracMatch, strand) VALUES
		('$inv_id', '$subtype', '$chr1', '$position1s', '$position1e', '$size1', '$chr2', '$position2s', '$position2e', '$size2', '$identity', '$relativeorientation');";

		$result2 = mysql_query($query2);
		if (!$result2) {die('Invalid query: ' . mysql_error());}
		echo"TE added successfully!";
	}

	#IR
	if($type == "IR"){
		// $query3= "CALL  `INVFEST-DB`.`add_IR`('$inv_id', '$chr1', '$position1s', '$position1e', '$size1', '$chr2', '$position2s', '$position2e', '$size2', '$identity', '$relativeorientation');";
		$query3="INSERT INTO IR_in_BP (`inv_id`, `chrom`, `chromStart`,`chromEnd`,`size`,`otherChrom`,`otherStart`,`otherEnd`,`otherSize`,`fracMatch`, `strand`) VALUES
		('$inv_id', '$chr1', '$position1s', '$position1e', '$size1', '$chr2', '$position2s', '$position2e', '$size2', '$identity', '$relativeorientation');";

		$result3 = mysql_query($query3);
		if (!$result3){
			die('Invalid query: ' . mysql_error());
		}
		echo"IR added successfully!";
	}

?>
