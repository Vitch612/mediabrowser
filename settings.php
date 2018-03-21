<?php
include "include.php";
function getshares() {
  global $mysql;
  $result=$mysql->select("shares",["*"]);
  $sharesform='<div id="sharesformdiv" class="row box"><div class="col-md-12"><div class="row"><div class="col-md-12"><label>Current Shares</label></div></div><form>';
  foreach($result as $row) {         
    $sharesform.='<div class="row"><div class="col-md-2"><img class="delete form-group" target="'.$row["ID"].'" src="pix/delete.png"/><label style="position:relative;top:1px;">Searchable&nbsp;</label><input style="position:relative;top:2px;" type="checkbox" class="form-check-input" name="search_'.$row["ID"].'"'.($row["Searchable"]?"checked":"").'></div><div class="col-md-10">'.$row["Path"].'</div></div>';
  }
  if (count($result)>0)
    $sharesform.='<div class="row"><div class="col-md-12"><input class="btn btn-primary" style="margin-top:10px;margin-bottom:10px;" type="submit" value="Save" name="Save"/></div></div></form></div></div>';
  else
    $sharesform.='</form></div></div>';
  return $sharesform; 
}
function getsubfolders($dirpath) {
  $subfolders = [];
  $dir_handle = @opendir($dirpath) or die;
  while (($file = readdir($dir_handle))) {
    if ($file == ".") {
      continue;
    }
    $fullpath = $dirpath . "/" . $file;
    if (is_dir($fullpath)) {
      $subfolders[] = $file;
    }
  }
  closedir($dir_handle);
  return $subfolders;
}

authenticate();

