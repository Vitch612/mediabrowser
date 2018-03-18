<?php
error_reporting(E_ERROR | E_PARSE);
include "database.php";
$max_search=1;
$shares=["C:/movies"=>1,"E:/Media/Audio"=>0];
$file_types=["image","audio","video","pdf","zip","exe","html","folder","other"];
$file_icons=["pix/image.png","pix/mp3.png","pix/video.png","pix/pdf.png","pix/zip.png","pix/exe.png","pix/html.png","pix/folder.png","pix/text.png"];
$base=substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"/",1));
$folder=substr($_SERVER["SCRIPT_FILENAME"],0,strrpos($_SERVER["SCRIPT_FILENAME"],"/"));
$mysql=new database();

$datalock=false;

function get_id() {
  list($usec, $sec) = explode(' ', microtime());
  srand($sec + $usec * 1000000);
  $id="";
  for ($i=0;$i<10;$i++) {
    $id.=rand(0,9);
  }
  return $id;
}

function logmsg($text) {
 global $folder;
 file_put_contents("$folder/logfile.txt",date("Y-m-d h:i:sa").": ".$text."\n",FILE_APPEND);
}

function readPersistent($name) {
  global $folder;
  while($datalock);
  $s = file_get_contents("$folder/data/data.sr");
  $a = unserialize($s);
  return $a[$name];
}

function deletePersistent($name) {
  global $folder;
  while($datalock);
  $s=[];
  $s = file_get_contents("$folder/data/data.sr");
  $a = unserialize($s);
  unset($a[$name]);
  $s=serialize($a);
  $datalock=true;
  while (!file_put_contents("$folder/data/data.sr", $s));
  $datalock=false;
}

function savePersistent($name,$value) {
  global $folder;
  while($datalock);
  $s=[];
  $s = file_get_contents("$folder/data/data.sr");
  $a = unserialize($s);
  $a[$name]=$value;
  $s=serialize($a);
  $datalock=true;
  while(!file_put_contents("$folder/data/data.sr", $s));
  $datalock=false;
}

function startWith($haystack,$needle,$case=true) {
  if ($case)
    return (strcasecmp(substr($haystack,0,strlen($needle)),$needle)===0);
  else
    return (strcmp(substr($haystack,0,strlen($needle)), $needle)===0);
}

function endWith($haystack,$needle,$case=true) {
	if($case)
		return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);
	else
    return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);
}

function get_file_type($file) {
  if (endWith($file,".gif",0000) || endWith($file,".png",0000) || endWith($file,".jpg",0000) || endWith($file,".jpeg",0000) || endWith($file,".bmp",0000))
	  return 0;
	else if (endWith($file,".mp3",0000) || endWith($file,".wmv",0000) || endWith($file,".wav",0000))
	  return 1;
	else if (endWith($file,".mkv",0000) || endWith($file,".avi",0000) || endWith($file,".mp4",0000)|| endWith($file,".3gp",0000)|| endWith($file,".mpeg",0000)|| endWith($file,".mpg",0000)|| endWith($file,".wmv",0000))
	  return 2;
	else if (endWith($file,".pdf",0000))
	  return 3;
	else if (endWith($file,".zip",0000) || endWith($file,".gz",0000) || endWith($file,".tgz",0000) || endWith($file,".rar",0000) || endWith($file,".tar",0000) || endWith($file,".jar",0000))
	  return 4;
	else if (endWith($file,".exe",0000) || endWith($file,".bat",0000) || endWith($file,".com",0000) || endWith($file,".vbs",0000) || endWith($file,".sh",0000))
	  return 5;
	else if (endWith($file,".htm",0000) || endWith($file,".html",0000) || endWith($file,".txt",0000) || endWith($file,".inf",0000) || endWith($file,".srt",0000) || endWith($file,".sub",0000) || endWith($file,".info",0000) )
	  return 6;
	else
	  return 8;
}

function show_nav() {
  global $base;
  echo "<div class=\"row\"><div class=\"col-xs-12 navmenu\"><ul><li><a href=\"$base\"><img src=\"$base/pix/home.png\"/></a></li><li>
    <a href=\"".$base."/playlists.php\"><img src=\"$base/pix/playlist.png\"/></a>
    </li><li><a href=\"".$base."/showduplicates.php\"><img style=\"margin-bottom:3px;\" src=\"$base/pix/duplicate.png\"/></a></li></ul></div></div>";
}

function clean_dirpath($path) {
  $concat=str_replace("\\","/",$path);
  while (($pos=strpos($concat,"/../"))>0) {
    $prev=substr($concat,0,$pos-4);
    if (($bef=strrpos($prev,"/"))>0)
      $concat=substr($concat,0,$bef).substr($concat,$pos+3);
    else
      return $path;
  }
  if (($pos=strpos($concat,"/.."))>0) {
    $prev=substr($concat,0,$pos-3);
    if (($bef=strrpos($prev,"/"))>0)
      $concat=substr($concat,0,$bef);
  }
  return $concat;
}

function check_permission($path) {
  global $shares;
  $allowed=false;
  foreach ($shares as $share=>$value) {
    if (startWith($path, $share, false)) {
      $allowed = true;
    }
  }
  return $allowed;
}