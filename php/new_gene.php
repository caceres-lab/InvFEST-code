<?php include('security_layer.php');
$inv_id = $_GET["inv_id"];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>New Gene</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<script type="text/javascript" src="../js/jquery.js"></script>

<!-- <script type="text/javascript" src="http://paynesnotebook.net/IT/AJAX/04/ajax.js"></script> -->

	<link rel="stylesheet" type="text/css" href="../css/style.css" />
	<link rel="stylesheet" type="text/css" href="../css/report.css" />

	<script type="text/javascript" src="../js/autocomplete/jquery.js"></script>
	<script type="text/javascript" src="../js/autocomplete/dimensions.js"></script>
	<script type="text/javascript" src="../js/autocomplete/autocomplete.js"></script>

	<link rel="stylesheet" type="text/css" href="../css/autocomplete.css" media="screen" />

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
	
		function validate() {

			var idHsRefSeqGenes=document.getElementById("idHsRefSeqGenes");
			if (idHsRefSeqGenes.value=="") {
				alert ("Please find a valid RefSeq gene before trying to add it to the inversion");
				study.focus();
				return false;
			}

			return true;
		}




function displayGeneInfo()
{

	var str=document.getElementById("refseq").value;
	var str2=document.getElementById("symbol").value;
	
  // If the str is empty, set queryResult and return.
	if((str == "") && (str2 == "")) {
		//document.getElementById("queryResult").innerHTML="Please write one PubMed ID.<br >";
		alert("Please provide a RefSeq Accession number OR Gene Symbol.");
		return;
	}

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
      document.getElementById("geneAjax").innerHTML = xmlhttp.responseText;
    }
  }

  // Prepare query for ajax.php
  xmlhttp.open("GET", "ajaxNewGene.php?refseq=" + str + "&symbol=" + str2, true);

  // Send query to ajax.php
  xmlhttp.send();

}
		</script>
</head>

<body>

<div class="TitleA">New Gene</div>
<div class="ContentA">

<form name="new_gene" method="post" action="add_gene.php"  enctype="multipart/form-data" >
<input type="hidden" name="inv_id" id="inv_id" value="<?php echo $inv_id; ?>">
Please enter a RefSeq Accession Number OR Gene Symbol to search for a gene in the database. When you retrieve the complete information for that gene, you will be able to add the gene for the current inversion. <br/>
RefSeq Accession <input type="text" name="refseq" id="refseq" value=""/> <br/>
Symbol <input type="text" name="symbol" id="symbol" value=""/> <br/>
<input type="button" value="Search gene" onclick="displayGeneInfo()"/> <br/>&nbsp;<br/>&nbsp;

<div id="geneAjax" style="display:inline-block"></div><br />

</form>
</div>
</body>

</html>
