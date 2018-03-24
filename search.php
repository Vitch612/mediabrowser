<?php

function shutdown() {
  $curconc = readPersistent("search");
  if ($curconc > 1)
    savePersistent("search", $curconc - 1);
  else
    deletePersistent("search");
}

$count = 0;
function dirsearch($dirpath, $searchstring, $playlist = false) {
  global $count;
  $dir_handle = @opendir($dirpath) or die;
  while ($file = readdir($dir_handle)) {
    if ($file == "." || $file == "..")
      continue;
    $entry = $dirpath . "/" . $file;
    if (is_dir($entry)) {
      dirsearch($entry, $searchstring, $playlist);
    } else {
      $words = explode(" ", $searchstring);
      $match = true;
      foreach ($words as $word) {
        if (substr($word,0,1)!="|") {
          if (strpos(strtolower($file), strtolower($word)) === false)
            $match = false;
        } else {
          if (strpos(strtolower($file), strtolower(substr($word,1))) !== false)
            $match = true;
        }
      }
      if ($match) {
        $count++;
        if ($playlist)
          echo '<img target="' . $count . '" src="pix/add.png" width="15" height="15" style="margin-right:10px;margin-bottom:4px;" class="addtoplaylist"/><a id="' . $count . '" title="' . utf8_encode($entry) . '" target="_blank" class="singlesearchresult" href="show/' . base64_encode($entry) . '">' . utf8_encode(basename($entry)) . '</a><BR>';
        else
          echo "<a  title=\"" . utf8_encode($entry) . "\" target=\"_blank\" href=\"show/" . base64_encode($entry) . "\">" . utf8_encode(basename($entry)) . "</a><BR>";
      }
    }
  }
  closedir($dir_handle);
}

ini_set('max_execution_time', 0);
include("include.php");

$curconc = readPersistent("search");
if (isset($curconc))
  $curconc += 1;
else
  $curconc = 1;

if ($curconc > $max_search)
  die("Maximum number of concurrent searches reached");
else
  savePersistent("search", $curconc);

register_shutdown_function('shutdown');
ob_implicit_flush(1);

if (isset($_REQUEST["searchstring"]) && $_REQUEST["searchstring"] != "" && isset($_REQUEST["playlist"])) {
  foreach ($shares as $share => $info) {
    if ($info["searchable"] == 1)
      dirsearch(substr($share, 0, strlen($share) - 1), $_REQUEST["searchstring"], true);
  }
} else if (isset($_REQUEST["searchstring"]) && $_REQUEST["searchstring"] != "") {
  foreach ($shares as $share => $info) {
    if ($info["searchable"] == 1)
      dirsearch(substr($share, 0, strlen($share) - 1), $_REQUEST["searchstring"]);
  }
}