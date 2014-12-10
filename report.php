<? 
session_start(); //Inicio la sesión
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<!--
	2009 Miquel Ràmia <miquel.ramia@uab.cat>
	Lab. de Bioinformàtica de la variació genètica
	Grup de Genòmica, Bioinformàtica i Evolució
	Departament de Genètica i Microbiolgia
	Universitat Autònoma de Barcelona   

	2012 Raquel Egea <raquel.egea@uab.cat>
	Servei de Genomica i Bioinformatica
	Institut de Biologia i Biotecnologia
	Universitat Autonoma de Barcelona
-->
<?php include_once('php/select_report.php');?> 
<?php include_once('php/php_global_variables.php');?>
<?php
// if ($_SESSION["autentificado"]=='SI' && ($r['status']!='Withdrawn' && $r['status']!='withdrawn') {
//	include_once('php/select_new_validation.php');
//	}
?> 
<head>
	<link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
	<link rel="shortcut icon" href="img/InvFEST_ico.png">
	<title>Inversion Report: <?php echo $r['name']; ?></title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<script type="text/javascript" src="js/jquery.js"></script>

	<script type="text/javascript" src="js/validations.js"></script>
    
  	<script type="text/javascript" src="js/header.js"></script>


	<script type="text/javascript">
		$(document).ready(function(){
			$(".hidden").hide();                                  //hide all contents 
			$(".section-title").click(function(){                 //toggle when click title
				$(this).next(".hidden, .grlsection-content").slideToggle(600);
				var title = $(this).html();

				var regExp = /\+/;
				if (title.match(regExp)) {
					title = title.replace('+','-');
					$(this).html(title);
				} 
				else {
					title = title.replace('-','+');
					$(this).html(title);
				}
			});	
		
		/*	$('#selectStudy').change(function () {
				var selectStudy = $('#selectStudy').val();
				var parameters = 'selectStudy=' + selectStudy ;
				doAjax('php/refresh_frequency.php', parameters, 'displayResult', 'POST', '0', '<img id=\'load\' src=\'css/img/load.gif\' >');

			}
*/
		});

		function displayResult(text){
			document.getElementById("tableStudy").innerHTML = text;
		}

