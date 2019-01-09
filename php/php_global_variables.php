<?php
/******************************************************************************
	PHP_GLOBAL_VARIABLES.PHP

	Defines the form for the inversion's atributes: status, effects, definition method
*******************************************************************************/


	$array_status = array(
		"TRUE"   		    => "<font color='green'>Validated</font>", 
		"FALSE"  		    => "<font color='red'>False</font>", 
		"Ambiguous/FALSE"  	=> "<font color='red'>Ambiguous/False</font>", 
		"FILTERED OUT"  	=> "<font color='grey'>Unreliable prediction</font>", 
		"ND" 			    => "<font color='black'>Predicted</font>", 
		"WITHDRAWN"		    => "<font color='grey'>Obsolete</font>", 
		"Withdrawn"		    => "<font color='grey'>Obsolete</font>", 
		"withdrawn"		    => "<font color='grey'>Obsolete</font>",         
		"AMBIGUOUS"  		=> "<font color='blue'>Ambiguous</font>",
		"Inconclusive"  	=> "<font color='black'>Inconclusive</font>",  
		"Ambiguous"  		=> "<font color='blue'>Ambiguous</font>", 
		"ambiguous"  		=> "<font color='blue'>Ambiguous</font>", 
		"possible_TRUE"		=> "<font color='black'>Predicted</font>", 
		"possible_FALSE"	=> "<font color='black'>Predicted</font>",
		"Breakpoint refinement"	=> "<font color='black'>Breakpoint refinement</font>",
		"Genotyping"	=> "<font color='black'>Genotyping</font>"
	);
	$array_status_no_format = array(
        "TRUE"              => "Validated", 
        "FALSE"             => "False", 
        "Ambiguous/FALSE"   => "Ambiguous/False",
        "FILTERED OUT"      => "Unreliable prediction",
        "ND"                => "Predicted",
        "WITHDRAWN"         => "Obsolete",
        "Withdrawn"         => "Obsolete",
        "withdrawn"         => "Obsolete",
        "AMBIGUOUS"         => "Ambiguous",
        "Ambiguous"         => "Ambiguous",
        "ambiguous"         => "Ambiguous",
        "possible_TRUE"     => "Predicted",
        "possible_FALSE"    => "Predicted",
        "Breakpoint refinement"	=> "Breakpoint refinement",
        "Genotyping"	=> "Genotyping"
    );

	$array_effects = array(
		"NA" 					            => "<font color='grey'>NA</font>",
		"intergenic" 				        => "<font color='grey'>Intergenic breakpoints</font>",
		"intergenic, NA" 			        => "<font color='grey'>Intergenic breakpoints</font>",
		"breakWithinGene_withinIntron" 		=> "Inverts a region within an intron of a gene",
		"breakWithinGene, withinIntron" 	=> "Inverts a region within an intron of a gene",
		"break1gene" 				        => "<font color='red'>Breaks one gene</font>",
		"break1gene, NA" 			        => "<font color='red'>Breaks one gene</font>",
		"breakWithinGene_amongDiffRegions" 	=> "<font color='red'>Inverts different exons and introns of a gene</font>",
		"breakWithinGene, amongDiffRegions" => "<font color='red'>Inverts different exons and introns of a gene</font>",
		"break2genes" 				        => "<font color='red'>Breaks two genes</font>",
		"break2genes, NA" 			        => "<font color='red'>Breaks two genes</font>",
		"breakWithinGene_withinExon" 		=> "<font color='red'>Inverts a region within an exon of a gene</font>",
		"breakWithinGene, withinExon" 		=> "<font color='red'>Inverts a region within an exon of a gene</font>",
	);

	$array_definitionmethod = array(
		"manual curation" 		        => "<font color='green'>Manual curation</font>",
		"default informatic definition" => "<font color='red'>Default informatic definition</font>",
	);

?>
