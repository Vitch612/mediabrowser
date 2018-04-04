<?php
error_reporting(E_ERROR | E_PARSE);
//function script_end() {
//  logmsg("Served Following Request:\n".print_r(["URL"=>$_SERVER["REQUEST_URI"],"HEADERS"=>["REQUEST"=>getallheaders(),"RESPONSE"=>headers_list()],"ENDSCRIPT"=>connection_aborted()?"Connection Aborted":"Normal End"],true));
//}
//register_shutdown_function("script_end");
$useragent=$_SERVER['HTTP_USER_AGENT'];
$mobile=false;
if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
$mobile=true;
include "database.php";
$mysql=new database();
$max_search=2;
$result=$mysql->select("shares",["*"]);
$shares=[];
foreach($result as $row) {
  $shares[$row["Path"]]=["searchable"=>(int)$row["Searchable"],"ID"=>$row["ID"]];
}
$file_types=["image","audio","video","pdf","zip","exe","html","text","folder","other"];
$file_icons=["pix/image.png","pix/mp3.png","pix/video.png","pix/pdf.png","pix/zip.png","pix/exe.png","pix/html.png","pix/text.png","pix/folder.png","pix/other.png"];
$base=substr($_SERVER["PHP_SELF"],0,strpos($_SERVER["PHP_SELF"],"/",1));
$applicationfolder=substr($_SERVER["SCRIPT_FILENAME"],0,strrpos($_SERVER["SCRIPT_FILENAME"],"/"));
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

function getfile($filepath) {
  $retval="";
  if (file_exists($filepath)) {
    while(($fh=fopen($filepath,"r"))===false);
    while(flock($fh, LOCK_SH)===false);
    $chunk=1024;
    while(!feof($fh)) {
      while(($s=fread($fh,$chunk))===false);
      if (strlen($s)>0) 
        $retval.=$s;
    } 
    flock($fh, LOCK_UN);
    while(fclose($fh)===false);    
  }
  return $retval;
}
function putfile($filepath,$data) {
  while(($fh=fopen($filepath,"w"))===false);  
  while(flock($fh, LOCK_EX)===false);
  fwrite($fh, $data);
  flock($fh, LOCK_UN);
  while(fclose($fh)===false);
}
function readPersistent($name) {
  global $applicationfolder;
  if (($s=getfile("$applicationfolder/data/data.sr"))!="")
    if (($a = unserialize($s))!==false)
      if (isset($a[$name]))
        return $a[$name];
  return NULL;
}
function deletePersistent($name) {
  global $applicationfolder;
  if (file_exists("$applicationfolder/data/data.sr"))
    if (($s=getfile("$applicationfolder/data/data.sr"))!="")
      if(($a = unserialize($s))!==false)
        if (isset($a[$name])) {
          unset($a[$name]);
          $s=serialize($a);          
          putfile("data.sr", $s);
          return true;
        }
  return false;
}
function savePersistent($name,$value) {  
  global $applicationfolder;
  if (!file_exists("$applicationfolder/data/data.sr")) {
    $a=[];  
  } else {
    if (($s=getfile("$applicationfolder/data/data.sr"))=="" || ($a = unserialize($s))===false)
      return false;
  }
  $a[$name]=$value;
  $s=serialize($a);  
  putfile("$applicationfolder/data/data.sr", $s);        
  return true;
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
