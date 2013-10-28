<?php 
$creator='
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
-->';
$head="<head>
<link href='http://fonts.googleapis.com/css?family=Ubuntu:regular,italic,bold,bolditalic' rel='stylesheet' type='text/css'>
<link rel='shortcut icon' href='http://invfestdb.uab.cat/img/InvFEST_ico.png'>
<title>InvFEST: Human Polymorphic Inversion DataBase</title>
<meta http-equiv='content-type' content='text/html;charset=utf-8' />







<script type=\"text/javascript\" src=\"js/jquery.min.js\"></script>
<script type=\"text/javascript\">
$(document).ready(function() {
        // Tooltip only Text
        $('.masterTooltip').hover(function(){
                // Hover over code
                var title = $(this).attr('title');
                $(this).data('tipText', title).removeAttr('title');
                $('<p class=\"tooltip\"></p>')
                .text(title)
                .appendTo('body')
                .fadeIn('slow');
        }, function() {
                // Hover out code
                $(this).attr('title', $(this).data('tipText'));
                $('.tooltip').remove();
        }).mousemove(function(e) {
                var mousex = e.pageX + 20; //Get X coordinates
                var mousey = e.pageY + 10; //Get Y coordinates
                $('.tooltip')
                .css({ top: mousey, left: mousex })
        });
});
</script>

<script type='text/javascript' src='js/myAJAXlib.js'></script>
	<script type='text/javascript' src='js/jquery.js'></script>
	<script type='text/javascript' src='js/jquery.tablesorter.min.js'></script>
	<link rel='stylesheet' type='text/css' href='css/style.css' />
	<link rel='stylesheet' type='text/css' href='css/report.css' />

	<!-- Para el highslide: -->
	<link href='css/css_highslide.css' rel='stylesheet' type='text/css' />

	<script type='text/javascript' src='js/highslide_complete.js'></script>
	<script type='text/javascript'>
	//                document.write('<style type=\"text/css\">');    
	//                document.write('div.domtab div{display:none;}<');
	//                document.write('/s'+'tyle>');     
	</script>
	<script type='text/javascript'>    
	    hs.graphicsDir = 'img/highslide_graphics/';
	    hs.outlineType = 'rounded-white';
	    hs.outlineWhileAnimating = true;
	</script>

	<script type='text/javascript'>

