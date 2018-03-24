<?php
include "include.php";
authenticate();
include "head.php";
$names=[];
$words=[];
$rwords=[];
$wordcount=0;
function getMP3BitRateSampleRate($filename)
{
    if (!file_exists($filename)) {
        return false;
    }

    $bitRates = array(
                      array(0,0,0,0,0),
                      array(32,32,32,32,8),
                      array(64,48,40,48,16),
                      array(96,56,48,56,24),
                      array(128,64,56,64,32),
                      array(160,80,64,80,40),
                      array(192,96,80,96,48),
                      array(224,112,96,112,56),
                      array(256,128,112,128,64),
                      array(288,160,128,144,80),
                      array(320,192,160,160,96),
                      array(352,224,192,176,112),
                      array(384,256,224,192,128),
                      array(416,320,256,224,144),
                      array(448,384,320,256,160),
                      array(-1,-1,-1,-1,-1),
                    );
    $sampleRates = array(
                         array(11025,12000,8000), //mpeg 2.5
                         array(0,0,0),
                         array(22050,24000,16000), //mpeg 2
                         array(44100,48000,32000), //mpeg 1
                        );
    $bToRead = 1024 * 12;

    $fileData = array('bitRate' => 0, 'sampleRate' => 0);
    $fp = fopen($filename, 'r');
    if (!$fp) {
        return false;
    }
    //seek to 8kb before the end of the file
    fseek($fp, -1 * $bToRead, SEEK_END);
    $data = fread($fp, $bToRead);

    $bytes = unpack('C*', $data);
    $frames = array();
    $lastFrameVerify = null;

    for ($o = 1; $o < count($bytes) - 4; $o++) {

        //http://mpgedit.org/mpgedit/mpeg_format/MP3Format.html
        //header is AAAAAAAA AAABBCCD EEEEFFGH IIJJKLMM
        if (($bytes[$o] & 255) == 255 && ($bytes[$o+1] & 224) == 224) {
            $frame = array();
            $frame['version'] = ($bytes[$o+1] & 24) >> 3; //get BB (0 -> 3)
            $frame['layer'] = abs((($bytes[$o+1] & 6) >> 1) - 4); //get CC (1 -> 3), then invert
            $srIndex = ($bytes[$o+2] & 12) >> 2; //get FF (0 -> 3)
            $brRow = ($bytes[$o+2] & 240) >> 4; //get EEEE (0 -> 15)
            $frame["channels"]=($bytes[$o+3] & 192) >> 6;            
            $frame['padding'] = ($bytes[$o+2] & 2) >> 1; //get G
            if ($frame['version'] != 1 && $frame['layer'] > 0 && $srIndex < 3 && $brRow != 15 && $brRow != 0 &&
                (!$lastFrameVerify || $lastFrameVerify === $bytes[$o+1])) {
                //valid frame header

                //calculate how much to skip to get to the next header
                $frame['sampleRate'] = $sampleRates[$frame['version']][$srIndex];
                if ($frame['version'] & 1 == 1) {
                    $frame['bitRate'] = $bitRates[$brRow][$frame['layer']-1]; //v1 and l1,l2,l3
                } else {
                    $frame['bitRate'] = $bitRates[$brRow][($frame['layer'] & 2 >> 1)+3]; //v2 and l1 or l2/l3 (3 is the offset in the arrays)
                }

                if ($frame['layer'] == 1) {
                    $frame['frameLength'] = (12 * $frame['bitRate'] * 1000 / $frame['sampleRate'] + $frame['padding']) * 4;
                } else {
                    $frame['frameLength'] = 144 * $frame['bitRate'] * 1000 / $frame['sampleRate'] + $frame['padding'];
                }

                $frames[] = $frame;
                $lastFrameVerify = $bytes[$o+1];
                $o += floor($frame['frameLength'] - 1);
            } else {
                $frames = array();
                $lastFrameVerify = null;
            }
        }
        if (count($frames) < 3) { //verify at least 3 frames to make sure its an mp3
            continue;
        }

        $header = array_pop($frames);
        $fileData['sampleRate'] = $header['sampleRate'];
        $fileData['bitRate'] = $header['bitRate'];
        $fileData['channels'] = ($header['channels'] ==3?"mono":"stereo");
        break;
    }
    return $fileData;
}

function dirscan($dirpath) {
  global $names;
  global $words;
  global $rwords;
  global $wordcount;
  $allowed="abcdefghijklmnopqrstuvwyz ";
  $dir_handle = @opendir($dirpath) or die;
  while (($file = readdir($dir_handle))) {
    if ($file == "." || $file == "..") {
      continue;
    }
    $fullpath = $dirpath . "/" . $file;
    if (is_dir($fullpath)) {
      dirscan($fullpath);
    } else {
      $nametmp=strtolower(substr($file,0,strrpos($file,".")));      
      $name="";
      $previous="";
      for ($i = 0; $i < strlen($nametmp); $i++) {
        $char=$nametmp[$i];
        if ($char=="_" || $char=="-" || $char==".") {
          $char=" ";
        }
        if ($previous!="") {
          if ($previous==" " && $char==" ") {
            $char="";
          }
        }
        if ($char!="")
          $previous=$char;          
        if (strpos($allowed,$char)!==false) {
          $name .= $char;
        }
      }
      $namewords = explode(" ", $name);
      $namesig = [];
      foreach ($namewords as $word) {
        if ($word != "") {
          if (!isset($words[$word])) {
            $words[$word] = $wordcount;
            $rwords[$wordcount] = $word;
            $wordcount++;
          }
          $namesig[$words[$word]] = $words[$word];
        }
      }
      asort($namesig);
      $namecode = "";
      foreach($namesig as $point) {
        $namecode.=$point."/";
      }      
      if (isset($names[$namecode])) {
        $names[$namecode]["count"]++;
        $names[$namecode]["path"][]=$fullpath;
      } else {
        $names[$namecode]["sig"]=$namesig;
        $names[$namecode]["name"]=$name;
        $names[$namecode]["count"]=1;
        $names[$namecode]["path"][]=$fullpath;
      }
    }
  }
  closedir($dir_handle);        
}

