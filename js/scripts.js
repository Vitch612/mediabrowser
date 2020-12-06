function swipedetect(el, callback) {

    var touchsurface = el,
            swipedir,
            startX,
            startY,
            distX,
            distY,
            threshold = 50, //required min distance traveled to be considered swipe
            restraint = 30, // maximum distance allowed at the same time in perpendicular direction
            allowedTime = 1500, // maximum time allowed to travel that distance
            elapsedTime,
            startTime,
            handleswipe = callback || function (swipedir) {};

    touchsurface.addEventListener('touchstart', function (e) {
        var touchobj = e.changedTouches[0];
        swipedir = 'none';
        dist = 0;
        startX = touchobj.pageX;
        startY = touchobj.pageY;
        startTime = new Date().getTime(); // record time when finger first makes contact with surface
        //e.preventDefault()
    }, false);

    touchsurface.addEventListener('touchmove', function (e) {
        //e.preventDefault()
    }, false);

    touchsurface.addEventListener('touchend', function (e) {
        var touchobj = e.changedTouches[0];
        distX = touchobj.pageX - startX; // get horizontal dist traveled by finger while in contact with surface
        distY = touchobj.pageY - startY; // get vertical dist traveled by finger while in contact with surface
        elapsedTime = new Date().getTime() - startTime; // get time elapsed

        if (elapsedTime <= allowedTime) { // first condition for awipe met
            if (Math.abs(distX) >= threshold && Math.abs(distY) <= restraint) { // 2nd condition for horizontal swipe met
                swipedir = (distX < 0) ? 'left' : 'right'; // if dist traveled is negative, it indicates left swipe
            } else if (Math.abs(distY) >= threshold && Math.abs(distX) <= restraint) { // 2nd condition for vertical swipe met
                swipedir = (distY < 0) ? 'up' : 'down'; // if dist traveled is negative, it indicates up swipe
            }
        }
        if (swipedir!=="none") {
            e.preventDefault();
            handleswipe(swipedir);
        }

    }, false);
}

function iteratethroughresults() {
    if (xhr.responseText !== undefined)
        if (xhr.responseText.length > lastsize) {
            $("#displaytext").html(xhr.responseText);
            lastsize = xhr.responseText.length;
        }
    if (searchresults.readyState !== 4)
        setTimeout(iteratethroughresults, 100);
}

var lastsize = 0;
var searchresults;
var xhr = $.ajaxSettings.xhr();

function xhrProvider() {
    return xhr;
}

function gotill(got) {
    if (got.is(":visible")) {
        if (got.children(".key").size() <= 0) {
            got.hide();
            got.children().hide();
            gotill(got.next());
        }
    } else {
        if (got.children(".key").size() <= 0) {
            got.show();
            got.children().show();
            gotill(got.next());
        }
    }
}

