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
  $("#ScanPage").attr("href","scanfolders.php");
  $("#searchbutton").click(function(event) {
    $(".progress").show();
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
      $(".progress").hide();
    });
    event.preventDefault();
  });  
  $("#ScanPage").click(function(e) {
    if (!confirm("The scan page can take a long time to run, are you sure you want to open it?")) {
      e.preventDefault();
    }
  });  
});