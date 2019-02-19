<?php

$head = "
<head>

	<!-- Define the font to use within the entire site -->
		<link rel='stylesheet' type='text/css'
			href='http://fonts.googleapis.com/css?family=Ubuntu:regular,italic,bold,bolditalic' >
	<!-- Define the favicon (32x32) to show on the explorer's address bar --> 
		<link rel='shortcut icon' href='img/InvFEST_ico.png'>
	<!-- Define the page title -->
		<title>InvFEST: Human Polymorphic Inversion DataBase</title>

	<!-- Define the site metadata -->
		<meta charset='UTF-8'>
		<!-- 
			<meta name=\"keywords\" content=\"inversion,inversions,inversiones\">
			<meta name=\"description\" content=\"Human Polymorphic Inversions Database\">
			<meta name=\"author\" content=\"Miquel Ràmia/Raquel Egea\">
		-->

	<!-- **************************************************************************** -->
	<!-- Include JQuery from a CDN (or locally if the site is offline) -->
		<script src=\"https://code.jquery.com/jquery-1.4.2.min.js\" async></script>
		<script>
			!$(window).jQuery && $(document).write('<script src=\"js/jquery-1.4.2.min.js\" async><\/script>')
		</script>

	<!-- **************************************************************************** -->
	<!-- Script to allow tooltips: an \"note\" that appears when a user hovers over an obejct -->
		<script>
			$(document).ready(function() {
		        // Tooltip only Text
		        $('.masterTooltip').hover(function() {
	                // Hover over code
	                var title=$(this).attr('title');
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
	                var mousex=e.pageX + 20; //Get X coordinates
	                var mousey=e.pageY + 10; //Get Y coordinates
	                $('.tooltip')
	                .css({ top: mousey, left: mousex })
		        });
			});
		</script>

	<!-- JS-AJAX script allows information uptating without reloading the whole page -->
		<script src='js/myAJAXlib.js'></script>
	
	<!-- **************************************************************************** -->
	<!-- Include style sheets -->
		<link rel='stylesheet' type='text/css' href='css/style.css'/>
		<link rel='stylesheet' type='text/css' href='css/report.css'/>
        <link rel='stylesheet' type='text/css' href='css/search.css'/>

	<!-- **************************************************************************** -->
	<!-- Loads Hihgslide JS: image, media and gallery viewer written in JavaScript -->
		<link href='css/css_highslide.css' rel='stylesheet' type='text/css'/>
		<script src='js/highslide_complete.js'></script>

		<script>    
		    hs.graphicsDir='img/highslide_graphics/';
		    hs.outlineType='rounded-white';
		    hs.outlineWhileAnimating=true;
		</script>


	<!-- **************************************************************************** -->
	<!-- Include the script to sort table content -->
		<script src='js/jquery.tablesorter.min.js'></script>

		<script>
			function pad(number, length) {
			    var str='' + number;
			    while (str.length < length) {
			        str='0' + str;
			    }
			    return str;
			}

			// Add parser through the tablesorter addParser method 
			$.tablesorter.addParser({ 
				// Set a unique id 
				id: 'size', 
				is: function(s) { 
					// Return false so this parser is not auto detected 
					return false; 
				}, 
				format: function(s) { 
					// Format your data for normalization 
					return s.replace(/,/g,''); 
				}, 
				// Set type, either numeric or text 
				type: 'numeric' 
			}); 

			$.tablesorter.addParser({ 
				// Set a unique id 
				id: 'status', 
				is: function(s) { 
					// Return false so this parser is not auto detected 
					return false; 
				}, 
				format: function(s) { 
					// Format your data for normalization 
					return s.replace('Validated','1').replace('Predicted','2').replace('Unreliable prediction','3').replace('False','4'); 
				}, 
				// Set type, either numeric or text 
				type: 'text' 
			});

			$.tablesorter.addParser({ 
				// Set a unique id 
				id: 'effect', 
				is: function(s) { 
					// Return false so this parser is not auto detected 
					return false; 
				}, 
				format: function(s) { 
					// Format your data for normalization 
					return s.replace('Breaks two genes','1').replace('Breaks one gene','2').replace('Breaks different exons and introns of a gene','3').replace('Breaks a region within an exon of a gene','4').replace('Breaks a region within an intron of a gene','5').replace('Intergenic','6').replace('NA','7'); 
				}, 
				// Set type, either numeric or text 
				type: 'text' 
			});

			$.tablesorter.addParser({ 
				// Set a unique id 
				id: 'position', 
				is: function(s) { 
					// Return false so this parser is not auto detected 
					return false; 
				}, 
				format: function(s) { 
					// Format your data for normalization 
		            var myposition=s.match(/^chr([^:]+):(\d+)-(\d+)$/);
		            var mychrom=myposition[1];
		            mychrom=mychrom.replace('X',997).replace('Y',998).replace('M',999);
		            var mystart=myposition[2];
		            var myend=myposition[3];

				    var mynewchrom=pad(mychrom,20);
				    var mynewstart=pad(mystart,20);
				    var mynewend=pad(myend,20);
				    
				    var mynewposition=mynewchrom + mynewstart + mynewend;

				    return mynewposition;
				},
				// Set type, either numeric or text 
				type: 'text' 
			});
            
            // Script to allow 'expandable' divisions
			$(document).ready(function(){
				$('.section-title').click(function(){			//Toggle when click title
					$(this).next('.section-content').slideToggle(600);
					var title=$(this).html();

					var regExp=/\+/;
					if (title.match(regExp)) {
						title=title.replace('+','-');
						$(this).html(title);
					} 
					else {
						title=title.replace('-','+');
						$(this).html(title);
					}
				});	

				$('#sort_table').tablesorter({headers: {1:{sorter:'position'},2:{sorter:'size'},3:{sorter:'status'},5:{sorter:'effect'}}}); 
			});

		</script>

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
		
		var selectSizeA= '<select name=\"boolean[]\" id=\"boolean';
		var selectSizeB= '\"><option selected value=\">\">></option><option value=\"<\"><</option></select>';
		
		var selectYNA= '<select name=\"boolean[]\" id=\"boolean';
		var selectYNB= '\"><option selected value=\"yes\">yes</option><option value=\"no\">no</option></select>';
		
		var selectAOA= '<select name=\"boolean[]\" id=\"boolean';
		var selectAOB= '\"><option selected value=\"direct\">direct</option><option value=\"inverted\">inverted</option></select>';
		
		var selectFieldA='<select name=\"field[]\" id=\"field';
		var selectFieldB='\" onchange=\"displayFields(this)\"><option selected value=\"foo\" disabled=\"disabled\">-Select-</option><option value=\"size\">Size</option><option value=\"inv_status\">Inversion Status</option><option value=\"val_study\">Validation Study</option><option value=\"val_method\">Validation Method</option><option value=\"val_status\">Validation Status</option><option value=\"val_fosmids\">Validation with Fosmids</option><option value=\"val_ind\">Validated Individuals</option><option value=\"freq_pop\">Frequency in Populations</option><option value=\"seg_dup\">Segmental Duplications in Breakpoints</option><option value=\"aff_gene\">Affected Gene</option><option value=\"inv_sp\">Ancestral Orientation</option></select>';

	//	<option value=\"size\">Size</option>
		var sizeA='<input text=\"text\" name=\"field_value[]\" id=\"size';
		var sizeB='\" size=25>&nbsp;in Mb<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Size] filters with [AND]\" width=\"18\"/>&nbsp;';

	//	<option value='inv_status'>Inversion Status</option>
		var invStatusA= '<select name=\"field_value[]\" id=\"inversion_status';
		    var invStatusB= '\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $inversion_status_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Inversion Status] filters with [OR]\" width=\"18\"/>&nbsp;';

	//	<option value='val_study'>Validation Study</option>
		var valStudyA='<select name=\"field_value[]\" id=\"research';
		    var valStudyB='\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $research_name_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Validation Study] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

	//	<option value='val_method'>Validation Method</option>
		var valMethodA='<select name=\"field_value[]\" id=\"validation_method';
		    var valMethodB='\" ><option value=\"\" selected disabled=\"disabled\">-Select-</option> $validation_method_option </select>&nbsp;<img src=\"img/alert.png\" class=\"masterTooltip\" title=\"Joins to other [Validation Method] filters with [OR]   ///   Will retrieve [validated] inversions only!\" width=\"18\"/>&nbsp;';

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

	</script>
</head>
";

?>