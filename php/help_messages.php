   
<?php
/******************************************************************************
	HELP_MESSAGES.PHP
	Creates updated temporary files to help the user with the search page tools. 

*******************************************************************************/
// error_reporting(E_ALL);
// ini_set('display_errors',1);
?>

<?php
 
  // Select specific data into variables which are retrieved in other php pages
  include_once('php/select_index.php');

?>

<?php

# PREDICTIONS HELP
	$path = getcwd()."/tmp_files/";
	$pred_name = "EXAMPLE_predictions";
	$pred_path = $path.$pred_name;
	$pred_text = "
    #######################################
    #					  #
    #	MULTIPLE PREDICTIONS INPUT HELP   #
    #					  #
    #######################################
 
 In the case of a correct data input, all the predictions in the table will be added to InvFEST database. 

 REQUIRED FORMAT
-------------------------------------	

	Prediction ID:Chromosome:Breakpoint 1 start:Breakpoint 1 end:Breakpoint 1 between start-end:Breakpoint 2 start:Breakpoint 2 end:Breakpoint 2 between start-end:Study Name:Prediction name
	
Real example:

	1:chr10:127180574:127180574:FALSE:127187215:127187215:FALSE:INVFEST curation:CINV_delly_INV00003655

  FIELD SPECIFICATIONS
-------------------------------------	

 Those marked with a * are mandatory:


	Prediction ID *		ID necessary to identify the corresponding individuals. It can be repeated if individuals are shared between different predictions.
	Chromosome * 		 		In format chrN (being 'N' a number, Y, X or MT). 	
	Breakpoint 1 start *  			A natural number corresponding to a position in the sequence.	
	Breakpoint 1 end *  			A natural number corresponding to a position in the sequence.	
	Breakpoint 1 between start-end * 	TRUE or FALSE. TRUE means taht the real breakpoint is located between the two provided coordinates.	
	Breakpoint 2 start * 			A natural number corresponding to a position in the sequence.
	Breakpoint 2 end * 			A natural number corresponding to a position in the sequence.
	Breakpoint 2 between start-end * 	TRUE or FALSE. TRUE means that the real breakpoint is located between the two provided coordinates.
	Study Name * 				Make sure that the studies in your table already exist and are written exactly the same way as they appear in the database. 
	Prediction name 			The name or ID of the inversion in the original article. 

  FIELD OPTIONS - Study Name
-------------------------------------
An updated list with the available studies is provided. 
New studies can be added using the 'Add new study' form in the 'Search inversions' page.  

";

	$arr = implode("\n\t", $checkpoint_research);
	$pred_text.=  "\n\t".$arr;

	$pred_output = fopen("$pred_path", 'w') or die("Unable to create output file!".$pred_path);
	fwrite($pred_output, $pred_text);
	fclose($pred_output);

# PREDICTION INDIVIDUALS HELP

	$prind_name = "EXAMPLE_prindividuals";
	$prind_path = $path.$prind_name;
	$prind_text = "

    ########################################
    #					   #
    #	PREDICTION INDIVIDUALS INPUT HELP  #
    #					   #
    ########################################

 In the case of a correct prediction data input, the corresponding individuals will join to the prediction information. 

 REQUIRED FORMAT
-------------------------------------	
 Tab-separated (\\t) columns 

 Header:

	code	gender	population	region	family	relationship	genotype	allele_comment	allele_level	panel	other_code	
	code\\tgender\\tpopulation\\tregion\\tfamily\\trelationship\\tgenotype\\tallele_comment\\tallele_level\\tpanel\\tother_code	

 Line structure:

	ID 	code	gender	population	region	family	relationship	genotype	allele_comment	allele_level	panel	other_code	
	ID\\tcode\\tgender\\tpopulation\\tregion\\tfamily\\trelationship\\tgenotype\\tallele_comment\\tallele_level\\tpanel\\tother_code	

 Real Example line (without allele_comment, allele_level, panel, other_code): 

	1	NA12156	Female	CEU	Europe	1408-13	Maternal Grandmother	INV/INV
	1\\tNA12156\\tFemale\\tCEU\\tEurope\\t1408-13\\tMaternal Grandmother\\tINV/INV

 EXTENDED EXAMPLE 1: two out of three predictions have some shared individuals 
-------------------------------------------------------------------------------

	> INDIVIDUALS FILE

code	gender	population	region	family	relationship	genotype	allele_comment	allele_level											
1	NA12156	Female	CEU	Europe	1408-13	Maternal Grandmother	INV/INV													
1	NA12878	Female	CEU	Europe	1463-2	Mother	STD/INV																							
1	NA18555	Female	CHB	Asia	none	unrelated	INV/INV													
1	NA18956	Female	JPT	Asia	none	unrelated	STD/INV													
1	NA19129	Female	YRI	Africa	Y077	Child	STD/INV													
1	NA19240	Female	YRI	Africa	Y117	Child	INV/INV													
1	HuRef 	Male	Unknown	Europe	none	unrelated	INV/INV		
2	NA12156	Female	CEU	Europe	1408-13	Maternal Grandmother	INV/INV													
2	NA12878	Female	CEU	Europe	1463-2	Mother	STD/INV																								
2	NA19129	Female	YRI	Africa	Y077	Child	STD/INV													
2	NA19240	Female	YRI	Africa	Y117	Child	INV/INV													
2	HutestRef 	Male	Unknown	Europe	none	unrelated	INV/INV	

	> PREDICTIONS FILE

