<?php
error_reporting(E_ERROR | E_PARSE);
//function script_end() {
//  logmsg("Served Following Request:\n".print_r(["URL"=>$_SERVER["REQUEST_URI"],"HEADERS"=>["REQUEST"=>getallheaders(),"RESPONSE"=>headers_list()],"ENDSCRIPT"=>connection_aborted()?"Connection Aborted":"Normal End"],true));
//}
//register_shutdown_function("script_end");
include "database.php";
$mysql=new database();
$max_search=2;
$result=$mysql->select("shares",["*"]);
$shares=[];
foreach($result as $row) {
  $shares[$row["Path"]]=["searchable"=>(int)$row["Searchable"],"ID"=>$row["ID"]];
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
  if (endWith($file,".gif") || endWith($file,".png") || endWith($file,".jpg") || endWith($file,".jpeg") || endWith($file,".bmp"))
	  return 0;
	else if (endWith($file,".mp2") || endWith($file,".m4a") || endWith($file,".mp3") || endWith($file,".wmv") || endWith($file,".wav") || endWith($file,".ogg"))
	  return 1;
	else if (endWith($file,".mkv") || endWith($file,".avi") || endWith($file,".mp4")|| endWith($file,".3gp")|| endWith($file,".mpeg")|| endWith($file,".mpg")|| endWith($file,".wmv"))
	  return 2;
	else if (endWith($file,".pdf"))
	  return 3;
	else if (endWith($file,".zip") || endWith($file,".gz") || endWith($file,".tgz") || endWith($file,".rar") || endWith($file,".tar") || endWith($file,".jar"))
	  return 4;
	else if (endWith($file,".exe") || endWith($file,".bat") || endWith($file,".com") || endWith($file,".vbs") || endWith($file,".sh"))
	  return 5;
	else if (endWith($file,".htm") || endWith($file,".html"))
	  return 6;
	else if (endWith($file,".txt") || endWith($file,".inf") || endWith($file,".srt") || endWith($file,".sub") || endWith($file,".ini",0000))
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
   <li class=\"list-group-item\"><a id=\"ScanPage\" title=\"Scan Files In Searchable Shares\" href=\"$base/scanfolders.php\"><img src=\"$base/pix/scan.png\"/></a></li>
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
  foreach ($shares as $share=>$info) {
    if (startWith($path, $share)) {
      $allowed = true;
    }
  }
  if (!$allowed) 
    logmsg("Access Denied: ".$path);
  return $allowed;
}

function authenticate() {
  global $base;
  if (readPersistent("password") != "") {
    session_start();    
    if (!isset($_SESSION["loggedin"])) {
      if (isset($_SERVER['PHP_AUTH_USER']))
        if (hash("sha512", $_SERVER['PHP_AUTH_PW']) == readPersistent("password"))
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
