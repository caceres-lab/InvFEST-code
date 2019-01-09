<?php
/******************************************************************************
	AJAXUPDATE_INFO.PHP

	Allows the edit/update options for both origin and comments sections of the report webpage
*******************************************************************************/
    session_start();
    include('security_layer.php');


    $origin=$_GET["or"]; //comments -> tabla predictions.comments || bp_origin -> tabla inversions.origin
    $text=$_GET['up']; //Texto a introducir
    $id=$_GET['p']; //ID (de la predicción cuando es comment, es necesario el de la inversion?)????????????????????????

    if ($origin == 'comments_pred') {
	    $table='predictions';
	    $field='comments';
    } else if ($origin=='inv_bp_origin') {
	    $table='inversions';
	    $field='origin';
    } else if ($origin == 'comments_inv') {
	    $table='inversions';
	    $field='comment';
    } else if ($origin == 'comments_bp') {
	    $table='breakpoints';
	    $field='comments';
    } else if ($origin == 'comments_eh') {
	    $table='inversions';
	    $field='comments_eh';
    }

    include_once('db_conexion.php');

    $f="CALL update_info('$id', '$origin', '$text', '".$_SESSION["userID"]."')";

    mysql_query("CALL update_info('$id', '$origin', '$text', '".$_SESSION["userID"]."')");
    /*
    `update_info`
	    IN key_val VARCHAR(255)
	    IN origin_val VARCHAR(255)
	    IN info_val VARCHAR(255)
	    IN user_id_val INT)
    */

    if($origin == 'comments_pred') {
	    
        //ID lo descompongo en r_id y r_name
	    $ids = preg_split('/;/', $id);
	    $sql="SELECT accuracy, comments 
		    FROM predictions 
		    WHERE research_id='".$ids[0]."' and research_name='".$ids[1]."';";
	    $result=mysql_query($sql);
	    $data= mysql_fetch_array($result);
        
        //$data['comments']=$text;
        //$data['comments']=$f;
	    echo "<div  id='acc".$id."'>";
	    if ($data['accuracy'] != ''||$data['accuracy'] != NULL) {
		    echo $data['accuracy']."<br>";
	    }
	    echo "</div><div  id='comments_pred".$id."'>".$data['comments']."</div>";

	    if ($_SESSION["autentificado"]=='SI') {
		    echo "<input type='button' class='right' value='Update' onclick=\"updateTD('comments_pred','".$id."')\" />";
	    }

    } else if ($origin == 'inv_bp_origin') { 
	    $sql="SELECT origin 
		    FROM inversions 
		    WHERE id=$id;";
	    $result=mysql_query($sql);
	    $data= mysql_fetch_array($result);
        //$data['origin']=$text;

	    //echo "<div  id='inv_bp_origin".$id."'>".$data['origin']."</div><input type='button' value='Edit' class='right' onclick=\"updateTD('inv_bp_origin','$id')\">";
	    echo $data['origin'];
	
    } else if ($origin == 'comments_inv') { 
	    $sql="SELECT comment 
		    FROM inversions 
		    WHERE id=$id;";
	    $result=mysql_query($sql);
	    $data= mysql_fetch_array($result);
        //$data['origin']=$text;

	    //echo "<div  id='inv_bp_origin".$id."'>".$data['origin']."</div><input type='button' value='Edit' class='right' onclick=\"updateTD('inv_bp_origin','$id')\">";
	    echo $data['comment'];
    } else if ($origin == 'comments_bp') { 
	    $sql="SELECT comments 
		    FROM breakpoints 
		    WHERE inv_id=$id;";
	    $result=mysql_query($sql);
	    $data= mysql_fetch_array($result);
        //$data['origin']=$text;

	    //echo "<div  id='inv_bp_origin".$id."'>".$data['origin']."</div><input type='button' value='Edit' class='right' onclick=\"updateTD('inv_bp_origin','$id')\">";
	    echo $data['comments'];
    } else if ($origin == 'comments_eh') { 
	    $sql="SELECT comments_eh 
		    FROM inversions 
		    WHERE id=$id;";
	    $result=mysql_query($sql);
	    $data= mysql_fetch_array($result);
        //$data['origin']=$text;

	    //echo "<div  id='inv_bp_origin".$id."'>".$data['origin']."</div><input type='button' value='Edit' class='right' onclick=\"updateTD('inv_bp_origin','$id')\">";
	    echo $data['comments_eh'];
    }
    mysql_close($con);

?>