1:chr10:127180574:127180574:FALSE:127187215:127187215:FALSE:INVFEST curation:CINV_delly_INV00003655
2:chr10:127180623:127180623:FALSE:127188511:127188511:FALSE:INVFEST curation:CINV_delly_INV00003656
3:chr10:127180222:127180222:FALSE:127187999:127187999:FALSE:INVFEST curation:CINV_delly_INV00003657

 EXTENDED EXAMPLE 2: two predictions have exactly the same individuals
------------------------------------------------------------------------

	> INDIVIDUALS FILE

code	gender	population	region	family	relationship	genotype	allele_comment	allele_level											
1	NA12156	Female	CEU	Europe	1408-13	Maternal Grandmother	INV/INV													
1	NA12878	Female	CEU	Europe	1463-2	Mother	STD/INV																							
1	NA18555	Female	CHB	Asia	none	unrelated	INV/INV													
1	NA18956	Female	JPT	Asia	none	unrelated	STD/INV													
1	NA19129	Female	YRI	Africa	Y077	Child	STD/INV													
1	NA19240	Female	YRI	Africa	Y117	Child	INV/INV													
1	HuRef 	Male	Unknown	Europe	none	unrelated	INV/INV		

	> PREDICTIONS FILE

1:chr10:127180574:127180574:FALSE:127187215:127187215:FALSE:INVFEST curation:CINV_delly_INV00003655
1:chr10:127180623:127180623:FALSE:127188511:127188511:FALSE:INVFEST curation:CINV_delly_INV00003656

";

	$prind_output = fopen("$prind_path", 'w') or die("Unable to create output file!".$prind_name);
	fwrite($prind_output, $prind_text);
	fclose($prind_output);

#  VALIDATIONS HELP

	$val_name = "EXAMPLE_validations";
	$val_path = $path.$val_name;
	$val_text = "

    ########################################
    #					   #
    #	MULTIPLE VALIDATIONS INPUT HELP   #
    #					   #
    ########################################
 
 In the case of a correct data input, all the validations in the table will be added to InvFEST database. 

 REQUIRED FORMAT
-------------------------------------	

	Validation ID:Inversion name:Study Name:Method:Status:Force status:Comments:Population:Analyzed individuals:Inverted alleles:Standard frequency:Inverted frequency

Real Example (without frequencies):

	1:HsInv0003:INVFEST curation:PCR:TRUE:not:Testing line:::::

Real Example (without comments):

	1:HsInv0003:INVFEST curation:PCR:TRUE:not::ACB (African (AFR)):50:23:0.25:0.75

Real Example (complete):

	1:HsInv0003:INVFEST curation:PCR:TRUE:not:Testing line:ACB (African (AFR)):50:23:0.25:0.75

Extended examples in 'Individuals table' documentation
	
  FIELD SPECIFICATIONS
-------------------------------------	

 Those marked with a * are mandatory:	
	
      > GENERAL INFORMATION

	Validation ID *		ID necessary to identify the corresponding genotyped individuals. It can be repeated if individuals are shared between different validations.
	Inversion name *	HsInvNNNN format. 
	Study Name *	 	Make sure that the studies in your table already exist and are written exactly the same way as they appear in the database. 
	Method *		Make sure that the methods in your table already exist and are written exactly the same way as they appear in the database.
	Status *		Make sure that the status in your table already exist and are written exactly the same way as they appear in the database.
	Force status *		Either yes or not, it will stablish automatically the new status until another status is forced.
	Comments

      > FREQUENCY WITHOUT GENOTYPES --> These values are not mandatory but if specified, all of them must be present.

	Population		Make sure that the populations in your table already exist and are written exactly the same way as they appear in the database.
	Analyzed individuals	A natural number.
	Analyzed individuals	A natural number.
	Inverted alleles	A natural number.
	Standard frequency	Standard alleles/Total alleles.
	Inverted frequency	Inverted alleles/Total alleles.



  FIELD OPTIONS - Study Name
-------------------------------------
An updated list with the available studies is provided. 
New studies can be added using the 'Add new study' form in the 'Search inversions' page.  

";

	$arr = implode("\n\t", $checkpoint_research);
	$val_text.=  "\n\t".$arr;

	$val_text.= "


  FIELD OPTIONS - Method
-------------------------------------
An updated list with the available methods is provided. 	

";
	
	$arr = implode("\n\t", $checkpoint_method);
	$val_text.=  "\n".$arr;

	$val_text.= "


  FIELD OPTIONS - Status
