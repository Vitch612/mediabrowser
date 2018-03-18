var audio;
var canplay=false;

$(document).ready(function() {
  audio = $("#aplay");    
  audio.on("ended", function () {
  });
  audio.on("canplay", function () {
  });
  audio.on("error", function () {
  });
  audio.on("stalled", function () {
  });
  audio.on("abort", function () {
  });
});
