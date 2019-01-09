function validate() {

	var research=document.getElementById("research_name");
	if (research.value=="") {
		alert ("Please fill in the Study Name field");
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

	var fosmids=document.getElementById("searchFosmids");
	var results=document.getElementById("fosmids_results");
	if (method.value.match(/PCR|FISH|MLPA/) ) { } //experimental
/*	else if (method.value != '') { //bioinformatics
		if (fosmids.value=="" ){
		alert ("Please fill in the Fosmids information from Validation details");
		fosmids.focus();
		return false;}
		else if (results.value==""){
		alert ("Please fill in the Results information from Validation details");
		results.focus();
		return false;}
	}
*/	
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

function validate_funct() {
	var type=document.getElementById("effect_type");
	if (type.value=="") {
		alert("Please select a Type of effect");
		type.focus();
		return false;
	}
	else if (type.value=='eff_genomic'){
		/*gene_func -> gene
		genomic_eff_func -> effect
		source_genomic_func -> study
		conseq_func -> consequences
		*/
		var gene = document.getElementById("gene_func");
		var effect = document.getElementById("genomic_eff_func");
		var study = document.getElementById("source_genomic_func");
		var conseq = document.getElementById("conseq_func");
		if (gene.value=="") {
			alert ("Please fill in the Gene field");
			gene.focus();
			return false;
		}
		else if (effect.value=="") {
			alert ("Please fill in the Effect field");
			effect.focus();
			return false;
		}
		else if (study.value=="") {
			alert ("Please fill in the Study field");
			study.focus();
			return false;
		}
		else if (conseq.value=="") {
			alert ("Please fill in the Consequences field");
			conseq.focus();
			return false;
		}
	}
	else if (type.value=='eff_phenotypic'){
		/*
        phenotypic_eff_func -> effect
		source_phenotypic_func -> study
		*/
		var effect = document.getElementById("phenotypic_eff_func");
		var study = document.getElementById("source_phenotypic_func");
		if (effect.value=="") {
			alert ("Please fill in the Effect field");
			effect.focus();
			return false;
		}
		else if (study.value=="") {
			alert ("Please fill in the Study field");
			study.focus();
			return false;
		}
	}
	return true;
}

function validate_evol() {

	var type=document.getElementById("evol_type");
	if (type.value=="") {
		alert("Please select a Type of information");
		type.focus();
		return false;
	}
	else if (type.value=="evolution_orientation") {
		/*
        evolution_orientation
		orientation_species
		orientation_orientation
		method_orientation
		source_orientation
        */
		var orient_sp=document.getElementById("orientation_species");
		var orient_orient=document.getElementById("orientation_orientation");
		var orient_method=document.getElementById("method_orientation");
		var orient_source=document.getElementById("source_orientation");
		if (orient_sp.value=="") {
			alert ("Please fill in the Species field");
			orient_sp.focus();
			return false;
		}
		else if (orient_orient.value=="") {
			alert ("Please fill in the Orientation field");
			orient_orient.focus();
			return false;
		}
		else if (orient_method.value=="") {
			alert ("Please fill in the Method field");
			orient_method.focus();
			return false;
		}
		else if (orient_source.value=="") {
			alert ("Please fill in the Study field");
			orient_source.focus();
			return false;
		}
		
	}
	else if (type.value=="evolution_age") {
		/*
        evolution_age	
		age_age
		method_age
		source_age
        */
		var age_age=document.getElementById("age_age");
		var age_method=document.getElementById("method_age");
		var age_source=document.getElementById("source_age");
		if (age_age.value=="") {
			alert ("Please fill in the Age field");
			age_age.focus();
			return false;
		}
		else if (age_method.value=="") {
			alert ("Please fill in the Method field");
			age_method.focus();
			return false;
		}
		else if (age_source.value=="") {
			alert ("Please fill in the Study field");
			age_source.focus();
			return false;
		}
	}
	else if (type.value=="evolution_origin") {
		/*
        evolution_orientation
		origin_origin
		method_origin
		source_origin
        */
		var origin=document.getElementById("origin_origin");
		var origin_method=document.getElementById("method_origin");
		var origin_source=document.getElementById("source_origin");
		if (origin.value=="") {
			alert ("Please fill in the Origin field");
			origin.focus();
			return false;
		}
		else if (origin_method.value=="") {
			alert ("Please fill in the Method field");
			origin_method.focus();
			return false;
		}
		else if (origin_source.value=="") {
			alert ("Please fill in the Study field");
			origin_source.focus();
			return false;
		}
	}
	return true;
}


