<?php

include "include.php";
$target = $_REQUEST["file"];
$path = base64_decode($target);
$path = str_replace("\\", "/", clean_dirpath($path));
if (check_permission($path)) {
  foreach ($shares as $share => $info) {
    if (startWith($path, $share)) {
      if (unlink($path)) {
        $mysql->delete("files", "`Share`='".$info["ID"]."' AND `Path`='".$mysql->conn->real_escape_string(substr($path, strlen($share)))."'");
        echo "OK";
      } else {
        echo "Could not delete file";
      }
    }
  }
} else {
  echo "Access denied";
}