ini_set('max_execution_time', 0);
ob_implicit_flush(true);
show_nav();
echo '<div class="row box"><div class="col-xs-12">Please wait for directory scan before results start being displayed. <img width="20" height="20" class="progress" src="pix/progress.gif"><BR>notes:<BR>&nbsp;&nbsp;- bitrates are inaccurate in case of vbr mp3 files<BR>&nbsp;&nbsp;- delete buttons only work after all results are displayed<BR><BR>';
ob_flush();
foreach ($shares as $share=>$info) {
  if ($info["searchable"]==1)
    dirscan(substr($share,0,strlen($share)-1));
}

$count1=0;
$count2=0;
echo '<script>
var target;
$(document).ready(function() {
  var background;
  $(".progress").hide();
  $(".song").mouseenter(function() {
    background=$(this).css("background-color");
    $(this).css("background-color","pink");
  }).mouseleave(function() {  
    $(this).css("background-color",background);
  });
  $(".delete").click(function() {
    target=$(this);
    if (confirm("Are you sure you want to delete this file?")) {      
      $.ajax({
         url: "'.$base.'/deletefile.php",
         method: "POST",
        data: {file:$(this).attr("target")}
      }).done(function(data) {
        if (data!="OK") {
          alert(data);
        } else {
          target.parent().remove();      
        }
      });
    }
  });
});
</script>';
  
foreach($names as $namecode=>$entry) {
  if ($entry["count"]>1 && count($entry["sig"])>1) {
    $string="";
    $result="<B>".$entry["name"]."</B><BR>";
    $count1++;
    $previous="";    
    foreach($entry["path"] as $file) {
      $mark=false;
      if ($previous!="")
        if (strtolower(basename($file))!=$previous)
          $mark=true;
      $previous=strtolower(basename($file));
      $count2++;
      if (strtolower(substr($file,strlen($file)-4))==".mp3") {
          $mp3info=getMP3BitRateSampleRate($file);          
          $result.="<span><img class=\"delete\" target=\"".base64_encode($file)."\" src=\"pix/delete.png\"/><a title=\"".utf8_encode($file)."\" target=\"_blank\" href=\"show/".base64_encode($file)."\">".utf8_encode(basename($file))."</a> (".filesize($file).") ".$mp3info["channels"]."/".$mp3info["bitRate"]."/".$mp3info["sampleRate"]."<BR></span>";
      } else {
        $result.="<span><img class=\"delete\" target=\"".base64_encode($file)."\" src=\"pix/delete.png\"/><a  title=\"".utf8_encode($file)."\" target=\"_blank\" href=\"show/".base64_encode($file)."\">".utf8_encode(basename($file))."</a> (".filesize($file).")<BR></span>";  
      }
    }
    echo "<div class=\"song\" style=\"padding:3px;margin-bottom:10px;".($mark?"background-color:lightgreen;":"background-color:lightyellow;")."\">$result</div>";
  }
}

foreach($names as $namecode=>$entry) {
  if ($entry["count"]>1 && count($entry["sig"])==1) {
    $string="";
    $result="<B>".$entry["name"]."</B><BR>";
    $count1++;
    $previous="";
    
    foreach($entry["path"] as $file) {
      $mark=false;
      if ($previous!="")
        if (strtolower(basename($file))!=$previous)
          $mark=true;
      $previous=strtolower(basename($file));
      $count2++;
      if (strtolower(substr($file,strlen($file)-4))==".mp3") {
          $mp3info=getMP3BitRateSampleRate($file);          
          $result.="<span><img class=\"delete\" target=\"".base64_encode($file)."\" src=\"pix/delete.png\"/><a title=\"".utf8_encode($file)."\" target=\"_blank\" href=\"show/".base64_encode($file)."\">".utf8_encode(basename($file))."</a> (".filesize($file).") ".$mp3info["channels"]."/".$mp3info["bitRate"]."/".$mp3info["sampleRate"]."<BR></span>";
      } else {
        $result.="<span><img class=\"delete\" target=\"".base64_encode($file)."\" src=\"pix/delete.png\"/><a  title=\"".utf8_encode($file)."\" target=\"_blank\" href=\"show/".base64_encode($file)."\">".utf8_encode(basename($file))."</a> (".filesize($file).")<BR></span>";  
      }
    }
    echo "<div class=\"song\" style=\"padding:3px;margin-bottom:10px;".($mark?"background-color:Lightsteelblue;":"background-color:Lightskyblue;")."\">$result</div>";
  }
}
echo $count1." ".$count2."<BR>";
echo '</div></div></div></body></html>';