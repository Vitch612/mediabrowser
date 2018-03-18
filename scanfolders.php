<?php
include "include.php";
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
      echo $fullpath."<BR>";
      if ($active) {
        try {
          $search = $mysql->select("files", ["ID"], "`Path`='$fullpath'");
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
ob_implicit_flush(true);
ini_set('max_execution_time', 0);
foreach ($shares as $folder=>$value) {
  break;
}
dirscan($folder);