-------------------------------------
An updated list with the available status is provided. 	

";
	
	$arr = implode("\n\t", $checkpoint_status);
	$val_text.=  "\n\t".$arr;

	$val_text.= "


  FIELD OPTIONS - Population
-------------------------------------
An updated list with the available populations is provided. 	

";
	
	$arr = implode("\n\t", $checkpoint_fngpopulation);
	$val_text.=  "\n\t".$arr;




	$val_output = fopen("$val_path", 'w') or die("Unable to create output file!".$val_name);
	
	fwrite($val_output, $val_text);

# VALIDATION INDIVIDUALS HELP

	$ind_name = "EXAMPLE_individuals";
	$ind_path = $path.$ind_name;
	$ind_text = "

    ########################################
    #					   #
    #	VALIDATION GENOTYPES INPUT HELP    #
    #					   #
    ########################################

 In the case of a correct validation data input, the corresponding genotypes will join to the validation information. 

 REQUIRED FORMAT
-------------------------------------	
 Tab-separated (\\t) columns 

 Header:

	code	gender	population	region	family	relationship	genotype	allele_comment	allele_level	panel	other_code	
	code\\tgender\\tpopulation\\tregion\\tfamily\\trelationship\\tgenotype\\tallele_comment\\tallele_level\\tpanel\\tother_code	

 Line structure:

	ID 	code	gender	population	region	family	relationship	genotype	allele_comment	allele_level	panel	other_code	
	ID\\tcode\\tgender\\tpopulation\\tregion\\tfamily\\trelationship\\tgenotype\\tallele_comment\\tallele_level\\tpanel\\tother_code	

 Real Example line (without allele_comment, allele_level, panel, other_code): 

	1	NA12156	Female	CEU	Europe	1408-13	Maternal Grandmother	INV/INV
	1\\tNA12156\\tFemale\\tCEU\\tEurope\\t1408-13\\tMaternal Grandmother\\tINV/INV

 EXTENDED EXAMPLE 1: two validations have some shared genotypes and one has frequencies without genotypes
-----------------------------------------------------------------------------------------------------------	

	> INDIVIDUALS FILE

code	gender	population	region	family	relationship	genotype	allele_comment	allele_level											
1	NA12156	Female	CEU	Europe	1408-13	Maternal Grandmother	INV/INV													
1	NA12878	Female	CEU	Europe	1463-2	Mother	STD/INV																							
1	NA18555	Female	CHB	Asia	none	unrelated	INV/INV													
1	NA18956	Female	JPT	Asia	none	unrelated	STD/INV													
1	NA19129	Female	YRI	Africa	Y077	Child	STD/INV													
1	NA19240	Female	YRI	Africa	Y117	Child	INV/INV													
1	HuRef 	Male	Unknown	Europe	none	unrelated	INV/INV		
2	NA12156	Female	CEU	Europe	1408-13	Maternal Grandmother	INV/INV													
2	NA12878	Female	CEU	Europe	1463-2	Mother	STD/INV																								
2	NA19129	Female	YRI	Africa	Y077	Child	STD/INV													
2	NA19240	Female	YRI	Africa	Y117	Child	INV/INV													
2	HutestRef 	Male	Unknown	Europe	none	unrelated	INV/INV	

	> VALIDATIONS FILE

1:HsInv0003:Aguado et al. 2014:PCR:TRUE:not:THIS IS A TEST:::::
2:HsInv0006:Aguado et al. 2014:PCR:TRUE:yes::::::
3:HsInv0030:Aguado et al. 2014:PCR:TRUE:not:THIS IS A TEST:ACB (African (AFR)):50:23:0.25:0.75

 EXTENDED EXAMPLE 2: two validations have the same genotypes
-------------------------------------------------------------

	> INDIVIDUALS FILE

code	gender	population	region	family	relationship	genotype	allele_comment	allele_level											
1	NA12156	Female	CEU	Europe	1408-13	Maternal Grandmother	INV/INV													
1	NA12878	Female	CEU	Europe	1463-2	Mother	STD/INV																							
1	NA18555	Female	CHB	Asia	none	unrelated	INV/INV													
1	NA18956	Female	JPT	Asia	none	unrelated	STD/INV													
1	NA19129	Female	YRI	Africa	Y077	Child	STD/INV													
1	NA19240	Female	YRI	Africa	Y117	Child	INV/INV													
1	HuRef 	Male	Unknown	Europe	none	unrelated	INV/INV		

	> VALIDATIONS FILE

1:HsInv0003:Aguado et al. 2014:PCR:TRUE:not:THIS IS A TEST:::::
1:HsInv0006:Aguado et al. 2014:PCR:TRUE:yes::::::

";

	$ind_output = fopen("$ind_path", 'w') or die("Unable to create output file!".$ind_name);
	fwrite($ind_output, $ind_text);
	fclose($ind_output);

?>