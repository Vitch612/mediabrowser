<?php

function shutdown()
{  
  $curconc=  readPersistent("search");
  if ($curconc > 1)
    savePersistent("search", $curconc-1);
  else 
    deletePersistent("search");
}

function dirsearch($dirpath, $searchstring) {
    $dir_handle = @opendir($dirpath) or die;
    while ($file = readdir($dir_handle)) {
        if ($file == "." || $file == "..")
            continue;
        $entry = $dirpath . "/" . $file;
        if (is_dir($entry)) {
            dirsearch($entry, $searchstring);
        } else {
            if (strpos(strtolower($file), strtolower($searchstring)) !== false) {
                echo $entry . "<BR>";
            }
        }
    }
    closedir($dir_handle);
}

include("include.php");

$curconc=readPersistent("search");
if (isset($curconc))
  $curconc+=1; 
else 
  $curconc=1;

if ($curconc>$max_search)
  die("Maximum number of concurrent searches reached");
else 
  savePersistent ("search", $curconc);

register_shutdown_function('shutdown');
ob_implicit_flush(1);



if (isset($_REQUEST["searchstring"]) && $_REQUEST["searchstring"]!="") {
  foreach ($shares as $share=>$value) {
    if ($value==1)
      dirsearch($share,$_REQUEST["searchstring"]);
  }
  echo "Search complete";
}