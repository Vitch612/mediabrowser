<?php

include "include.php";
include "head.php";

function search() {
    echo '<div class="row box"><div class="col-xs-12"><div class="row" style="margin-top:10px;"><div class="col-xs-7"><form id="searchform" method="post" action="search.php"><input class="form-control" type="text" id="searchstring" name="searchstring"/></div><div class="col-xs-1 searchbutton"><input class="btn btn-primary" type="button" id="searchbutton" value="Search"/></div><div class="col-xs-1 searchprogress"><img width="20" height="20" class="img-fluid progress" src="pix/progress.gif"></form></div></div>';
    echo '<div class="row"><div class="col-xs-12" style="margin-left:10px;" id="displaytext"></div></div></div></div>';
}

function getsafe($var, $name = "", $spacer = "") {
    $firstrun = false;
    if ($spacer == "") {
        $returnvalue = '<div class="getSafe"><div class="b0"><div class="name">' . $name . '</div></div>';
        $firstrun = true;
    } else
        $returnvalue = "";
    $inc = "&nbsp;&nbsp;&nbsp;";
    $spacer .= $inc;
    if ($var) {
        $L0 = count($var);
        $T0 = gettype($var);
        if ($L0 >= 1) {
            $counter = 0;
            foreach ($var as $key => $value) {
                $counter++;
                $L2 = count($value);
                $T2 = gettype($value);
                if ($L2 > 1) {
                    $returnvalue .= '<div class="b0"><div class="spacer">' . $spacer . '</div><div class="key">[' . $key . '](' . $L2 . ')</div></div>';
                    if ($key != $name) {
                        $spacer .= $inc;
                        $returnvalue .= getsafe($value, $key, $spacer);
                        $spacer = substr($spacer, 0, strlen($spacer) - strlen($inc));
                    }
                } else if ($L2 == 1) {
                    if ($T2 == "array") {
                        $returnvalue .= '<div class="b0"><div class="spacer">' . $spacer . '</div><div class="key">[' . $key . '](' . $L2 . ')</div></div>';
                        $spacer .= $inc;
                        $returnvalue .= getsafe($value, $key, $spacer);
                        $spacer = substr($spacer, 0, strlen($spacer) - strlen($inc));
                    } else {
                        $value = htmlentities($value);
                        $returnvalue .= '<div class="b0"><div class="spacer">' . $spacer . '</div><div class="b1">[' . $key . '](' . $L2 . ':' . $T2 . ')</div><div class="b2">' . $value . '</div></div>';
                    }
                } else if ($L2 == 0) {
                    $returnvalue .= '<div class="b0"><div class="spacer">' . $spacer . '</div><div class="b1">[' . $key . '](' . $L2 . ')</div><div class="b2 ISNULL">NULL</div></div>';
                }
            }
            if ($firstrun)
                return $returnvalue . "</div>";
            else
                return $returnvalue;
            /* } elseif ($L0==1) {
              $var=htmlentities($var);
              if ($name!="")
              $returnvalue.= '<div class="b0"><div class="spacer">'.$spacer.'</div><div class="b1">['.$name.']('.$L0.':'.$T0.')</div><div class="b2">'.$var.'</div></div>';
              else
              $returnvalue.= '<div class="b0"><div class="spacer">'.$spacer.'</div><div class="b1">('.$L0.':'.$T0.')</div><div class="b2">'.$var.'</div></div>';
              if ($firstrun)
              return $returnvalue."</div>";
              else
              return $returnvalue;
             */
        } elseif ($L0 == 0) {
            $var = htmlentities($var);
            $returnvalue .= '<div class="b0"><div class="spacer">' . $spacer . '</div><div class="b1">[' . $L0 . '][' . $name . ']</div><div class="b2 ISNULL">NULL</div></div>';
            if ($firstrun)
                return $returnvalue . "</div>";
            else
                return $returnvalue;
        }
    } else {
        return $returnvalue . '<div class="b0"><div class="panic">nothing to show</div></div>';
    }
    if ($firstrun)
        return $returnvalue . '<div class="b0"><div class="panic">why on earth are we here??</div></div></div>';
    else
        return $returnvalue . '<div class="b0"><div class="panic">why on earth are we here??</div></div>';
}

function makeSafe($msg) {
    $msg = str_replace(">", "&gt;", $msg);
    $msg = str_replace("<", "&lt;", $msg);
    return $msg;
}

function debug() {
    $_SERVER;
    $_REQUEST;
    return getsafe($GLOBALS, "GLOBALS");
}

function dirSize($directory) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

