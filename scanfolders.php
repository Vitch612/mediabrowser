<?php
include "include.php";
include "head.php";
$abort = false;
$active = true;
$starttime = time();
$allowedruntime = 0;
//$chunksize = 2048;

function dirscan($dirpath, $startpoint = "") {
  global $abort;
  global $active;
  global $starttime;
  global $allowedruntime;
  //global $chunksize;
  global $mysql;

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
          $search = $mysql->select("files", ["ID"], "`Path`='".$mysql->conn->real_escape_string($fullpath)."'");
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
            if (!$mysql->insert("files", ["Path" => $fullpath, "Filename" => $file, "MD5" => $md5, "Size" => filesize($fullpath), "Modtime" => filemtime($fullpath)])) {
              if (strpos($mysql->error, "Duplicate entry") >= 0) {
                if (!$mysql->insert("duplicates", ["Path" => $fullpath, "Filename" => $file, "MD5" => $md5, "Size" => filesize($fullpath), "Modtime" => filemtime($fullpath)])) {
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
$allowedruntime=ini_get('max_execution_time');
if ($allowedruntime>10)
  $allowedruntime-=10;
ob_implicit_flush(true);
show_nav();

if ($allowedruntime==0) {
  echo "Scanning with no time limit, closing this page before the scan is complete can cause data corruption.<BR><BR>";
} else {
  echo "Scanning with a time limit of $allowedruntime seconds, closing this page before the scan is complete can cause data corruption.<BR><BR>";
}
ob_flush();
foreach ($shares as $folder=>$value) {
  break;
}
dirscan($folder);

$dups=$mysql->select("duplicates",["*"]);
if (count($dups)==0) {
  echo "No duplicates";
} else {
  foreach($dups as $row) {
    echo $row["Path"]."<BR>";
    $file=$mysql->select("files",["*"],"`MD5`='".$row["MD5"]."' AND `Size`='".$row["Size"]."'");
    echo $file[0]["Path"]."<BR><BR>";
  }  
}