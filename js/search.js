function iteratethroughresults() {
  if (xhr.responseText !==undefined)
    if (xhr.responseText.length>lastsize) {
      $("#displaytext").html(xhr.responseText);
      lastsize=xhr.responseText.length;
    }  
  if (searchresults.readyState!==4)
    setTimeout(iteratethroughresults,100);    
}

var lastsize=0;
var searchresults;
var xhr = $.ajaxSettings.xhr();

function xhrProvider() {
  return xhr;
}

$(document).ready(function() {  
  $("#searchbutton").click(function(event) {
    lastsize = 0;
    xhr.abort();
    $("#displaytext").html("");
    searchresults = $.ajax({
      url: "search.php",
      method: "POST",
      data: { searchstring: $("#searchstring").val()},
      context: document.body,
      xhr: xhrProvider,
      beforeSend: function () {
        setTimeout(iteratethroughresults,10);
      }
    }).done(function() {
    });
    event.preventDefault();
  });  
});