<?php
/******************************************************************************
	NEW_STUDY.PHP

	"New study" form page which is open when clicking on the "add a new study" button.
	It sends the specified information to php/add_study.php
*******************************************************************************/

    include('security_layer.php');
?>


<!DOCTYPE html>
<html>

    <?php include_once('select_new_study.php');?> 
    <head>

        <!-- **************************************************************************** -->
        <!-- SITE SETTINGS -->
        <!-- **************************************************************************** -->
		<title>New Study</title>
	    <meta http-equiv="content-type" content="text/html;charset=utf-8" />

        <!-- **************************************************************************** -->
        <!-- STYLES -->
        <!-- **************************************************************************** -->
	    <link rel="stylesheet" type="text/css" href="../css/style.css" />
	    <link rel="stylesheet" type="text/css" href="../css/report.css" />

        <!-- **************************************************************************** -->
        <!-- SCRIPTS -->
        <!-- **************************************************************************** -->
	    <!-- Include JQuery from a CDN (or locally if the site is offline) -->
		<script src="https://code.jquery.com/jquery-1.4.2.min.js"></script>
		<script>
			$(window).jQuery || $(document).write('<script src="js/jquery-1.4.2.min.js" async><\/script>')
		</script>

        
	    <script>
	
		    var numericExpression = /^[0-9]+$/;
		    var numericExpression2 = /[1-9]+/;

		    function validate() {

			    var origin=document.getElementById("origin").value;
			    if (origin=='prediction') {
				    var resolution=document.getElementById("resolution");
				    //var resolutionValue = new Number(document.getElementById("resolution").value);
				    if (resolution.value==""){alert("Please fill in the Resolution field");return false;}
				    if (resolution.value.match(numericExpression) && resolution.value.match(numericExpression2) ) {}
				    else {alert ("Resolution must be a number"); resolution.focus(); return false;}
			    }
		
			    var study=document.getElementById("study");
			    if (study.value=="") {
				    alert ("Please fill in the Study field");
				    study.focus();
				    return false;
			    }

			    var method=document.getElementById("method");
			    if (method.value=="") {
				    alert ("Please select a Method");
				    method.focus();
				    return false;
			    }

			    var description=document.getElementById("description");
			    if (description.value=="" || description.value == null) {
				    alert ("Please fill in the Description field");
				    description.focus();
				    return false;
			    }

			    return true;
		    }

            function displayArticleInfo() {

	            var str=document.getElementById("pubmedID").value;

                // If the str is empty, set queryResult and return.
	            if(str == "") {
		            //document.getElementById("queryResult").innerHTML="Please write one PubMed ID.<br >";
		            alert("Please write one PubMed ID.");
		            return;
	            }
	            if (str.match(numericExpression) && str.match(numericExpression2) ) {}
	            else { alert ("PubMed ID must be a number"); document.getElementById("pubmedID").focus(); return false; }

                // Display "The result is comming."
                //document.getElementById("queryResult").innerHTML = "The result is comming.<br >";

                // Create XMLHttpRequest object.
                if (window.XMLHttpRequest) {
                    xmlhttp = new XMLHttpRequest();
                } else { // For old IE.
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }

                // Create callback function to react when the response from the server is ready.
                xmlhttp.onreadystatechange = function () {
                    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                        document.getElementById("studyAjax").innerHTML = xmlhttp.responseText;
                    }
                }

                // Prepare query for ajax.php
                xmlhttp.open("GET", "ajaxPubmedID.php?q=" + str, true);

                // Send query to ajax.php
                xmlhttp.send();

            }
        </script>
    </head>

    <body class="classBody_login">

        <?php
            $type=$_GET['t']; 
            //opciones: val | evolOrient | evolAge | evolOrigin
        ?> 
        <div class="TitleA">New study</div>
        <div class="ContentA">

        <form name="new_validation" method="post" action="add_study.php"  enctype="multipart/form-data" >

            <table class="classTable_login">
                <tr>
                    <td class = "classTd_right">
                        PubMed&nbsp;ID 
                    </td>
                    <td class = "classTd_left">
                        <input type="text" name="pubmedID" id="pubmedID" /> 
                        <input type="button" value="Search study" onclick="displayArticleInfo()"/> 
                    </td>
                </tr>
                <tr>
                    <td class = "classTd_right">
                        Study 
                        <div class="compulsory">*</div>
                    </td>
                    <td class = "classTd_left">
                        <div id="studyAjax">
                            <input type="text" name="study" id="study" /> 
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class = "classTd_right">
                        <p id="auto" class="classP">
                            <label>
	                        <?php if ($type =='pred') { ?>
	                            Prediction&nbsp;Method
	                        <?php } else { ?>
	                            Validation&nbsp;Method
	                        <?php } ?>
                                <div class="compulsory">&nbsp;*</div>
                            </label>
                        </p>
                    </td>
                    <td class = "classTd_left">
	                    <input type="text" id="searchValMethod" name="searchValMethod" />
                    </td>
                </tr>
                <tr>
                    <td class = "classTd_right">
	                    <?php if ($type =='pred') { ?>
                            Resolution 
                        <div class="compulsory">*</div>
                    </td>
                    <td class = "classTd_left">
                        <input type='text' id='resolution' name='resolution'/>
	                    <?php } ?>
                        <br />
                    </td>
                </tr>
                <tr>
                    <td class = "classTd_right">
	                    Description 
                        <div class="compulsory" >*</div> 
                    </td>
                    <td class = "classTd_left">
                        <textarea class="classTextarea" id="description" name="description" ></textarea>
                        <br />
                    </td>
                </tr>
            </table>

	        <?php 
	        if ($type =='pred')             {echo '<input type="hidden" name="origin" id="origin" value="prediction" />';}
	        elseif ($type =='val')          {echo '<input type="hidden" name="origin" id="origin" value="validation" />';}
	        elseif ($type=='evolOrigin')    {echo '<input type="hidden" name="origin" id="origin" value="origin" />';}
	        elseif ($type=='evolOrient')    {echo '<input type="hidden" name="origin" id="origin" value="species" />';}
	        elseif ($type=='evolAge')       {echo '<input type="hidden" name="origin" id="origin" value="age" />';}
	        elseif ($type=='effPhenotypic') {echo '<input type="hidden" name="origin" id="origin" value="phenotypicEffect" />';}
	        elseif ($type=='effGenomic')    {echo '<input type="hidden" name="origin" id="origin" value="genomicEffect" />';}
            ?>
            
            <!-- <input type="hidden" name="chr" value="<?php echo $chr['chr'] ?>" /> -->
	        <input type="submit" value="Add study" />
	        <input type="reset" value="Clear" /><br/>
        </form>
        </div>
    </body>

</html>
