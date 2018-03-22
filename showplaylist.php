<?php
include "include.php";
if (isset($_REQUEST["entry"])) {
  $playlistid = $mysql->conn->real_escape_string($_REQUEST["playlist"]);
  $playlist = $mysql->select("playlist", ["Name"], "`ID`='" . $playlistid . "'");
  if (count($playlist) > 0) {
    $entries = $mysql->select("playlistentries", ["*"], "`Playlist`='" . $playlistid . "'");
    $numentries = count($entries);
    $entry = $entries[$_REQUEST["entry"]];
    $file = $mysql->select("Files", ["Path", "Share"], "`ID`='" . $entry["File"] . "'");
    if (count($file) > 0) {
      $share = $mysql->select("Shares", ["Path"], "`ID`='" . $file[0]["Share"] . "'");
      if (count($share) > 0) {
        $path = $share[0]["Path"] . $file[0]["Path"];        
        $fullurl = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $base . "/file/" . base64_encode($path) . substr($path, strrpos($path, "."));        
        die($fullurl.",".base64_encode("<a href=\"$fullurl\" class=\"filelink\">".utf8_encode(basename($path))."</a>"));
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
  if (count($playlist) > 0) {
    $entries = $mysql->select("playlistentries", ["*"], "`Playlist`='" . $playlistid . "'");
    $numentries = count($entries);
    $entry = $entries[0];
    $file = $mysql->select("Files", ["Path", "Share"], "`ID`='" . $entry["File"] . "'");
    if (count($file) > 0) {
      $share = $mysql->select("Shares", ["Path"], "`ID`='" . $file[0]["Share"] . "'");
      if (count($share) > 0) {
        $path = $share[0]["Path"] . $file[0]["Path"];
        $fullurl = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $base . "/file/" . base64_encode($path) . substr($path, strrpos($path, "."));
      }
    }
  }

  echo '<script>
var aud;
var canplay=false;
var pl_length='.$numentries.';
var currententry=0;
var previousentry=0;
var currentplaylist='.$playlistid.';
  
function pointtorand() {
  currententry=Math.floor((Math.random() * (pl_length-1)));  
}

function pointtonext() {
  var loop=$("input[name=\'loop\']").is(":checked");
  var shuffle=$("input[name=\'shuffle\']").is(":checked");
  previousentry=currententry;  
  if (shuffle) {
    pointtorand();
    return true;
  }
  if (currententry<pl_length-2) {  
    currententry++;
    return true;
  }
  if (currententry>=pl_length-2 && loop) {
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
  if (currententry>1) {
    currententry--;
    return true;
  }
  if (currententry<=1 && loop) {
    currententry=pl_length-1;
    return true;
  }
  return false;
}

function getnext(d) {
  d = d || false;
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
      aud.src=ret[0];
      $(".entryname").html(atob(ret[1]));
    });
  }
};

$(document).ready(function() {
  $(".previousentry").click(function() {
    getnext(true);
  });
  $(".nextentry").click(function() {
    getnext();
  });
  aud = $("#aplay")[0];    
  aud.oncanplay = function() {
    aud.play();
  };
  aud.onerror = function() {
    getnext();
  };
  aud.onstalled = function() {
    alert("stalled");
  };
  aud.onended  = function() {
    getnext();
  };
});
</script>';

  if (check_permission($path)) {
    if (file_exists($path) && is_file($path)) {
      $filename = utf8_encode(substr($path, strrpos($path, "/") + 1));
      echo '<div class="row box"><div class="col-xs-12 mediacontainer"><div class="row" style="margin-bottom:20px;"><div class="col-xs-12">&#8634;&nbsp;<input style="margin-right:10px;" type="checkbox" name="loop" class="form-check-input">&#10542;&nbsp;<input style="margin-right:10px;" type="checkbox" name="shuffle" class="form-check-input"><a href="#" class="previousentry" style="margin-right:15px;">&#9194; previous</a><span class="entryname"><a href="'.$fullurl.'" class="filelink">'.$filename.'</a></span><a style="margin-left:15px;" class="nextentry" href="#">next &#9193;</a></div></div>';
      switch ($file_types[get_file_type($path)]) {
        case "video":
          echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><video id=\"vplay\" controls><source src=\"$fullurl\" type=\"video/mp4\">Your browser does not support the video tag.</video></div></div>";
          echo '<script type="text/javascript" src="' . $base . '/js/video.js"></script> ';
          break;
        case "audio":
          echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><audio id=\"aplay\" controls><source src=\"$fullurl\" type=\"audio/mpeg\">Your browser does not support the audio element.</audio></div></div>";
          break;
        case "image":
          echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><img class=\"img-responsive\" src=\"$fullurl\"/></div></div>";
          break;
        default:
          echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\">Unhandled media type. Download to device by clicking above link.</div></div>";
      }
      echo "</div></div>";
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
