function createObject() {
var tipo_richiesta;
var browser = navigator.appName;
if(browser == "Microsoft Internet Explorer"){
tipo_richiesta = new ActiveXObject("Microsoft.XMLHTTP");
}else{
tipo_richiesta = new XMLHttpRequest();
}
return tipo_richiesta;
}

var http = createObject();