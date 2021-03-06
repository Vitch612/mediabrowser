<?php
include "include.php";
if (isset($_REQUEST["entry"])) {
  $playlistid = $mysql->conn->real_escape_string($_REQUEST["playlist"]);
  $playlist = $mysql->select("playlist", ["Name"], "`ID`='" . $playlistid . "'");
  if (count($playlist) > 0) {
    $entries = $mysql->select("playlistentries", ["*"], "`Playlist`='" . $playlistid . "'");
    $numentries = count($entries);
    $entry = $entries[$_REQUEST["entry"]];
    $file = $mysql->select("files", ["Path", "Share"], "`ID`='" . $entry["File"] . "'");
    if (count($file) > 0) {
      $share = $mysql->select("shares", ["Path"], "`ID`='" . $file[0]["Share"] . "'");
      if (count($share) > 0) {
        $path = $share[0]["Path"] . $file[0]["Path"];
        $fullurl = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $base . "/file/" . base64_encode($path) . substr($path, strrpos($path, "."));
        die($fullurl.",".base64_encode("<a href=\"$fullurl\" title=\"".$path."\"class=\"filelink\">".basename($path)."</a>"));
      }
    }
  }
  header("HTTP/1.1 404 Invalid Request");
  die("<h3>File Not Found</h3>");
} else {
  include "head.php";
  show_nav();
  $playlistid = $mysql->conn->real_escape_string($_REQUEST["playlist"]);
  $playlist = $mysql->select("playlist", ["Name"], "`ID`='" . $playlistid . "'");
  $srturl="";
  if (count($playlist) > 0) {
    $entries = $mysql->select("playlistentries", ["*"], "`Playlist`='" . $playlistid . "'");
    $numentries = count($entries);
    $entry = $entries[0];
    $file = $mysql->select("files", ["Path", "Share"], "`ID`='" . $entry["File"] . "'");
    if (count($file) > 0) {
      $share = $mysql->select("shares", ["Path"], "`ID`='" . $file[0]["Share"] . "'");
      if (count($share) > 0) {
        $path = $share[0]["Path"] . $file[0]["Path"];
        $fullurl = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $base . "/file/" . base64_encode($path) . substr($path, strrpos($path, "."));
        if (strrpos($path,".")!==false) {
          if (file_exists(substr($path,0,strrpos($path,".")).".srt")) {
            $srturl = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $base . "/file/" . base64_encode(substr($path,0,strrpos($path,".")).".srt") . ".srt";
          }
        }
      }
    }
  }
  if (check_permission($path)) {
    if (file_exists($path) && is_file($path)) {
      $filename = substr($path, strrpos($path, "/") + 1);
      echo '<div class="row box"><div class="col-xs-12 mediacontainer"><div class="row"><div class="col-xs-12">&#8634;&nbsp;<input style="margin-right:10px;" type="checkbox" checked name="loop" class="form-check-input">&#8605;&nbsp;<input style="margin-right:10px;" type="checkbox" checked name="shuffle" class="form-check-input"><a href="#" class="previousentry" style="font-size:22px;margin-right:15px;margin-left:10px;">&#9194;</a><a class="nextentry" style="font-size:22px;" href="#">&#9193;</a><BR><span class="entryname"><a href="'.$fullurl.'" title="'.$path.'" class="filelink">'.$filename.'</a></span></div></div>';
      $type;
      switch ($file_types[get_file_type($path)]) {
        case "video":
          $type="video";
          if (strlen($srturl)>0 && !$mobile) {
            echo '<script type="text/javascript" src="'.$base.'/js/videosub-0.9.9.js"></script>';
            echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><video style=\"margin-top:10px;height:auto;\" id=\"avplay\" controls><source src=\"$fullurl\" type=\"video/mp4\">
                  <track label=\"English\" kind=\"subtitles\" srclang=\"en\" src=\"$srturl\" default>
                  Your browser does not support the video tag.</video></div></div>";
          }
          else
            echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><video style=\"margin-top:10px;height:auto;\" id=\"avplay\" controls><source src=\"$fullurl\" type=\"video/mp4\">Your browser does not support the video tag.</video></div></div>";
          break;
        case "audio":
          $type="audio";
          echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><audio style=\"margin-top:10px;\" id=\"avplay\" controls autoplay><source src=\"$fullurl\" type=\"audio/mpeg\">Your browser does not support the audio element.</audio></div></div>";
          break;
        case "image":
          $type="image";
          echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><div id=\"background\" style=\"display:none;\"></div><img  style=\"margin-top:10px;max-height:73vh;\" id=\"viewimage\" class=\"img-responsive\" src=\"$fullurl\"/></div></div>";
          break;
        default:
          $type="other";
          echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\">Unhandled media type. Download to device by clicking above link.</div></div>";
      }
      echo "</div></div>";
 echo '<script>
      var mediatype="'.$type.'";
      var player;
      var canplay=false;
      var pl_length='.$numentries.';
      var currententry=0;
      var previousentry=0;
      var currentplaylist='.$playlistid.';
      var retry=0;
      var tryingtoplay=false;

      function pointtorand() {
        currententry=Math.round((Math.random() * (pl_length-1)));
      }

      function addmsg(text) {
        $(".message").html($(".message").html()+" "+text);
      }

      function isPlaying() {
        if (player.error===null)
          return player.currentTime > 0 && !player.paused && !player.ended && player.readyState > 2;
        else
          return player.error.code==0 && player.currentTime > 0 && !player.paused && !player.ended && player.readyState > 2;
      }

      function tryplay(first) {
        first=first||false;
        if (first) {
          if (tryingtoplay) {
            //addmsg("firsttryrejected");
            return;
          } else {
            //addmsg("firsttry");
            tryingtoplay=true;
          }
        }
        var numretries=30;
        if (mediatype=="video")
          numretries=250;
        if (!isPlaying()) {
          if (retry>=numretries) {
            addmsg("tryfailed");
            tryingtoplay=false;
            getnext();
          } else {
            player.play();
            retry++;
            setTimeout(tryplay,50);
          }
        } else {
          tryingtoplay=false;
          retry=0;
        }
      }

      function pointtonext() {
        var loop=$("input[name=\'loop\']").is(":checked");
        var shuffle=$("input[name=\'shuffle\']").is(":checked");
        previousentry=currententry;
        if (shuffle) {
          pointtorand();
          return true;
        }
        if (currententry<pl_length-1) {
          currententry++;
          return true;
        }
        if (currententry>=pl_length-1 && loop) {
          currententry=0;
          return true;
        }
        return false;
      }

      function pointtoprev() {
        var loop=$("input[name=\'loop\']").is(":checked");
        var shuffle=$("input[name=\'shuffle\']").is(":checked");
        if (shuffle) {
          var tmp=currententry;
          currententry=previousentry;
          previousentry=tmp;
          return true;
        }
        previousentry=currententry;
        if (currententry>0) {
          currententry--;
          return true;
        }
        if (currententry<=0 && loop) {
          currententry=pl_length-1;
          return true;
        }
        return false;
      }

      function imageanim() {
        if (!pause)
          getnext();
        setTimeout(imageanim,2000);
      }

      function getnext(d) {
        d = d || false;
        var cur=currententry;
        var prev=previousentry;
        retry=0;
        var doload;
        if (d)
          doload=pointtoprev();
        else
          doload=pointtonext();
        if (doload) {
          $.ajax({
            url: "'.$base.'/showplaylist.php",
            method: "POST",
            data: {playlist:currentplaylist,entry:currententry}
          }).done(function (data) {
            var ret=data.split(",");
            if (mediatype=="audio" || mediatype=="video") {
              player.src=ret[0];
            } else if (mediatype=="image") {
              $("#viewimage")[0].src=ret[0];
            }
            $(".entryname").html(atob(ret[1]));
          }).fail(function() {
            currententry=cur;
            previousentry=prev;
            setTimeout(getnext,250,d);
          });
        }
      };
      var fullscreen=false;
      var pause=false;
      function togglefullscreen() {
        if (fullscreen) {
          $("#background").css("display","none");
          $("#background").css("position","");
          $("#background").css("top","");
          $("#background").css("left","");
          $("#background").css("margin-top","");
          $("#background").css("width","");
          $("#background").css("height","");
          $("#background").css("min-width","");
          $("#background").css("min-height","");
          $("#background").css("z-index","");
          $("#background").css("background-color","");
          $("#viewimage").css("position","");
          $("#viewimage").css("top","");
          $("#viewimage").css("left","");
          $("#viewimage").css("margin-top","10px");
          $("#viewimage").css("margin-left",";");
          $("#viewimage").css("margin-right",";");
          $("#viewimage").css("min-height","");
          $("#viewimage").css("min-width","");
          $("#viewimage").css("max-width","");
          $("#viewimage").css("max-height","73vh");
          $("#viewimage").css("z-index","");
          $("#viewimage").css("transform","");
        } else {
          $("#background").css("display","block");
          $("#background").css("position","fixed");
          $("#background").css("top","0");
          $("#background").css("left","0");
          $("#background").css("margin-top","0");
          $("#background").css("width","100vh");
          $("#background").css("height","100vh");
          $("#background").css("min-width","100vw");
          $("#background").css("min-height","100vh");
          $("#background").css("z-index","10");
          $("#background").css("background-color","white");
          $("#viewimage").css("position","fixed");
          $("#viewimage").css("top","50%");
          $("#viewimage").css("left","50%");
          $("#viewimage").css("margin-top","0");
          $("#viewimage").css("margin-left","auto;");
          $("#viewimage").css("margin-right","auto;");
          $("#viewimage").css("min-height","100vh");
          $("#viewimage").css("max-height","100vh");
          $("#viewimage").css("max-width","100vw");
          $("#viewimage").css("z-index","20");
          $("#viewimage").css("transform","translate(-50%, -50%)");
        }
        fullscreen=!fullscreen;
      }
      $(document).ready(function() {
        $(document).keydown(function(e) {
          //alert(e.keyCode);
          if (e.keyCode==27 && fullscreen) {
            togglefullscreen();
          } else if (e.keyCode==13 || e.keyCode==0) {
            getnext();
          } else if (e.keyCode==32) {
            pause=!pause;
            if (player!==undefined) {
              if (player.paused) {
                player.play();
              } else {
                player.pause();
              }
            }
          } else if (e.keyCode==37) {
            getnext(true);
          } else if (e.keyCode==39) {
            getnext();
          }
          return false;
        });
        if (mediatype=="image") {
          setTimeout(imageanim,2000);
          $("#viewimage").on("load",function() {
            if (fullscreen) {
              var maxwidth= window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
              var maxheight= window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
              if ($("#viewimage")[0].naturalWidth/maxwidth>$("#viewimage")[0].naturalHeight/maxheight) {
                $("#viewimage").css("min-width","100vw");
                $("#viewimage").css("min-height","");
              } else {
                $("#viewimage").css("min-height","100vh");
                $("#viewimage").css("min-width","");
              }
            }
          });
          $("#viewimage").click(function() {
            togglefullscreen();
          });
        }
        $(".previousentry").click(function() {
          getnext(true);
        });
        $(".nextentry").click(function() {
          getnext();
        });
        if (mediatype=="audio" || mediatype=="video") {
          player = $("#avplay")[0];
          player.onloadedmetadata = function() {
            //addmsg("loadedmetadata");
          }
          player.onplaying= function() {
            player.playbackRate=1;
          }
          player.oncanplay = function() {
            //addmsg("canplay");
            tryplay(true);
          };
          player.onabort = function() {
            //addmsg("abort");
          };
          player.onerror = function() {
            addmsg("Error("+player.error.code+") "+player.error.message);
            if (player.error.code==3 || player.error.code==4) {
              getnext();
            } else
              tryplay(true);
          };
          player.onstalled = function() {
            //addmsg("stalled");
            tryplay(true);
          };
          player.onended  = function() {
            //addmsg("ended");
            getnext();
          };
        }
      });
      </script>';
      echo '<div class="row"><div class="col-xs-12 message"></div></div>';
    } else {
      header("HTTP/1.1 404 Invalid Request");
      die("<h3>File Not Found</h3>");
    }
  } else {
    header("HTTP/1.1 403 Access Denied");
    die("<h3>Access Denied</h3>");
  }
  echo '</div></body></html>';
}