$(document).ready(function () {
    $('#searchform').submit(function() {
      return false;
    });
    $("#searchbutton").click(function (event) {
        $(".progress").show();
        lastsize = 0;
        xhr.abort();
        $("#displaytext").html("");
        searchresults = $.ajax({
            url: "search.php",
            method: "POST",
            data: {searchstring: $("#searchstring").val()},
            context: document.body,
            xhr: xhrProvider,
            beforeSend: function () {
                setTimeout(iteratethroughresults, 10);
            }
        }).done(function () {
            $(".progress").hide();
        });
        event.preventDefault();
    });
    $(".nolink").click(function (event) {
        if ($($(this).attr("href")).hasClass("mycollapsed")) {
            $($(this).attr("href")).removeClass("mycollapsed");
        } else {
            $($(this).attr("href")).addClass("mycollapsed");
        }
        event.preventDefault();
        return false;
    });
    $(".b0").children().not(".key").not(".spacer").not(".name").parent().hide();
    $(".spacer").click(function () {
        $(".spacer").hide();
    });
    $(".b1").click(function () {
        $(".b1").hide();

    });
    $(".b2").click(function () {
        $(".b2").hide();
    });
    $(".key").click(function () {
        gotill($(this).parent().next());
    });
    $(".name").click(function () {
        $(".b2").show();
        $(".spacer").show();
        $(".b1").show();
        $(".b0").show();
        $(".key").show();
    });

    $(".b0").hover(function () {
        $(this).children().addClass("hilite");
    }, function () {
        $(this).children().removeClass("hilite");
    });

    var prev=function () {
        var query="";
        if (window.location.toString().lastIndexOf("?")>=0) {
            query=window.location.toString().substr(window.location.toString().lastIndexOf("?")+1,window.location.toString().length);
            if (query.indexOf("gofullscreen=true")>0) {
                query="gofullscreen=true";
            } else if (query.indexOf("gofullscreen=false")>0) {
                query="gofullscreen=false";
            }
        }
        if ($("#previous").length>0) {
            $(".displayedimage").css("position","relative");
            $(".displayedimage").css("left","0px");
            if ($(".displayedimage").length>0) {
                $(".displayedimage").animate({"left":"2000px"},400,function() {
                    window.location="/browse/show/"+$("#previous").html()+"?"+query;
                });
            } else {
                window.location="/browse/show/"+$("#previous").html()+"?"+query;
            }

        }
    };

    var next=function() {
        var query="";
        if (window.location.toString().lastIndexOf("?")>=0) {
            query=window.location.toString().substr(window.location.toString().lastIndexOf("?")+1,window.location.toString().length);
            if (query.indexOf("gofullscreen=true")>0) {
                query="gofullscreen=true";
            } else if (query.indexOf("gofullscreen=false")>0) {
                query="gofullscreen=false";
            }
        }        
        if ($("#next").length>0) {
            $(".displayedimage").css("position","relative");
            $(".displayedimage").css("right","0px");
            if ($(".displayedimage").length>0) {
                $(".displayedimage").animate({"right":"2000px"},400,function() {
                    window.location="/browse/show/"+$("#next").html()+"?"+query;
                });                
            } else {
                window.location="/browse/show/"+$("#next").html()+"?"+query;
            }
        }
    };
    $(".copybutton").click(function() {
        var tmptext = document.createElement("textarea");
        tmptext.value=$(this).data("text");
        tmptext.id="tempdivforcopy";
        document.getElementsByTagName("BODY")[0].appendChild(tmptext);
        tmptext.select();
        document.execCommand("copy");
        document.getElementsByTagName("BODY")[0].removeChild(tmptext);
    });
    var folder=function() {
        var query="";
        if (window.location.toString().lastIndexOf("?")>=0) {
            query=window.location.toString().substr(window.location.toString().lastIndexOf("?")+1,window.location.toString().length);
        }
        if ($("#folder").length>0) {
            $(".displayedimage").css("position","relative");
            $(".displayedimage").css("top","0px");
            if ($(".displayedimage").length>0) {
                $(".displayedimage").animate({"top":"5000px"},400,function() {
                    window.location="/browse/?path="+$("#folder").html();
                });
            } else {
                window.location="/browse/?path="+$("#folder").html();
            }
            
        }
    };
    window.next=next;
    window.prev=prev;
    
    
    
    $(".fullscreenlink").click(function (e) {
        e.preventDefault();
        var query="";
        if (window.location.toString().lastIndexOf("?")>=0) {
            query=window.location.toString().substr(window.location.toString().lastIndexOf("?")+1,window.location.toString().length);
        }
        var redirect=$(this)[0].href;
        if (query.indexOf("fullscreen=true")>=0) {
            redirect=$(this)[0].href.replace("fullscreen=true","fullscreen=false");
        }
        if (typeof window.pagenumber !== "undefined") {
            redirect+="&pagenumber="+window.pagenumber;
        }
        window.location=redirect;
    });

    var swcallback = function (direction) {
        if (direction === "right") {
            window.prev();
        }
        if (direction === "left") {
            window.next();
        }
    };

    var tapedTwice = false;
    var tapHandler=function(event) {
        if(!tapedTwice) {
            tapedTwice = true;
            setTimeout( function() {tapedTwice = false;}, 300 );
            return false;
        } else {
            event.preventDefault();
            folder();
        }
    }
    if ($(".displayedimage").length>0)
        $(".displayedimage")[0].addEventListener("touchstart", tapHandler);

    $(".prevlink").click(function(e) {
       e.preventDefault();
       prev();
    });
    $(".nextlink").click(function(e) {
       e.preventDefault();
       next();
    });

    window.addEventListener("keydown", function (key) {
        if (key.keyCode === 13 || key.keyCode === 27) {
            folder();
        }
        if (key.keyCode === 37) {
            window.prev();
        }
        if (key.keyCode === 39) {
            window.next();
        }
    });

    if ($(".displayedimage").length>0)
        swipedetect($(".displayedimage")[0], swcallback);
    if ($("#pdfdiv").length>0)
        swipedetect($("#pdfdiv")[0], swcallback);

    /*
     var scripts = document.getElementsByTagName('script');
     var base = scripts[scripts.length-1].src;
     base=base.substring(0,base.lastIndexOf("/")+1);
     var sc=document.createElement("script");
     sc.setAttribute('type', 'text/javascript');
     sc.setAttribute('src', base+"bootstrap.min.js");
     document.documentElement.appendChild(sc);
     */
});
