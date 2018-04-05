<?php
ini_set('memory_limit',2147483648);
ini_set('max_execution_time', 60);
include "include.php";

function search() {
  echo '<div class="row box"><div class="col-xs-12"><div class="row" style="margin-top:10px;"><div class="col-xs-7"><form id="searchform" method="post" action="search.php"><input class="form-control" type="text" id="searchstring" name="searchstring"/></div><div class="col-xs-1 searchbutton"><input class="btn btn-primary" type="button" id="searchbuttonplaylist" value="Search"/></div><div class="col-xs-1 searchprogress"><img width="20" height="20" class="img-fluid progress" src="pix/progress.gif"></form></div></div>';
  echo '<div class="row searchresultbuttons" style="margin-top:10px;display:none;margin-bottom:10px;"><div class="col-xs-12"><input class="btn btn-primary" type="button" name="addall" value="Add All" style="margin-right:10px;"><img width="20" height="20" style="display:none;margin-left:-5px;margin-right:5px;" class="img-fluid addallprogress" src="pix/progress.gif"><input class="btn btn-primary" type="button" name="clearall" value="Clear"></div></div><div class="row"><div class="col-xs-12" style="margin-left:10px;" id="displaytext"></div></div></div></div>';
}

if (isset($_REQUEST["addtoplaylist"])) {
  $target=base64_decode($_REQUEST["target"]);
  foreach($shares as $share=>$info) {
    if (startWith($target, $share)) {
      $result=$mysql->select("files",["ID","Filename"],"`Path`='".$mysql->conn->real_escape_string(substr($target,strlen($share)))."' AND `Share`='".$info["ID"]."'");
      if (count($result)!=0) {
        if ($mysql->insert("playlistentries",["Playlist"=>$_REQUEST["addtoplaylist"],"File"=>$result[0]["ID"],"Weight"=>0])) {
          echo $mysql->insert_id." ".$result[0]["Filename"]."<BR>";
        } else
          echo "Error: ".$mysql->error;
      } else
        echo "Error: File not found in database, run scan again";
    }
  }
} else if (isset($_REQUEST["removeplaylist"])) {
  if ($mysql->delete("playlist","`ID`='".$mysql->conn->real_escape_string($_REQUEST["removeplaylist"])."'")) {
    echo "OK";
  } else {
    echo $mysql->error;
  }
} else if (isset($_REQUEST["addplaylist"])) {
  if ($mysql->insert("playlist",["Name"=>$_REQUEST["addplaylist"]])) {
    echo '<span><img class="delete" target="' . $mysql->insert_id  . '" src="pix/delete.png"/><a href="#'.$mysql->insert_id .'" class="showplaylist">' . $_REQUEST["addplaylist"] . '</a><form action="'.$base.'/showplaylist.php" method="POST" style="display:inline;margin-left:5px;position:relative;top:7px;"><input type="hidden" name="playlist" value="'.$mysql->insert_id .'"><input type="submit" style="font-size:26px;background-color:transparent;border-style:none;position:relative;top:-4px;" Value="&#9658;"/></form><BR></span>';
    echo '<div style="display:none;" id="playlist_'.$mysql->insert_id.'">';    
  } else {
    echo "Error: ".$mysql->error;
  }
} else {
  include "head.php";
  show_nav();
  search();
  echo '<div class="modal fade" id="playlistselect" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h5 class="modal-title" id="exampleModalLabel">Choose Playlist</h5>
      </div>
      <div class="modal-body" id="playlistslist">';
  $result = $mysql->select("playlist", ["*"]);
  $first=true;
  foreach($result as $row) {    
    if ($first) {
      $first=false;
      echo '<div class="radio"><label><input type="radio" name="playlist" checked value="'.$row["ID"].'">'.$row["Name"].'</label></div>';
    } else {
      echo '<div class="radio"><label><input type="radio" name="playlist" value="'.$row["ID"].'">'.$row["Name"].'</label></div>';
    }    
  }
  echo '</div>
      <div class="modal-footer">
        <button type="button" id="btnaddtoplaylist" class="btn btn-primary" data-dismiss="modal">Ok</button>
      </div>
    </div>
  </div>
</div>';
  
  echo '<script>
  var file;
  function addToPlaylist(playlist) {
    $.ajax({
      url: "'.$base.'/playlists.php",
      method: "POST",
      data: {addtoplaylist:playlist,target:file}
    }).done(function (data) {
      if (data.substr(0,5)=="Error") {
        alert(data);
      } else {
        $("#playlist_"+playlist).html($("#playlist_"+playlist).html()+data);
      }      
    });
  }
  function registershowpl() {
    $(".showplaylist").click(function(e) {
      e.preventDefault();
      if ($("#"+"playlist_"+$(this).attr("href").substring(1)).is(":visible")) {
        $("#"+"playlist_"+$(this).attr("href").substring(1)).hide();
      } else {
        $("#"+"playlist_"+$(this).attr("href").substring(1)).show();
      }  
    });
  }
  function registerdelete() {
    $(".delete").click(function() {
      target=$(this);
      $.ajax({
         url: "'.$base.'/playlists.php",
         method: "POST",
        data: {removeplaylist:$(this).attr("target")}
      }).done(function(data) {
        if (data!="OK") {
          alert(data);
        } else {
          var pid=target.attr("target");
          target.parent().remove();
          $("#playlist_"+pid).remove();
          $(":radio[value="+pid+"]").parent().parent().remove();
          $(":radio").first().prop("checked", true);
        }
      });
    });
  }
  $("#btnaddtoplaylist").click(function() {
    if (addall) {
      var playlist=$("input[name=playlist]:checked").val();
      var spread=0;
      var lastone=$(".singlesearchresult").length;
      var count=0;
      $(".addallprogress").show();
      $(".singlesearchresult").each(function() {          
          var plentry=$(this).attr("href").substring($(this).attr("href").lastIndexOf("/")+1);
          setTimeout(function() {
          $.ajax({
            url: "'.$base.'/playlists.php",
            method: "POST",
            data: {addtoplaylist:playlist,target:plentry}
          }).done(function (data) {
            count++;
            if (data.substr(0,5)=="Error") {
              alert(data);
            } else {
              $("#playlist_"+playlist).html($("#playlist_"+playlist).html()+data);
            }
            if (count==lastone) {
              $(".addallprogress").hide();
            }
          });
        },spread);
        spread+=100;
      });
      addall=false;
    } else {
      addToPlaylist($("input[name=playlist]:checked").val());    
    }    
  });
  var addall=false;
  $(document).ready(function() {
    $(".close").click(function() {
      addall=false;
    });
    registerdelete();
    $("#searchbuttonplaylist").click(function (event) {
      $(".progress").show();
      event.preventDefault();
      lastsize = 0;
      xhr.abort();
      $("#displaytext").html("");
      searchresults = $.ajax({
        url: "'.$base.'/search.php",
        method: "POST",
        data: {searchstring: $("#searchstring").val(),playlist:"1"},
        context: document.body,
        xhr: xhrProvider,
        beforeSend: function () {
          setTimeout(iteratethroughresults, 10);
        }
      }).done(function () {
        $(".progress").hide();
        setTimeout(function() {
          $(".searchresultbuttons").show();
          $("input[name=\'addall\']").click(function() {
            if (document.getElementById("playlistslist").childElementCount!=0) {            
              addall=true;
              $("#playlistselect").modal("show");
            } else {
              alert("Create a playlist first");
            }
          });
          $("input[name=\'clearall\']").click(function() {
            $("#displaytext").html("");
            $(".searchresultbuttons").hide();
          });          
          $(".addtoplaylist").click(function() {
            if (document.getElementById("playlistslist").childElementCount!=0) {        
              file=document.getElementById($(this).attr("target"));
              file=file.href.substring(file.href.lastIndexOf("/")+1);
              $("#playlistselect").modal("show");
            } else {
              alert("Create a playlist first");
            }
          });
        },100);
      });
    });
    registershowpl();
    $("input[name=\'addplaylist\']").click(function() {
      $.ajax({
        url: "'.$base.'/playlists.php",
        method: "POST",
        data: {addplaylist:$("input[name=\'playlistname\']").val()}
      }).done(function(data) {      
        if (data.substr(0,5)=="Error") {
          alert(data);
        } else {
          var pid=data.substring(data.indexOf("target=")+8,data.indexOf(\'"\',data.indexOf("target=")+8));
          var dat=data.substring(0,data.indexOf("</a>"));
          var pna=dat.substring(dat.lastIndexOf(\'">\')+2);
          var pll=document.getElementById("playlistslist");
          var r = document.createElement("INPUT");
          var att = document.createAttribute("value");
          att.value = pid;
          r.setAttributeNode(att);
          if (pll.childElementCount==0) {
            var att = document.createAttribute("checked");
            r.setAttributeNode(att);          
          }
          att = document.createAttribute("type");
          att.value = "radio";
          r.setAttributeNode(att);
          att = document.createAttribute("name");
          att.value = "playlist";
          r.setAttributeNode(att);
          var l = document.createElement("LABEL");
          var d = document.createElement("DIV");
          var att = document.createAttribute("class");
          att.value = "radio";
          d.setAttributeNode(att);
          var n = document.createTextNode(pna);
          l.appendChild(r);
          l.appendChild(n);
          d.appendChild(l);          
          pll.appendChild(d);
          $(".playlists").html($(".playlists").html()+data);
          setTimeout(registerdelete,100);
          setTimeout(registershowpl,100);
        }
      });
    });   
  });</script>';
  echo '<div class="row box"><div class="col-xs-12">';
  echo '<div class="row" style="margin-top:10px;"><div class="col-xs-6">';
  echo '<input class="form-control" type="text" value="" name="playlistname">';
  echo '</div><div class="col-xs-1">';
  echo '<input class="btn btn-primary" type="button" value="Add" name="addplaylist">';
  echo '</div></div><div class="row"><div class="playlists col-xs-12">';
  foreach ($result as $row) {
    echo '<span><img class="delete" target="' . $row["ID"] . '" src="pix/delete.png" style="margin-right:10px;"/><a href="#'.$row["ID"].'" class="showplaylist">' . $row["Name"] . '</a><form action="'.$base.'/showplaylist.php" method="POST" style="display:inline;margin-left:5px;position:relative;top:7px;"><input type="hidden" name="playlist" value="'.$row["ID"] .'"><input type="submit" style="font-size:26px;background-color:transparent;border-style:none;position:relative;top:-4px;" Value="&#9658;"/></form><BR></span>';
    echo '<div style="display:none;" id="playlist_'.$row["ID"].'">';
    $plresult = $mysql->select("playlistentries", ["*"],"`Playlist`='".$row["ID"]."'");  
    foreach($plresult as $plrow) {
        $plentry=$mysql->select("files",["*"],"`ID`='".$plrow["File"]."'");
        echo $plrow["ID"]." ".$plentry[0]["Filename"]."<BR>";
    }
    echo '</div>';
  }
  echo '</div></div>';
  echo '</div></div></div></body></html>';
}