function dirlist($dirpath, $show = 0) {
    global $file_types;
    global $file_icons;
    $id = get_id();
    echo "<div class=\"dirList box row\"><div class=\"col-xs-12\">";
    echo "<div class=\"row\"><div class=\"col-xs-12\"><a href=\"#$id\" class=\"nolink\">Folder: <b>" . clean_dirpath($dirpath) . "</b></a></div></div>";

    $dir_handle = @opendir($dirpath) or die;
    echo "<div id=\"$id\" class=\"row" . ($show == 0 ? " mycollapsed" : "") . "\"><div class=\"col-xs-12\"><table class=\"table-responsive\" width=\"100%\">";
    $TheLinkedFile = $dirpath . "../";
    $cpath = base64_encode($TheLinkedFile);
    echo "<tr bgcolor=\"FFFFFF\"><td><a href=\"?path=$cpath\"><div class=\"namecontainer\"><img class=\"img-fluid\" src=\"pix/up.png\"/></div></a></td><td width=\"100%\"><a href=\"?path=$cpath\"><div class=\"namecontainer\">..</div></a></td><td></td></tr>";
    $toggle = "false";
    while ($file = readdir($dir_handle)) {
        if ($file == "." || $file == "..")
            continue;
        $TheLinkedFile = $dirpath . $file;
        $len = strlen($file);
        if (is_dir($TheLinkedFile)) {
            if ($toggle == "true") {
                $toggle = "false";
                $BGCOLOR = "FFFFFF";
            } else {
                $toggle = "true";
                $BGCOLOR = "F0F0F0";
            }
            $fsize=dirSize($TheLinkedFile);
            if (!is_numeric($fsize)) {
                $fsize = "";
            } else {
                if ($fsize/(1024*1024*1024)>1)
                    $fsize=round($fsize/(1024*1024*1024),1)." GB";
                else if ($fsize/(1024*1024)>1)
                    $fsize=round($fsize/(1024*1024),1)." MB";
                else if ($fsize/(1024)>1)
                    $fsize=round($fsize/(1024),1)." KB";
                else
                    $fsize.=" B";
            }
            $cpath = base64_encode(clean_dirpath($TheLinkedFile . "/"));
            $filename = $file;
            echo "<tr bgcolor=\"$BGCOLOR\"><td><a href=\"?path=$cpath\"><div class=\"namecontainer\"><img class=\"img-fluid\" src=\"pix/folder.png\"/></div></a></td><td width=\"100%\"><a href=\"?path=$cpath\"><div class=\"namecontainer\">" . $filename . "</div></a></td><td align=right><div class=\"nolinkcontainer\">$fsize</div></td></tr>";
        }
    }
    closedir($dir_handle);
    $dir_handle = @opendir($dirpath) or die;
    $filelist=[];
    while ($file = readdir($dir_handle)) {
        if ($file == "." || $file == "..")
            continue;
        $TheLinkedFile = $dirpath . $file;
        $len = strlen($file);
        $insert = substr($file, $len - 5, 5);
        if (is_dir($TheLinkedFile)) {
            continue;
        } else {
            if ($toggle == "true") {
                $toggle = "false";
                $BGCOLOR = "FFFFFF";
            } else {
                $toggle = "true";
                $BGCOLOR = "F0F0F0";
            }
            $ftype = get_file_type($file);
            $icon = $file_icons[$ftype];
            $cpath = base64_encode($TheLinkedFile);
            $linko = "<a href=\"show/$cpath{query}\">";
            $linke = "</a>";
            $fsize = filesize($TheLinkedFile);
            
            if (!is_numeric($fsize)) {
                $fsize = "";
            } else {
                if ($fsize/(1024*1024*1024)>1)
                    $fsize=round($fsize/(1024*1024*1024),1)." GB";
                else if ($fsize/(1024*1024)>1)
                    $fsize=round($fsize/(1024*1024),1)." MB";
                else if ($fsize/(1024)>1)
                    $fsize=round($fsize/(1024),1)." KB";
                else
                    $fsize.=" B";
            }
            $filename = $file;
            
            $filelist[]=["row"=>"<tr bgcolor=\"$BGCOLOR\"><td>$linko<div class=\"namecontainer\"><img class=\"img-fluid\" src=\"$icon\"/></div>$linke</td></td><td width=\"100%\">$linko<div class=\"namecontainer\">$filename</div>$linke</td><td align=right><div class=\"nolinkcontainer\">$fsize</div></td></tr>","cpath"=>$cpath];
            
        }
    }
    
    foreach($filelist as $key=>$file) {
        
        if (isset($filelist[$key-1])) {
            $next=$filelist[$key-1]["cpath"];
        }
        if (isset($filelist[$key+1])) {
            $prev=$filelist[$key+1]["cpath"];
        }
        $query="";
        if ($next!==null || $prev !==null) {
            $query="?".($next!==null?"next=$next":"").($prev!==null?($next!==null?"&":"")."prev=$prev":"");
        }
        
        echo str_replace("{query}","",$file["row"]);
        //echo str_replace("{query}",$query,$file["row"]);
    }
    closedir($dir_handle);
    echo "</table></div></div></div></div>";
}

show_nav();
search();
if (isset($_GET["path"])) {
    $req = str_replace("\\", "/", clean_dirpath(base64_decode($_GET["path"])));
    if (check_permission($req))
        dirlist($req, 1);
    else {
        header("HTTP/1.1 403 Access Denied");
        die("<h3>Access Denied</h3>");
    }
} else {
    foreach ($shares as $share => $value) {
        dirlist($share);
    }
}
//echo '<pre>'.print_r($_SERVER,true).'</pre>';
//echo '<div class="incGetSafe">'.debug().'</div>';
echo '</div></body></html>';