/*	function showEvolInfoResults() {
		div = document.getElementById('add_evol_info_result');
		$(div).append('<iframe style="width: 150px;" name="test" id="test"></iframe>');
}
	function hideEvolInfoResults() {
		div = document.getElementById('add_evol_info_result');
		$(div).empty('');
}*/


	</script>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link rel="stylesheet" type="text/css" href="css/report.css" />

	<!-- Para el highslide: -->
	<link href="css/css_highslide.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript" src="js/highslide_complete.js"></script>
	<script type="text/javascript">
	//                document.write('<style type="text/css">');    
	//                document.write('div.domtab div{display:none;}<');
	//                document.write('/s'+'tyle>');                   
	</script>
	<script type="text/javascript">    
	    hs.graphicsDir = 'img/highslide_graphics/';
	    hs.outlineType = 'rounded-white';
	    hs.outlineWhileAnimating = true;
	</script>
	
	<!-- Per Add frequencies without genotypes -->
	<script type="text/javascript">    
	    function calculateFng(elem) {
	    
	    	var value = elem.value;
    		var id    = elem.id;
    		var chrom = '<?php echo $r["chr"]; ?>';
    		var multi = '';
    		
    		if (chrom == 'chrY') {
    			multi = 1;
    		} else {
    			multi = 2;
    		}
    		
    		if ((id == 'fng_individuals') && (chrom != 'chrX')) {
    		
    			if (document.getElementById('fng_invalleles').value != '') {
    				document.getElementById('fng_invfreq').value = document.getElementById('fng_invalleles').value / (document.getElementById('fng_individuals').value * multi);
    				document.getElementById('fng_stdfreq').value = 1-document.getElementById('fng_invfreq').value;
    			}
    			
    		} else if (id == 'fng_invalleles') {
    		
    			if ((document.getElementById('fng_individuals').value != '') && (chrom != 'chrX')) {
    				document.getElementById('fng_invfreq').value = document.getElementById('fng_invalleles').value / (document.getElementById('fng_individuals').value * multi);
    				document.getElementById('fng_stdfreq').value = 1-document.getElementById('fng_invfreq').value;
    			}
    		
    		} else if (id == 'fng_stdfreq') {
    			document.getElementById('fng_invfreq').value = 1-value;
    			
    			if ((document.getElementById('fng_individuals').value != '') && (chrom != 'chrX')) {
    				document.getElementById('fng_invalleles').value = document.getElementById('fng_individuals').value * multi * document.getElementById('fng_invfreq').value;
    			}
    			
    		} else if (id == 'fng_invfreq') {
    			document.getElementById('fng_stdfreq').value = 1-value;
    			
    			if ((document.getElementById('fng_individuals').value != '') && (chrom != 'chrX')) {
    				document.getElementById('fng_invalleles').value = document.getElementById('fng_individuals').value * multi * document.getElementById('fng_invfreq').value;
    			}
    			
    		} else {} 
	    
	    
	    }
	</script>	

	<!-- Para el add_validation -->
	<script type="text/javascript" src="js/autocomplete/jquery.js"></script>
	<script type="text/javascript" src="js/autocomplete/dimensions.js"></script>
	<script type="text/javascript" src="js/autocomplete/autocomplete.js"></script>

	<link rel="stylesheet" type="text/css" href="css/autocomplete.css" media="screen" />
	<script type='text/javascript'>//<![CDATA[ 
	$(window).load(function(){
	$(".chkbox").change(function() {
	    var val = $(this).val();
	  if( $(this).is(":checked") ) {
	    
	    $(":checkbox[class='"+val+"']").attr("checked", true);
	  }
	    else {
		$(":checkbox[class='"+val+"']").attr("checked", false);
	    }
	});
	});//]]>  

	</script>

	<script type="text/javascript">
		$(function(){
			setAutoComplete("searchFosmids", "results", "php/autocomplete_fosmids.php?part=");  
//			setAutoComplete("searchValMethod", "results", "php/fosmids_autocomplete.php?part=");  
				//id of input field + id of the div that will hold the returned data + URL
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

		if ('<?php echo $_GET["o"]; ?>'=='add_val'){alert('Validation added succesfully');}
		else if ('<?php echo $_GET["o"]; ?>'=='add_valError'){alert('Validation did not success');}

		});

		function changeEvolForm(evolInfo,evolInfoForm) {
			//typo of info: evolInfo.value //"" "evolution_orientation" "evolution_age""evolution_origin"
			//evolInforForm -> div to append the form
			$('#evolInfoForm').empty('');
			if (evolInfo.value == "evolution_orientation"){
//				if ("<?php echo $species;?>" != '') {
					$('#evolInfoForm').append(
					"Species <div class='compulsory'>*</div>  <select id='orientation_species' name='orientation_species'>"+
					"<option value=''>-Select-</option><?php echo $species ?></select> "+
					"<br>Orientation  <div class='compulsory'>*</div> <select id='orientation_orientation'"+ 
					"name='orientation_orientation'>"+
					"<option value=''>-Select-</option><?php echo $orientation ?></select> "+
					"<br>Method  <div class='compulsory'>*</div> <select id='method_orientation' name='method_orientation'>"+
					"<option value=''>-Select-</option><?php echo $method_add_val ?></select> "+
					"<br>Study  <div class='compulsory'>*</div> <select id='source_orientation' name='source_orientation'>"+
					"<option value=''>-Select-</option><?php echo $research_name ?></select> <a class='highslide-resize' href='php/new_study.php?&t=evolOrient' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:200,  objectWidth:1000 })'> Add a new study</a>");
//				}
//				else {
//					$('#evolInfoForm').append("There are no species available to add information");
//				}
			}
			else if (evolInfo.value == "evolution_age"){
				$('#evolInfoForm').append("Age  <div class='compulsory'>*</div> <input type='text' name='age_age' id='age_age' > "+
				"<br>Method  <div class='compulsory'>*</div> <select id='method_age' name='method_age'><option value=''>-Select-</option>"+
				"<?php echo $method_add_val ?></select> <br>Study  <div class='compulsory'>*</div>  <select id='source_age' name='source_age'>"
				+"<option value=''>-Select-</option> <?php echo $research_name ?></select> <a class='highslide-resize' href='php/new_study.php?&t=evolAge' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:200,  objectWidth:1000 })'> Add a new study</a>");
			}
			else if (evolInfo.value == "evolution_origin"){
				$('#evolInfoForm').append(
				"Origin  <div class='compulsory'>*</div> <input type='text' name='origin_origin' id='origin_origin' > "+
				"<br>Method  <div class='compulsory'>*</div> <select id='method_origin' name='method_origin'>"+
				"<option value=''>-Select-</option><?php echo $method_add_val ?></select> <br>Study  <div class='compulsory'>*</div> "+
				"<select id='source_origin' name='source_origin'><option value=''>-Select-</option>"+
				"<?php echo $research_name ?></select> <a class='highslide-resize' href='php/new_study.php?&t=evolOrigin' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:200,  objectWidth:1000 })'> Add a new study</a>");
			}
			else {
			document.getElementById("evol_type_null").selected=true;
			}


		}

		function changeFuncForm(functInfo,functEffForm) {
			//typo of info: functInfo.value //"" "eff_genomic" "eff_phenotypic"
			//functEffForm -> div to append the form
			$('#functEffForm').empty('');
			if (functInfo.value == "eff_genomic"){
				$('#functEffForm').append("Gene  <div class='compulsory'>*</div> <select id='gene_func' name='gene_func'>"+
				"<option value=''>-Select-</option><?php echo $echo_symbols;?></select> <a class='highslide-resize' href='php/new_gene.php?inv_id=<?php echo $id; ?>&t=effGenomic' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:250,  objectWidth:1000 })'>Add new gene</a><br>"+
				"Effect  <div class='compulsory'>*</div> <input type='text' id='genomic_eff_func' name='genomic_eff_func'><br>"+
				"Study <div class='compulsory'>*</div> <select id='source_genomic_func' name='source_genomic_func'><option value=''>"+
				"-Select-</option><?php echo $research_name; ?></select> <a class='highslide-resize' href='php/new_study.php?&t=effGenomic' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:200,  objectWidth:1000 })'>Add new study</a><br>"+
				"Functional consequences  <div class='compulsory'>*</div> <input type='text' name='conseq_func' id='conseq_func'>");
			}
			else if (functInfo.value == "eff_phenotypic"){
				$('#functEffForm').append("Effect <div class='compulsory'>*</div> "+
				"<input type='text' id='phenotypic_eff_func' name='phenotypic_eff_func'><br>"+
				"Study <div class='compulsory'>*</div> <select id='source_phenotypic_func' name='source_phenotypic_func'>"+
				"<option value=''>-Select-</option><?php echo $research_name; ?></select> <a class='highslide-resize' href='php/new_study.php?&t=effPhenotypic' onclick='return hs.htmlExpand(this, {objectType: \"iframe\", objectHeight:200,  objectWidth:1000 })'>Add new study</a><br>");
			}
			else {
			document.getElementById("effect_type_null").selected=true;
			}
		}

		function changeFreqs(study,IDtable,inv_id,population,region){
			//IDtable -> id table to change
			//study -> study.value
			var table=document.getElementById(IDtable);

			// crear un ajax que cambie la tabla 'table'
			// Create XMLHttpRequest object.
			if (window.XMLHttpRequest) {xmlhttp = new XMLHttpRequest();}
			else {xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");} // For old IE.

			// Create callback function to react when the response from the server is ready.
			xmlhttp.onreadystatechange = function () {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200){table.innerHTML = xmlhttp.responseText;}
			}

			// Prepare query for ajax.php
			xmlhttp.open("GET", "php/ajaxChangeFreqs.php?q="+inv_id+"&pop="+population+"&reg="+region+"&stud=" + study.value, true);

			// Send query to ajax.php
			xmlhttp.send();
		}

		function updateTD(type,id) {
			accuracy='';
			if (type=='comments_pred'){
				accuracy=document.getElementById('acc'+id).innerHTML;
			}
			tdElement=document.getElementById(type+id);
			texto=document.getElementById('DIV'+type+id).innerHTML;
			$(tdElement).empty('');
			$(tdElement).append(accuracy+"<textarea id='updatable"+id+"' cols='60'>"+texto+"</textarea><input type='hidden' name='origin' value='"+type+"'><input type='button' value='Update' class='right' onclick=\"ajaxUpdateTD('"+type+"','"+id+"')\">");
			//FALTA QUE EL BOTON LLAME A UNA FUNCION!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		}

		function ajaxUpdateTD(origin,id){
			var newtext=document.getElementById('updatable'+id).value;

			var tdElement;
			//if (origin=='comments'){ tdElement=document.getElementById(id); }
			//else { tdElement=document.getElementById(origin+id); }
			tdElement=document.getElementById(id); 
			if (origin =='inv_bp_origin') {tdElement2=document.getElementById('inv_origin'+id); }

			// crear un ajax que cambie el TD correspondiente
			// Create XMLHttpRequest object.
			if (window.XMLHttpRequest) {xmlhttp = new XMLHttpRequest();}
			else {xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");} // For old IE.

			// Create callback function to react when the response from the server is ready.
			xmlhttp.onreadystatechange = function () {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200 && origin =='inv_bp_origin'){
					texto = xmlhttp.responseText;

					tdElement.innerHTML="<div  id='inv_bp_origin"+id+"'>"+texto+"</div><input type='button' value='Edit' class='right' onclick=\"updateTD('inv_bp_origin','"+id+"')\">";
					tdElement2.innerHTML=texto;
				}	
				else if (xmlhttp.readyState == 4 && xmlhttp.status == 200){tdElement.innerHTML = xmlhttp.responseText;}
			}

			// Prepare query for ajax.php
			xmlhttp.open("GET", " php/ajaxUpdate_info.php?p="+id+"&up="+newtext+"&or="+origin, true);

			// Send query to ajax.php
			xmlhttp.send();
		}

function formget(f, url, divID) {
	var poststr = getFormValues(f);
	if (divID=='evolution') {
		var type=document.getElementById("evol_type").value;
		if (type == "evolution_orientation"){divID='table_evol_orientation_sp';}
		else if (type == "evolution_age"){divID='table_evol_age';}
		else if (type == "evolution_origin"){divID='table_evol_origin';}
	}
	else if (divID=='functional'){
		var type=document.getElementById("effect_type").value;
		if (type == 'eff_genomic'){
			//para los efectos genomicos, debemos capturar el mecanismo
			var n=poststr.split("&"); 
			for(var i in n){
				if (n[i].match('gene_func')){
					id_gene=n[i].replace("gene_func=",""); 
					var mechanism=document.getElementById(id_gene+'_mechanism');
					poststr=poststr+'&mechanism='+mechanism.textContent;
				} 
			}
			divID='functional_effectAjax|'+id_gene;
		}
		else if (type == 'eff_phenotypic'){divID='table_phenotypical_effect';}
	}
	postData(url, poststr, divID);
}

function getFormValues(fobj) {
	var str = "";
	var valueArr = null;
	var val = "";
	var cmd = "";
	 
	for(var i = 0;i < fobj.elements.length;i++) {
		switch(fobj.elements[i].type) {
			case "text":
				str += fobj.elements[i].name + "=" + escape(fobj.elements[i].value) + "&";
			break;
			case "textarea":
				str += fobj.elements[i].name + "=" + escape(fobj.elements[i].value) + "&";
			break;
			case "select-one":
				str += fobj.elements[i].name + "=" + fobj.elements[i].options[fobj.elements[i].selectedIndex].value + "&";
			break;
			case "checkbox":
				if ($(fobj.elements[i]).is(":checked")) {  
					str += fobj.elements[i].name + "=" + escape(fobj.elements[i].value) + "&";
				}
			break;
//alert(str);
			case "file":
				str += fobj.elements[i].name + "=" + escape(fobj.elements[i].value) + "&";
//alert(str);
			break;
			case "hidden":
				str += fobj.elements[i].name + "=" + escape(fobj.elements[i].value) + "&";
			break;


		}
	}
	str = str.substr(0,(str.length - 1));
	return str;
}

function postData(url, parameters, divID){
	var xmlHttp = AJAX();
	xmlHttp.onreadystatechange =  function(){
		//if(xmlHttp.readyState > 0 && xmlHttp.readyState < 4){
		//	document.getElementById(divID).innerHTML=loadingmessage;
		//}
		if (xmlHttp.readyState == 4) {
			var modify=document.getElementById(divID);
//			$(modify).append(xmlHttp.responseText);
			var newText=xmlHttp.responseText;

			//error=/^Error:/;alert('-'+newText+'-');
			if (newText.match(/Error: /)){alert(newText);} //si es error, lo muestro con alert
			//si el div es una tabla:
			else if (divID.match(/^table/)){
				if ($('#'+divID).length){//si la tabla ya existe:
					$('#'+divID).find('tbody:last').append(newText);
				} else {//si la tabla no existe, primero la creamos y luego añadimos la fila
					createTable(divID);
					$('#'+divID).find('tbody:last').append(newText);
				}
			}
			else if (divID.match('functional_effectAjax')) {
				id=divID.replace("functional_effectAjax|",""); 
				$("#"+id).empty();
				$("#"+id).append(newText);
			}
			else {$(modify).append(newText);} //si no es error, lo añado al DIV correspondiente
			
		}
	}
	xmlHttp.open("POST", url, true);
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", parameters.length);
	xmlHttp.setRequestHeader("Connection", "close");
	xmlHttp.send(parameters);
}

function AJAX(){
	var xmlHttp;
	try{
		xmlHttp=new XMLHttpRequest(); // Firefox, Opera 8.0+, Safari
		return xmlHttp;
	}
	catch (e){
		try{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP"); // Internet Explorer
			return xmlHttp;
		}
		catch (e){
			try{
				xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
				return xmlHttp;
			}
			catch (e){
				alert("Your browser does not support AJAX!");
				return false;
			}
		}
	}
 
}

	function createTable (id) {
		if (id == 'table_evol_orientation_sp'){
			$("#evol_orientation_sp").empty();
			$('#evol_orientation_sp').append('<table width="100%" id="table_evol_orientation_sp"><tr>'+
			"<td class='title'>Species</td><td class='title'>Orientation</td><td class='title'>Method</td><td class='title'>Study</td>"+
			"</tr></table>");
		}
		else if (id == 'table_evol_age'){
			$("#evol_age").empty();
			$('#evol_age').append('<table width="100%" id="table_evol_age"><tr>'+
			"<td class='title'>Age</td><td class='title' width='37%'>Method</td><td class='title'>Study</td>"+
			"</tr></table>");
		}
		else if (id == 'table_evol_origin'){
			$("#evol_origin").empty();
			$('#evol_origin').append('<table width="100%" id="table_evol_origin"><tr>'+
			"<td class='title'>Species</td><td class='title' width='37%'>Method</td><td class='title'>Study</td>"+
			"</tr></table>");
		}
		else if (id == 'table_phenotypical_effect'){
			$("#phenotypical_effect").empty();
			$('#phenotypical_effect').append('<table width="100%" id="table_phenotypical_effect"><tr>'+
			"<td class='title'>Effect</td><td class='title'>Study</td>"+
			"</tr></table>");
		}

	}

	function submitNewValidation (f) { //AJAX
		returned=validate();
		if (returned===true) {
			formget(f, 'php/ajaxAdd_validation.php', 'validationsAjax');
			//set the form to 0
			document.getElementById("research_name_null").selected=true;
			document.getElementById("method_null").selected=true;
			document.getElementById("status_null").selected=true;
			document.getElementById("checked").checked=false;
			document.getElementById("commentE").value='';
			document.getElementById("experimental_conditions").value='';
			document.getElementById("primers").value='';
			document.getElementById("searchFosmids").value='';
			document.getElementById("fosmids_results").value='';
			document.getElementById("commentB").value='';
			document.getElementById("individuals").value='';
			document.getElementById("bp1s").value='';
			document.getElementById("bp1e").value='';
			document.getElementById("bp2s").value='';
			document.getElementById("bp2e").value='';
			document.getElementById("description").value='';


		}
	}

	function submitNewEvolInfo (f) {
		returned=validate_evol();
		if (returned===true) {
			formget(f, 'php/ajaxAdd_evol_info.php', 'evolution');
			//set the form to 0
			changeEvolForm('new_evol_info','evolInfoForm');
		}
	}

	function submitNewFunctionalInfo (f) {
		returned=validate_funct();
		if (returned===true) {
			formget(f, 'php/ajaxAdd_funct_effect.php', 'functional');
			//set the form to 0
			changeFuncForm('functEff_form','functEffForm');
		}
	}

	</script>

	<!-- Para el pie chart de las frecuencias: -->
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		function drawChart() {
			$("#chart_graph").empty(); //vaciamos el output para no duplicar DIVs
			var data = new Array;

			var datos = document.forms["frequency_graph"].elements["graph[]"];
			for (var i = 0, len = datos.length; i < len; i++) {
			  
				if ($(datos[i]).is(":checked")) {  
					//alert('checked '+datos[i].value);    // checkbox is checked -> guardamos los datos
					var n=datos[i].value.split(";"); 
					data[i]= google.visualization.arrayToDataTable([
						["Orientation", "Frequency"],
						["STD",    Number(n[0])],
						["INV",    Number(n[1])]
					]);
					
					var sample = parseInt(n[0]) + parseInt(n[1]);
					var titleText = n[2] + " (N=" + sample + ")";

					var options = {
						//legend:{position: 'right', alignment: 'center', textStyle: {color: 'black', fontSize: 16}},
						colors:['#99cc66','#ff4444'],
						is3D:true /*true or false*/,
						tooltip:{text:'both'},
						pieSliceText:'percentage',
						title:titleText,
						width:300,
					};
					//append div
					$('#chart_graph').append('<div id=\"chart_div'+i+'\" style=\"width: 200px; height: 200px; display:inline-block\" >');

					var chart = new google.visualization.PieChart(document.getElementById('chart_div'+i));
					chart.draw(data[i], options);

				}
			}
		}
		
		function drawChartNew(region) {

			$("#chart_graph_"+region).empty(); //vaciamos el output para no duplicar DIVs

			var data = new Array;
			var datos = document.forms["frequency_graphNew"].elements["NewGraphs_"+region+"[]"];
			var typeChart = document.getElementById("typeChart_"+region).value;
 					
			if (typeChart == "all") {

				for (var i = 0, len = datos.length; i < len; i++) {
				  
					if ($(datos[i]).is(":checked")) {  
						//alert('checked '+datos[i].value);    // checkbox is checked -> guardamos los datos
						var n=datos[i].value.split(";"); 
						data[i]= google.visualization.arrayToDataTable([
							["Orientation", "Frequency"],
							["STD",    Number(n[0])],
							["INV",    Number(n[1])]
						]);
					
						var sample = Number(n[0]) + Number(n[1]);
						
						if (sample > 0) {
						
							if (sample > 1) {
							
								var titleText = n[2] + " (N=" + sample + ")";
								var options = {
								//legend:{position: 'right', alignment: 'center', textStyle: {color: 'black', fontSize: 16}},
								colors:['#99cc66','#ff4444'],
								is3D:true /*true or false*/,
								tooltip:{text:'both'},
								pieSliceText:'percentage',
								title:titleText,
								width:300,
								};
								
							} else {
							
								var titleText = n[2];
								var options = {
								//legend:{position: 'right', alignment: 'center', textStyle: {color: 'black', fontSize: 16}},
								colors:['#99cc66','#ff4444'],
								is3D:true /*true or false*/,
								tooltip:{text:'percentage'},
								pieSliceText:'percentage',
								title:titleText,
								width:300,
								};
							
							}
						
							//append div
							$('#chart_graph_'+region).append('<div id=\"chart_div'+region+i+'\" style=\"width: 225px; height: 200px; display:inline-block\" >');

							var chart = new google.visualization.PieChart(document.getElementById('chart_div'+region+i));
					
							chart.draw(data[i], options);
						
						}

					}
				}			
			
			} else {
			
				// Start global variables
				
				var global_sample = 0;
				var global_STD = 0;
				var global_INV = 0;
				
				var global_sample_nogenotypes = 0;
				var global_STD_nogenotypes = 0;
				var global_INV_nogenotypes = 0;
			
				for (var i = 0, len = datos.length; i < len; i++) {
				  
					if ($(datos[i]).is(":checked")) {  

						var n=datos[i].value.split(";"); 
						
						if ((Number(n[0]) + Number(n[1])) > 1) {
						
							global_sample += (Number(n[0]) + Number(n[1]));
							global_STD += Number(n[0]);
							global_INV += Number(n[1]);
						
						} else {
						
							global_sample_nogenotypes += 1;
							global_STD_nogenotypes += Number(n[0]);
							global_INV_nogenotypes += Number(n[1]);
						
						}
						
					}
				}
				
				// Further prepare data
				if (global_sample_nogenotypes == 0) {
				
					//No cal fer res més
				
				} else {
				
					if (global_sample == 0) {
					
						//Només dades sense genotips, promig diferents poblacions
						global_sample = 1;
						global_STD = global_STD_nogenotypes/global_sample_nogenotypes;
						global_INV = global_INV_nogenotypes/global_sample_nogenotypes;
					
					} else {
					
						//Dades amb i sense genotips, només es mostra dades amb genotips
						//No cal fer res més
					
					}				
				
				}
				
				
				// Now generate single graph
				data[0]= google.visualization.arrayToDataTable([
					["Orientation", "Frequency"],
					["STD",    Number(global_STD)],
					["INV",    Number(global_INV)]
				]);
				
				if (global_sample > 0) {
				
					if (global_sample > 1) {
					
						var titleText = region + " (N=" + global_sample + ")";	
						var options = {
						//legend:{position: 'right', alignment: 'center', textStyle: {color: 'black', fontSize: 16}},
						colors:['#99cc66','#ff4444'],
						is3D:true /*true or false*/,
						tooltip:{text:'both'},
						pieSliceText:'percentage',
						title:titleText,
						width:300,
						};
						
					} else {
					
						var titleText = region;	
						var options = {
						//legend:{position: 'right', alignment: 'center', textStyle: {color: 'black', fontSize: 16}},
						colors:['#99cc66','#ff4444'],
						is3D:true /*true or false*/,
						tooltip:{text:'percentage'},
						pieSliceText:'percentage',
						title:titleText,
						width:300,
						};
					
					}
	
				//append div
				$('#chart_graph_'+region).append('<div id=\"chart_div'+region+0+'\" style=\"width: 225px; height: 200px; display:inline-block\" >');

				var chart = new google.visualization.PieChart(document.getElementById('chart_div'+region+0));
			
				chart.draw(data[0], options);
				
				}
		
			}

		}

		function clearChartNew(region) {

			$("#chart_graph_"+region).empty(); //vaciamos el output para no duplicar DIVs

		}


	</script>

<script type="text/javascript">
/*	$(document).ready(function(){
		$("#selectStudy").change( function () {
			
var table = document.getElementById('tableStudy').innerHTML;
//alert(table);
//obtener el value
//transformar el value en nuevo innerHTML que sera la tabla entera
		});
	});
*/
</script>

</head>

<body>

<?php include('php/echo_menu.php');?>

<!--
	<div id="wrapper">
    <div  id="floatingbar">
    <div id= "haedtitle">
<img border="0" src="img/InvFEST.png" width="130" style="float: left;"></img>
Human Polymorphic Inversion DataBase
        <div id="login" class='right'>
				<?if ($_SESSION["autentificado"]=='SI'){echo'<a href="php/logout.php?origin=report&q='.$id.'">Logout</a>';}
				else {?>
				<a id="login2" href="php/login.php?origin=report&q=<?php echo $id;?>" onclick="return hs.htmlExpand(this, {objectType: 'iframe', width: 300, preserverContent: false })" >Login</a><?}?>
		</div>
    </div>
    <div style="float: left">
    <ul>
		  <li></li>
		   <li><a href="http://158.109.215.162/invdb/"><button>Home</button></a></li>
		   <li><a href=""><button class="default">About the Project</button></a></li>
		   <li><a href=""><button class="default">Links</button></a></li>
		   <li><a href=""><button class="default">Data Submissions</button></a></li>
		   <li><a href=""><button class="default">Download</button></a></li>
		   <li><a href=""><button class="default">Contact</button></a></li>
           <li></li>
		 </ul>
    </div>
		 
	</div>
	


	<div  id="minibar">
        <div id= "haedtitle">Inversion <?php echo $r['name'];?> Report
        <div id="login" class='right'>
				<?if ($_SESSION["autentificado"]=='SI'){echo'<a href="php/logout.php?origin=report&q='.$id.'">Logout</a>';}
				else {?>
				<a id="login2" href="php/login.php?origin=report&q=<?php echo $id;?>" onclick="return hs.htmlExpand(this, {objectType: 'iframe', width: 300, preserverContent: false })" >Login</a><?}?>
		</div>
    </div>
    <div style="float: left">	
		 <ul>
		  <li><img border=0 src="images/rocketbar.png"></img></li>
		   <li><a href="http://158.109.215.162/invdb/"><button>Home</button></a></li>
		    <li><a href=""><button class="default">About the Project</button></a></li>
		   <li><a href=""><button class="default">Links</button></a></li> 
		   <li><a href=""><button class="default">Data Submissions</button></a></li>
		   <li><a href=""><button class="default">Download</button></a></li>
		   <li><a href=""><button class="default">Contact</button></a></li>
		   <li><a href="javascript:#"><button title="Scroll" id="dirbutton" class="default">
		   <img border=0 src="img/bottomarrow.png"></img>
		   </button></a></li>
          </ul>
         </div>
	</div>
	 -->
	<!--	<div id="head"><div id="logo"></div>
			Human Inversion DataBase
			<div id="login">
				<?if ($_SESSION["autentificado"]=='SI'){echo'<a href="php/logout.php?origin=report&q='.$id.'">LOGOUT</a>';}
				else {?>
				<a id="login2" href="php/login.php?origin=report&q=<?php echo $id;?>" onclick="return hs.htmlExpand(this, {objectType: 'iframe', width: 300, preserverContent: false })" >Login</a><?}?>
			</div>
            <div id="report_title">Inversion <?php echo $r['name'];?> Report</div>
		    <div id="menu_div">
            <ul id="menu">
                <li id="menu_item"><a href="">Home</a></li>
                <li id="menu_item"><a href="">News</a></li>
                <li id="menu_item"><a href="">Contact</a></li>
                <li id="menu_item"><a href="">About</a></li>
            </ul>
            </div>
        </div>
       --> 
		<div id="report">
			
	<!--		<h1 id="report_title">Inversion <?php echo $r['name'];?> Report</h1> --->
	
	<div id="general_info" class="report-section" ><!---------- GENERAL INFORMATION ------>
		<div class="TitleStatic">General information 
		</div>
		<div class="grlsection-content ContentA">
<table width='100%'>
	<tr>
		<td class="title" width='20%'>Accession</td>
		<td width='30%'><?php echo $r['name']; ?></td>
		<td class="title" width='20%'>Region of the inversion</td>
		<td width='30%'><?php echo $r['chr'].':'.$r['range_start'].'-'.$r['range_end'];?></td>
	</tr>
	<tr>
		<td class="title">Estimated Inversion Size</td>
		<td><?php echo number_format($r['size']);?> bp</td>
		<td class="title">Supporting predictions</td>
		<td><?php echo $r['num_pred'];?></td>
	</tr>
	<tr>
		<td class="title">Status</td>
		<td><?php 
		
		echo $array_status[$r['status']];
		
		?></td>
		<td class="title">Number of validations</td>
		<td><?php echo $r['num_val'];?></td>
	</tr>
	<tr>
		<td class="title">Inverted allele frequency</td>
		<td><?php 
		
		if ($r['status'] == 'FALSE') {
			$r_inv_freq = "<font color='grey'>NA</font>";		
		} else if (($r_inv_freq == '') or ($r_inv_freq == 'NA')) {
			$r_inv_freq = "<font color='grey'>ND</font>";
		} else {}
				
		echo $r_inv_freq /*$r['frequency_distribution']*/;
		
		?></td>
		<td class="title">Mechanism of origin</td>
		<td id='inv_origin<?php echo $id; ?>'><?php 
		
		if ($r['status'] == 'FALSE') {
			$r['origin'] = "<font color='grey'>NA</font>";		
		} else if ($r['origin'] == '') {
			$r['origin'] = "<font color='grey'>ND</font>";
		} else {
			$r['origin'] = ucfirst($r['origin']);
		}
				
		echo $r['origin'];
		
		?></td>
	</tr>
	<tr>
		<td class="title">Functional effect</td>
		<td><?php 
		
		echo $array_effects[$r['genomic_effect']];
		
		?></td>
		<td class="title">Breakpoint 1</td>
		<td><?php echo $r['chr'].':'.$r['bp1_start'].'-'.$r['bp1_end'];?></td>
	</tr>
	<tr>
		<td class="title">Ancestral orientation</td>
		<td><?php
		
		if ($r['status'] == 'FALSE') {
			$r['ancestral_orientation'] = "<font color='grey'>NA</font>";		
		} else if ($r['ancestral_orientation'] == '') {
			$r['ancestral_orientation'] = "<font color='grey'>ND</font>";
		} else {
			$r['ancestral_orientation'] = ucfirst($r['ancestral_orientation']);
		}
				
		echo $r['ancestral_orientation'];
		
		?></td>
		<td class="title">Breakpoint 2</td>
		<td><?php echo $r['chr'].':'.$r['bp2_start'].'-'.$r['bp2_end'];?></td>
	</tr>
	
	<?php if (($r['comment'] != '') or ($_SESSION["autentificado"]=='SI')) {
	
	echo "
	<tr>
		<td class='title'>Comments</td>
		<td colspan=3 id='comments_inv".$id."'><div  id='DIVcomments_inv".$id."'>".$r['comment']."</div>";
		
		
            	if ($_SESSION["autentificado"]=='SI') {
        		        		
        		echo "<input type='button' class='right' value='Edit' onclick=\"updateTD('comments_inv','".$id."')\" />";

            	}		
		
		echo "</td>
	</tr>
	
	";
	
	}
	
	?>
</table>

<?php
/*
//			if ($r['name'] != "" || $r['name'] != NULL) {
				echo '<p class="field"><strong>Name: </strong>'. $r['name'].'</p>';
//			}
//			if ($r['size'] != "" || $r['size'] != NULL) {
				echo '<p class="field"><strong>Size: </strong> '.$r['size'].'</p>';
//			}
//			if ($r['status'] != "" || $r['status'] != NULL) {
				echo '<p class="field"><strong>Status: </strong> '.$r['status'].'</p>';
//			}
			echo '<p class="field"><strong>Global frequency: </strong> campo?? </p>';
			echo '<p class="field"><strong>Functional effect: </strong> campo?? </p>';
			echo '<p class="field"><strong>Ancestral orientation: </strong> campo?? </p>';
//			if ($r['comment'] != "" || $r['comment'] != NULL) {
				echo '<p class="field"><strong>Comment: </strong> '.$r['comment'].'</p>';
//			}
			echo '</div>
			<div class="right">';
			echo '<p class="field"><strong>Inversion position: </strong> '.$r['chr'].': '.$r['range_start'].'-'.$r['range_end'].' </p>';
			echo '<p class="field"><strong>Supporting predictions: </strong> campo?? </p>';
			echo '<p class="field"><strong>Number of validations: </strong> campo?? </p>';
//			if ($r['origin'] != "" || $r['origin'] != NULL) {
				echo '<p class="field"><strong>Mechanism of Origin: </strong>'.$r['origin'].'</p>';
//			}
			echo '<p class="field"><strong>Breakpoint1: </strong> '.$r['chr'].': '.$r['bp1_start'].'-'.$r['bp1_end'].' </p>';
			echo '<p class="field"><strong>Breakpoint2: </strong> '.$r['chr'].': '.$r['bp2_start'].'-'.$r['bp2_end'].' </p>';

//			if ($r['chr'] != "" || $r['chr'] != NULL) {
//				echo '<p class="field"><strong>Chromosome: </strong> '.$r['chr'].'</p>';
//			}
//			if ($r['range_start'] != "" || $r['range_start'] != NULL) {
//				echo '<p class="field"><strong>Range start: </strong> '.$r['range_start'].'</p>';
//			}
//			if ($r['range_end'] != "" || $r['range_end'] != NULL) {
//				echo '<p class="field"><strong>Range end: </strong> '.$r['range_end'].'</p>';
//			}
//			if ($r['type'] != "" || $r['type'] != NULL) {
//				echo '<p class="field"><strong>Type: </strong> '.$r['type'].'</p>';
//			}
//			if ($r['detected_amount'] != "" || $r['detected_amount'] != NULL) {
//				echo '<p class="field"><strong>Detected ammount: </strong> '.$r['detected_amount'].'</p>';
//			}
//			if ($ind_pred['c'] != "0" ) {
//				echo '<p class="field"><strong>Predicted individuals: </strong> '.$ind_pred['c'].'</p>';
//			}
//			if ($ind_val['c'] != "0") {
//				echo '<p class="field"><strong>Validated individuals: </strong> '.$ind_val['c'].'</p>';
//			}
			echo '</div>';
*/
			?>
<!--			<div class="right bkp">
				<h4>Breakpoint 1</h4>
				<p class="field"><strong>Start: </strong><?php echo $r['bp1_start'];?></p>
				<p class="field"><strong>End: </strong><?php echo $r['bp1_end'];?></p>
				<br>
				<h4>Breakpoint 2</h4>
				<p class="field"><strong>Start: </strong><?php echo $r['bp2_start'];?></p>
				<p class="field"><strong>End: </strong><?php echo $r['bp2_end'];?></p>
				<br>
				<?php
				if ($r['definition_method'] != "" || $r['definition_method'] != NULL) {
					echo '<p class="field"><strong>Definition method: </strong>'.$r['definition_method'].'</p>';
				}
				if ($r['seg_dup_id'] != "" || $r['seg_dup_id'] != NULL) {
					echo '<p class="field"><strong>Segmental duplications: </strong>'.$r['seg_dup_id'].'</p>';
				}
				?>
				
			</div>
-->
			<div style="clear:both;"></div>
		</div>	

	</div>
	<div id="region_map" class="report-section" >	<!---------- REGION MAP ------>
		<div class="TitleStatic">Region map 
			</div>
		<div class="grlsection-content ContentA">
		
			<a href="http://genome.ucsc.edu/cgi-bin/hgTracks?hgS_doOtherUser=submit&hgS_otherUserName=InvFEST&hgS_otherUserSessionName=InvFEST&db=hg18&position=<?php echo $r['chr'];?>:<?php echo $start_image; #$pos['inicio']?>-<?php echo $end_image; #$pos['fin']?>" target="_blank"><img id="region" src="http://158.109.215.162/invdb/image.pl<?php echo $perlurl; ?>" /> </a>  <!-- http://genome.ucsc.edu/cgi-bin/hgTracks?db=hg18&position=<?php echo $r['chr'];?>%3A<?php echo $start_image; #$pos['inicio']?>-<?php echo $end_image; #$pos['fin']?>&Submit=submit -->
			
		</div>

	</div>
	<div id="predictions" class="report-section" >
		<div class="section-title TitleA">+ Predictions  <!---------- PREDICTIONS ------>
		</div>
		<div class="hidden">
		<div class="grlsection-content ContentA">
		<?php 
		if ($echo_predictions != "") { ?>
		<!--	<table id="validation_table">
			<thead>
			<tr>
			    <td>Study</td>	<td>Breakpoint1</td>	<td>Breakpoint2</td>
				<td>Status</td>
				<td>Comment</td>	<td>Accuracy</td>	<td>Fosmids</td>	<td>Individuals</td>
			</tr>
			</thead>
			<tbody>
		-->		<?php echo $echo_predictions; ?>
		<!--	</tbody>
			</table>
		-->	
		<?php }
		else { echo 'No predictions are found';}
		?>
		</div>	
		</div>
	</div>
	<div id="validations" class="report-section" >
		<div class="section-title TitleA">+ Validation and genotyping  <!---------- VALIDATIONS ------>
		</div>
		<?php if ($_GET['o']!='add_val' && $_GET['o']!='add_valError'){?>
		<div class="hidden">
		<?php } ?>
		<div class="grlsection-content ContentA">
		<div id="validationsAjax">
		<?php
		if ($echo_validations != "") {
		/*	echo '<table id="validation_table">
			<thead>
			<tr>
				<td>Research Name</td>	<td>Method</td>	<td>Status</td>	';
			if ($_SESSION["autentificado"]=='SI') {
				echo '<td>Experimental conditions</td>	<td>Primers</td>	';
			}
			echo'<td>Comment</td>	<td>Fosmids</td>	<td>Individuals</td>
			</tr>
			</thead>
			<tbody>
				'.$echo_validations.'
			</tbody>
			</table>
			';
			*/
			echo $echo_validations;
		}
		else  { echo 'No validations are found';}
		echo "</div>"; //end validationsAjax
		if ($_SESSION["autentificado"]=='SI' && ($r['status']!='Withdrawn' && $r['status']!='withdrawn')){?>
<!--			<p><a class="highslide-resize" href="php/new_validation.php?&q=<?echo $id?>" onclick="return hs.htmlExpand(this, {objectType: 'iframe', objectHeight:300 })">Add a new validation</a></p> -->
			<div class="section-title TitleB">+ New validation
			</div>
			<div class="hidden">
			<div class="grlsection-content ContentA"> 

<!--<form name="new_validation" method="post" enctype="multipart/form-data"  > -->
<form name="new_validation" method="post" action="php/add_validation.php" onsubmit="return validate()" enctype="multipart/form-data" >
<!--<onsubmit="submitNewValidation(this.form)">-->
	Study Name <div class="compulsory">*</div> <select id="research_name" name="research_name" ><option value="" id='research_name_null'>-Select-</option>
		<?echo $research_name?></select><p style="display:inline-block"><a class="highslide-resize" href="php/new_study.php?&t=val" onclick="return hs.htmlExpand(this, {objectType: 'iframe', objectHeight:200,  objectWidth:1000 })"> Add a new study</a></p><br>
	Method <div class="compulsory">*</div> <select name="method" id="method" ><option value="" id='method_null'>-Select-</option>
		<?echo $method_add_val?></select><br>
	Status <div class="compulsory">*</div> <select name="status" id="status" ><option value="" id='status_null'>-Select-</option>
		<?echo $status_add_val?></select><br>
	Checked <input name="checked" id="checked" type="checkbox" value="yes" /><br> <!-- ELIMINAR!!! -->
	Comment <input name="commentE" id="commentE" type="text" /><br>

	<div id="validation" class="report-section" >
		<div class="section-title TitleB">+ Validation details <!--<div id="includeCompulsory"><div class="compulsory">*</div></div>-->:
		</div>
		<div class="hidden" >
		<div class="grlsection-content ContentA">
			<div id="includeExperimental">
				Experimental conditions <input name="experimental_conditions" id="experimental_conditions" type="text" /><br>
				Primers <input name="primers" id="primers" type="text" /><br>
			</div>
			<div id="includeBioinformatics">
			<p id="auto" style="display: inline-block"> 
				<label>Fosmids <!--<div class="compulsory">*</div>--> </label>
				<input type="text" id="searchFosmids" name="searchFosmids" />
			</p><br>
				Results <!--<div class="compulsory">*</div>--> <input name="fosmids_results" id="fosmids_results" type="text" /><br>
				Comment <input name="commentB" id="commentB" type="text" /><br>
			</div>
			<div id="includeMethod">
				Please select a method
			</div>
		</div>
		<?php if ($_GET['o']!='add_val'){?>
		</div>
		<?php } ?>
	</div>

	<div id="individuals" class="report-section" >
		<div class="section-title TitleB">+ Individuals:
		</div>
		<div class="hidden">
		<div class="grlsection-content ContentA">
			Individuals <input type="file" name="individuals" id="individuals" /><br>
		</div>
		</div>
	</div>
	<div id="nogenotypes" class="report-section" >
		<div class="section-title TitleB">+ Frequency without genotypes:
		</div>
		<div class="hidden">
		<div class="grlsection-content ContentA">
			<font color='red'>Please be aware that this information will be displayed in the Frequency section of the Inversion report, but the following will not be available: Hardy-Weinberg test and genotype file for download. Also, data will not be averaged with other studies of the same population.</font><br><br>
			Population <select id='fng_population' name='fng_population'>
				<option value='null'>-Select-</option>
				<?php echo $fng_population; ?>
			</select><br>
			Analyzed individuals <input name="fng_individuals" id="fng_individuals" type="text" onchange="calculateFng(this)"/><br>
			Inverted alleles <input name="fng_invalleles" id="fng_invalleles" type="text" onchange="calculateFng(this)" /><br>
			Standard frequency <input name="fng_stdfreq" id="fng_stdfreq" type="text" onchange="calculateFng(this)" /><br>
			Inverted frequency <input name="fng_invfreq" id="fng_invfreq" type="text" onchange="calculateFng(this)" /><br>
		</div>
		</div>
	</div>
	<div id="addBreakpoints" class="report-section" >
		<div class="section-title TitleB">+ Manually curated breakpoints:
		</div>
		<div class="hidden">
		<div class="grlsection-content ContentA">
			Breakpoint 1 start <input name="bp1s" id="bp1s" type="text" /><br>
			Breakpoint 1 end <input name="bp1e" id="bp1e" type="text" /><br>
			Breakpoint 2 start <input name="bp2s" id="bp2s" type="text" /><br>
			Breakpoint 2 end <input name="bp2e" id="bp2e" type="text" /><br>
			Description <input name="description" id="description" type="text" /><br>
		</div>
		</div>
	</div>
	
	<input type="hidden" name="inv_id" id="inv_id" value="<?echo $id?>" />
	<input type="hidden" name="chr" id="chr" value="<?echo $chr['chr'] ?>" />
	<input type="submit" value="Add validation" />
	<!--<input type="button" value="Add validation"  onclick="submitNewValidation(this.form);" /> -->
	<input type="reset" value="Clear" /><br><br>
</form>

			</div>
			</div>


		<?}?>
		</div>
		</div>	
	</div>
	<div id="frequency" class="report-section" >
		<div class="section-title TitleA">+ Frequency  <!---------- FREQUENCY ------>
		</div>
		<div class="hidden">
		<div class="grlsection-content ContentA">
		<?php if ($echo_frequency != "") {?>
		<!--	<table id="validation_table">
			<thead>
			<tr>
				<td>Region</td>	<td>Population name</td>
				<td>Frequency</td>
			</tr>
			</thead>
			<tbody>
		-->
			<?php echo '<form name="frequency_graph" id="frequency_graph">'; ?>
			<?php echo $echo_frequency;?>
			<?php echo '</form>'; ?>
		<!--	</tbody>
			</table>
		-->
				
		<?php echo $NewGraphs;?>
		
<!--		<div id="chart_div0" style="width: 100px; height: 100px; display:inline-block"></div>
		<div id="chart_div1" style="width: 100px; height: 100px; display:inline-block"></div>
		<div id="chart_div2" style="width: 100px; height: 100px; display:inline-block"></div>
		<div id="chart_div3" style="width: 100px; height: 100px; display:inline-block"></div>
		<div id="chart_div4" style="width: 100px; height: 100px; display:inline-block"></div>
		<div id="chart_div5" style="width: 100px; height: 100px; display:inline-block"></div> -->
			<?php }
		else {echo 'No frequency studied';} ?>
		</div>	
		</div>	
	</div>
	<div id="breakpoints" class="report-section" >
		<div class="section-title TitleA">+ Breakpoints  <!---------- BREAKPOINTS ------>
		</div>
		<div class="hidden">
		<div class="grlsection-content ContentA">
			<table width='100%'>
				<tr><td class='title' width='18%'>Breakpoint 1</td><td><?php echo $r['chr'].':'.$r['bp1_start'].'-'.$r['bp1_end'];?></td>
                    <td class='title' width='18%'>Breakpoint 2<td><?php echo $r['chr'].':'.$r['bp2_start'].'-'.$r['bp2_end'];?></td></td>
                </tr>
 				
 				<?php if (($r['studyname'] !='') or ($_SESSION["autentificado"]=='SI')) { ?>
 				
                		<tr><td class='title'>Study</td><td colspan="3"><?php echo $r['studyname'];?></td></tr>
 
 				<?php }
 				
 				if (($r['description'] !='') or ($_SESSION["autentificado"]=='SI')) { ?> 

				<tr><td class='title'>Description</td><td colspan="3"><?php echo ucfirst($r['description']);?></td></tr>				

				<?php }
 				
 				if (($r['definition_method'] !='') or ($_SESSION["autentificado"]=='SI')) { ?> 
 
                		<tr><td class='title'>Definition method</td><td colspan="3"><?php echo $array_definitionmethod[$r['definition_method']];?></td></tr>

				<?php }
 				
 				if (($r['origin'] !='') or ($_SESSION["autentificado"]=='SI')) { ?> 

				<tr><td class='title'>Mechanism of origin</td>
				    <td id='inv_bp_origin<?php echo $id?>' colspan="3"><div id='DIVinv_bp_origin<?php echo $id?>'><?php echo ucfirst($r['origin']);?></div>
                            <?php if ($_SESSION["autentificado"]=='SI') { ?>	
                            <input type='button' value='Edit' class='right' onclick="updateTD('inv_bp_origin','<?php echo $id?>')"><?php } ?>
                    </td>
				</tr>
				
				<?php }

				if (($r['origin'] ='') or ($_SESSION["autentificado"]=='SI')) { ?> 
 
                		<tr><td class='title'>Mechanism of origin</td><td colspan="3"><?php echo $r['Mech']." (predicted by BreakSeq)";?></td></tr>

				<?php }

				if (($r['Flexibility'] !='') or ($_SESSION["autentificado"]=='SI')) { ?> 
 
                		<tr><td class='title'>Flexibility</td><td colspan="3"><?php echo $r['Flexibility'];?></td></tr>

				<?php }

				if (($r['GC'] !='') or ($_SESSION["autentificado"]=='SI')) { ?> 
 
                		<tr><td class='title'>GC content (%)</td><td colspan="3"><?php echo $r['GC'];?></td></tr>

				<?php }

				if (($r['Stability'] !='') or ($_SESSION["autentificado"]=='SI')) { ?> 
 
                		<tr><td class='title'>Stability</td><td colspan="3"><?php echo $r['Stability'];?></td></tr>

				<?php }


	if (($r['breakpoint_comments'] != '') or ($_SESSION["autentificado"]=='SI')) {
	
	echo "
	<tr>
		<td class='title'>Comments</td>
		<td colspan=3 id='comments_bp".$id."'><div  id='DIVcomments_bp".$id."'>".$r['breakpoint_comments']."</div>";
		
		
            	if ($_SESSION["autentificado"]=='SI') {
        		        		
        		echo "<input type='button' class='right' value='Edit' onclick=\"updateTD('comments_bp','".$id."')\" />";

            	}		
		
		echo "</td>
	</tr>
	
	";
	
	}
	

				if (($bp_seq_features !='') or ($_SESSION["autentificado"]=='SI')) {
					
					echo "<tr><td class='title'>Sequence features</td><td colspan='3'><b>Segmental duplications:</b><br/>";
					echo "	<table width='100%'><tr><td class='title'>Position SD1</td><td class='title'>Size (bp)</td>
						<td class='title'>Position SD2</td><td class='title'>Size (bp)</td>
						<td class='title'>Identity</td><td class='title'>Relative orientation</td></tr>";
					echo $bp_seq_features;
					echo "</table></td></tr>";
				}?>
				
			</table>
		</div>	
		</div>	
	</div>
<!--	<div id="segmental_duplication" class="report-section" >
		<div class="section-title">Segmental duplication  	 SEGMENTAL DUPLICATION ------>
<!--		</div>
		<div class="hidden">
		<div class="grlsection-content ContentA">
			<table id="validation_table">
			<thead>
			<tr>
				<td>Chromosome</td>	<td>Chromosome start</td>
				<td>Chromosome end</td>	<td>Strand</td>
				<td>Other start</td>	<td>Other end</td>
			</tr>
			</thead>
			<tbody>
				<?php echo $segmental_duplication;?>
			</tbody>
			</table>
		</div>	
		</div>	
	</div>
<br />		-->
	<div id="evolutionary_history" class="report-section" >
		<div class="section-title TitleA">+ Evolutionary history  <!---------- EVOLUTIONARY HISTORY ------>
		</div>
		<?php if ($_GET['o']!='add_evol'){?>
		<div class="hidden">
		<?php } ?>
		<div class="grlsection-content ContentA">
			<table width='100%'>
				<tr>
					<td class='title' width='18%'>Ancestral allele</td><td width='32%'><?php echo ucfirst($r['ancestral_orientation']);?></td>
					<td class='title' width='18%'>Age</td><td width='32%'><?php echo $echo_summary_age; //$r['age'] ?></td></tr>
				<tr>
					<td class='title' width='18%'>Derived allele</td><td width='32%'><?php 
						if(ucfirst($r['ancestral_orientation'])=='Standard'){echo 'Inverted';} 
                        elseif(ucfirst($r['ancestral_orientation'])=='Inverted'){echo 'Standard';}
                        elseif(ucfirst($r['ancestral_orientation'])!='Standard' and ucfirst($r['ancestral_orientation'])!='Inverted'){echo 'NA';}
                        elseif(ucfirst($r['ancestral_orientation'])=="<font color='grey'>NA</font>"){echo '<font color="grey">NA</font>';}
                        elseif(ucfirst($r['ancestral_orientation'])=="<font color='grey'>ND</font>"){echo '<font color="grey">ND</font>';}
                        //else{echo $r['ancestral_orientation'];}
                         ?></td>
					<td class='title' width='18%'>Origin</td><td width='32%'><?php if ($r['evo_origin'] != '') {echo ucfirst($r['evo_origin']);} else {echo '<font color="grey">ND</font>';} ?></td>
				</tr>
				
	<?php if (($r['comments_eh'] != '') or ($_SESSION["autentificado"]=='SI')) {
	
	echo "
	<tr>
		<td class='title'>Comments</td>
		<td colspan=3 id='comments_eh".$id."'><div  id='DIVcomments_eh".$id."'>".$r['comments_eh']."</div>";
		
		
            	if ($_SESSION["autentificado"]=='SI') {
        		        		
        		echo "<input type='button' class='right' value='Edit' onclick=\"updateTD('comments_eh','".$id."')\" />";

            	}		
		
		echo "</td>
	</tr>
	
	";
	
	}
	
	?>				
				
			</table>
			
			<div class="report-section" >
			<div class="section-title TitleA">+ Orientation in other species
			</div>
			<div class="hidden">
			<div class="grlsection-content ContentB">
			<?php if ($echo_evolution_orientation!=''){ ?>
				<table width='100%' id='table_evol_orientation_sp'>  
					<tr><td class='title' width='25%'>Species</td><td class='title' width='25%'>Orientation</td>
					<td class='title' width='25%'>Method</td><td class='title' width='25%'>Study</td></tr>
					<?php echo $echo_evolution_orientation; ?> 
                    <!--EJEMPLO:
					<tr><td><em>Pan troglodytes</em></td><td>STD</td><td>Genome sequencing</td><td>Caceres et al.</td></tr>
					<tr><td><em>Macaca multatta</em></td><td>STD</td><td>FISH</td><td>Caceres et al.</td></tr>
                     -->
				</table>
			<?php } else {echo "<div id='evol_orientation_sp'>Not defined</div>";}?>
			</div>
			</div>
			</div>
            <div class="report-section" >
			<div class="section-title TitleA">+ Age
			</div>
			<div class="hidden">
			<div class="grlsection-content ContentB">
			<?php if ($echo_evolution_age!=''){ ?>
				<table width='100%' id='table_evol_age'>
					<tr><td class='title' width='20%'>Age</td><td class='title' width='40%'>Method</td>	<td class='title' width='40%'>Study</td></tr>
					<?php echo $echo_evolution_age; ?> 
                    <!--EJEMPLO: 
					<tr><td>1 My</td><td>Divergence with chimpanzee</td><td>Caceres et al.</td></tr>
					<tr><td>0,5 My</td><td>Polymorphism data</td><td>Caceres et al.</td></tr>
                    -->
				</table>
			<?php } else {echo "<div id='evol_age'>Not defined</div>";}?>
			</div>
			</div>
			</div>
            <div class="report-section" >
			<div class="section-title TitleA">+ Origin
			</div>
			<div class="hidden">
			<div class="grlsection-content ContentB">
			<?php if ($echo_evolution_origin!=''){ ?>
				<table width='100%' id='table_evol_origin'>
					<tr><td class='title' width='20%'>Origin</td><td class='title' width='40%'>Method</td><td class='title' width='40%'>Study</td></tr>
					<?php echo $echo_evolution_origin; ?>
                    <!--EJEMPLO: 
					<tr><td>Monophiletic</td><td>Xxxx</td><td>Caceres et al.</td></tr>
                    -->
				</table>
			<?php } else {echo "<div id='evol_origin'>Not defined</div>";}?>
			</div>
			</div>
			</div>
    <!--        <div class="report-section" >
			<div class="section-title TitleA">+ Nucleotide variation
			</div>
			<div class="hidden">
			<div class="grlsection-content ContentB">
				Not defined
			</div>
			</div>
			</div>
            <div class="report-section" >
			<div class="section-title TitleA">+ Selection tests
			</div>
			<div class="hidden">
			<div class="grlsection-content ContentB">
				Not defined
			</div>
			</div>
			</div>
-->

		<!-- Modify evolutionary information ....................................................................  -->
		<?php if ($_SESSION["autentificado"]=='SI' && ($r['status']!='Withdrawn' && $r['status']!='withdrawn')){?>
			<div class="report-section" >
            <div class="section-title TitleB">+ Add evolutionary information
			</div>
			<div class="hidden">
			<div class="grlsection-content ContentA"> 

<!--<form name="new_evol_info" method="post" action="php/add_evol_info.php" onsubmit="showEvolInfoResults()" onsubmit="return validate_evol()" enctype="multipart/form-data" target="test" >-->
<form name="new_evol_info" method="post" enctype="multipart/form-data"  >
	Type of information <div class="compulsory">*</div> <select id="evol_type" name="evol_type"  onchange="changeEvolForm(this,'evolInfoForm')"> <option value="" id='evol_type_null'>-Select-</option>
 	<option value="evolution_orientation" >Orientation in other species</option>
		<option value="evolution_age" >Age</option>
		<option value="evolution_origin" >Origin</option></select>

	<div id="evolInfoForm"></div>

	<input type="hidden" name="inv_id" value="<?echo $id?>" />
	<!--<input type="submit" value="Add information" />-->
	<input type="button" value="Add Evolutionary Information"  onclick="submitNewEvolInfo(this.form);" />
	<input type="reset" value="Clear" /><br><br>
</form>

	<div id='add_evol_info_result'>
	</div>
			</div>
			</div>
            </div>
		<?}?>

<!--			<table id="validation_table">
			<thead>
			<tr>
				<td>Name</td>	<td>Gender</td>
				<td>Orientation</td>
			</tr>
			</thead>
			<tbody>
				<?php echo $evolution;?>
			</tbody>
			</table>
-->
		<?php if ($_GET['o']!='add_evol'){?>
		</div>
		<?php } ?>
		</div>	
	</div>
	<div id="functional_effect" class="report-section" >
		<div class="section-title TitleA">+ Functional effects  <!---------- FUNCTIONAL EFFECT ------>
		</div>
		<div class="hidden">
		<div class="grlsection-content ContentA">

			<div id='functional_effectAjax'>
			<?php 
			
			if ($echo_functional_effect != '') {
				echo $echo_functional_effect;
			} else {
				echo "No genes disrupted by the inversion breakpoints";
			}
			
			?>
			</div>

            <div class="report-section" >
			<div class="section-title TitleA">+ Inversion phenotypical effects
			</div>
			<div class="hidden">
			<div class="grlsection-content ContentB">
			<?php if ($echo_phenotypical_effect!=''){ ?>
				<table width='100%' id='table_phenotypical_effect'> 
                <!--EJEMPLO: 
					<tr><td class='title'>Effect</td><td class='title'>Study</td></tr>
					<tr><td>Associated to increase fertility</td><td>Steffanson et al</td></tr>
                    -->
					<? echo $echo_phenotypical_effect; ?>
				</table>
			<?php } else {echo "<div id='phenotypical_effect'>Not defined</div>";}?>
			</div>
			</div>
			</div>

			<!-- Modify functional effect ....................................................................  -->
			<?php if ($_SESSION["autentificado"]=='SI' && ($r['status']!='Withdrawn' && $r['status']!='withdrawn')){?>
				<div class="report-section" >
                <div class="section-title TitleB">+ Add functional effects
				</div>
				<div class="hidden">
				<div class="grlsection-content ContentA"> 

<!--<form name="functEff_form" method="post" action="php/add_functional_effect.php" onsubmit="return validate_funct()" enctype="multipart/form-data"  >-->
<form name="functEff_form" method="post" enctype="multipart/form-data"  >
		Type of effect <div class="compulsory">*</div> <select id="effect_type" name="effect_type"  onchange="changeFuncForm(this,'functEffForm')"> <option value="" selected='selected' id='effect_type_null'>-Select-</option>
			<option value="eff_genomic" >Genomic</option>
			<option value="eff_phenotypic" >Phenotypic</option>
			</select>

		<div id="functEffForm"></div>

		<input type="hidden" name="inv_id" value="<?echo $id?>" />
		<!--<input type="submit" value="Add information" />-->
		<!--<input type='button' value="Add information"  onclick="formget(this.form, 'php/ajaxAdd_funct_effect.php', 'function')" />-->
		<input type="button" value="Add information"  onclick="submitNewFunctionalInfo(this.form);" />
		<input type="reset" value="Clear" /><br><br>
	</form>

		<div id='add_evol_info_result'>
		</div>
				</div>
				</div>
            </div>
			<?}?>

<!--			<table id="validation_table">
			<thead>
			<tr>
				<td>Gene relation</td>	<td>Functional effect</td>
				<td>Symbol</td>
			</tr>
			</thead>
			<tbody>
				<?php echo $genomic_effect;?>
			</tbody>
			</table>
-->
		</div>	
		</div>	
	</div>
	<?php 						//<!---------- REPORT HISTORY ------>
			echo '
		<div id="inversion_history" class="report-section" >';
//			if ($r['status']!='Withdrawn' && $r['status']!='withdrawn'){
				echo '<div class="section-title TitleA">+ Report history  
				</div>
				<div class="hidden">';
//			} 
//			else {
//				echo '<div class="section-title">- Report history  
//				</div>';
//			}
			echo'					
			<div class="grlsection-content ContentA">
			'.$history.$bp_history;
//echo "<a href=\"php/breakpoints_history.php?id=$id\" onclick=\"return hs.htmlExpand(this, {objectType: 'iframe' })\" >See the historial</a>";
/*			echo '
				<div class="section-title TitleA">- Breakpoints history  
				</div>

				<div class="grlsection-content ContentB"> <table >
					<thead>
					<tr>
						<td class="title">Breakpoint1</td><td class="title">Breakpoint2</td><td class="title">Description</td>
						<td class="title">Definition method</td><td class="title">Date</td>
					</tr>
					</thead>
					<tbody>
						'.$bp_history.'
					</tbody>
					</table> 
				</div>';
*/
			echo '</div>
			</div>';	
//			if ($r['status']!='Withdrawn' && $r['status']!='withdrawn'){
				echo '</div>';
//			} 
//		echo '<br />';
//		}
	?>

	<?if ($_SESSION["autentificado"]=='SI'  && ($r['status']!='Withdrawn' && $r['status']!='withdrawn')){?>
	<?include('php/db_conexion.php');
$sql_inversion_status="select distinct status from inversions where status is not null order by status;";
$result_inversion_status = mysql_query($sql_inversion_status) or die("Query fail: " . mysql_error());
while($thisrow = mysql_fetch_array($result_inversion_status)){
	$inversion_status_option.="<option value=\"".$thisrow["status"]."\">".$thisrow["status"]."</option>";
}?>
	<div id="advanced_edition" class="report-section" >
		<div class="section-title TitleB">+ Advanced inversion edition <!---------- ADVANCED EDITION ------>
		</div>

		<div class="hidden">
		<div class="grlsection-content ContentA">
			<div class="section-title TitleB">+ Merge current inversion with another
			</div>
			<div class="hidden">
			<div class="grlsection-content ContentA">
				<form name="merge" action="php/add_merge_inversions.php" method="post" >
				Inversion/s to be merged<br> <select name="inv2[]" multiple="multiple"><?echo $inv2?>
				</select>
				<input type="hidden" name="inv1" value="<?echo $id?>" /><br>
				Status for the new inversion <select name="status"><?echo $inversion_status_option?></select>
				<input type="submit" value="Merge" />
				</form>
			</div>	
			</div>	
			<br />
			<div class="section-title TitleB">+ Split current inversion in two others
			</div>
			<div class="hidden">
			<div class="grlsection-content ContentA">
<!--<a href="php/split_inversions.php?q=<?echo $id?>"  onclick="return hs.htmlExpand(this, {objectType: 'iframe' })">Split this inversion</a> -->
				<form name="split_validation" method="post" action="php/add_split_inversions.php">
					<table>
					 <thead>
					  <td>Predictions</td>
					  <td>New Inversion 1</td>
					  <td>New Inversion 2</td>
					 </thead>
					<?php 
					if ($predictions == "" || $predictions == NULL) {
					 echo "<tr><td colspan=\"3\">No predictions found</td></tr>";
					}
					else {echo $predictions;}
					?>
					 <tr></tr>
					 <thead>
					  <td>Validations</td>
					  <td>New Inversion 1</td>
					  <td>New Inversion 2</td>
					 </thead>
					<?php 
					if ($validations == "" || $validations == NULL) {
					 echo "<tr><td colspan=\"3\">No validations found</td></tr>";
					}
					else {echo $validations;}
					?>
					<thead>
					  <td>Status</td>
					  <td>New Inversion 1</td>
					  <td>New Inversion 2</td>
					 </thead>
<tr><td></td><td> <select name="status1"><?echo $inversion_status_option?></td></select></td><td><select name="status2"><?echo $inversion_status_option?></td></tr></select></td>
					</table>
					<input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Split&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" />
					<input type="hidden" name="inv_id" value="<?echo $id?>" />
					<input type="reset" value="Clear" /><br><br>
				</form>
			</div>	
			</div>	
		<br>
		</div>	
		</div>	
	</div>
	<br />
	<?}?>

			

			<div class="clear"></div>
		</div> <!--end Report-->
		<div id="foot">
<?php include('php/footer.php');?>
    	</div>
	</div><!--end Wrapper-->
</body>
</html>
