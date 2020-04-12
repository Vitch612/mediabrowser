<?php
$fullscreen=false;
if ($_GET["gofullscreen"]=="true") {    
    $fullscreen=true;
}
include "include.php";
include "head.php";
if ($fullscreen) 
    echo '<body style="padding:0;"><div class="container" style="width:100%;">';


function get_siblings($dirpath,$cpatho) {
    $filelist=[];
    $prev=null;
    $next=null;
    $dir_handle = @opendir($dirpath) or die;
    while ($file = readdir($dir_handle)) {
        if ($file == "." || $file == "..")
            continue;
        $TheLinkedFile = $dirpath . $file;        
        if (is_dir($TheLinkedFile)) {
            continue;
        } else {
            $pcpath=$cpath;
            $cpath = base64_encode($TheLinkedFile);
            if ($cpath==$cpatho) {
                $prev=$pcpath;
            }
            
            if ($pcpath==$cpatho) {           
                $next=$cpath;
            }
        }
        if ($next !== null) 
            break;
    }
    return ["prev"=>$prev,"next"=>$next];
}



$fullscreen=false;
if ($_GET["gofullscreen"]=="true") {    
    $fullscreen=true;
}
if (!$fullscreen)
    show_nav();
$url=strtok($_SERVER["REQUEST_URI"], '?');
$path = base64_decode(substr($url,strrpos($url,"/")+1));
$cpath=substr($url,strrpos($url,"/")+1);
$path = str_replace("\\","/",clean_dirpath($path));
$folder=substr($path,0,strrpos($path,"/")+1);
$folderhash=base64_encode($folder);
$siblings=get_siblings($folder,$cpath);
$fullurl=$_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"].$base."/file/".substr($url,strrpos($url,"/")+1).substr($path,strrpos($path,"."));
if (check_permission($path)) {
  if (file_exists($path) && is_file($path)) {
    $filename = utf8_encode(substr($path, strrpos($path, "/") + 1));
    if ($fullscreen) 
        echo "<div class=\"row\">";
    else
        echo "<div class=\"row box\">";
    if ($siblings["next"]!==null)            
        echo '<div id="next">'.$siblings["next"]."</div>";
    if ($siblings["prev"]!==null)
        echo '<div id="previous">'.$siblings["prev"]."</div>";
    echo '<div id="folder">'.$folderhash."</div>";
    if (!$fullscreen) {
        echo '<div class="navlinks">';
        if ($siblings["prev"]!==null)
            echo "<a href=\"show/".$siblings["prev"]."\" class=\"prevlink\">&lt;&lt;</a>";        
        if ($siblings["next"]!==null)            
            echo "<a href=\"show/".$siblings["next"]."\" class=\"nextlink\">&gt;&gt;</a>";        
        echo '<a href="/browse/?path='.$folderhash.'" class="folderlink">Folder</a>';
        echo '</div>';
    }
    if ($fullscreen)
        echo "<div class=\"col-xs-12 mediacontainer\" style=\"padding:0;\">";
    else 
        echo "<div class=\"col-xs-12 mediacontainer\">";
    switch ($file_types[get_file_type($path)]) {
      case "video":
        $srturl="";        
        if (file_exists(substr($path,0,strrpos($path,".")).".srt")) {
          $srturl = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $base . "/file/" . base64_encode(substr($path,0,strrpos($path,".")).".srt") . ".srt";
        }
        if (strlen($srturl)>0 && !$mobile)
          echo '<script type="text/javascript" src="'.$base.'/js/videosub-0.9.9.js"></script>';
        echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><video style=\"margin-top:10px;height:auto;\" id=\"avplay\" controls><source src=\"$fullurl\" type=\"video/mp4\">";
        if (strlen($srturl)>0 && !$mobile)
          echo "<track label=\"English\" kind=\"subtitles\" srclang=\"en\" src=\"$srturl\" default>";
        echo "Your browser does not support the video tag.</video></div></div>";
        break;
      case "audio":
        echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><audio style=\"margin-top:40px;\" id=\"avplay\" controls><source src=\"$fullurl\" type=\"audio/mpeg\">Your browser does not support the audio element.</audio></div></div>";
        break;
      case "image":
        echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\"><a href=\"?gofullscreen=true\" class=\"fullscreenlink\"><img class=\"img-responsive img-fluid displayedimage\" style=\"width:100%;\" src=\"$fullurl\"/></img></div></div>";
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
      case "pdf":
        echo "<div class=\"row\"><a href=\"?gofullscreen=true\" class=\"fullscreenlink\"><div id=\"pdfdiv\" class=\"col-xs-12 mediadiv\"></div></a></div>";
        echo '<script>
                $(document).ready(function() {
                var pdffile = "'.$fullurl.'";
                var thePdf = null;
                var scale = 5;
                var query="";
                if (window.location.toString().lastIndexOf("?")>=0) {
                    query=window.location.toString().substr(window.location.toString().lastIndexOf("?")+1,window.location.toString().length);
                }
                window.pagenumber=1;
                if (query.indexOf("pagenumber")>0) {
                    query=query.substr(query.indexOf("pagenumber")+11,query.length);
                    if (query.indexOf("&")>0)
                        query=query.substr(0,query.indexOf("&"));
                    try {
                        window.pagenumber=parseInt(query);
                    } catch(e) {}
                }
                function renderPage(pageNumber, canvas) {
                    thePdf.getPage(pageNumber).then(function(page) {
                        viewport = page.getViewport(scale);
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;
                        $(canvas).css("display","");
                        page.render({canvasContext: canvas.getContext("2d"), viewport: viewport});
                    });
                }
                
                pdfjsLib.getDocument(pdffile).then(function(pdf) {
                    thePdf = pdf;
                    viewer = document.getElementById("pdfdiv");
                    canvas = document.createElement("canvas");
                    canvas.className = "pdf-page-canvas";                    
                    viewer.appendChild(canvas);
                    renderPage(window.pagenumber, canvas);
                });
                window.next=function() {
                    if (window.pagenumber<thePdf.numPages) {
                        window.pagenumber=window.pagenumber+1;
                        $(canvas).animate({"right":"2000px"},400,function() {
                            $(canvas).css("display","none");
                            $(canvas).css("right","");                            
                            renderPage(window.pagenumber, canvas);
                        });
                    }
                }
                window.prev=function() {
                    if (window.pagenumber>1) {
                        window.pagenumber=window.pagenumber-1;
                        $(canvas).animate({"left":"2000px"},400,function() {                            
                            $(canvas).css("display","none");
                            $(canvas).css("left","");
                            renderPage(window.pagenumber, canvas);
                        });                                                
                    }
                }
                
                });
             </script>';
        break;
      default :
        echo "<div class=\"row\"><div class=\"col-xs-12 mediadiv\">Unhandled media type. Download to device by clicking link below.</div></div>";
    }
    if (!$fullscreen)
        echo "<div class=\"row\"><div class=\"col-xs-12\" style=\"padding-bottom:5px;\">Direct Link <a href=\"$fullurl\" title=\"".$path."\" download=\"".$filename."\" class=\"filelink\">$filename</a><img src=\"../pix/copy.png\" class=\"copybutton\" data-text=\"$fullurl\"/></div></div>";
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