function pad(number, length) {
   
    var str = '' + number;
    while (str.length < length) {
        str = '0' + str;
    }
   
    return str;

}
	
	// add parser through the tablesorter addParser method 
	$.tablesorter.addParser({ 
		// set a unique id 
		id: 'size', 
		is: function(s) { 
			// return false so this parser is not auto detected 
			return false; 
		}, 
		format: function(s) { 
			// format your data for normalization 
			return s.replace(/,/g,''); 
		}, 
		// set type, either numeric or text 
		type: 'numeric' 
	}); 

	$.tablesorter.addParser({ 
		// set a unique id 
		id: 'status', 
		is: function(s) { 
			// return false so this parser is not auto detected 
			return false; 
		}, 
		format: function(s) { 
			// format your data for normalization 
			return s.replace('Validated','1').replace('Predicted','2').replace('Unreliable prediction','3').replace('False','4'); 
		}, 
		// set type, either numeric or text 
		type: 'text' 
	});

	$.tablesorter.addParser({ 
		// set a unique id 
		id: 'effect', 
		is: function(s) { 
			// return false so this parser is not auto detected 
			return false; 
		}, 
		format: function(s) { 
			// format your data for normalization 
			return s.replace('Breaks two genes','1').replace('Breaks one gene','2').replace('Breaks different exons and introns of a gene','3').replace('Breaks a region within an exon of a gene','4').replace('Breaks a region within an intron of a gene','5').replace('Intergenic','6').replace('NA','7'); 
		}, 
		// set type, either numeric or text 
		type: 'text' 
	});

	$.tablesorter.addParser({ 
		// set a unique id 
		id: 'position', 
		is: function(s) { 
			// return false so this parser is not auto detected 
			return false; 
		}, 
		format: function(s) { 
			// format your data for normalization 
		            var myposition = s.match(/^chr([^:]+):(\d+)-(\d+)$/);
		            var mychrom = myposition[1];
		            mychrom = mychrom.replace('X',997).replace('Y',998).replace('M',999);
		            var mystart = myposition[2];
		            var myend = myposition[3];

			    var mynewchrom = pad(mychrom,20);
			    var mynewstart = pad(mystart,20);
			    var mynewend = pad(myend,20);
			    
			    var mynewposition = mynewchrom + mynewstart + mynewend;

			    return mynewposition;
		}, 
		// set type, either numeric or text 
		type: 'text' 
	});

		$(document).ready(function(){
			$('.section-title').click(function(){			//toggle when click title
				$(this).next('.section-content').slideToggle(600);
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

		$('#sort_table').tablesorter({headers: {1:{sorter:'position'},2:{sorter:'size'},3:{sorter:'status'},5:{sorter:'effect'}}}); 
		});

	</script>




	<script type='text/javascript'>
		$(document).ready(function(){
count=0;
			$('#add_filter').click(addField);
		//	$('#add_filter').click(displayFields('',count+1)); //hide fields when showing
		//	$('select').change(displayFields('',count+1)); //when select changes, hide or show corresponding fields
		//	displayFields(); //hide or show corresponding fields

		});

		var selectBooleanA= '<select name=\"boolean[]\" id=\"boolean';
		var selectBooleanB= '\"><option selected value=\"=\">is</option><option value=\"!=\">is not</option></select>';
		var selectSizeA= '<select name=\"boolean[]\" id=\"boolean';
		var selectSizeB= '\"><option selected value=\">\">></option><option value=\"<\"><</option></select>';
		var selectYNA= '<select name=\"boolean[]\" id=\"boolean';
		var selectYNB= '\"><option selected value=\"yes\">yes</option><option value=\"no\">no</option></select>';
		var selectAOA= '<select name=\"boolean[]\" id=\"boolean';
		var selectAOB= '\"><option selected value=\"direct\">direct</option><option value=\"inverted\">inverted</option></select>';
		var selectFieldA = '<select name=\"field[]\" id=\"field';
		var selectFieldB = '\" onchange=\"displayFields(this)\"><option selected value=\"foo\" disabled=\"disabled\">-Select-</option><option value=\"size\">Size</option><option value=\"inv_status\">Inversion Status</option><option value=\"val_study\">Validation Study</option><option value=\"val_method\">Validation Method</option><option value=\"val_status\">Validation Status</option><option value=\"val_fosmids\">Validation with Fosmids</option><option value=\"val_ind\">Validated Individuals</option><option value=\"freq_pop\">Frequency in Populations</option><option value=\"seg_dup\">Segmental Duplications in Breakpoints</option><option value=\"aff_gene\">Affected Gene</option><option value=\"inv_sp\">Ancestral Orientation</option></select>';

//<option value=\"size\">Size</option>
var sizeA='<input text=\"text\" name=\"field_value[]\" id=\"size';
var sizeB='\" size=25>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Size] filters with [AND]\" width=\"18\"/>&nbsp;';

//<option value='inv_status'>Inversion Status</option>
var invStatusA= '<select name=\"field_value[]\" id=\"inversion_status';
var invStatusB= '\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $inversion_status_option</select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Inversion Status] filters with [OR]\" width=\"18\"/>&nbsp;';

//<option value='val_study'>Validation Study</option>
var valStudyA = '<select name=\"field_value[]\" id=\"research';
var valStudyB = '\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $research_name_option</select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Validation Study] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

//<option value='val_method'>Validation Method</option>
var valMethodA = '<select name=\"field_value[]\" id=\"validation_method';
var valMethodB = '\" > <option value=\"\" selected disabled=\"disabled\">-Select-</option> $validation_method_option</select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Validation Method] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

//<option value='val_status'>Validation Status</option>
var valStatusA = '<select name=\"field_value[]\" id=\"validation_status';
var valStatusB = '\" > <option value=\"\" selected disabled=\"disabled\">-Select-</option> $validation_status_option</select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Validation Status] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

//<option value='val_fosmids'>Validation with Fosmids</option>
var valFosmidsA='<input type=\"hidden\" value=\"\" name=\"field_value[]\" id=\"validation_fosmids';
var valFosmidsB='\" size=25>';

//<option value='val_ind'>Validated Individuals</option>
var valIndividualsA = '<select name=\"field_value[]\" id=\"individual';
var valIndividualsB = '\" > <option value=\"\" selected disabled=\"disabled\">-Select-</option> $individuals_option</select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Validated Individuals] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

//<option value='freq_pop'>Frequency in Populations</option>
var freqPopA='<input text=\"text\" name=\"field_value[]\" id=\"freq_pop';
var freqPopB='\" size=25>';
var freqPopA2 = '<select name=\"field_value2[]\" id=\"freq_pop2';
var freqPopB2 = '\" > <option value=\"\" selected disabled=\"disabled\">-Select-</option>  $population_option  </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Frequency in Populations] filters with [AND]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

//<option value='seg_dup'>Segmental Duplications in Breakpoints</option>
var segDupA='<input type=\"hidden\" value=\"\" name=\"field_value[]\" id=\"seg_dup';
var segDupB='\" size=25>';

//<option value='aff_gene'>Affected Gene</option>
var affGeneA='<input text=\"text\" name=\"field_value[]\" id=\"aff_gene';
var affGeneB='\" size=25>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Affected Gene] filters with [OR]   ///   Will retrieve inversions that [affect genes] only!\" width=\"18\"/>&nbsp;';

//<option value='inv_sp'>Ancestral Orientation</option
var inversionsA='<input type=\"hidden\" value=\"\" name=\"field_value[]\" id=\"inv';
var inversionsB='\" size=25>';

var field_value2_hiddenA='<input type=\"hidden\" value=\"\" name=\"field_value2[]\" id=\"field_value2_hidden';
var field_value2_hiddenB='\" size=25>';

		function displayFields(field) {
var pos=field.id.substring(5);
if (field.value =='size') { 
	$('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectSizeA+pos+selectSizeB+sizeA+pos+sizeB);
 }
else if (field.value =='inv_status') { 
	$('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+invStatusA+pos+invStatusB);
 }
else if (field.value =='val_study') { 
	$('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+valStudyA+pos+valStudyB);
 }
else if (field.value =='val_method') { 
	$('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+valMethodA+pos+valMethodB);
 }
else if (field.value =='val_status') { 
	$('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+valStatusA+pos+valStatusB);
 }
else if (field.value =='val_fosmids') { 
	$('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectYNA+pos+selectYNB+valFosmidsA+pos+valFosmidsB);
 }
else if (field.value =='val_ind') { 
	$('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+valIndividualsA+pos+valIndividualsB);
 }
else if (field.value =='freq_pop') { 
	$('#'+pos).html(freqPopA2+pos+freqPopB2+selectSizeA+pos+selectSizeB+freqPopA+pos+freqPopB);
 }
else if (field.value =='seg_dup') { 
	$('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectYNA+pos+selectYNB+segDupA+pos+segDupB);
 }
else if (field.value =='aff_gene') { 
	$('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+affGeneA+pos+affGeneB);
 }
else if (field.value =='inv_sp') { 
	$('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectAOA+pos+selectAOB+inversionsA+pos+inversionsB);
 }
}


		function addField() {
			count++;

		//	$('#search').append(selectBooleanA+count+selectBooleanB+selectFieldA+count+selectFieldB+
		//		invStatusA+count+invStatusB+valStudyA+count+valStudyB+valMethodA+count+valMethodB+
		//		valStatusA+count+valStatusB+valIndividualsA+count+valIndividualsB+
		//		' <input text=\"text\" name=\"search_field2[]\" id=\"search_field2'+count+'\" size=25><br>');
			$('#search').append('<div id=\"'+count+count+'\" style=\"display:inline\" >'+selectFieldA+count+selectFieldB+'<div id=\"'+count+'\" style=\"display:inline\" ></div><input type=\"image\" name=\"remove_filter\" id=\"remove_filter\" src=\"img/cross2.png\" alt=\"Remove filter\" onclick=\"remove('+count+count+')\" width=\"18\" /><br /></div>');
			
		// <input type=\"button\" name=\"remove_filter\" class=\"button\" id=\"remove_filter\" value=\"-\" onclick=\"remove('+count+count+')\"/>
		
		}
		
		function remove(id) {
		//	window.open(id)
    		
    			return (elem=document.getElementById(id)).parentNode.removeChild(elem);
		}


	</script>
</head>

";

$id_head="
  <div id='head'>Human Inversion DataBase
   <div id='login'>";
if ($_SESSION["autentificado"]=='SI'){
	$id_head.='<a href="php/logout.php?origin=index">LOGOUT</a>';
} else {
     $id_head.="<a id='login2' href='php/login.php?origin=index' onclick=\"return hs.htmlExpand(this, {objectType: 'iframe', width: 300, preserverContent: false })\" >Login</a>";
}
$id_head.="
   </div>
  </div>";

$search_inv = "
  <div id='main' class='report-section' >
   <div class='section-title TitleA'>- Search inversions
   </div>
   <!-- <div class='hidden'> -->
   <div class='section-content ContentA'>
   <!-- <div class='section-content'> -->
    <form action='search_invdb2.php' method='POST' id='searchInvFEST'>
     <p id='search'>
      <b>Query position:</b>&nbsp;&nbsp;<select id='assembly' name='assembly'> 
      				<option value='hg18' selected>Mar. 2006 (NCBI36/hg18)</option>
				<option value='hg19'>Feb. 2009 (GRCh37/hg19)</option>		
			</select>   <!--   <a href='#assembly'><img src=\"img/alert.png\" width=\"18\"/></a>   -->
      &nbsp;<input text='text' id='search_field' name='search_field' size=50 onfocus='if(this.value == \"enter position, inversion ID or gene symbol\"){this.value = \"\";}' onblur='if(this.value == \"\"){this.value=\"enter position, inversion ID or gene symbol\";}' value='enter position, inversion ID or gene symbol'/>

<!--      &nbsp;<input type='image' src='img/queryInvFEST.png' alt='Search' height='23'> 
      &nbsp;<a href='search_invdb2.php'><img src='img/listAll.png' alt='List All' height='23'></a>    -->  

      <a href='#' onclick='document.searchInvFEST.submit()'><button class='default'>Search</button></a>     
      <a href='search_invdb2.php'><button class='default'>List All</button></a>     
      
      <br /></p>
     <p id='addfilter'>
  <!--    <input type='button' name='add_filter' class='button' id='add_filter' value='Add filter' />   -->
      <a name='add_filter' id='add_filter' onclick='#add_filter'><font color='#1c4257'>< Add filter ></font></a>
     </p>
    </form>
   <!-- </div> -->
   </div>
  </div>
";


   #   <input type='submit' name='submit_btn' class='button' id='submit_btn' value='Search' />
 #  <BUTTON name='submit' value='submit' type='submit'>
 #   <IMG src='img/search.png' alt='Search' width='16'> Search inversions</BUTTON>
#       <IMG src='img/search.png' alt='Search' width='23' onclick='Javascript:document.getElementById(\"searchInvFEST\").submit();'>


$add_pred='<div class="report-section">
  <div class="section-title TitleB">- Add inversion prediction
  </div>
  <div class="section-content ContentA">
    <form name="add_pred" action="php/add_prediction.php" method="post"> 
    <!--Name: <input type="text" id="pred_id" name="pred_id"/><br />-->
	<p style="display:inline-block"><a class="highslide-resize" href="php/new_study.php?&t=pred" ';
	$add_pred.= "onclick=\"return hs.htmlExpand(this, {objectType: 'iframe', objectHeight:200,  objectWidth:1000 })\">";
	$add_pred.= 'Add a new study</a></p><br />
	Chromosome: <div class="compulsory">*</div> <select id="pred_chr" name="pred_chr"> <option value="" selected>-Select-</option>
		<option value="chr1">1</option>		<option value="chr2">2</option>		<option value="chr3">3</option>
		<option value="chr4">4</option>		<option value="chr5">5</option>		<option value="chr6">6</option>
		<option value="chr7">7</option>		<option value="chr8">8</option>		<option value="chr9">9</option>
		<option value="chr10">10</option>	<option value="chr11">11</option>	<option value="chr12">12</option>
		<option value="chr13">13</option>	<option value="chr14">14</option>	<option value="chr15">15</option>
		<option value="chr16">16</option>	<option value="chr17">17</option>	<option value="chr18">18</option>
		<option value="chr19">19</option>	<option value="chr20">20</option>	<option value="chr21">21</option>
		<option value="chr22">22</option>	<option value="chrX">X</option>		<option value="chrY">Y</option>
		<option value="chrM">MT</option>
	</select><br />

	Breakpoint 1 start <div class="compulsory">*</div> <input type="text" id="pred_bp1s" name="pred_bp1s" /><br />
	Breakpoint 1 end <div class="compulsory">*</div> <input type="text" id="pred_bp1e" name="pred_bp1e" /><br />
	Breakpoint 2 start <div class="compulsory">*</div> <input type="text" id="pred_bp2s" name="pred_bp2s" /><br />
	Breakpoint 2 end <div class="compulsory">*</div> <input type="text" id="pred_bp2e" name="pred_bp2e" /><br />
	Study Name: <div class="compulsory">*</div> <select id="pred_study_name" name="pred_study_name" > <option value="" selected>-Select-</option>
		'.$research_name_user.'
	</select> <br />
	<br />

	<input type="submit" name="submit" class="button" id="submit_pred" value="Add prediction" />
    </form>
  </div>
  </div>
';


