<?php
ini_set('memory_limit',1073741824);
ini_set('max_execution_time', 0);
ob_implicit_flush(true);
include "include.php";
authenticate();
include "head.php";
show_nav();
echo '<div class="row box"><div class="col-xs-12">';
echo '<div class="row"><form action="' . $base . '/scanfolders.php" method="POST">
    <div class="col-md-1"><input data-placement="bottom" title="This process can take a very long time" class="btn btn-primary" type="submit" name="scanfiles" value="Scan FS"></div>
    <div class="col-md-1"><input class="form-control" type="number" name="chunksize" value="0" min="0" max="65535" step="1024"></div>
    <div class="col-md-8"><label>Number of bytes to scan in each file to generate checksum. 0 means the whole file will be scanned.<BR>(note that after the first scan the same value should be used when rescanning)</label></div>        
    </form></div>';
echo '<div class="row"><form action="' . $base . '/scanfolders.php" method="POST">
    <div class="col-md-2"><input class="btn btn-primary" type="submit" name="checkchanges" value="Scan DB"></div>
    <div class="col-md-10"><label>Run a scan to verify all the files in the database still exist on the filesystem</label></div>
    </form></div>';
echo '</div></div>';
if ($_REQUEST["scanfiles"]) {
  $abort = false;
  $active = true;
  $starttime = time();
  $allowedruntime = 0;
  $shareid = 0;
  $chunksize = 0;

  function dirscan($dirpath, $startpoint = "") {
    global $share;
    global $abort;
    global $active;
    global $starttime;
    global $allowedruntime;
    global $mysql;
    global $shareid;
    global $chunksize;
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
      if ($allowedruntime > 0) {
        if (time() - $starttime > $allowedruntime) {
          $abort = true;
        }
      }
      if (is_dir($fullpath)) {
        $fullpath = dirscan($fullpath, $startpoint);
      } else {
        if ($active) {
          try {
            $search = $mysql->select("files", ["ID"], "`Path`='" . $mysql->conn->real_escape_string(substr($fullpath, strpos($fullpath, $share) + strlen($share))) . "'");
            if (count($search) == 0) {
              $fh = fopen($fullpath, "r");
              if (filesize($fullpath) > 2 * $chunksize && $chunksize!=0) {
                $buffbegin = fread($fh, $chunksize);
                fseek($fh, filesize($fullpath) - $chunksize);
                $buffend = fread($fh, $chunksize);
                $md5 = md5($buffbegin . $buffend);
              } else {
                $md5 = md5(fread($fh, filesize($fullpath)));
              }
              fclose($fh);
              if (!$mysql->insert("files", ["Share" => $shareid, "Path" => substr($fullpath, strpos($fullpath, $share) + strlen($share)), "Filename" => $file, "MD5" => $md5, "Size" => filesize($fullpath), "Modtime" => filemtime($fullpath)])) {
                if (strpos($mysql->error, "Duplicate entry") >= 0) {
                  if (!$mysql->insert("duplicates", ["Share" => $shareid, "Path" => substr($fullpath, strpos($fullpath, $share) + strlen($share)), "Filename" => $file, "MD5" => $md5, "Size" => filesize($fullpath), "Modtime" => filemtime($fullpath)])) {
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
  
//ignore_user_abort(true);
  $allowedruntime = ini_get('max_execution_time');
  if ($allowedruntime > 10)
    $allowedruntime -= 10;
  
  echo '<script>$(document).ready(function() {$(".progress").hide();});</script>';
  $chunksize=$_REQUEST["chunksize"];
  echo '<div class="row box"><div class="col-xs-12">';
  if ($allowedruntime == 0) {
    echo 'Scanning with no time limit, this might take a while, please wait for results. <img width="20" height="20" class="progress" src="pix/progress.gif"><BR><BR>';
  } else {
    echo 'Scanning with a time limit of ' . $allowedruntime . ' seconds, please wait for results. <img width="20" height="20" class="progress" src="pix/progress.gif"><BR><BR>';
  }
  
  ob_flush();
  foreach ($shares as $share => $info) {
    if ($info["searchable"] == true) {
      $shareid = $info["ID"];
      dirscan(substr($share, 0, strlen($share) - 1));
    }
  }
  $dups = $mysql->select("duplicates", ["*"]);
  if (count($dups) == 0) {
    echo "Scan complete. No duplicates found";
  } else {
    echo "Scan complete. The following files might be duplicates.";
    foreach ($dups as $row) {
      echo $share . $row["Path"] . "<BR>";
      $file = $mysql->select("files", ["*"], "`MD5`='" . $row["MD5"] . "' AND `Size`='" . $row["Size"] . "'");
      echo $share . $file[0]["Path"] . "<BR><BR>";
    }
  }
  echo '</div></div></div></body></html>';
} else if ($_REQUEST["checkchanges"]) {
  echo '<div class="row box"><div class="col-xs-12">';
  $count=0;
  echo '<script>$(document).ready(function() {$(".progress").hide();});</script>';
  echo '<img width="20" height="20" class="progress" src="pix/progress.gif">';
  ob_flush();
  foreach ($shares as $share=>$info) {
    if ($info["searchable"]==1) {
      $results=$mysql->select("files",["*"],"`Share`='".$info["ID"]."'");
      foreach($results as $point=>$row) {
        if (!file_exists($share.$row["Path"])) {
          $count++;
          $mysql->delete("files","`ID`='".$row["ID"]."'");
        }
      }
    }      
  }
  echo "Removed $count entries from database";
  echo '</div>';
}

echo '</div></body></html>';