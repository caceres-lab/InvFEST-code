<?php include('security_layer.php');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php include_once('select_new_study.php');?> 
<head>
	<title>New Study</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<script type="text/javascript" src="../js/jquery.js"></script>

<!-- <script type="text/javascript" src="http://paynesnotebook.net/IT/AJAX/04/ajax.js"></script> -->

	<link rel="stylesheet" type="text/css" href="../css/style.css" />
	<link rel="stylesheet" type="text/css" href="../css/report.css" />

	<script type="text/javascript" src="../js/autocomplete/jquery.js"></script>
	<script type="text/javascript" src="../js/autocomplete/dimensions.js"></script>
	<script type="text/javascript" src="../js/autocomplete/autocomplete.js"></script>

	<link rel="stylesheet" type="text/css" href="../css/autocomplete.css" media="screen" />
	<script type="text/javascript">
		$(function(){
			setAutoComplete("searchValMethod", "results", "autocomplete_valmethod.php?part=");  
//			setAutoComplete("searchValMethod", "results", "php/fosmids_autocomplete.php?part=");  
				//id of input field + id of the div that will hold the returned data + URL
		});
	</script>
<!--
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js"></script>
<link rel="stylesheet" href="http://jqueryui.com/progressbar/resources/demos/style.css" />

	<script type="text/javascript">

 $(function() {
var availableTags = [
"ActionScript",
"AppleScript",
"Asp",
"BASIC",
"C",
"C++",
"Clojure",
"COBOL",
"ColdFusion",
"Erlang",
"Fortran",
"Groovy",
"Haskell",
"Java",
"JavaScript",
"Lisp",
"Perl",
"PHP",
"Python",
"Ruby",
"Scala",
"Scheme"
];
$( "#searchValMethod" ).autocomplete({
source: availableTags
});
});
	</script>
-->
	<script type="text/javascript">
	
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




function displayArticleInfo()
{

	var str=document.getElementById("pubmedID").value;

  // If the str is empty, set queryResult and return.
	if(str == "") {
		//document.getElementById("queryResult").innerHTML="Please write one PubMed ID.<br >";
		alert("Please write one PubMed ID.");
		return;
	}
	if (str.match(numericExpression) && str.match(numericExpression2) ) {}
	else {alert ("PubMed ID must be a number"); document.getElementById("pubmedID").focus(); return false;}

  // Display "The result is comming."
  //document.getElementById("queryResult").innerHTML = "The result is comming.<br >";

  // Create XMLHttpRequest object.
  if (window.XMLHttpRequest)
  {
    xmlhttp = new XMLHttpRequest();
  }
  else // For old IE.
  {
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }

  // Create callback function to react when the response from the server is ready.
  xmlhttp.onreadystatechange = function ()
  {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
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

<body>

<?php $type=$_GET['t']; 
//opciones: val | evolOrient | evolAge | evolOrigin
?> 
<div class="TitleA">New study</div>
<div class="ContentA">

<form name="new_validation" method="post" action="add_study.php"  enctype="multipart/form-data" >

PubMed ID <input type="text" name="pubmedID" id="pubmedID" /> <input type="button" value="Search study" onclick="displayArticleInfo()"/> 
Study <div class="compulsory">*</div><div id="studyAjax" style="display:inline-block"><input type="text" name="study" id="study" /> </div><br />
<p id="auto" style="display: inline-block"> 
	<?php if ($type =='pred'){ ?>
	<label>Prediction Method
	<?php } else {?>
	<label>Validation Method
	<?php } ?> <div class="compulsory">*</div> </label>
	<input type="text" id="searchValMethod" name="searchValMethod" />
</p><br>
	<?php if ($type =='pred'){ ?>
	Resolution <div class="compulsory">*</div><input type='text' id='resolution' name='resolution'/>
	<?php } ?>
<br />
	Description <div class="compulsory" >*</div> <textarea id="description" name="description" ></textarea>
<br />

	<?php 
	if ($type =='pred'){echo '<input type="hidden" name="origin" id="origin" value="prediction" />';}
	elseif ($type =='val'){echo '<input type="hidden" name="origin" id="origin" value="validation" />';}
	elseif ($type=='evolOrigin'){echo '<input type="hidden" name="origin" id="origin" value="origin" />';}
	elseif ($type=='evolOrient'){echo '<input type="hidden" name="origin" id="origin" value="species" />';}
	elseif ($type=='evolAge'){echo '<input type="hidden" name="origin" id="origin" value="age" />';}
	elseif ($type=='effPhenotypic'){echo '<input type="hidden" name="origin" id="origin" value="phenotypicEffect" />';}
	elseif ($type=='effGenomic'){echo '<input type="hidden" name="origin" id="origin" value="genomicEffect" />';}

?>
<!--	<input type="hidden" name="chr" value="<?echo $chr['chr'] ?>" /> -->
	<input type="submit" value="Add study" />
	<input type="reset" value="Clear" /><br><br>
</form>
</div>
</body>

</html>
