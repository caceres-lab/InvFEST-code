function createREQ() {
	if (window.XMLHttpRequest) {
		// Code for IE7+, Firefox, Chrome, Opera, Safari
		return new XMLHttpRequest();
	}
	if (window.ActiveXObject) {
		// Code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
	return null;
}

function requestGET(url, query, req) {
	var myRand=parseInt(Math.random()*99999999);
	req.open("GET",url+'?'+query+'&rand='+myRand,true);
	req.send(null);
}

function requestPOST(url, query, req) {
	req.open("POST", url,true);
	req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	req.send(query);
}

function doCallback(callback,item) {
	eval(callback + '(item)');
}

function doAjax(url,query,callback,reqtype,getxml, waitMessage) {
	var myreq = createREQ();
	doCallback(callback, waitMessage);
	myreq.onreadystatechange = function() {
		if(myreq.readyState == 4) {
			if(myreq.status == 200) {
				var item = myreq.responseText;
				if(getxml==1) {
					item = myreq.responseXML;
				}
				doCallback(callback, item);
			}
		}
	}
	if(reqtype=='POST') {
		requestPOST(url,query,myreq);
	} else {
		requestGET(url,query,myreq);
	}
}
