<?php
include "include.php";
//filectime filemtime
$abort=false;
$active=true;

function dirscan($dirpath,$startpoint="") {
  $entry="";
  $dir_handle = @opendir($dirpath) or die;
  while ($file = readdir($dir_handle) && !$abort) {
    if ($file == "." || $file == "..")
      continue;
    $entry = $dirpath . "/" . $file;
    if (!$active && $entry==$startpoint) {
      $active=true;
    }
    if (is_dir($entry)) {
      $entry=dirscan($entry);
    } else {
      if ($active) {
        
      }
    }
  }
  closedir($dir_handle);
  return $entry;
}


$status=readPersistent($scanstatus);

