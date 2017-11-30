function getstatus() {
  if (video[0].paused) {
    alert("paused");
  } else {
    alert("playing");
  }  
}

var video;
var canplay=false;

$(document).ready(function() {
  video = $("#vplay");
  if (typeof video.attr("autoplay")==="string")
    video[0].play();
  //dump(video[0]);
  
  /*
  setTimeout(function() {
    if (canplay===false) {
      video.remove();
      $(".mediadiv").html("Cannot play file");
    }
  }, 10000);
  */
 
  video.on("ended", function () {    
  });
  video.on("canplay", function () {
    canplay=true;
  });
  video.on("error", function () {
    alert("error");
  });
  video.on("stalled", function () {
    alert("stalled");
  });


});


