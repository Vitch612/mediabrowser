<?php
ini_set('max_execution_time', 0);
ob_implicit_flush(true);
include "include.php";
include "head.php";
show_nav();
echo '<div class="row box"><div class="col-xs-12">';
//$result=$mysql->select("files",["*"]);
//foreach($shares as $share=>$searchable) {
//  break;
//}
//
//foreach($result as $row) {  
//  if (strpos($row["Path"],$share)!=-1) {    
//    $mysql->update("files",["Path"=>substr($row["Path"],strpos($row["Path"],$share)+strlen($share))],"`ID`='".$row["ID"]."'");
//  }  
//}
echo '</div></div></div></body></html>';
