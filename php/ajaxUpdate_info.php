<?php
/******************************************************************************
	AJAXUPDATE_INFO.PHP

	Allows the edit/update options for both origin and comments sections of the report webpage
*******************************************************************************/
	session_start(); //Inicio la sesión

    include('security_layer.php');
    include_once('db_conexion.php');


    $origin=$_GET["or"]; //comments -> tabla predictions.comments || bp_origin -> tabla inversions.origin
    $text=$_GET['up'];//texto a introducir
    $id=$_GET['p'];// id (de la prediccion cuando es comment, es necesario el de la inversion?)????????????????????????
    $current_user = $_SESSION["user"];

    #IF NOT EXISTS(SELECT inv_id FROM comments WHERE inv_id=939) THEN INSERT INTO comments (inv_id, user,date,inversion_com,predict_com,val_com,bp_com,evolutionary_history_com) select $id, '$current_user',now(),inversion_com,predict_com,val_com,'$text', evolutionary_history_com from comments where inv_id = $id ORDER BY inv_id DESC LIMIT 1

    #IF EXISTS(SELECT inv_id FROM comments WHERE inv_id=$id)THEN INSERT INTO comments (inv_id, user,date,inversion_com,predict_com,val_com,bp_com,evolutionary_history_com) select $id, '$current_user',now(),inversion_com,predict_com,val_com,'$text', evolutionary_history_com from comments where inv_id = $id ORDER BY inv_id DESC LIMIT 1
    #ELSE INSERT INTO comments (inv_id, user,date,bp_com) VALUES ($id, '$current_user',now(),'$text');

    #INSERT INTO table_name (column1,column2,column3,...) VALUES (value1,value2,value3,...);


    #Prediction comments
    if ($origin == 'comments_pred') {
	    $result = mysql_query("SELECT inv_id FROM comments WHERE inv_id = '$id';");
	    if( mysql_num_rows($result) > 0) {
		    $insert_comment = "insert into comments (inv_id, user,date,inversion_com,predict_com,val_com,bp_com,evolutionary_history_com) select $id, '$current_user',now(),inversion_com,\"$text\",val_com,bp_com, evolutionary_history_com from comments where inv_id = $id ORDER BY inv_id DESC LIMIT 1;";
    		    $result= mysql_query("$insert_comment");
	    } else {
		    $insert_comment = "INSERT INTO comments (inv_id, user,date,predict_com) VALUES ($id, '$current_user',now(),\"$text\");";
    		    $result= mysql_query("$insert_comment");
	    }
	    #$result=mysql_query($insert_comment);
	    $table='predictions';
	    $field='comments';
    } 

    #Inversion bp origin comments
    if ($origin=='inv_bp_origin') {
	    $table='inversions';
	    $field='origin';
    }
 
    #Inversion comments
    if ($origin == 'comments_inv') {
	    $result = mysql_query("SELECT inv_id FROM comments WHERE inv_id = '$id';");
	    if( mysql_num_rows($result) > 0) {
		    $insert_comment = "insert into comments (inv_id, user, date, inversion_com, predict_com, val_com, bp_com, evolutionary_history_com) select $id, '$current_user',now(),\"$text\",predict_com,val_com,bp_com, evolutionary_history_com from comments where inv_id = $id ORDER BY inv_id DESC LIMIT 1;";
    		    $result= mysql_query("$insert_comment");
	    } else {
		    $insert_comment = "INSERT INTO comments (inv_id, user,date,inversion_com) VALUES ($id, '$current_user', now(), \"$text\");";
    		    $result= mysql_query("$insert_comment");
	    }
	    $table='inversions';
	    $field='comment';
    } 

    #Breakpoints comments
    if ($origin == 'comments_bp') {
	    $result = mysql_query("SELECT inv_id FROM comments WHERE inv_id = '$id';");
	    if( mysql_num_rows($result) > 0) {
		    $insert_comment = "insert into comments (inv_id, user,date,inversion_com,predict_com,val_com,bp_com,evolutionary_history_com) select $id, '$current_user',now(),inversion_com,predict_com,val_com,\"$text\", evolutionary_history_com from comments where inv_id = $id ORDER BY comment_id DESC LIMIT 1;";
    		    $result= mysql_query("$insert_comment");
	    } else {
		    $insert_comment = "INSERT INTO comments (inv_id, user,date,bp_com) VALUES ($id, '$current_user', now(), \"$text\");";
    		    $result= mysql_query("$insert_comment");
	    }
	    $table='breakpoints';
	    $field='comments';
    } 

    #Evolutionary history comments
    if ($origin == 'comments_eh') {
	    $result = mysql_query("SELECT inv_id FROM comments WHERE inv_id = '$id';");
	    if( mysql_num_rows($result) > 0) {
		    $insert_comment = "insert into comments (inv_id, user,date,inversion_com,predict_com,val_com,bp_com,evolutionary_history_com) select $id, '$current_user',now(),inversion_com,predict_com,val_com,bp_com, \"$text\" from comments where inv_id = $id ORDER BY comment_id DESC LIMIT 1;";
    		    $result= mysql_query("$insert_comment");
	    } else {
		    $insert_comment = "INSERT INTO comments (inv_id, user,date,evolutionary_history_com) VALUES ($id, '$current_user', now(), \"$text\");";
    		    $result= mysql_query("$insert_comment");
	    }
	    $table='inversions';
	    $field='comments_eh';
    }

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


