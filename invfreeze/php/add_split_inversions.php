<?php
/******************************************************************************
	ADD_SPLIT_INVERSIONS.PHP

	Splits one inversion into two or more new inversions.
	It is executed when splitting one inversion into two or more new inversions by the "Split current inversion in two others" subsection from the "Advanced inversion edition" section of the current inversion's report webpage.
	It then adds the new resulting inversions from the split to the database.
	After adding the generated new inversions to the database, it executes automatically run_breakseq.sh for the inversions' BreakSeq annotation
*******************************************************************************/
?>


<?php
    session_start(); 
    include('security_layer.php');
?>

<!DOCTYPE html>
<html>

    <?php
    $inv_id=$_POST["inv_id"];
    $chr=$_POST["chr"];
    $research_name=$_POST["research_name"];
    $pinv1_arr=$_POST["pinv1"];
    $pinv2_arr=$_POST["pinv2"];
    $vinv1_arr=$_POST["vinv1"];
    $vinv2_arr=$_POST["vinv2"];

    $pinv1 = implode(",", $pinv1_arr);
    $pinv2 = implode(",", $pinv2_arr);
    $vinv1 = implode(",", $vinv1_arr);
    $vinv2 = implode(",", $vinv2_arr);
    $user_id = $_SESSION["userID"];
    if($pinv1 == null){$pinv1 = "NA";}
    if($pinv2 == null){$pinv2 = "NA";}
    if($vinv1 == null){$vinv1 = "NA";}
    if($vinv2 == null){$vinv2 = "NA";}
    $new_status1=$_POST["status1"];
    $new_status2=$_POST["status2"];
    if($new_status1 == null){$new_status1 == "NA";}
    if($new_status2 == null){$new_status2 == "NA";}

    $new_ids = array();
    include('db_conexion.php');

    //Split
    $query="CALL split_inv('$inv_id','$pinv1','$pinv2','$vinv1','$vinv2','$new_status1','$new_status2','$user_id');";
    print $query.'<br >';
    $result = mysql_query($query) or die("Query fail: " . mysql_error());
    if($result){print "Split done succesfully".'<br >';}
    mysql_free_result($result);
    //mysql_close($con);

    //select new splited inversions
    //include('db_conexion.php');
    $query1="select id, name from inversions WHERE status != 'Obsolete' ORDER BY name DESC LIMIT 2;";
    $result1 = mysql_query($query1) or die("Query fail: " . mysql_error());
    while($row1 = mysql_fetch_array($result1)) {
        /*foreach ($row1 as $value){echo $value.'<br>';}*/
        echo "<br /><input type='submit' value=\"Go to the new inversion " .$row1['name']."\" name='gsubmit'  onclick=\"window.open('../report.php?q=".$row1['id']."')\" />";
        array_push($new_ids, $row1['id']);
    }
    mysql_free_result($result1);

    //Update definition method new splited inversions
    $id_new_inv1 = $new_ids[0];
    $id_new_inv2 = $new_ids[1];

    $query_bp_method = "update breakpoints set definition_method = 'default informatic definition' where inv_id = '$id_new_inv1';";
    $result_inv1 = mysql_query($query_bp_method) or die("Query fail: " . mysql_error());
    mysql_free_result($result_inv1);
    $query_bp_method = "update breakpoints set definition_method = 'default informatic definition' where inv_id = '$id_new_inv2';";
    $result_inv2 = mysql_query($query_bp_method) or die("Query fail: " . mysql_error());
    mysql_free_result($result_inv2);



    mysql_close($con);


    //Breakseq gff input file generation
    //----------------------------------------------------------------------------
    include('db_conexion.php');
    exec("kill $(ps aux | grep 'breakseq-1.3' | awk '{print $2}') > /dev/null 2>&1");
    $gff_file = fopen("/home/invfest/BPSeq/breakseq_annotated_gff/input.gff", "w") or die("Unable to create gff file!");
    
    //Select inversions
    $query2="SELECT i.name, b.id, b.chr, b.bp1_start, b.bp1_end, b.bp2_start, b.bp2_end, i.status, b.GC FROM inversions i, breakpoints b  WHERE i.id=b.inv_id AND b.GC is null AND b.chr NOT IN ('chrM');";
    #print "$sql_bp".'<br/>';
    $result_bp = mysql_query($query2) or die("Query fail: " . mysql_error());
    while($bprow = mysql_fetch_array($result_bp)) {
	    $midpoint_BP1=round(($bprow['bp1_end']+$bprow['bp1_start'])/2);
	    $bp2_end =$bprow['bp2_end'];
	    $bp2_start =$bprow['bp2_start'];
	    #print "$bp2_end\t$bp2_start\n";
    	    $midpoint_BP2=round(($bp2_start+$bp2_end)/2);
	    #print "$midpoint_BP2\n";
    	    $chr=$bprow['chr'];
	    $name=$bprow['name'];
	    $id_bp= $bprow['id'];
	    //$gene_id= $bprow['gene_id'];
        $inverion_gff_line= "$chr\t$name\tInversion\t$midpoint_BP1\t$midpoint_BP2\t.\t.\t.\n";
    
        fwrite($gff_file, $inverion_gff_line);
    }


    fclose($gff_file);

    //BreakSeq execution
    //---------------------------------------------------------------------------
    exec("nohup ./run_breakseq.sh > /dev/null 2>&1 &");
    print "<br ><br >BreakSeq is now performing the breakpoints annotation, results will be automatically updated on the inversion report page in a few minutes.".'<br >';
    $query3 = "UPDATE inversions SET status = '$new_status1' WHERE id=$new_ids[1];";
	    $result3 = mysql_query($query3) or die("Query fail: " . mysql_error());
	    print $query3.'<br>';
	    $row3 = mysql_fetch_array($result3);
    $query4 = "UPDATE inversions SET status = '$new_status2' WHERE id=$new_ids[0];";
	    $result4 = mysql_query($query4) or die("Query fail: " . mysql_error());
		    print $query4.'<br>';
	    $row4 = mysql_fetch_array($result4);
?>
