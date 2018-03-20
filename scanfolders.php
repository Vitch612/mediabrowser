<?php
include "include.php";
authenticate();
include "head.php";
$abort = false;
$active = true;
$starttime = time();
$allowedruntime = 0;
$shareid=0;
//$chunksize = 2048;

function dirscan($dirpath, $startpoint = "") {
  global $share;
  global $abort;
  global $active;
  global $starttime;
  global $allowedruntime;
  global $mysql;
  global $shareid;
  //global $chunksize;  
  $fullpath = $dirpath;
  $dir_handle = @opendir($dirpath) or die;
  while (($file = readdir($dir_handle)) && !$abort) {
    if ($file == "." || $file == "..") {
      continue;
    }
    $fullpath = $dirpath . "/" . $file;
    if (!$active && $fullpath == $startpoint) {
      $active = true;
    }
    if ($allowedruntime >0) {
      if (time() - $starttime > $allowedruntime) {
        $abort = true;
      }      
    }
    if (is_dir($fullpath)) {
      $fullpath = dirscan($fullpath, $startpoint);
    } else {
      if ($active) {
        try {
          $search = $mysql->select("files", ["ID"], "`Path`='".$mysql->conn->real_escape_string(substr($fullpath,strpos($fullpath,$share)+strlen($share)))."'");
          if (count($search) == 0) {
            $fh = fopen($fullpath, "r");
//          if (filesize($fullpath) > 2 * $chunksize) {            
//            $buffbegin = fread($fh, $chunksize);
//            fseek($fh, filesize($fullpath) - $chunksize);
//            $buffend = fread($fh, $chunksize);
//            $md5 = md5($buffbegin . $buffend);
//          } else {
            $md5 = md5(fread($fh, filesize($fullpath)));
//          }
            fclose($fh);
            if (!$mysql->insert("files", ["Share"=>$shareid, "Path" => substr($fullpath,strpos($fullpath,$share)+strlen($share)), "Filename" => $file, "MD5" => $md5, "Size" => filesize($fullpath), "Modtime" => filemtime($fullpath)])) {
              if (strpos($mysql->error, "Duplicate entry") >= 0) {
                if (!$mysql->insert("duplicates", ["Share"=>$shareid, "Path" => $fullpath, "Filename" => $file, "MD5" => $md5, "Size" => filesize($fullpath), "Modtime" => filemtime($fullpath)])) {
                  logmsg("insert in duplicates: " . $mysql->error);
                }
              } else {
                logmsg("insert in files: " . $mysql->error);
              }
            }
          }
        } catch (Exception $e) {
          logmsg("error: " . $e->getMessage());
        }
      }
    }
  }
  closedir($dir_handle);
  return $fullpath;
}

ini_set('max_execution_time', 0);
//ignore_user_abort(true);
$allowedruntime=ini_get('max_execution_time');
if ($allowedruntime>10)
  $allowedruntime-=10;
ob_implicit_flush(true);
show_nav();
echo '<script>$(document).ready(function() {$(".progress").hide();});</script>';

echo '<div class="row"><div class="col-xs-12">';
if ($allowedruntime==0) {
  echo 'Scanning with no time limit, this might take a while, please wait for results. <img width="20" height="20" class="progress" src="pix/progress.gif"><BR><BR>';
} else {
  echo 'Scanning with a time limit of '.$allowedruntime.' seconds, please wait for results. <img width="20" height="20" class="progress" src="pix/progress.gif"><BR><BR>';
}
ob_flush();
foreach ($shares as $share=>$value) {
  break;
}
$result=$mysql->select("shares",["*"]);
foreach($result as $row) {
  if ($row["Path"]==$share) {
    $shareid=(int)$row["ID"];
  }
}
dirscan($share);

$dups=$mysql->select("duplicates",["*"]);
if (count($dups)==0) {
  echo "No duplicates";
} else {
  foreach($dups as $row) {
    echo $share.$row["Path"]."<BR>";
    $file=$mysql->select("files",["*"],"`MD5`='".$row["MD5"]."' AND `Size`='".$row["Size"]."'");
    echo $share.$file[0]["Path"]."<BR><BR>";
  }  
}

echo '</div></div></div></body></html>';