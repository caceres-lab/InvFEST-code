<?php include('security_layer.php');?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<?php include_once('select_new_validation.php');?> 
<head>
	<title>New Validation</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<script type="text/javascript" src="../js/autocomplete/jquery.js"></script>
	<script type="text/javascript" src="../js/autocomplete/dimensions.js"></script>
	<script type="text/javascript" src="../js/autocomplete/autocomplete.js"></script>

	<link rel="stylesheet" type="text/css" href="../css/style.css" />
	<link rel="stylesheet" type="text/css" href="../css/report.css" />
	<link rel="stylesheet" type="text/css" href="../css/autocomplete.css" media="screen" />
	<script type="text/javascript">
		$(function(){
			setAutoComplete("searchField", "results", "fosmids_autocomplete.php?part=");  
				//id of input field + id of the div that will hold the returned data + URL
		});

		function validate() {

			var research=document.getElementById("research_name");
			if (research.value=="") {
				alert ("Please fill in the Research Name field");
				research.focus();
				return false;
			}

			var method=document.getElementById("method");
			if (method.value=="") {
				alert ("Please fill in the Method field");
				method.focus();
				return false;
			}

			var status=document.getElementById("status");
			if (status.value=="") {
				alert ("Please fill in the Status field");
				status.focus();
				return false;
			}

			/*var fosmids=document.forms["new_validation"]["searchField"].value;
			var results=document.forms["new_validation"]["fosmids_results"].value;
			if ((fosmids!="") && (results=="")){
				alert ("Please fill in the Results from Bioinformatic validation");
				fosmids.focus();
				return false;
			}*/
			var fosmids=document.getElementById("searchField");
			var results=document.getElementById("fosmids_results");
			if (method.value.match(/PCR|FISH|MLPA/) ) { } //experimental
			else if (method.value != '') { //bioinformatics
				if (fosmids.value=="" ){
				alert ("Please fill in the Fosmids information from Validation details");
				fosmids.focus();
				return false;}
				else if (results.value==""){
				alert ("Please fill in the Results information from Validation details");
				results.focus();
				return false;}
			}
			
			var bp1s=document.getElementById("bp1s"); //bp1s.value does not make the comparison ok
			var bp1e=document.getElementById("bp1e");
			var bp2s=document.getElementById("bp2s");
			var bp2e=document.getElementById("bp2e");

			var bp1sv = new Number(document.getElementById("bp1s").value);
			var bp1ev = new Number(document.getElementById("bp1e").value);
			var bp2sv = new Number(document.getElementById("bp2s").value);
			var bp2ev = new Number(document.getElementById("bp2e").value);

			var numericExpression = /^[0-9]+$/;
			var numericExpression2 = /[1-9]+/;
			if (bp1s.value!="" || bp1e.value!="" || bp2s.value!="" || bp2e.value!=""){
				if (bp1s.value=="" || bp1e.value=="" || bp2s.value=="" || bp2e.value==""){
					alert ("Please fill in all the Add Breakpoints fields");
					return false;
				}
				if (bp1s.value.match(numericExpression) && bp1s.value.match(numericExpression2) ) {}
				else {alert ("Numbers only please"); bp1s.focus(); return false;}
				if (bp1e.value.match(numericExpression) && bp1e.value.match(numericExpression2)) {}
				else {alert ("Numbers only please"); bp1e.focus(); return false;}
				if (bp2s.value.match(numericExpression) && bp2s.value.match(numericExpression2)) {}
				else {alert ("Numbers only please"); bp2s.focus(); return false;}
				if (bp2e.value.match(numericExpression) && bp2e.value.match(numericExpression2)) {}
				else {alert ("Numbers only please"); bp2e.focus(); return false;}

				if (bp2ev > bp2sv && bp2sv > bp1ev && bp1ev > bp1sv) {}
				else {alert ("Positions of the breakpoints are not correct"); bp1s.focus(); return false;}
			}
			return true;
		}

		$(document).ready(function(){
			$(".hidden").hide();                                  //hide all contents 
			$(".section-title").click(function(){                 //toggle when click title
				$(this).next(".hidden, .grlsection-content").slideToggle(600);
			});
		});

		$(document).ready(function(){
			$("#includeExperimental").hide();
			$("#includeBioinformatics").hide();
			$("#includeMethod").show();
			$("#includeCompulsory").hide();

			var method=document.getElementById("method");
			$("#method").change(function(){
				if (method.value.match(/PCR|FISH|MLPA/) ) { //experimental
					$("#includeExperimental").show();
					$("#includeBioinformatics").hide();
					$("#includeMethod").hide();
					$("#includeCompulsory").hide();
				} else if (method.value != '') { //bioinformatics
					$("#includeExperimental").hide();
					$("#includeBioinformatics").show();
					$("#includeMethod").hide();
					$("#includeCompulsory").show();
				} else if (method.value == "") { //non-selected
					$("#includeExperimental").hide();
					$("#includeBioinformatics").hide();
					$("#includeMethod").show();
					$("#includeCompulsory").hide();
				}
			});

		});
