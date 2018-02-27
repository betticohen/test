var scrollstate=0; // 0: std, 1: small
var isMobile=null;
var scrollStep =0;

window.requestAnimationFrame = window.requestAnimationFrame
 || window.mozRequestAnimationFrame
 || window.webkitRequestAnimationFrame
 || window.msRequestAnimationFrame
 || function(f){setTimeout(f, 1000/60)};
 
function startup() {
	if (typeof window.orientation !== 'undefined') 
		isMobile = true;
		
	var cplayer = new CirclePlayer("#jquery_jplayer_1",
			{
			/*	mp3: "audio/ravel1.mp3" */
			}, {
				cssSelectorAncestor: "#cp_container_1", supplied: "mp3", wmode: "window"
			});

	$("#jquery_jplayer_1").bind($.jPlayer.event.play, function(e) { playvideo(null); });

	$("#menu a").bind("click", function(event) { document.getElementById('menuopen').checked = false; });
	
	$(window).bind("scroll", function(event) {

		requestAnimationFrame(function () {
			var y = $(window).scrollTop();
			var wH = $(window).height(); 
		
			$(".content-bg-container:in-viewport").each(function() {
				var posTop = $(this).offset().top - y;
		 
				var scrollpos = (posTop + $(this).height()) / (wH + $(this).height());
				if(!isMobile)
					this.style.backgroundPosition = Math.round(scrollpos*1000)/10 + "% "+ Math.round(-200 + scrollpos * 400) + "px";
			}); 

			if(isMobile)
				return;
			if(!scrollstate && y >= wH) {
				scrollstate=1;
				document.getElementById("menu").className = "menu-small";
			} else if(scrollstate==1 && y < wH) {
				scrollstate=0;
				document.getElementById("menu").className = "menu-std";
			}
		});
	});

	var i, videobox = document.getElementsByClassName("playvideo");
	
	for(i = 0; i < videobox.length; ++i) {
		var videoid = videobox[i].title;
		videobox[i].getElementsByClassName("bullauge")[0].style.backgroundImage = 
			"url('http://img.youtube.com/vi/"+videoid+"/0.jpg')";
		videobox[i].onclick = function() { 
			playvideo(this.title);
			playaudio();
		};
	}
	
	var audiobox = document.getElementsByClassName("playaudio");

	for(i = 0; i < audiobox.length; ++i) {
		var filename = audiobox[i].title;
		audiobox[i].onclick = function() {
			playaudio("audio/" + this.title);
		};
	}
	
	$("#musicians div.bullauge").bind("click", function(event) {
       /* $("#musicians div.active").removeClass("active");
        $(this).addClass("active");
        $("#"+this.id+"_text").addClass("active"); */
        if(this.id == "bio1")
        	show(this, "bio1_text", "bio1sel", "triography_button", "triography");
        else
        	show(this, this.id+"_text");
        $("#bio_img").css("background-image", $(this).css("background-image"));
    });
    
    onload();
    $("body").css("opacity",1);

	$(".calendarbox").bind("scroll", function(e) {

	    var elem = $(e.currentTarget);
		$("#cal_down").css("opacity",(elem[0].scrollHeight - elem.scrollTop() == elem.outerHeight()) ? "0" : "1");
		$("#cal_up").css("opacity", (!elem.scrollTop()) ? "0" : "1");		
	});

    scrollStep = $(".active.calendarbox").height()/2 + 30;
    $("#cal_up").bind("click", function(event) {
		$(".active.calendarbox").animate({scrollTop: $(".active.calendarbox").scrollTop()-scrollStep}, 500);
		$("#cal_down").css("opacity","1");
	});
    $("#cal_down").bind("click", function(event) {
		$(".active.calendarbox").animate({scrollTop: $(".active.calendarbox").scrollTop()+scrollStep}, 500);
		$("#cal_up").css("opacity","1");
	});

}


function playvideo(videoid) {
	if(!videoid) {
		$("#videoframe").attr("src", "about:blank");			
		$("#videoplayer").css("display", "none");
		return;
	}

	$("#videoframe").attr("src", "http://www.youtube.com/embed/"+videoid+"?vq=hd1080&autoplay=1&modestbranding=1");
	$("#videoplayer").css("display", "inline");
	playaudio();
}

function playaudio(filename) {

	if(filename) {
		$('.cp-jplayer').jPlayer('setMedia', { mp3: filename }).jPlayer('play');
		$("#audioplayer").css('display', 'block');
		// Stop video
		playvideo(null);
	} else { // Stop playing and close player
		$('.cp-jplayer').jPlayer('stop');
		$("#audioplayer").css('display', 'none');
	}
}


function show() {
	var showid,me = arguments[0];

	$(me.parentNode.getElementsByClassName("active")).removeClass("active");

	for (var i = 1; i < arguments.length; i++) {
		showid = arguments[i];
		$(document.getElementById(showid).parentNode.getElementsByClassName("active")).removeClass("active");
		$("#"+showid).addClass("active");
	}
	
	$(me).addClass("active");

}

var current_dia=0;

function diashow(me,filename) {
	current_dia = me;
	$("#dia").attr("src", "img/spinner.gif");
	$("#diashow").css("display", "inline");
	setTimeout('$("#dia").attr("src", "'+filename+'");',100);

}
function dia_next() {
	if(!current_dia)
		return;
	if(current_dia.nextElementSibling)
		current_dia.nextElementSibling.click();
	else
		current_dia.parentNode.firstElementChild.click();
}
function dia_prev() {
	if(!current_dia)
		return;
	if(current_dia.previousElementSibling)
		current_dia.previousElementSibling.click();
	else
		current_dia.parentNode.lastElementChild.click();
}