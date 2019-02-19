<?php
	$outputpath = $_POST['pathoutput'];
	$file = $outputpath;
	#echo "$file\n";

	if(!file_exists($file)){header('Location: ../search.php');die;}

	    $type = filetype($file);
	    
	    // Get a date and timestamp
	    $today = date("F j, Y, g:i a");
	    $time = time();
	    
	    // Send file headers
	    header("Content-type: $type");
	    $filenamearray = explode("/", $file);$filename = end($filenamearray);
	    header("Content-Disposition: attachment;filename=".$filename);
	    header("Content-Transfer-Encoding: binary"); 
	    header('Pragma: no-cache'); 
	    header('Expires: 0');
	    
	    // Send the file contents.
	    set_time_limit(0); 
	    readfile($file);
	    
	    //Delete file
	    unlink('$outputpath');
		if (!unlink($outputpath)){echo ("Error deleting $outputpath");
	}
?>
