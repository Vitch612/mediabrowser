var audio;
var canplay=false;

$(document).ready(function() {
  audio = $("#aplay");  
  if (audio.attr("autoplay")==="string")
    audio[0].play();
  
  /*
  setTimeout(function() {
    if (canplay===false) {
      audio.remove();
      $(".mediadiv").html("Cannot play file");
    }
  }, 10000);
  */
  
  audio.on("ended", function () {
  });
  audio.on("canplay", function () {
    canplay=true;
  });
  audio.on("error", function () {
    alert("error");
  });
  audio.on("stalled", function () {
    alert("stalled");
  });


});
