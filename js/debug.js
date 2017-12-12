function dump(obj,searchdepth,showinherited) {
  if (searchdepth === undefined) searchdepth=2;
  if (showinherited === undefined) showinherited=false;
 
  if (typeof obj !== "object") {
    if ($("#jsdebug").length===0) {
      $('body').append('<div id="jsdebug" class="jsdebug"></div>');
    }
    $("#jsdebug").html($("#jsdebug").html()+"<pre>NEW DUMP of type: "+(typeof obj)+"\n"+obj+"</pre>"+"<HR>");    
  } else {
    if ($("#jsdebug").length===0) {
      $('body').append('<div id="jsdebug" class="jsdebug"></div>');
    }
    $("#jsdebug").html($("#jsdebug").html()+"<pre>NEW DUMP of type: object\n"+inspect(obj,0,searchdepth,showinherited)+"</pre>"+"<HR>");
  }
}

function inspect(obj, depth,searchdepth,showinherited) {
  var log="",decal=" ";
  if (typeof depth  !== "number") {
    depth=0;
  } else if (depth>searchdepth)
    return "[MAX DEPTH!]\n";  
  for (var i=0;i<depth;i++) {
    decal+="<span style=\"background-color:lightblue;\">    </span>";
  }
  for (var p in obj) {
    var t = typeof obj[p];
    if (obj.hasOwnProperty(p)) {
      if (p!=="innerHTML" && p!=="outerHTML")
        log+=depth+decal+"obj["+p+"] of type "+t+" = "+(t === "object" ? "\n"+inspect(obj[p], depth+1,searchdepth,showinherited) : obj[p])+"\n";
    } else {
      if (showinherited) {
        if (p!=="innerHTML" && p!=="outerHTML")
          log+=depth+decal+"obj["+p+"] of type "+t+" (inh) = "+ (t === "object" ? "\n"+inspect(obj[p], depth+1,searchdepth,showinherited) : obj[p])+"\n";        
      }
    }
  }
  return log;
}
