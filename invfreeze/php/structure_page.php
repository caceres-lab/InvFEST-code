<?php
/******************************************************************************
	STRUCTURE_PAGE.PHP

	Contains predefined structure HTML sections and PHP variables that can be used in any other PHP page just including the structure_page.php
*******************************************************************************/

    $creator = 
	    '<!--
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

    // **************************************************************************** //
    //	HEAD: HTML header, common for all pages except 'report'
    // **************************************************************************** //
    $head = file_get_contents('html/header.html');


    // This script is only used within the search page(s) and, since it uses PHP variables, cannot be nested in the HTLM head-file.
    
    $head_search = "
        <!-- **************************************************************************** -->
	    <!-- Include the script to add filters to the inversions' search -->
	    <script>

		    $(document).ready(function(){
			    count=0;
			    $('#add_filter').click(addField);

			    /*
			    $('#add_filter').click(displayFields('',count+1)); // Hide fields when showing
			    $('select').change(displayFields('',count+1)); // When select changes, hide or show corresponding fields displayFields(); //Hide or show corresponding fields
			    */
		    });

		    var selectBooleanA= '<select name=\"boolean[]\" id=\"boolean';
		    var selectBooleanB= '\"><option selected value=\"=\">is</option><option value=\"!=\">is not</option></select>';
			var selectBooleanC= '\"><option selected value=\" like \">is</option><option value=\" not like \">is not</option></select>';
		
		    var selectSizeA= '<select name=\"boolean[]\" id=\"boolean';
		    var selectSizeB= '\"><option selected value=\">\"></option><option value=\"<\"></option></select>';
		
		    var selectYNA= '<select name=\"boolean[]\" id=\"boolean';
		    var selectYNB= '\"><option selected value=\"yes\">yes</option><option value=\"no\">no</option></select>';
		
		    var selectAOA= '<select name=\"boolean[]\" id=\"boolean';
		    var selectAOB= '\"><option selected value=\"direct\">direct</option><option value=\"inverted\">inverted</option></select>';
		
		    var selectFieldA='<select name=\"field[]\" id=\"field';
		    var selectFieldB='\" onchange=\"displayFields(this)\"><option selected value=\"foo\" disabled=\"disabled\">-Select-</option><option value=\"size\">Size</option><option value=\"inv_status\">Inversion Status</option><option value=\"pred_name\">Prediction Name</option><option value=\"pred_study\">Prediction Study</option><option value=\"pred_method\">Prediction Method</option><option value=\"pred_ind\">Predicted Individuals</option><option value=\"val_study\">Validation Study</option><option value=\"val_method\">Validation Method</option><option value=\"val_status\">Validation Status</option><option value=\"val_fosmids\">Validation with Fosmids</option> <option value=\"val_ind\">Validated Individuals</option><option value=\"freq_pop\">Frequency in Populations</option><option value=\"seg_dup\">Segmental Duplications in Breakpoints</option><option value=\"aff_gene\">Affected Gene</option><option value=\"inv_sp\">Ancestral Orientation</option></select>';
		

	    //	<option value=\"size\">Size</option>
		    var sizeA='<input text=\"text\" name=\"field_value[]\" id=\"size';
		    var sizeB='\" size=25>&nbsp;in Mb<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Size] filters with [AND]\" width=\"18\"/>&nbsp;';

	    //	<option value='inv_status'>Inversion Status</option>
		    var invStatusA= '<select name=\"field_value[]\" id=\"inversion_status';
		    var invStatusB= '\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $inversion_status_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Inversion Status] filters with [OR]\" width=\"18\"/>&nbsp;';

		//	<option value='pred_name'>Prediction Name</option>
		    var predNameA='<input text=\"text\" name=\"field_value[]\" id=\"pred_name';
		    var predNameB='\" size=25>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Prediction Name] filters with [OR]   ///   Will retrieve inversions that [affect genes] only!\" width=\"18\"/>&nbsp;';


		//	<option value='pred_study'>Prediction Study</option>
		    var predStudyA='<select name=\"field_value[]\" id=\"research';
		    var predStudyB='\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $research_name_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Prediction Study] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';
       
        //	<option value='pred_method'>Prediction Method</option>
		    var predMethodA='<select name=\"field_value[]\" id=\"prediction_method';
		    var predMethodB='\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $prediction_method_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Prediction Method] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

	    //	<option value='val_study'>Validation Study</option>
		    var valStudyA='<select name=\"field_value[]\" id=\"research';
		        var valStudyB='\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $research_name_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Validation Study] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

	    //	<option value='val_method'>Validation Method</option>
		    var valMethodA='<select name=\"field_value[]\" id=\"validation_method';
		        var valMethodB='\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $validation_method_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Validation Method] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';
       
        //	<option value='pred_ind'>Predicted Individuals</option>
	    	var predIndividualsA='<select name=\"field_value[]\" id=\"prediction_individual';
	        var predIndividualsB='\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $individuals_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Predicted Individuals] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

	    //	<option value='val_status'>Validation Status</option>
		    var valStatusA='<select name=\"field_value[]\" id=\"validation_status';
		    var valStatusB='\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $validation_status_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Validation Status] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

	    //	<option value='val_fosmids'>Validation with Fosmids</option>
		    var valFosmidsA='<input type=\"hidden\" value=\"\" name=\"field_value[]\" id=\"validation_fosmids';
		    var valFosmidsB='\" size=25>';

	    //	<option value='val_ind'>Validated Individuals</option>
		    var valIndividualsA='<select name=\"field_value[]\" id=\"individual';
		        var valIndividualsB='\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $individuals_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Validated Individuals] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

	    //	<option value='freq_pop'>Frequency in Populations</option>
		    var freqPopA='<input text=\"text\" name=\"field_value[]\" id=\"freq_pop';
		    var freqPopB='\" size=25>';
		    var freqPopA2='<select name=\"field_value2[]\" id=\"freq_pop2';
		        var freqPopB2='\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $population_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Frequency in Populations] filters with [AND]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

	    //	<option value='seg_dup'>Segmental Duplications in Breakpoints</option>
		    var segDupA='<input type=\"hidden\" value=\"\" name=\"field_value[]\" id=\"seg_dup';
		    var segDupB='\" size=25>';

	    //	<option value='aff_gene'>Affected Gene</option>
		    var affGeneA='<input text=\"text\" name=\"field_value[]\" id=\"aff_gene';
		    var affGeneB='\" size=25>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Affected Gene] filters with [OR]   ///   Will retrieve inversions that [affect genes] only!\" width=\"18\"/>&nbsp;'; 

	    //	<option value='inv_sp'>Ancestral Orientation</option
		    var inversionsA='<input type=\"hidden\" value=\"\" name=\"field_value[]\" id=\"inv';
		    var inversionsB='\" size=25>';

		    var field_value2_hiddenA='<input type=\"hidden\" value=\"\" name=\"field_value2[]\" id=\"field_value2_hidden';
		    var field_value2_hiddenB='\" size=25>';

		    function displayFields(field) {
			    var pos=field.id.substring(5);
			    if (field.value== 'size') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectSizeA+pos+selectSizeB+sizeA+pos+sizeB);
			    }
			    else if (field.value== 'inv_status') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+invStatusA+pos+invStatusB);
			    }
			    
			    else if (field.value== 'pred_name') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+predNameA+pos+predNameB);
			    }

			    else if (field.value== 'pred_study') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+predStudyA+pos+predStudyB);
			    }
			    else if (field.value== 'pred_method') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanC+predMethodA+pos+predMethodB);
			    }

			    else if (field.value== 'pred_ind') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+predIndividualsA+pos+predIndividualsB);
			    }

			    else if (field.value== 'val_study') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+valStudyA+pos+valStudyB);
			    }
			    else if (field.value== 'val_method') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+valMethodA+pos+valMethodB);
			    }
			    else if (field.value== 'val_status') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+valStatusA+pos+valStatusB);
			    }
			    else if (field.value== 'val_fosmids') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectYNA+pos+selectYNB+valFosmidsA+pos+valFosmidsB);
			    }
			    else if (field.value== 'val_ind') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+valIndividualsA+pos+valIndividualsB);
			    }
			    else if (field.value== 'freq_pop') { 
				    $('#'+pos).html(freqPopA2+pos+freqPopB2+selectSizeA+pos+selectSizeB+freqPopA+pos+freqPopB);
			    }
			    else if (field.value== 'seg_dup') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectYNA+pos+selectYNB+segDupA+pos+segDupB);
			    }
			    else if (field.value== 'aff_gene') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectBooleanA+pos+selectBooleanB+affGeneA+pos+affGeneB);
			    }
			    else if (field.value== 'inv_sp') { 
				    $('#'+pos).html(field_value2_hiddenA+pos+field_value2_hiddenB+selectAOA+pos+selectAOB+inversionsA+pos+inversionsB);
			    }
		    }


		    function addField() {
			    count++;

		        // 2016/03/29 Remove filter
			    $('#search').append('<div id=\"'+count+count+'\" class=\"classFilter\">'+selectFieldA+count+selectFieldB+'<div id=\"'+count+'\" class=\"classFilter\"></div><input type=\"image\" name=\"remove_filter\" id=\"remove_filter\" src=\"img/cross2.png\" alt=\"Remove filter\" onclick=\"getElementById('+count+count+').remove();\" width=\"18\" /><br /></div>');
			
		    }
		
	    //  2016/03/29 Remove filter
        //  Allows to remove an element both by its Id and its Class
        //      How to use 1: document.getElementById('my-element').remove();
	    //      How to use 2: document.getElementByClass('my-element').remove();
		    function remove() {
    		
    		    Element.prototype.remove = function() {
			        this.parentElement.removeChild(this);
			    }
			    NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
			        for(var i = 0, len = this.length; i < len; i++) {
			            if(this[i] && this[i].parentElement) {
			                this[i].parentElement.removeChild(this[i]);
			            }
			        }
			    }
		    }


		// 2016/03/29 Batch query
	        function Show_Div(Div_id) {

	            if (false == $(Div_id).is(':visible')) {
	                $(Div_id).show(250);
	            }
	            else {
	                $(Div_id).hide(250);
	            }
	        }
			function changeTest() {
				Show_Div(subtype);}
			function changeTest2() {
				Show_Div(subtype2);} 
			function changeTest3() {
				Show_Div(subtype3);}
			function changeTest_accutare_filter() {
				Show_Div(accutare_filter);}
				
				
	    </script>

    ";

    // **************************************************************************** //
    //	SEARCH FORM: division code of the search form. Visible for all the users
    // **************************************************************************** //
    $search_inv="
   
	    <div id='main' class='report-section' >
		    <div class='section-title TitleA'>- Search inversions</div>
		    <!-- <div class='hidden'> -->
		    <div class='section-content ContentA'>
			    <!-- <div class='section-content'> -->
	    	    <form id='searchInvFEST' method='POST' action='search_invdb2.php'>
		    	    <p id='search'>
			    	    <b>Query position:</b>&nbsp;&nbsp;<select id='assembly' name='assembly'>
			    		    <option value='hg18' selected>Mar. 2006 (NCBI36/hg18)</option>
						    <option value='hg19'>Feb. 2009 (GRCh37/hg19)</option>		
					    </select>
					    <!-- <a href='#assembly'><img src=\"img/alert.png\" width=\"18\"/></a> -->
			    	    &nbsp;
			    	    <input text='text' id='search_field' name='search_field' size=50 onfocus='if(this.value==  \"enter position, inversion ID or gene symbol\"){this.value=\"\";}' onblur='if(this.value==  \"\"){this.value=\"enter position, inversion ID or gene symbol\";}' value='enter position, inversion ID or gene symbol'/>

					    <!-- &nbsp;<input type='image' src='img/queryInvFEST.png' alt='Search' height='23'>
					    &nbsp;<a href='search_invdb2.php'><img src='img/listAll.png' alt='List All' height='23'></a> -->  

			    	    <a href='#' onclick='document.searchInvFEST.submit()'><button class='default'>Search</button></a>     
			    	    <a href='search_invdb2.php'><button class='default'>List All</button></a>
		    	    <br /></p>
			        <p id='addfilter'>
				 	    <!-- <input type='button' name='add_filter' class='button' id='add_filter' value='Add filter' /> -->
					    <a id='add_filter' onclick='#add_filter' name='add_filter'>
                            <font color='#1c4257'>
							    <span class='classSpan_button'>< Add filter ></span>
                            </font>
                        </a>
				    </p>
			    </form>
			    <br />

		

							
				<!-- BATCH QUERY v.4 -RGG- -->

		   		<form id='Batchquery' method='POST' action='tool_batch_query.php' enctype='multipart/form-data'>
		    	    <p id='batchsearch'>
			    	    <b>Batch query:</b>&nbsp;&nbsp;Look for multiple genomic coordinates by uploading them into a single file (<font color='grey'>ID:</font>chrN:start1<font color='grey'>,end1</font>-start2<font color='grey'>,end2</font> in plain text format).&nbsp;<font color='grey'><sup>*Optional</sup></font></b>
					<br /><br />

					";




	// if (isset($query_inv)){
	// 	$search_inv.="	<input type='file' name='fileToUpload' id='fileToUpload'> ";					
	// 	foreach($query_inv as $value){
 //  			$search_inv.='<input type="hidden" name="result[]" value="'. $value. '">';
	// 	}
	// }else{
	// 	$search_inv.="	<input type='file' name='fileToUpload' id='fileToUpload'> ";
	// }
	// $search_inv.="
 //  					<button class='default' name='submit' title='Inversions overlapping these intervals'>Region match</button>
	// 				<button class='default' name='accutare_filter_value' value='accutare_filter_value' title='Only inversions with breakpoints overlapping these intervals'>Breakpoint match</button>
	// 			" ;
	// if (isset($query_inv)){
	// 	$search_inv.="<br /> <input type='checkbox' name='fileAlready' id='fileAlready' value='yes' checked/> Use previous file";
	// }
	
	// $search_inv.="	<p id='addbpbias2'>
	// 				    <a name='add_bpbias' id='add_bpbias' value='add_bpbias' onclick='changeTest2()'><font color='#1c4257'>< Extend query region interval ></font></a>
	// 				</p>
	// 				<div id='subtype2' class='content' style='display: none;'>
	// 						Add a <input type='text' name='add_bp' dir='rtl' size='10'>  bp confidence interval to each side of your query breakpoints
	// 					</b><br /><br />
	// 				</div>
					
				
	// 			</form>

	// 		    <!-- </div> -->
	// 	    </div>
	//     </div>
    // ";


	if (isset($query_inv)){
		$search_inv.="	<input type='file' name='fileToUpload' id='fileToUpload'> ";					
		foreach($query_inv as $value){
  			$search_inv.='<input type="hidden" name="result[]" value="'. $value. '">';
		}
	}else{
		$search_inv.="	<input type='file' name='fileToUpload' id='fileToUpload'> ";
	}
	$search_inv.="
  					<button class='default' name='submit' title='Inversions overlapping this intervals'>Region match</button>
					<button class='default' name='accutare_filter_value' value='accutare_filter_value' title='Only inversions with breakpoints overlapping this intervals, not the whole inversion region'>Breakpoint match</button>
					<button class='default' name='overlap_search' value='overlap_search' title='Search for reciprocal overlap'>Overlap match</button>" ;
	if (isset($query_inv)){
		$search_inv.="<br /> <input type='checkbox' name='fileAlready' id='fileAlready' value='yes' checked/> Use previous file";
	}
	
	$search_inv.="	<p id='addbpbias2'>
					    <a name='add_bpbias' id='add_bpbias' value='add_bpbias' onclick='changeTest2()'><font color='#1c4257'>< Extend query region interval ></font></a>
					</p>
					<div id='subtype2' class='content' style='display: none;'>
							Add a <input type='text' name='add_bp' dir='rtl' size='10'>  bp confidence interval to each side of your query breakpoints
						</b><br /><br />
					</div>
					<p id='addbpbias3'>
					    <a name='add_bpbias' id='add_bpbias' value='add_bpbias' onclick='changeTest3()'><font color='#1c4257'>< Add modifiers to Overlap match ></font></a>
					</p>
					<div id='subtype3' class='content' style='display: none;'>
							Add a <input type='text' name='add_bp3' dir='rtl' size='10'>  bp confidence interval to each side of your query breakpoints.
						</b><br /><br />
						Search for a <input type='text' name='overlap_percent' dir='rtl' size='1'>  % of reciprocal overlap. 
						</b><br /><br />
						<input type='checkbox' id='internal' name='internal' value = 'TRUE' /> Take into account only the internal part of the inversion. 
					</div>
				
				</form>

			    <!-- </div> -->
		    </div>
	    </div>
	    ";


      include_once('help_messages.php');


    // **************************************************************************** //
    //	ADD PREDICTION FORM: this division is only visible for logged users
    // **************************************************************************** //

    $add_pred='

    	<style \'type=text/css\'>
		    div.right_example {
			    float: right;
			    width: 400px;
			    background-color: #ADD8E6;
			    padding: 10px;
			    margin-top: 10px;
			    margin-bottom: 10px;
			}
    	</style>

	    <div class="report-section">
		    <div class="section-title TitleB">- Add inversion prediction</div>
		    <div class="hidden">
			    <div class="section-content ContentB">
			        <p class="classP_HS"><a id="idA_popup" class="highslide-resize" href="php/new_study.php?&t=pred" ';
    $add_pred.=    		"onclick=\"return hs.htmlExpand(this, {objectType: 'iframe', objectHeight:250,  objectWidth:1000 })\">";
    $add_pred.=    		'Add a new study</a>
                    </p><br/>

                    <div class="section-title TitleB">- Single prediction</div>	
                    <div class="hidden">
                    	<div class="grlsection-content ContentB">
	                    	<form name="add_pred" action="tool_predictions.php" method="post"  enctype="multipart/form-data"> 
				       		 	</br>
				       		 	<table class="classTable_left">
									<tr>
										<td class = "classTd_left">
											Chromosome 
											<div class="compulsory">*</div>
										</td>
										<td class = "classTd_left">
											<select id="pred_chr" name="pred_chr">
												<option value="" selected>-Select-</option>
												<option value="chr1">1</option>
												<option value="chr2">2</option>
												<option value="chr3">3</option>
												<option value="chr4">4</option>
												<option value="chr5">5</option>
												<option value="chr6">6</option>
												<option value="chr7">7</option>
												<option value="chr8">8</option>
												<option value="chr9">9</option>
												<option value="chr10">10</option>
												<option value="chr11">11</option>
												<option value="chr12">12</option>
												<option value="chr13">13</option>
												<option value="chr14">14</option>
												<option value="chr15">15</option>
												<option value="chr16">16</option>
												<option value="chr17">17</option>
												<option value="chr18">18</option>
												<option value="chr19">19</option>
												<option value="chr20">20</option>
												<option value="chr21">21</option>
												<option value="chr22">22</option>
												<option value="chrX">X</option>
												<option value="chrY">Y</option>
												<option value="chrM">MT</option>
											</select>
										</td>
									</tr>
									<tr>
										<td class = "classTd_left">
											Breakpoint 1 start 
											<div class="compulsory">*</div>
										</td>
										<td class = "classTd_left">
											<input type="text" id="pred_bp1s" name="pred_bp1s" />
										</td>
									</tr>
									<tr>
										<td class = "classTd_left">
											Breakpoint 1 end 
											<div class="compulsory">*</div>
										</td>
										<td class = "classTd_left">
											<input type="text" id="pred_bp1e" name="pred_bp1e" />
										</td>
									</tr>
									<tr>
										<td class = "classTd_left">
											Breakpoint 1 between start-end 
										</td>
										<td class = "classTd_left">
											<input type="checkbox" id="between_bp1" name="between_bp1" />
										</td>
									</tr>
									<tr>
										<td class = "classTd_left">
											Breakpoint 2 start 
											<div class="compulsory">* </div>
										</td>
										<td class = "classTd_left">
											<input type="text" id="pred_bp2s" name="pred_bp2s" />
										</td>
									</tr>
									<tr>
										<td class = "classTd_left">
											Breakpoint 2 end 
											<div class="compulsory">*</div>
										</td>
										<td class = "classTd_left">
											<input type="text" id="pred_bp2e" name="pred_bp2e" />
										</td>
									</tr>
									<tr>
										<td class = "classTd_left">
											Breakpoint 2 between start-end 
										</td>
										<td class = "classTd_left">
											<input type="checkbox" id="between_bp2" name="between_bp2" />
										</td>
									</tr>
									<tr>
										<td class = "classTd_left">
											Study Name 
			                                <div class="compulsory">*</div>
										</td>
										<td class = "classTd_left">
											<select id="pred_study_name" name="pred_study_name" >
												<option value="" selected>-Select-</option>
												'.$research_name_user.'     
											</select>
										</td>
									</tr>
									<tr>
										<td class = "classTd_left">
											Prediction name
										</td>
										<td class = "classTd_left">
											<input type="text" id="pred_name" name="pred_name" />
										</td>
									</tr>
								</table>
								<br/>
	                    		<button class="default" name="submit" id="submit_pred" value="Add prediction">Submit values</button>
							</form>
                    		<br/>
                    	</div>
                    </div>	
                    </br>
                	<div class="section-title TitleB">- Multiple predictions</div>	
                	<div class="hidden">
                		<div class="grlsection-content ContentB">
                	    	<div class="right_example"</div>
			    				<b>Do you need some examples?</b><br/><br/> Check your table format and look up how the options must be written. 
				    			<br/> <br/> 
				    			<b> Predictions table </b> 
					    		<a href="'.$pred_path.'" download="'.$pred_path.'">  
					    			<input type="image" class="download"  src="img/download.png" name="pathoutput" title="Download table" alt="Submit Form" width="14" height="14" > 
					    		</a>
					    		<br/> <br/> 
			    				<b> Individuals table </b> 

			    				<a href="'.$prind_path.'" download="'.$prind_path.'">  <input type="image" class="download"  src="img/download.png" name="pathoutput" title="Download table" alt="Submit Form" width="14" height="14" > </a>

							</div>
							<form name="fileform_add" action="tool_predictions.php" method="post" enctype="multipart/form-data">
               					</br></br>
               					Add multiple predictions from a file to InvFEST database. 
               					<br/><br/><br/> 
               					<table class="classTable_left">
									<tr>
										<td>
											Predictions table
											<div class="compulsory">*</div>
										</td>
										<td>
											<input type="file" name="fileToUpload2" id="fileToUpload2">
              							</td>
              						</tr>
              						<tr>
              							<td>
											Individuals table
										</td>
										<td>
											<input type="file" name="fileToUpload5" id="fileToUpload5">
              							</td>
              						</tr>
								</table>
								</br>
								<button class="default" name="submit_table" value="Add prediction table">Submit table</button>		
	        				</form>
	        				<br/>
	  					</div>
	  				</div>	
	  				</br>
    			</div>
    		</div>
    ';

    // **************************************************************************** //
    //	ADD VALIDATION FORM: this division is only visible for logged users
    // **************************************************************************** //

    $add_pred.='
    		<br/>
	    	<div class="section-title TitleB">- Add inversion validation</div>
	    	<div class="hidden">
		    	<div class="section-content ContentB">

		    		

		    		<p class="classP_HS"><a id="idA_popup" class="highslide-resize" href="php/new_study.php?&t=pred"';
 	$add_pred.=			"onclick=\"return hs.htmlExpand(this, {objectType: 'iframe', objectHeight:250,  objectWidth:1000 })\">";
   	$add_pred.=	        'Add a new study</a>
   					</p>	    	

   					<div class="right_example"</div>
			    			<b>Do you need some examples?</b><br/><br/> Check your table format and look up how the options must be written. 
			    		<br/> <br/> 
			    		<b> Validations table </b> 
			    		
			    		<a href="'.$val_path.'" download="'.$val_path.'">  <input type="image" class="download"  src="img/download.png" name="pathoutput" title="Download table" alt="Submit Form" width="14" height="14" > </a>

						<br/> <br/> 
			    		<b> Individuals table </b> 

			    		<a href="'.$ind_path.'" download="'.$ind_path.'">  <input type="image" class="download"  src="img/download.png" name="pathoutput" title="Download table" alt="Submit Form" width="14" height="14" > </a>

			    	</div>

		   			<form name="fileform_add" action="tool_validations.php" method="post" enctype="multipart/form-data">
                   		<br/>
               			Add multiple validations from a file to InvFEST database. 
               			<br/><br/>
                   		<table class="classTable_left">
							<tr>
								<td>
									Validations table
									<div class="compulsory">*</div>
								</td>
								<td>
									<input type="file" name="fileToUpload3" id="fileToUpload3">
								</td>
							</tr>
							<tr>
								<td>
									Individuals table
								</td>
								<td>
									<input type="file" name="fileToUpload4" id="fileToUpload4">
								</td>
							</tr>
						</table>
						</br>
						<button class="default" name="submit_table" value="Add validation table">Submit files</button>						
					</form>
		    	</div>
	    	</div>
    ';





?>

