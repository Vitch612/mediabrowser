<?php
$BODYOPEN = '<html>
<head>
<!-- -->
<link rel="shortcut icon" href="'.$base.'/folder.ico" type="image/x-icon" />
<script type="text/javascript" src="'.$base.'/js/jquery.min.js"></script>
<script type="text/javascript" src="'.$base.'/js/bootstrap.min.js"></script>
<script type="text/javascript" src="'.$base.'/js/search.js"></script>
<script type="text/javascript" src="'.$base.'/js/debug.js"></script>

<script type="text/javascript">
function gotill(got) {
	if (got.is(":visible")) {
		if (got.children(".key").size()<=0) {
			got.hide();
			got.children().hide();
			gotill(got.next());
		}
	} else {
		if (got.children(".key").size()<=0) {
			got.show();
			got.children().show();
			gotill(got.next());
		}
	}
}

$(document).ready(function(){
  $(".nolink").click(function(event) {
    if ($($(this).attr("href")).hasClass("mycollapsed")) {
      $($(this).attr("href")).removeClass("mycollapsed");
    } else {
      $($(this).attr("href")).addClass("mycollapsed");
    }
    event.preventDefault();
    return false;
  });

	$(".b0").children().not(".key").not(".spacer").not(".name").parent().hide();
	$(".spacer").click(function(){
		$(".spacer").hide();
	});

	$(".b1").click(function(){
		$(".b1").hide();

	});

	$(".b2").click(function(){
		$(".b2").hide();
	});

	$(".key").click(function(){
		gotill($(this).parent().next());
	});

	$(".name").click(function(){
		$(".b2").show();
		$(".spacer").show();
		$(".b1").show();
		$(".b0").show();
		$(".key").show();

	});

	$(".b0").hover(function(){
		$(this).children().addClass("hilite");
	},function () {
		$(this).children().removeClass("hilite");
	});
});
</script>
<!-- -->
<style type="text/css">
.hilite {
background-color:#FFFFFF !important;
color:#000000 !important;
}
.navmenu ul li {
  display:inline;
  margin-right:20px;
}
.jsdebug {
  background-color:white;
  border-width:1px;
  border-style:solid;
  border-color:black;
}
.spacer{
float:left;
/*background-color:#FFFF00;*/
margin:0;
padding:0;
}
.mycollapsed {
  display:none;
}
#aplay {
  width:600px;
}
.delete {
  margin-bottom:-6px;
  margin-right:5px;
}
.b1 {
float:left;
margin:0;
padding:0;
font-weight: bold;
background-color:#AAAAFF;
}
.b2 {
margin:0;
padding:0;
margin-left:80px;
color:#1010FF;
background-color:#FF9999;
}
.ISNULL {
padding:0;
margin:0;
margin-left:80px;
color:#EE4422;
background-color:#000055;
font-weight: bold;
}
.name {
width:100%;
font-weight: bold;
text-decoration: underline;
margin:0;
background-color:#0000FF;
padding:0;
}
.key {
width:100%;
font-weight: bold;
background-color:#55FFCC;
margin:0;
padding:0;
}
.panic {
width:100%;
font-weight: bold;
background-color:#FF0000;
margin:0;
padding:0;
}
.b0 {
width:100%;
position:relative;
margin:0;
padding:0;
}
.getSafe {
position:relative;
overflow:auto;
margin:0;
padding:0;
}
.incGetSafe {
position:relative;
float:left;
width:100%;
height:92%;
margin:0;
background-color:#FFFFFF;
padding:0;
overflow:auto;
}
.dirList {
margin:0;
top: 15px;
padding:5px;
margin-top:5px;
margin-bottom:5px;
overflow:auto;
border-style:solid;
border-color:black;
border-width:1px;
}
.welcome {
width:100%;
height:15px;
background-color:#DDDDDD;
padding:0;
margin:0;
}

.table td {
  min-width:20px !important;
}
.nolink {
  text-decoration:none !important;
  cursor:default !important;
  color:black;
}
.filelink {
  text-decoration:none;
  display:block;
  margin-top:10px;
  margin-bottom:10px;
  color: green;
}
.bodydiv {
font-family: lucida console;
word-wrap:break-word;
text-wrap:break-word;
font-size:16px;
padding:5px;
margin:0;
}
body
{
background-color:lightgrey;
text-align:left;
margin:0;
padding:10px;
}
</style></head>
<body>
<div class="bodydiv container">';
echo $BODYOPEN;
