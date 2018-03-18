<?php
include "include.php";
$target=$_REQUEST["file"];
$path = base64_decode($target);
$path = str_replace("\\", "/", clean_dirpath($path));
if (check_permission($path)) {
  $result=$mysql->select("files",["*"],"`Path`='".$mysql->conn->real_escape_string($path)."'");
  if (unlink($path)) {
    $mysql->delete("files","`ID`='".$result[0]["ID"]."'");  
    echo "OK";
  } else {
    echo "Could not delete file";
  }
} else {
  echo "Access denied";
}

