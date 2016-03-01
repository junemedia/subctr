function AmpereMedia() {
};

AmpereMedia.prototype.init = function () {
	var xmlHttp=false;
/*@cc_on @*/
/*@if (@_jscript_version >= 5)
 try {
  xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
 } catch (e) {
  try {
   xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (E) {
   xmlHttp = false;
  }
 }
@end @*/
	if (!xmlHttp && typeof XMLHttpRequest!='undefined') {
	  xmlHttp = new XMLHttpRequest();
	}

	try {
		// Mozilla / Safari
		this._xh = new XMLHttpRequest();
	} catch (e) {
		// Explorer
		this._xh = new ActiveXObject("Microsoft.XMLHTTP");
	}
}

AmpereMedia.prototype.busy = function () {
	return (this._xh.readyState && (this._xh.readyState > 4))
}

AmpereMedia.prototype.send = function (url,data) {
	if (!this._xh) {
		this.init();
	}
	if (!this.busy()) {
		this._xh.open("GET",url,false);
		this._xh.send(data);
		if (this._xh.readyState == 4 && this._xh.status == 200) {
			return this._xh.responseText;
		}
	}
	return false;
}

var coRegPopup = new AmpereMedia();

function getObject(objectId) {
  // checkW3C DOM, then MSIE 4, then NN 4.
  //
  if(document.getElementById && document.getElementById(objectId)) {
	return document.getElementById(objectId);
   }
   else if (document.all && document.all(objectId)) {  
	return document.all(objectId);
   } 
   else if (document.layers && document.layers[objectId]) { 
	return document.layers[objectId];
   } else {
	return false;
   }
}