if (isset($_REQUEST["addfolder"])) {
  if ($_REQUEST["selectedfolder"] == "")
    $path = $_REQUEST["addfolder"];
  else 
    if (endWith($_REQUEST["selectedfolder"],"/"))
      $path = $_REQUEST["selectedfolder"] . $_REQUEST["addfolder"];
    else
      $path = $_REQUEST["selectedfolder"] . "/" . $_REQUEST["addfolder"];
  $path = clean_dirpath($path);

  if ($_REQUEST["addfolder"] == "[RESET]") {
    $rootdrives = [];
    if (PHP_OS == "WIN32" || PHP_OS == "WINNT" || PHP_OS == "Windows") {
      $fso = new COM('Scripting.FileSystemObject');
      foreach ($fso->Drives as $d) {
        $dO = $fso->GetDrive($d);
        if ($dO->DriveType == 2) {
          $rootdrives[] = $dO->DriveLetter . ":";
        }
      }
    } else {
      $rootdrives[] = "/";
    }
    $html = base64_encode("") . ",";
    $select = '<option value=""></option>';
    foreach ($rootdrives as $drive) {
      $select .= '<option value="' . $drive . '">' . $drive . '</option>';
    }
    $select .= "</select>";
    $html .= base64_encode($select);
  } else {
    $html = base64_encode($path) . ",";
    $select = '<option value=""></option>';
    $select .= '<option value="[RESET]">[RESET]</option>';
    foreach (getsubfolders($path) as $folder) {
      $select .= '<option value="' . $folder . '">' . $folder . '</option>';
    }
    $html .= base64_encode($select);
  }
  echo $html;
} else if (isset($_REQUEST["addshare"])) {
  if (endWith($_REQUEST["addshare"],"/"))
    $sharepath=$_REQUEST["addshare"];
  else
    $sharepath=$_REQUEST["addshare"]."/";
  if ($mysql->insert("shares",["Path"=>$sharepath,"Searchable"=>(strtolower($_REQUEST["searchable"])=="true"?1:0)])) {
    echo getshares();
  } else {
    echo "Error: ".$mysql->error;
  }    
} else if (isset($_REQUEST["deleteshare"])) {
  if ($mysql->delete("shares","`ID`='".$mysql->conn->real_escape_string($_REQUEST["deleteshare"])."'")) {
    echo getshares();
  } else {
    echo "Error: ".$mysql->error;
  } 
} else if (isset($_REQUEST["shareid"])) {
  $mysql->update("shares",["Searchable"=>($_REQUEST["status"]=="true"?1:0)],"`ID`='".$_REQUEST["shareid"]."'");
} else if (isset($_REQUEST["adminpassword"])) {
  savePersistent("password", hash("sha512",$_REQUEST["adminpassword"]));  
} else {
  include "head.php";
  show_nav();
  $rootdrives = [];
  if (PHP_OS == "WIN32" || PHP_OS == "WINNT" || PHP_OS == "Windows") {
    $fso = new COM('Scripting.FileSystemObject');
    foreach ($fso->Drives as $d) {
      $dO = $fso->GetDrive($d);
      if ($dO->DriveType == 2) {
        $rootdrives[] = $dO->DriveLetter . ":";
      }
    }
  } else {
    $rootdrives[] = "/";
  }
  $html = '<div class="row box" style="padding-bottom:20px;"><div class="col-md-12"><div class="row"><div class="col-md-12"><label>Add new share</label></div></div><div class="row"><div class="col-md-6"><input class="form-control" type="text" style="text-overflow: ellipsis;" disabled name="folderpath" value=""/>';
  $html .= '</div><div class="col-md-1"><select class="form-control" id="browseserver">';
  $html .= '<option value=""></option>';
  foreach ($rootdrives as $drive) {
    $html .= '<option value="' . $drive . '">' . $drive . '</option>';
  }
  $html .= '</select></div><div class="col-md-2"><label style="margin-top:6px;">Searchable&nbsp;</label><input style="position:relative;top:2px;" type="checkbox" class="form-check-input" name="searchable"></div><div class="col-md-1"><input id="addshare" class="btn btn-primary" type="submit" value="Add" name="Add"/></div></div></div></div>';
  echo $html;
  echo '<script>
    function regform() {
    $(".delete").click(function() {      
      $.ajax({
         url: "' . $base . '/settings.php",
         method: "POST",
        data: {deleteshare:$(this).attr("target")}
      }).done(function(data) {
        if (data.substr(0,5)=="Error") {
          alert(data);
        } else {
          $("#sharesformdiv").replaceWith(data);
          regform();
        }
      });   
    });    
    $("input[name=\'SavePassword\']").click(function(e) {      
      $.ajax({
        url: "' . $base . '/settings.php",
        method: "POST",
        data: {adminpassword:$("input[name=\'password\']").val()}
      });
      $("input[name=\'password\']").val("");
    });    

    $("input[name=\'Save\']").click(function(e) {
      e.preventDefault();
      $("input[name^=\'search_\']").each(function() {
          $.ajax({
            url: "' . $base . '/settings.php",
            method: "POST",
            data: {shareid:$(this).attr("name").substring($(this).attr("name").indexOf("_")+1),status:($(this).is(":checked")?"true":"false")}
          });  
      });
    });    
    }
    $(document).ready(function() {
    regform();
    $("#addshare").click(function() {
      $.ajax({
         url: "' . $base . '/settings.php",
         method: "POST",
        data: {addshare:$("input[name=\'folderpath\']").val(),searchable:$("input[name=\'searchable\']").is(":checked")}
      }).done(function(data) {
        if (data.substr(0,5)=="Error") {
          alert(data);
        } else {
          $("#sharesformdiv").replaceWith(data);
          regform();
        }
      });    
    });
    $("#browseserver").change(function() {
      if ($(this).val()!="") {
        $.ajax({
          url: "' . $base . '/settings.php",
          method: "POST",
          data: {addfolder:$(this).val(),selectedfolder:$("input[name=\'folderpath\']").val()}
        }).done(function(data) {
          var retval=data.split(",");
          selectedfolder:$("input[name=\'folderpath\']").val(atob(retval[0]));
          $("#browseserver")[0].innerHTML=atob(retval[1]);
        });
      }
    });
  });</script>';  
  echo getshares();
  
  echo '<div class="row box"><div class="col-xs-12">
  <div class="row"><div class="col-xs-12">
  <label>Set Administrator Password</label> 
  </div></div>
  <div class="row"><div class="col-xs-5">
  <input type="password" class="form-control" name="password">
  </div><div class="col-xs-1">
  <input class="btn btn-primary" style="margin-bottom:10px;" type="submit" value="Save" name="SavePassword"/>
  </div>
  </div></div>';
  
  echo '</div></body></html>';
}