/*  $(document).ready(function(){
	if($("#experimental").is(':checked')) {  
		$("#includeExperimental").show();
		$("#includeBioinformatics").hide();
	} else {
		$("#includeExperimental").hide();
		$("#includeBioinformatics").show();
	}  

	$("#experimental").click(function(){
		$("#includeExperimental").show();
		$("#includeBioinformatics").hide();
	});

	$("#bioinformatics").click(function(){
		$("#includeExperimental").hide();
		$("#includeBioinformatics").show();
	});
});*/
/*
function changeTable() {
	//First of all get the parentNodes of the tables: (the <td> that include them)
	var insertMuscle = document.getElementById('includeMuscle');
	var insertClustal = document.getElementById('includeClustal');

	//Remove a Muscle Table if there is any:
	while (insertMuscle.hasChildNodes() )
	{
		insertMuscle.removeChild(insertMuscle.lastChild);
	}
	
	//Remove the Clustal Tables (everything inside the corresponding <td>):
	while (insertClustal.hasChildNodes() )
	{
		insertClustal.removeChild(insertClustal.lastChild);
	}

	//If Muscle is checked --> Insert a Muscle table
	if (document.form.alprogram[0].checked==true)
	{
		var newTableMuscle = tablaMuscle.cloneNode(true);
		insertMuscle.appendChild(newTableMuscle);
	}

	//If Clustal is checked --> Insert the Clustal Tables
	if (document.form.alprogram[1].checked==true)
	{
		var newTableClustal1 = tablaClustal1.cloneNode(true);
		insertClustal.appendChild(newTableClustal1 );
		var newTableClustal2 = tablaClustal2.cloneNode(true);
		insertClustal.appendChild(newTableClustal2 );
	}
}
*/
	</script>
</head>

<body>
<?php $id=$_GET['q'];?>
<h3>Add a new validation for inversion INV_<?echo $id?></h3>


<form name="new_validation" method="post" action="add_validation.php" onsubmit="return validate()" enctype="multipart/form-data" >
	Research Name <div class="compulsory">*</div> <select id="research_name" name="research_name" ><option value="">-Select-</option>
		<?echo $research_name?></select><br>
	Method <div class="compulsory">*</div> <select name="method" id="method" ><option value="">-Select-</option>
		<?echo $method?></select><br>
	Status <div class="compulsory">*</div> <select name="status" id="status" ><option value="">-Select-</option>
		<?echo $status?></select><br>
	Checked <input name="checked" id="checked" type="checkbox" value="yes" /><br>
	Comment <input name="commentE" id="commentE" type="text" /><br>

	<div id="validation" class="report-section" >
		<div class="section-title">Validation details <div id="includeCompulsory"><div class="compulsory">*</div></div>:
		</div>
		<div class="hidden" >
		<div class="grlsection-content">
<!--			<input type="radio" checked='checked' id="experimental" value="experimental" name="validation"> Experimental
			<input type="radio" id="bioinformatics" value="bioinformatics" name="validation"> Bioinformatics
-->
			<div id="includeExperimental">
				Experimental conditions <input name="experimental_conditions" id="experimental_conditions" type="text" /><br>
				Primers <input name="primers" id="primers" type="text" /><br>
			</div>
			<div id="includeBioinformatics">
			<p id="auto" style="display: inline-block"> 
				<label>Fosmids <div class="compulsory">*</div> </label>
				<input type="text" id="searchField" name="searchField" />
			</p><br>
				Results <div class="compulsory">*</div> <input name="fosmids_results" id="fosmids_results" type="text" /><br>
				Comment <input name="commentB" id="commentB" type="text" /><br>
			</div>
			<div id="includeMethod">
				Please select a method
			</div>
		</div>
		</div>
	</div>
<!--
	<div id="experimental_validation" class="report-section" >
		<div class="section-title">Experimental validation:
		</div>
		<div class="hidden">
		<div class="grlsection-content">
			Experimental conditions <input name="experimental_conditions" id="experimental_conditions" type="text" /><br>
			Primers <input name="primers" id="primers" type="text" /><br>
			Comment <input name="commentE" id="commentE" type="text" /><br>
		</div>
		</div>
	</div>
	<div id="bioinformatics_validation" class="report-section" >
		<div class="section-title">Bioinformatics validation:
		</div>
		<div class="hidden">
		<div class="grlsection-content">
		<p id="auto">
			<label>Fosmids:</label>
			<input type="text" id="searchField" name="searchField" />
		</p>
			Results <input name="fosmids_results" id="fosmids_results" type="text" /><br>
			Comment <input name="commentB" id="commentB" type="text" /><br>

		</div>
		</div>
	</div>
-->
	<div id="individuals" class="report-section" >
		<div class="section-title">Individuals:
		</div>
		<div class="hidden">
		<div class="grlsection-content">
			Individuals <input type="file" name="individuals" id="individuals" /><br>
		</div>
		</div>
	</div>
	<div id="addBreakpoints" class="report-section" >
		<div class="section-title">Manual curated breakpoints:
		</div>
		<div class="hidden">
		<div class="grlsection-content">
			Breakpoint 1 start <input name="bp1s" id="bp1s" type="text" /><br>
			Breakpoint 1 end <input name="bp1e" id="bp1e" type="text" /><br>
			Breakpoint 2 start <input name="bp2s" id="bp2s" type="text" /><br>
			Breakpoint 2 end <input name="bp2e" id="bp2e" type="text" /><br>
			Description <input name="description" id="description" type="text" /><br>
		</div>
		</div>
	</div>
	
	<input type="hidden" name="inv_id" value="<?echo $id?>" />
	<input type="hidden" name="chr" value="<?echo $chr['chr'] ?>" />
	<input type="submit" value="Add validation" />
	<input type="reset" value="Clear" /><br><br>
</form>
</body>

</html>
