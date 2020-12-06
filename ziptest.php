<?php

echo "<HR>ZIP<HR>";
$zip = new ZipArchive();
$filename="newtest/newtest.zip";
if ($zip->open($filename) == TRUE) {
 for ($i = 0; $i < $zip->numFiles; $i++) {
     $filename = $zip->getNameIndex($i);
     echo $filename;
     if (strrpos($filename,"/")!=strlen($filename)-1) {
        //$filecontents=$zip->getFromName($filename);
        //echo gettype($filecontents)."<BR>";
        //echo "$filecontents<HR>";
        echo " is file<BR>";
     } else {
        echo " is directory<BR>";
     }
 }
 $zip->close();
} else {
    $zip->close();
    echo "can't open zip archive<BR>";
}






echo "<BR><HR>RAR<HR>";
$filename="newtest/newtest.rar";
if (($rar = RarArchive::open($filename)) !== FALSE) {
    $entries = $rar->getEntries();

    foreach ($entries as $entry) {
        echo $entry->getName();
        if ($entry->isDirectory()) {
            echo " is directory<BR>";
        } else {
            echo " is file<BR>";
            //$fs=$entry->getStream();
            //$content=stream_get_contents ($fs);
        }
    }
    $rar->close();
} else {
    $rar->close();
    echo "can;'t to open rar archive.<BR>";
}