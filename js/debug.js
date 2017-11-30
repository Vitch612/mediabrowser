function dump(obj) {
  if ($("#jsdebug").length===0) {
    $('body').append('<div id="jsdebug" class="jsdebug"></div>');
  }
  $("#jsdebug").html($("#jsdebug").html()+"<pre>"+inspect(obj)+"</pre>"+"<HR>");
}

function inspect(obj, depth) {
  var log="",decal=" ";
  if (typeof depth  !== "number") {
    depth=0;
  } else if (depth>1)
    return "[MAX DEPTH!]\n";  
  for (var i=0;i<depth;i++) {
    decal+="<span style=\"background-color:blue;\">    </span>";
  }
  for (var p in obj) {
    var t = typeof obj[p];
    if (obj.hasOwnProperty(p)) {
      if (p!=="innerHTML" && p!=="outerHTML")
        log+=depth+decal+"obj["+p+"] of type "+t+" = "+(t === "object" ? "\n"+inspect(obj[p], depth+1) : obj[p])+"\n";
    } else {      
      if (p!=="innerHTML" && p!=="outerHTML")
        log+=depth+decal+"obj["+p+"] of type "+t+" (inh) = "+ (t === "object" ? "\n"+inspect(obj[p], depth+1) : obj[p])+"\n";
    }
  }
  return log;
}
