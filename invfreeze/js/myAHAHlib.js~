var xmlhttp;

function showResult(){
	xmlhttp=GetXmlHttpObject();
	if (xmlhttp==null){
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url="php/search_invdb.php";
	//url=url+"?q="+str;
	url=url+"?sid="+Math.random();
	xmlhttp.open("GET",url,true);
	xmlhttp.onreadystatechange=stateChanged;
	xmlhttp.send(null);
}

function stateChanged(){
	if (xmlhttp.readyState==4){
		document.getElementById("search_results").innerHTML=xmlhttp.responseText;
	} else {
		document.getElementById("search_results").innerHTML='<img id="load" src="css/img/load.gif" />';
	}
}

function GetXmlHttpObject(){
	if (window.XMLHttpRequest){
		// code for IE7+, Firefox, Chrome, Opera, Safari
		return new XMLHttpRequest();
	}
	if (window.ActiveXObject){
		// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
	return null;
}
