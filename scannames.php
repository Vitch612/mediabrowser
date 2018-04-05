<?php
ini_set('max_execution_time', 0);
ini_set('memory_limit',2048);
include "include.php";
$count=0;
$charsets=[];
$notlinlist=[];
function dirsearch($dirpath) {
  global $count;   
  global $charsets;
  global $notinlist;
  $chars="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789()[]-_<>!&+.,'\"#;@~{}|:=%\$` ";
  $dir_handle = @opendir($dirpath) or die;
  while ($file = readdir($dir_handle)) {
    if ($file == "." || $file == "..")
      continue;
    $entry = $dirpath . "/" . $file;
    if (is_dir($entry)) {
      dirsearch($entry);
    } else {  
      if (isset($charsets[mb_detect_encoding($file)])) {
        $charsets[mb_detect_encoding($file)]++;
      } else {
        $charsets[mb_detect_encoding($file)]=1;
      }
      $displayed=false;
      for ($i=0; $i<strlen($file); $i++) {
        if (ord($file[$i])>=194 && ord($file[$i])<=223 && $i<strlen($file)-1) {
          $index=ord($file[$i])."-".ord($file[$i+1]);
          if (isset($notinlist[$index])) {
            $notinlist[$index]["count"]++;
            $notinlist[$index]["files"][$entry]=$file;
          } else {
            $notinlist[$index]["count"]=1;
            $notinlist[$index]["ord"]=ord($file[$i])." ".ord($file[$i+1]);
            $notinlist[$index]["value"]=$file[$i].$file[$i+1];
            $notinlist[$index]["files"][$entry]=$file;
          }
          if (!$displayed) {
            //echo mb_detect_encoding($file)."(".strlen($file).') <a href="#" title="'.$entry.'">'.$file.'</a><BR>';
            $displayed=true;
            $count++;
          }
          $i+=1;
        } else if (ord($file[$i])>=224 && ord($file[$i])<=239 && $i<strlen($file)-2) {
          $index=ord($file[$i])."-".ord($file[$i+1])."-".ord($file[$i+2]);
          if (isset($notinlist[$index])) {
            $notinlist[$index]["count"]++;
            $notinlist[$index]["files"][$entry]=$file;
          } else {
            $notinlist[$index]["count"]=1;
            $notinlist[$index]["ord"]=ord($file[$i])." ".ord($file[$i+1])." ".ord($file[$i+2]);
            $notinlist[$index]["value"]=$file[$i].$file[$i+1].$file[$i+2];
            $notinlist[$index]["files"][$entry]=$file;
          }
          if (!$displayed) {
            //echo mb_detect_encoding($file)."(".strlen($file).') <a href="#" title="'.$entry.'">'.$file.'</a><BR>';
            $displayed=true;
            $count++;
          }
          $i+=2;
        } else if (ord($file[$i])>=240 && ord($file[$i])<=255 && $i<strlen($file)-3) {
          $index=ord($file[$i])."-".ord($file[$i+1])."-".ord($file[$i+2])."-".ord($file[$i+3]);
          if (isset($notinlist[$index])) {
            $notinlist[$index]["count"]++;
            $notinlist[$index]["files"][$entry]=$file;
          } else {
            $notinlist[$index]["count"]=1;
            $notinlist[$index]["ord"]=ord($file[$i])." ".ord($file[$i+1])." ".ord($file[$i+2])." ".ord($file[$i+3]);
            $notinlist[$index]["value"]=$file[$i].$file[$i+1].$file[$i+2].$file[$i+3];
            $notinlist[$index]["files"][$entry]=$file;
          }
          if (!$displayed) {
            //echo mb_detect_encoding($file)."(".strlen($file).') <a href="#" title="'.$entry.'">'.$file.'</a><BR>';
            $displayed=true;
            $count++;
          }
          $i+=3;
        } else if (ord($file[$i])>128) {
          $notinlist["other"][]=ord($file[$i])." ".ord($file[$i+1])." ".ord($file[$i+2])." ".$file[$i].$file[$i+1].$file[$i+2]." ".$file;
        }  
      }      
    }
  }
  closedir($dir_handle);
}


include "head.php";
show_nav();
echo '<div class="row box"><div class="col-xs-12">';

//echo "<HR>"; 
//$str=chr(194).chr(129);
//$str=chr(194).chr(160);
//for ($i=0; $i<strlen($str); $i++) {
//  echo ord($str[$i])."<BR>";
//}
//display($str,"HTML-ENTITIES");
//echo mb_convert_encoding($str,"HTML-ENTITIES");
//echo "<HR>";
//
//echo "<pre>";
//$accents="«»°âàáåâäãÄÅÀÃÁæÆÿôöòóÖÙÓÚÕÔçÇïîìíÎÍêëèéÉÊûúùüÜÿñÑ";
//echo $accents."\n";
//$arr=[];
//function display($string,$encoding) {
//  echo strlen($string)." ".mb_detect_encoding($string)." ".ord($string[0])." ".mb_convert_encoding($string,$encoding)."\n";
//}
//// HTML-ENTITIES ISO-8859-1 Windows-1252
//for ($i=0; $i<strlen($accents); $i+=2) {
//  $sub=substr($accents,$i,2);
//  $arr[]=[ord($accents[$i]),ord($accents[$i+1]), $sub];
//}
//print_r($arr);
//echo "</pre>";

//$str='`';
//echo mb_detect_encoding($str)." ".ord($str);

/**/
foreach ($shares as $share => $info)
  if ($info["searchable"] == 1)
    dirsearch(substr($share, 0, strlen($share) - 1));
echo "<HR>$count<HR>";

asort($notinlist);
echo "<pre>";
print_r($notinlist);
echo "</pre>";

$filelist=[];
foreach($notinlist as $entry) {
  foreach($entry["files"] as $file=>$entry) {
    $filelist[$file]=$entry;
  }
}
echo count($filelist);
/**/

echo '</div></div></div></body></html>';


