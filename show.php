<?php

include "include.php";
include "head.php";
show_nav();

if (strpos($_SERVER["REQUEST_URI"],$base)==0) {
  $url=$_SERVER["REQUEST_URI"];
  $relurl=substr($url,strlen($base)+1);
  $relurl="../".str_replace("show", "file", $relurl);
} else {
  header("HTTP/1.1 404 Invalid Request");
  die("<h3>File Not Found</h3>");
}

$path = base64_decode(substr($_SERVER["REQUEST_URI"],strrpos($_SERVER["REQUEST_URI"],"/")+1));
$path = str_replace("\\","/",clean_dirpath($path));
$url.=".".strtolower(substr($path,strrpos($path,'.')+1));
$relurl.=".".strtolower(substr($path,strrpos($path,'.')+1));

if (check_permission($path)) {
  if (file_exists($path) && is_file($path)) {
    $filename = utf8_encode(substr($path, strrpos($path, "/") + 1));
    echo "<a href=\"$relurl\" class=\"filelink\">$filename</a>";
    switch ($file_types[get_file_type($path)]) {
      case "video":
        echo "<div class=\"mediadiv\"><video id=\"vplay\" width=\"320\" height=\"240\" controls><source src=\"$relurl\" type=\"video/mp4\">Your browser does not support the video tag.</video></div>";
        echo '<script type="text/javascript" src="' . $base . '/js/video.js"></script> ';
        break;
      case "audio":
        echo "<div class=\"mediadiv\"><audio id=\"aplay\" controls><source src=\"$relurl\" type=\"audio/mpeg\">Your browser does not support the audio element.</audio></div>";
        echo '<script type="text/javascript" src="' . $base . '/js/audio.js"></script> ';
        break;
      case "image":
        echo "<div class=\"mediadiv\"><img src=\"$relurl\"/></div>";
        break;
      case "html":
        $fullurl = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $url;
        echo "<div class=\"mediadiv form-group\"><textarea rows=\"30\" cols=\"50\" class=\"form-control\">" . file_get_contents($fullurl) . "</textarea></div>";
        break;
      default :
        echo "<div class=\"mediadiv\">Unhandled media type. Download to device by clicking above link.</div>";
    }
  } else {
    header("HTTP/1.1 404 Invalid Request");
    die("<h3>File Not Found</h3>");
  }
} else {
  header("HTTP/1.1 403 Access Denied");
  die("<h3>Access Denied</h3>");
}
include "foot.php";
