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
    $("#ScanPage").attr("href", "scanfolders.php");
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
    $("#ScanPage").click(function (e) {
        if (!confirm("The scan page can take a long time to run, are you sure you want to open it?")) {
            e.preventDefault();
        }
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
