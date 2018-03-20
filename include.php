<?php
error_reporting(E_ERROR | E_PARSE);
include "database.php";
$mysql=new database();
$max_search=2;
$result=$mysql->select("shares",["*"]);
$shares=[];
foreach($result as $row) {
  $shares[$row["Path"]]=(int)$row["Searchable"];
}
$file_types=["image","audio","video","pdf","zip","exe","html","text","folder","other"];
$file_icons=["pix/image.png","pix/mp3.png","pix/video.png","pix/pdf.png","pix/zip.png","pix/exe.png","pix/html.png","pix/text.png","pix/folder.png"];
$base=substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"/",1));
$applicationfolder=substr($_SERVER["SCRIPT_FILENAME"],0,strrpos($_SERVER["SCRIPT_FILENAME"],"/"));

$datalock=false;
$seed=0;

function get_id() {  
  global $seed;
  if ($seed==0) {
    list($usec, $sec) = explode(' ', microtime());
    $seed=$sec + $usec * 1000000;
    srand($seed);
  }  
  $id="";
  for ($i=0;$i<10;$i++) {
    $id.=rand(0,9);
  }
  return $id;
}

function logmsg($text) {
 global $applicationfolder;
 file_put_contents("$applicationfolder/logfile.txt",date("Y-m-d h:i:sa").": ".$text."\n",FILE_APPEND);
}

function readPersistent($name) {
  global $applicationfolder;
  global $datalock;
  while($datalock);
  $s = file_get_contents("$applicationfolder/data/data.sr");
  $a = unserialize($s);
  return $a[$name];
}

function deletePersistent($name) {
  global $applicationfolder;
  global $datalock;
  while($datalock);
  $s=[];
  $s = file_get_contents("$applicationfolder/data/data.sr");
  $a = unserialize($s);
  unset($a[$name]);
  $s=serialize($a);
  $datalock=true;
  while (!file_put_contents("$applicationfolder/data/data.sr", $s));
  $datalock=false;
}

function savePersistent($name,$value) {
  global $applicationfolder;
  global $datalock;
  while($datalock);
  $s=[];
  $s = file_get_contents("$applicationfolder/data/data.sr");
  $a = unserialize($s);
  $a[$name]=$value;
  $s=serialize($a);
  $datalock=true;
  while(!file_put_contents("$applicationfolder/data/data.sr", $s));
  $datalock=false;
}

function startWith($haystack,$needle,$case=false) {
  if ($case)
    return (strcasecmp(substr($haystack,0,strlen($needle)),$needle)===0);
  else
    return (strcmp(substr($haystack,0,strlen($needle)), $needle)===0);
}

function endWith($haystack,$needle,$case=false) {
	if($case)
		return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);
	else
    return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);
}

function get_file_type($file) {
  if (endWith($file,".gif",0000) || endWith($file,".png",0000) || endWith($file,".jpg",0000) || endWith($file,".jpeg",0000) || endWith($file,".bmp",0000))
	  return 0;
	else if (endWith($file,".mp3",0000) || endWith($file,".wmv",0000) || endWith($file,".wav",0000) || endWith($file,".ogg",0000))
	  return 1;
	else if (endWith($file,".mkv",0000) || endWith($file,".avi",0000) || endWith($file,".mp4",0000)|| endWith($file,".3gp",0000)|| endWith($file,".mpeg",0000)|| endWith($file,".mpg",0000)|| endWith($file,".wmv",0000))
	  return 2;
	else if (endWith($file,".pdf",0000))
	  return 3;
	else if (endWith($file,".zip",0000) || endWith($file,".gz",0000) || endWith($file,".tgz",0000) || endWith($file,".rar",0000) || endWith($file,".tar",0000) || endWith($file,".jar",0000))
	  return 4;
	else if (endWith($file,".exe",0000) || endWith($file,".bat",0000) || endWith($file,".com",0000) || endWith($file,".vbs",0000) || endWith($file,".sh",0000))
	  return 5;
	else if (endWith($file,".htm",0000) || endWith($file,".html",0000))
	  return 6;
	else if (endWith($file,".txt",0000) || endWith($file,".inf",0000) || endWith($file,".srt",0000) || endWith($file,".sub",0000) || endWith($file,".ini",0000))
	  return 7;
  else if (is_dir($file))
    return 8;  
	else
	  return 9;
}

function show_nav() {
  global $base;
  echo "<div class=\"row\"><div class=\"col-md-12 navmenu\"><ul class=\"list-group\">
   <li class=\"list-group-item\"><a title=\"Homepage\" href=\"$base\"><img src=\"$base/pix/home.png\"/></a></li>
   <li class=\"list-group-item\"><a title=\"Manage Playlists\" href=\"$base/playlists.php\"><img src=\"$base/pix/playlist.png\"/></a></li>
   <li class=\"list-group-item\"><a id=\"DuplicatesPage\" title=\"Manage Duplicates Sorted By Name\" href=\"$base/showduplicates.php\"><img src=\"$base/pix/duplicate.png\"/></a></li>
   <li class=\"list-group-item\"><a id=\"ScanPage\" title=\"Scan For Duplicates Based On File Contents And Length\" href=\"#\"><img src=\"$base/pix/scan.png\"/></a></li>
   <li class=\"list-group-item\"><a title=\"Settings Configuration\" href=\"$base/settings.php\"><img src=\"$base/pix/settings.png\"/></a></li>
    </ul></div></div>";
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
    if (startWith($path, $share)) {
      $allowed = true;
    }
  }
  return $allowed;
}

function authenticate() {
  global $base;
  if (readPersistent("password") != "") {
    session_start();    
    if (!isset($_SESSION["loggedin"])) {
      if (isset($_SERVER['PHP_AUTH_USER']))
        if (hash("sha3-512", $_SERVER['PHP_AUTH_PW']) == readPersistent("password"))
          $_SESSION["loggedin"] = time();
    } else {
      if (time() - $_SESSION["loggedin"] > 600) {
        unset($_SESSION["loggedin"]);
      } else {
        $_SESSION["loggedin"] = time();
      }
    }
    if (!isset($_SESSION["loggedin"])) {
      header('WWW-Authenticate: Basic realm="browse_' . get_id() . '"');
      header('HTTP/1.0 401 Unauthorized');
      include "head.php";
      show_nav();
      die('<div class="row box"><div class="col-xs-12">You need to be authenticated to access this section of the site</div></div></div></body></html>');
    }
    session_write_close();
  }
}
