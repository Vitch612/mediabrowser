<?php
$BODYOPEN = '<!doctype html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="shortcut icon" href="'.$base.'/pix/folder.ico" type="image/x-icon" />
<link href="'.$base.'/css/bootstrap.min.css" rel="stylesheet" />
<link href="'.$base.'/css/styles.css" rel="stylesheet" />
<script type="text/javascript" src="'.$base.'/js/jquery.min.js"></script>
<script type="text/javascript" src="'.$base.'/js/bootstrap.min.js"></script>
<script type="text/javascript" src="'.$base.'/js/jquery.min.js"></script>
<script type="text/javascript" src="'.$base.'/js/pdf.min.js"></script>
<script type="text/javascript" src="'.$base.'/js/scripts.js"></script>
</head>';
echo $BODYOPEN;
if (!$fullscreen)
    echo '<body><div class="bodydiv container-fluid">';    

    
