<?php
include "include.php";
include "head.php";
show_nav();

$path = base64_decode(substr($_SERVER["REQUEST_URI"],strrpos($_SERVER["REQUEST_URI"],"/")+1));
$path = str_replace("\\","/",clean_dirpath($path));
$fullurl=$_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"].$base."/file/".substr($_SERVER["REQUEST_URI"],strrpos($_SERVER["REQUEST_URI"],"/")+1).substr($path,strrpos($path,"."));

if (check_permission($path)) {
  if (file_exists($path) && is_file($path)) {
    $filename = utf8_encode(substr($path, strrpos($path, "/") + 1));
    echo "<div class=\"row box\"><div class=\"col-xs-12 mediacontainer\"><div class=\"row\"><div class=\"col-xs-12\">Direct Link <a href=\"$fullurl\" class=\"filelink\">$filename</a></div></div>";
    switch ($file_types[get_file_type($path)]) {
      case "video":
        $srturl="";        
        if (file_exists(substr($path,0,strrpos($path,".")).".srt")) {
          $srturl = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $base . "/file/" . base64_encode(substr($path,0,strrpos($path,".")).".srt") . ".srt";
        }
        if (strlen($srturl)>0)          
          echo '<script type="text/javascript" src="'.$base.'/js/videosub-0.9.9.js"></script>';
        echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><video style=\"margin-top:10px;height:auto;\" id=\"avplay\" controls><source src=\"$fullurl\" type=\"video/mp4\">";
        if (strlen($srturl)>0)          
          echo "<track label=\"English\" kind=\"subtitles\" srclang=\"en\" src=\"$srturl\" default>";
        echo "Your browser does not support the video tag.</video></div></div>";
        break;
      case "audio":
        echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><audio style=\"margin-top:40px;\" id=\"avplay\" controls><source src=\"$fullurl\" type=\"audio/mpeg\">Your browser does not support the audio element.</audio></div></div>";
        break;
      case "image":
        echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><img class=\"img-responsive\" src=\"$fullurl\"/></div></div>";
        break;
      case "html":
        echo '<div class="row"><div class="col-xs-12 mediadiv form-group">
              <script>
              $(document).ready(function() {
                $("#podisp").click(function() {
                  $("#podispbox").show();
                  $("#srcdispbox").hide();
                });
                $("#srcdisp").click(function() {
                  $("#podispbox").hide();
                  $("#srcdispbox").show();
                });
              });
              </script>
              <span id="podisp" class="filelink">Processed Output</span>&nbsp;/&nbsp;<span id="srcdisp" class="filelink">Source</span>
              <div id="podispbox">'.file_get_contents($fullurl).'</div>
              <div id="srcdispbox" style="display:none;"><textarea class="form-control" rows="19">'. file_get_contents($fullurl) . '</textarea></div>              
              </div></div>';
        break;
      case "text":
        echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv form-group\"><textarea class=\"form-control\" rows=\"30\" cols=\"50\" class=\"form-control\">" . file_get_contents($fullurl) . "</textarea></div></div>";
        break;
      default :
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
