<?php
header('Content-Type: text/html; charset=UTF-8');

setlocale(LC_ALL, 'de_DE.UTF-8');
session_start();

const MESSAGE_TIMEOUT = 120;

if(isset($_POST["ContactName"]) && $_POST["ContactName"]) { // Send MAIL
	if(isset($_SESSION["mail_timestamp"]))
		$diff = MESSAGE_TIMEOUT - (time() - $_SESSION["mail_timestamp"]);

	if(isset($_POST["g-recaptcha-response"])) {
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array('secret' => '6LdTyCEUAAAAAOXZUHqOicVlpFoGON6tCv7q0JEl', 
					  'response' => $_POST["g-recaptcha-response"],
					  'remoteip' => $_SERVER["REMOTE_ADDR"]);
		$data = http_build_query($data,null,'&');

		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded",
				'method'  => 'POST',
				'content' => $data
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === FALSE) { /* Handle error */ }

		$verify = json_decode($result);
	}

	if((isset($verify) && $verify->success) && (!isset($diff) || $diff <= 0)) {
		$Cname = $_POST["ContactName"];
		$Cemail = $_POST["ContactEmail"];
		$Cmsg = $_POST["ContactMessage"];
	
		$from = "TRIO GAON website (KOREAN)";
		$to = "triogaon@gmail.com";
		$subject = "Website-Message from www.triogaon.com";
		$body = "Name: $Cname\n E-Mail: $Cemail\n\n Message:\n $Cmsg";
		if(isset($_POST["ContactNewsletter"]) && $_POST["ContactNewsletter"] == "signup")
			$body .= "\n\nNewsletter requested!";

		$mailresult = mail($to, $subject, $body, $from);
		$_SESSION["mail_timestamp"] = time();
	}
	else {
		$mailresult = 0;
		$mailtimer = $diff;
	}
}

/* Calendar start */
require("termine.php");

$archive = $next_concert = $upcoming = "";
$upcoming_count = 0;

while($concert = getNextConcert()) {
	if(($timesig=strtotime($concert->concertdate)) >= (time()-(24*60*60)))
		++$upcoming_count;
	
	$day = strftime("%e",$timesig);
	$month = strftime("%B",$timesig);
	$weekday = htmlspecialchars(utf8_encode(strftime("%a",$timesig)));
	$time = substr($concert->concerttime,0,5);
	if($time != "00:00") {
		if(substr($time,3,2) == "00")
			$time = substr($time,0,2)."h";
		$concert->venue = "($time) {$concert->venue}";
	}


	$str = "<div class='cal-block' onClick=\"window.open('{$concert->website}');\"><div class='cal-left'><div class='cal-weekday'>$weekday</div><div class='cal-day'>$day</div><div class='cal-month'>$month</div></div>\n";
	$str .= "<div class='cal-right'><p class='cal-location'>{$concert->location}</p><div class='cal-venue'>{$concert->venue}</div><p class='cal-program'>{$concert->program}</p></div></div>\n";
	
	switch($upcoming_count) {
		case 0: // is previous concert
			$archive = $str . $archive;
			break;
		case 1: // is next concert
			$next_concert = $str;
			break;
		default:
			$upcoming = $upcoming . $str;
	}
}


/* Calendar stop */

$diashow = array();
$c = 0;
$thumbs_dir = 'img/diashow/thumbs/';
$dias_dir = 'img/diashow/';
if ($handle = opendir($thumbs_dir)) {

    while (false !== ($entry = readdir($handle))) {
        $diashow[$c++] = $entry;
	}
	closedir($handle);
}


?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<meta name="format-detection" content="telephone=no">

	<meta name="author" content="Samuel Lutzker">
	<meta name="description" content="Website of Munich-based piano trio TRIO GAON">
	<meta name="robots" content="index,follow,noarchive">
	<meta name="keywords" content="Trio Gaon, Gaon, Klaviertrio, Bayerischer Rundfunk, Piano Trio, Bavarian Radio, Samuel Lutzker, Tae-Hyung Kim, Jehye Lee" />


	<link rel="stylesheet" href="menu.css?v=<?php echo filemtime('menu.css'); ?>">
	<link rel="stylesheet" href="layout.css?v=<?php echo filemtime('layout.css'); ?>">
	<link rel="stylesheet" href="colors.css?v=<?php echo filemtime('colors.css'); ?>">
	<link rel="stylesheet" href="responsive.css?v=<?php echo filemtime('responsive.css'); ?>">
	
	<title>TRIO GAON - &#53944;&#47532;&#50724; &#44032;&#50728;</title>

	<link rel="stylesheet" href="cp/css/not.the.skin.css">
	<link rel="stylesheet" href="cp/circle.skin/circle.player.css">
	
	<script type="text/javascript" src='https://www.google.com/recaptcha/api.js?hl=en'  async defer></script>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="cp/js/jquery.transform2d.js"></script>
	<script type="text/javascript" src="cp/js/jquery.grab.js"></script>
	<script type="text/javascript" src="cp/js/jquery.jplayer.js"></script>
	<script type="text/javascript" src="cp/js/jquery.viewport.js"></script>
	<script type="text/javascript" src="cp/js/mod.csstransforms.min.js"></script>
	<script type="text/javascript" src="cp/js/circle.player.js"></script>
<!--	<script type="text/javascript" src="https://getfirebug.com/firebug-lite.js"></script> -->
	
	<script type="text/javascript" src="scripts.js?v=<?php echo filemtime('scripts.js'); ?>"></script>
	<script type="text/javascript">

<?php
if(isset($mailresult)) {
	if($mailresult)
		echo "alert('Message sent, thank you.');";
	else {
		if(isset($mailtimer))
			echo "alert('Please try again in $mailtimer seconds. Thank you.');";
		else
			echo "alert('Error sending message, perhaps you forgot to click the CAPTCHA?');";
	}
}
?>

  function onSubmit(token) {
    document.getElementById("send_mail").submit();
  }

  function validate(event) {
    event.preventDefault();
    if (!document.getElementById('CName').value || !document.getElementById('CEmail').value || !document.getElementById('CMessage').value) {
      alert("Please fill out the form");
    } else {
      grecaptcha.execute();
    }
  }

  function onload() {
    var element = document.getElementById('submitform');
    element.onclick = validate;
  }

	</script>

</head>

<body onload="startup();">

<!--  jPlayer div -->
<div id="jquery_jplayer_1" class="cp-jplayer"></div>



	<input type="checkbox" id="menuopen"><label for="menuopen" onclick></label> 

	<nav role="off-canvas" id="menunav">
		<ul id="menu" class="menu-std">
			<li id="homelogo"><a id="logo" href="#top">Trio Gaon</a></li>
			
			<li><a href="#musicians_top" title="About us">Vita</a></li>

			<li><a href="#calendartypes" title="Calendar">Concerts</a></li>

			<li><a href="#mediatypes" title="Video & Audio">Media</a></li>
		
			<li><a href="#contactform" title="Contact us!">Contact</a></li>
			
			
			<li id="socialmedia">
			<a href="http://www.triogaon.com/" style="background-image:url('img/deu.png');"></a>
			<a href="http://www.triogaon.com/english.php" style="background-image:url('img/eng.png');"></a>
				<a href="https://www.facebook.com/triogaon/" target="_blank" style="background-image:url('img/facebook.png');"></a>
				<a href="https://www.youtube.com/channel/UCcTy2vXHWz1XdjfCtDPH_OA" target="_blank" style="background-image:url('img/youtube.png');"></a>
			</li>
		
		</ul>
	</nav>
	

	<div id="home-bg"></div>

	<div id="home" class="page blue">

			<div class="content-block">
				<div class="content-small"></div>
				<div class="content-big">
				<h2>&raquo;&#51060; &#49464; &#47749;&#51032; &#50672;&#51452;&#45716; &#44536;&#51200; &#44257;&#51012; &#50672;&#51452;&#54616;&#45716; &#44172; &#50500;&#45768;&#46972; &#51020;&#50501; &#44536; &#51088;&#52404;&#51032; &#44592;&#49256;&#51012; &#51204;&#45804;&#54644;&#45236;&#45716;
&#45824;&#45800;&#54620; &#50676;&#51221;&#51032; &#50672;&#51452;&#51088;&#46308;&#51060;&#50632;&#45796;.&laquo; (Allg&auml;uer Zeitung)</h2>
<br />

<p>&#49436;&#47196; &#45796;&#47480; &#47928;&#54868;&#51201; &#48176;&#44221;&#44284; &#44060;&#49457;&#51012; &#51648;&#45772; &#49464; &#47749;&#51032; &#50672;&#51452;&#51088;&#44032; &#51032;&#44592;&#53804;&#54633;&#54620; &#55141;&#48120;&#47196;&#50868; &#45800;&#52404;&#51064; &#53944;&#47532;&#50724; &#44032;&#50728;&#51008; &#49464;&#44228; &#47928;&#54868;&#50696;&#49696;&#44228;&#51032; &#55120;&#47492;&#51064; &#49464;&#48169;&#54868;(glocalization)&#50752; &#50997;&#54633;(convergence)&#51032; &#51221;&#49888;&#51012; &#48152;&#50689;&#54616;&#50668; &#49692; &#54620;&#44544;&#50640;&#49436; &#45800;&#52404;&#51032; &#51060;&#47492;&#51012; &#52264;&#50857;&#54616;&#50688;&#45796;. &#8216;&#44032;&#50728;&#8217;&#51060;&#46972; &#54632;&#51008; &#8216;&#54620; &#44032;&#50868;&#45936;&#50640; &#51080;&#45716;(&#49464;&#44228;&#51032; &#51473;&#49900;)&#8217; &#51060;&#46972;&#45716; &#51032;&#48120;&#50752; &#54632;&#44760; &#8216;&#50612;&#46500; &#47932;&#51656;&#50640; &#50728;&#46020;&#47484; &#45908;&#54620;&#45796;&#8217;&#45716; &#51032;&#48120;&#47484; &#44032;&#51648;&#44256; &#51080;&#50612;, &#50672;&#51452;&#51088;&#46308;&#51060; &#52397;&#51473; &#44536;&#47532;&#44256; &#51020;&#50501;&#51032; &#8216;&#54620; &#44032;&#50868;&#45936;&#8217;&#50640;&#49436; &#8216;&#50728;&#46020;&#47484; &#45908;&#54616;&#50668;&#8217; &#51020;&#50501;&#51032; &#50640;&#45320;&#51648;&#47484; &#51204;&#45804;&#54616;&#44256;&#51088; &#54616;&#45716; &#47560;&#51020;&#51012; &#45812;&#50520;&#45796;.</p>

				</div>
		</div>

<!--		<div id="concertbubble" onClick="document.getElementById('mediatypes').scrollIntoView(); playvideo('C929IPOPIUU');">
			<div style="letter-spacing:2px;">Francis Poulenc</div>
			<div class="bullauge" style="background-image:url('img/poulenc.jpg');"></div>
			<div style="font-size:0.6em">&raquo;Les Chemins de l'Amour&laquo;</div>
		</div> -->

		<div id="concertbubble" onClick="window.open('http://www.acmtrioditrieste.it/i-vincitori-2/');">
			<div style="letter-spacing:2px;">1st Prize</div>
			<div class="bullauge" style="background-image:url('img/trieste.jpg');background-position:30%;box-shadow:none;"></div>
			<div style="font-size:0.6em">and two special prizes at &raquo;Premio Trio di Trieste&laquo;</div>
		</div>

	</div>

	<div id="musicians" class="page red">
		<div class="content-bg-container"><div class="content-bg" style="background-image:url('img/trio-sw.jpg');"><h1>Biography</h1></div></div>

		<div class="content-block" id="musicians_top">
			<div id="bio1" title="Triography" class="bullauge active" style="background-image:url('img/trio-concert.jpg');"></div>
			<div id="bio2" title="Tae-Hyung Kim" class="bullauge" style="background-image:url('img/taehyung.jpg');"></div>
			<div id="bio3" title="Jehye Lee" class="bullauge" style="background-image:url('img/jehye.jpg');"></div>
			<div id="bio4" title="Samuel Lutzker" class="bullauge" style="background-image:url('img/sam.jpg');"></div>
		</div>
		<div class="content-block">
			<div class="content-small">
				<div id="bio1sel" class="option active">
					<a href="javascript:void(0);" id="triography_button" onClick="show(this,'triography','bio1sel');" class="active">Vita</a>
					<a href="javascript:void(0);" id="repertoire_button" onClick="show(this,'repertoire','bio1sel');">Repertoire</a>
					<br />
					<br />
				</div>
				<div id="bio_img" class="bullauge" style="background-image:url('img/trio-concert.jpg');"></div>
			</div>
			<div id="bio1_text" class="content-big option active">

				<div id="triography" class="option active">
				<h2>&raquo;&#53944;&#47532;&#50724; &#44032;&#50728;&#51008; J. Fran&#1195;aix &#54588;&#50500;&#45432; &#53944;&#47532;&#50724; &#50672;&#51452;&#50640;&#49436; &#50976;&#47672;&#50752; &#50500;&#51060;&#47084;&#45768;, &#44536;&#47532;&#44256; &#47588;&#54841;&#51201;&#51064; &#48708;&#47476;&#53804;&#50724;&#51312;&#47484; &#51060;&#47336;&#50612;&#45256;&#51004;&#47728;, &#49464; &#47749;&#51032; &#45440;&#46972;&#50868; &#49556;&#47532;&#49828;&#53944;&#51032; &#51109;&#51216;&#44284; &#49892;&#45236;&#50501; &#51020;&#50501;&#51032; &#53804;&#47749;&#54632;, &#44536;&#44163;&#51012; &#50948;&#54620; &#45453;&#51061;&#51008; &#51221;&#49888;, &#44048;&#51221;&#50640; &#44618;&#51060; &#48152;&#54624; &#49688; &#48150;&#50640; &#50630;&#50632;&#45796;&laquo;<br />(Harald Eggebrecht - Sueddeutsche Zeitung)</h2><br />

<br />
<p>
&#53944;&#47532;&#50724; &#44032;&#50728;&#51008; &#49892;&#45236;&#50501; &#51648;&#46020;&#51032; &#47749;&#51064;&#51004;&#47196; &#44861;&#55176;&#45716; &#50500;&#45208; &#52628;&#47560;&#52408;&#53076;, &#54532;&#47532;&#46300;&#47564; &#48288;&#47476;&#44144;, &#53356;&#47532;&#49828;&#53664;&#54532; &#54252;&#54172; &#46321;&#51032; &#47928;&#54616;&#50640;&#49436; &#49892;&#45236;&#50501; &#51648;&#46020;&#47484; &#48155;&#50520;&#51004;&#47728;, European Chamber Music Academy (ECMA)&#51032; &#47716;&#48260;&#47196;&#49436; &#51648;&#49549;&#51201;&#51064; &#49892;&#45236;&#50501; &#44618;&#51060;&#47484; &#45908;&#54644;&#44032;&#44256; &#51080;&#45796;. 
</p><p>
2014&#45380; &#48012;&#54760; &#44032;&#49828;&#53440;&#51061; &#51020;&#50501; &#53097;&#53216;&#47476;(Musikpreis des Kulturkreis&#8216; Gasteig e.V.)&#50640;&#49436; &#52572;&#44256;&#51216;&#49688;&#47196; &#50864;&#49849;&#54616;&#50688;&#44256; 2015&#45380; &#48708;&#50644;&#45208;&#50640;&#49436; &#50676;&#47536; &#54616;&#51060;&#46304; &#53097;&#53216;&#47476;(Joseph-Haydn-Wettbewerbs f&#252;r Kammermusik )&#50640;&#49436; &#49345;&#50948; &#51077;&#49345;&#51012; &#54616;&#44592;&#46020; &#54620; &#53944;&#47532;&#50724; &#44032;&#50728;&#51008; &#52285;&#45800;&#46108; &#51648; &#50620;&#47560; &#46104;&#51648; &#50506;&#50520;&#51020;&#50640;&#46020; &#46021;&#51068; &#48012;&#54760;(Philharmonie), &#48296;&#44592;&#50640;, &#54532;&#46993;&#49828;, &#50724;&#49828;&#53944;&#47532;&#50500; &#44536;&#47532;&#44256; &#54620;&#44397;&#50640;&#49436; &#54876;&#48156;&#54620; &#54665;&#48372;&#47484; &#54204;&#52432;&#44032;&#44256; &#51080;&#45796;. &#53945;&#55176; 2013&#45380; 10&#50900; &#49464;&#44228;&#51201;&#51064; &#44428;&#50948;&#47484; &#51088;&#46993;&#54616;&#45716; &#50501;&#48372;&#52636;&#54032;&#49324;&#51064; &#54760;&#47112;(Henle Verlag)&#51032; &#49352;&#47196;&#50868; &#48288;&#53664;&#48292; &#52852;&#53580;&#44256;&#47532;&#51032; &#52636;&#48276;&#51012; &#44592;&#45392;&#54616;&#44592; &#50948;&#54644; &#48148;&#51060;&#50640;&#47480; &#44397;&#47549;&#46020;&#49436;&#44288;(Bayerische Staatsbibliothek M&#252;nchen)&#51032; F&#252;rstensaal&#50640; &#47560;&#47144;&#46108; &#50672;&#51452;&#54924;(Festakt Beethoven-Werkverzeichnis)&#50640; &#52488;&#52397;&#46104;&#50612; &#44277;&#50672;&#54616;&#45716; &#50689;&#50696;&#47484; &#44032;&#51648;&#44592;&#46020; &#54664;&#45796;. 
</p>
				</div>
				<div id="repertoire" class="option">
					<h1>Repertoire</h1>
<p><strong>A. Arensky</strong></p>
<p>Trio in d minor, op.32</p>
<p>&nbsp;</p>
<p><strong>L.v. Beethoven</strong></p>
<p>Trio in E-flat Major, Op.1 No.1<br />Trio in D Major, Op.70 No.1 &ldquo;Ghost&rdquo;<br />Triple concerto in C Major, op.56</p>
<p>&nbsp;</p>
<p><strong>J. Brahms</strong></p>
<p>Trio No.1 in B Major, Op.8<br />Trio No.2 in C Major, Op. 87<br />Trio No.3 in c minor, Op. 101</p>
<p>&nbsp;</p>
<p><strong>C. Debussy</strong></p>
<p>Trio in G Major, L.3</p>
<p>&nbsp;</p>
<p><strong>J. Franc&#807;aix</strong></p>
<p>Piano trio (1986)</p>
<p>&nbsp;</p>
<p><strong>J. Haydn</strong></p>
<p>Trio in E Major, Hob. XV<br />Trio in f minor, Hob. XV f1<br />Trio in D Major, Hob. XV 7<br />Trio in G major Hob. XV/25 &laquo; Gypsy &raquo;</p>
<p>&nbsp;</p>
<p><strong>F. Mendelssohn</strong></p>
<p>Trio in d minor No.1, Op.49<br />Trio in c minor No.2, Op.66</p>
<p>&nbsp;</p>
<p><strong>W.A. Mozart</strong></p>
<p>Trio in C Major, K. 548</p>
<p>&nbsp;</p>
<p><strong>M. Ravel</strong></p>
<p>Trio in a minor</p>
<p>&nbsp;</p>
<p><strong>D. Shostakovich</strong></p>
<p>Trio No.1 in c minor, Op. 8<br />Trio No.2 in e minor, Op. 67</p>
<p>&nbsp;</p>
<p><strong>F. Schubert</strong></p>
<p>Adagio &laquo; Notturno &raquo; in E-flat Major, D 897<br />Trio No.2 in E flat Major, D 929</p>
				</div>

			</div>
	
			<div id="bio2_text" class="content-big option">
				<p><h2>Pianist</h2>
				<a name="taehyung"><h1>Tae-Hyung Kim</h1></a> 
&#44397;&#45236;&#50808;&#50640;&#49436; &#44032;&#51109; &#52489;&#47581; &#48155;&#45716; &#54588;&#50500;&#45768;&#49828;&#53944;&#51032; &#54620; &#47749;&#51064; &#44608;&#53468;&#54805;&#51008; &#50696;&#50896;&#54617;&#44368;&#47484; &#44144;&#52432; &#49436;&#50872;&#50696;&#44256;&#47484; &#49688;&#49437; &#51320;&#50629;&#54616;&#50688;&#44256; &#54620;&#44397;&#50696;&#49696;&#51333;&#54633;&#54617;&#44368;&#50640;&#49436; &#44053;&#52649;&#47784;&#47484; &#49324;&#49324;&#54664;&#45796;. &#51060;&#54980; &#46021;&#51068; &#48012;&#54760; &#44397;&#47549;&#51020;&#45824;&#50640;&#49436; &#50648;&#47532;&#49548; &#48708;&#47476;&#49332;&#46972;&#52404;&#51032; &#51648;&#46020; &#50500;&#47000; &#52572;&#44256;&#50672;&#51452;&#51088;&#44284;&#51221;&#51012; &#47560;&#52824;&#44256; &#47784;&#49828;&#53356;&#48148; &#52264;&#51060;&#53076;&#54532;&#49828;&#53412; &#51020;&#50501;&#50896;&#51004;&#47196; &#51088;&#47532;&#47484; &#50734;&#44200; &#48708;&#47476;&#49332;&#46972;&#52404;&#51032; &#51648;&#49549;&#51201;&#51064; &#44032;&#47476;&#52840;&#51012; &#48155;&#50520;&#51004;&#47728;, &#48012;&#54760;&#44397;&#47549;&#51020;&#45824;&#50640;&#49436; &#54764;&#47924;&#53944; &#46020;&#51060;&#52824;&#51032; &#49324;&#49324;&#47196; &#49457;&#50501;&#44032;&#44257;&#48152;&#51452; &#52572;&#44256;&#50672;&#51452;&#51088;&#44284;&#51221;&#51012; &#51320;&#50629;&#54620; &#48148; &#51080;&#45796;. &#44608;&#53468;&#54805;&#51008; &#44536;&#44036; &#47217;-&#54000;&#48372; &#53097;&#53216;&#47476;, &#53304; &#50648;&#47532;&#51088;&#48288;&#49828; &#53097;&#53216;&#47476;, &#54616;&#48148;&#47560;&#52768; &#53097;&#53216;&#47476; &#46321; &#50976;&#49688;&#51032; &#44397;&#51228;&#44221;&#50672;&#50640;&#49436; &#46160;&#44033;&#51012; &#45208;&#53440;&#45236;&#44256; &#54252;&#47476;&#53664; &#53097;&#53216;&#47476;, &#54756;&#51060;&#49828;&#54021;&#49828; &#53097;&#53216;&#47476;, &#54532;&#46993;&#49828; &#50500;&#45768;&#47560;&#53664; &#53097;&#53216;&#47476; &#46321;&#50640;&#49436; &#50864;&#49849;&#54616;&#50668; &#51452;&#47785;&#51012; &#48155;&#50520;&#45796;. &#50689;&#44397; &#47196;&#50676; &#54596;&#54616;&#47784;&#45769; &#50724;&#52992;&#49828;&#53944;&#46972;, &#47084;&#49884;&#50500; &#45236;&#49492;&#45328; &#54596;&#54616;&#47784;&#45769; &#50724;&#52992;&#49828;&#53944;&#46972; &#46321;&#44284; &#54632;&#44760; &#50672;&#51452;&#54664;&#51004;&#47728; &#47084;&#49884;&#50500;&#47484; &#54252;&#54632;&#54620; &#50976;&#47101; &#47924;&#45824;&#47484; &#51473;&#49900;&#51004;&#47196; &#54876;&#48156;&#54620; &#54876;&#46041;&#51012; &#54204;&#52824;&#44256; &#51080;&#45796;.</p>
			</div>

			<div id="bio3_text" class="content-big option">
				<p><h2>Violinist</h2>
				<a name="jehye"><h1>Jehye Lee</h1></a> 
&#48148;&#51060;&#50732;&#47532;&#45768;&#49828;&#53944; &#51060;&#51648;&#54812;&#45716; &#50696;&#50896;&#54617;&#44368;, &#49436;&#50872;&#50696;&#44256;&#47484; &#51320;&#50629;&#54620; &#54980; &#54620;&#44397;&#50696;&#49696;&#51333;&#54633;&#54617;&#44368;&#50640; &#50689;&#51116; &#51077;&#54617;&#54616;&#50668; &#44608;&#45224;&#50980; &#44368;&#49688;&#51032; &#49324;&#49324; &#50500;&#47000; &#44984;&#51456;&#55176; &#49892;&#47141;&#51012; &#49939;&#50520;&#45796;. &#54620;&#44397;&#50696;&#49696;&#51333;&#54633;&#54617;&#44368; &#51320;&#50629; &#54980; &#48120;&#44397; &#48372;&#49828;&#53556;&#51004;&#47196; &#44148;&#45320;&#44032; &#48120;&#47532;&#50516; &#54532;&#47532;&#46300;&#51032; &#49324;&#49324; &#50500;&#47000; &#45684;&#51081;&#44544;&#47004;&#46300; &#53080;&#49436;&#48148;&#53664;&#47532;&#50640;&#49436; &#47560;&#49828;&#53552; &#44284;&#51221;&#51012; &#49688;&#47308;&#54620; &#54980; &#54788;&#51116; &#46021;&#51068; &#53356;&#47200;&#48288;&#47476;&#53356; &#50500;&#52852;&#45936;&#48120;&#50640;&#49436; &#50500;&#45208; &#52628;&#47560;&#52408;&#53076;&#47484; &#49324;&#49324; &#51473;&#51060;&#45796;. &#51060;&#51648;&#54812;&#45716; &#52264;&#51060;&#53076;&#54532;&#49828;&#53412; &#44397;&#51228; &#53097;&#53216;&#47476; 3&#50948; &#51077;&#49345;, &#49324;&#46972;&#49324;&#53580; &#44397;&#51228; &#53097;&#53216;&#47476; 1&#50948; &#48143; &#52397;&#51473;&#49345; &#49688;&#49345;, &#47112;&#50724;&#54260;&#53944; &#47784;&#52264;&#47476;&#53944; &#53097;&#53216;&#47476; 1&#50948; &#48143; &#52397;&#51473;&#49345;&#44284; &#49892;&#45236;&#50501;&#49345;&#44620;&#51648; &#49688;&#49345;&#54616;&#47728; &#44397;&#45236;&#50808;&#50640;&#49436; &#53360; &#51452;&#47785;&#51012; &#48155;&#50520;&#45796;.</p>			
				
			</div>

			<div id="bio4_text" class="content-big option">
				<p><h2>Cellist</h2>
				<a name="samuel"><h1>Samuel Lutzker</h1></a> 
	&#46021;&#51068; &#52636;&#49373;&#51032; &#52412;&#47532;&#49828;&#53944; &#49324;&#47924;&#50648; &#47336;&#52768;&#52964;&#45716; 2014&#45380;&#48512;&#53552; &#44144;&#51109; &#47560;&#47532;&#49828; &#50560;&#49552;&#49828;&#44032; &#51060;&#45124;&#45716; &#48148;&#51060;&#50640;&#47480; &#48169;&#49569; &#44368;&#54693;&#50501;&#45800;&#51032; &#47716;&#48260;&#47196; &#54876;&#46041;&#54616;&#44256; &#51080;&#51004;&#47728;, &#48288;&#47484;&#47536;&#44284; &#48148;&#51060;&#47560;&#47476;&#50640;&#49436; &#44033;&#44033; &#50700;&#49828; &#54168;&#53552; &#47560;&#51064;&#52768;&#50752; &#48380;&#54532;&#44053; &#50656;&#47560;&#45572;&#50648; &#49800;&#48120;&#53944;&#50752; &#44277;&#48512;&#54616;&#50688;&#45796;. Deutschen Volkes, Villa Musica &#51116;&#45800;&#44284; Werner Richard - Dr. Carl D&#246;rken &#51116;&#45800;&#50640;&#49436; &#54980;&#50896;&#51012; &#48155;&#50520;&#51004;&#47728; &#48372;&#45940;&#51228; &#51020;&#50501; &#53097;&#53216;&#47476;, &#54616;&#52264;&#53804;&#47532;&#50504; &#51020;&#50501; &#53097;&#53216;&#47476;&#50752; Sinfonima-Stiftung &#51020;&#50501; &#53097;&#53216;&#47476; &#46321; &#45796;&#50577;&#54620; &#44397;&#51228;&#51020;&#50501;&#53097;&#53216;&#47476;&#50640;&#49436; &#49849;&#47532;&#54616;&#50688;&#45796;. &#49892;&#45236;&#50501; &#51452;&#51088;&#47196;&#49436; &#46021;&#51068; &#44397;&#45236;&#50808;&#50640;&#49436; &#45796;&#50577;&#54620; &#54876;&#46041;&#51012; &#44216;&#54616;&#44256; &#51080;&#51004;&#47728; Lynn Harrell, Pierre-Laurent Aimard, Atar Arad &#44536;&#47532;&#44256; Nina Tichman &#46321;&#44284; &#54632;&#44760; &#50672;&#51452;&#54616;&#44592;&#46020; &#54616;&#50688;&#45796;.</p>

			</div>

		</div>

		
	</div> <!-- musicians -->
	
		<!-- calendar start -->
	<div id="calendar" class="page silver">
		<div class="content-bg-container"><div class="content-bg" style="background-image:url('img/trieste-big.jpg');"><h1>Konzerte</h1></div></div>

		<div class="content-block" id="calendartypes">
			<a href="javascript:void(0);" onClick="show(this,'upcoming');$('.calendarbox').scrollTop(0).scroll();" class="active">N&auml;chste</a>
			<a href="javascript:void(0);" onClick="show(this,'archive');$('.calendarbox').scrollTop(0).scroll();">Vergangene</a>
		</div>
		<div class="content-block">
			
			<div id="cal_up"></div>
		
			<div id="upcoming" class="calendarbox option active">

			<div style="background:#d9d1be;">
			<?php echo $next_concert; ?>
			</div>

			<?php echo $upcoming; ?>
			</div>

			<div id="archive" class="calendarbox option">
			<?php echo $archive; ?>
			</div>
			
			<div id="cal_down"></div>

		</div>
	</div>
	<!-- calendar stop -->
	
	<div id="media" class="page brass">
		<div class="content-bg-container"><div class="content-bg" style="background-image:url('img/media.jpg');"><h1>Media</h1></div></div>

		<div class="content-block" id="mediatypes">
			<a href="javascript:void(0);" onClick="show(this,'videos');" class="active">Videos</a>
			<a href="javascript:void(0);" onClick="show(this,'audios');" id="audio_button">Audios</a>
			<a href="javascript:void(0);" onClick="show(this,'photos');">Photos</a>
		</div>
		<div class="content-block">
			<ul id="videos" class="mediabox option active">
				<li class="playvideo" title="r43rmTzIf50">
					<div class="bullauge"></div>
					<div>
						<h1>J. Brahms Trio C Major, Op. 87</h1>
						<h2>Kontrapunkt Klavierwerkstatt - March 2017</h2>
					</div>
				</li>
				<li class="playvideo" title="FoNqun-0A-Y">
					<div class="bullauge"></div>
					<div>
						<h1>J. Brahms Trio B Major, Op. 8 revised 1889</h1>
						<h2>Seoul Arts Center - October 2017</h2>
					</div>
				</li>
				<li class="playvideo" title="hRdnqsFGWic">
					<div class="bullauge"></div>
					<div>
						<h1>C. Debussy Trio L. 3</h1>
						<h2>Seoul Arts Center - October 2017</h2>
					</div>
				</li>

				<li class="playvideo" title="AOkpQdfWFxc">
					<div class="bullauge"></div>
					<div>
						<h1>J. Franc&#807;aix Piano Trio (1986)</h1>
						<h2>Seoul Arts Center - October 2017</h2>
					</div>
				</li>
				<li class="playvideo" title="49EOZPjobkA">
					<div class="bullauge"></div>
					<div>
						<h1>Maurice Ravel Trio a Minor</h1>
						<h2>Trieste Competition - September 2017</h2>
					</div>
				</li>
				<li class="playvideo" title="2I4P7Bl2znY">
					<div class="bullauge"></div>
					<div>
						<h1>Beethoven Trio Op. 70,1 D Major &raquo;Ghost&laquo;</h1>
						<h2>Grafenegg Concert Impressions - 05/2017</h2>
					</div>
				</li>

				<li class="playvideo" title="C929IPOPIUU">
					<div class="bullauge"></div>
					<div>
						<h1>Francis Poulenc &raquo;Les Chemins de l'Amour&laquo;</h1>
						<h2>&raquo;The House Concert&laquo; - Seoul 2016 - Live Recording</h2>
					</div>
				</li>
			</ul>	


			<ul id="audios" class="mediabox option">
				<li>
					<div class="bullauge" style="background-image:url('img/brahms.jpg')"></div>
					<div>
						<h1>Brahms Trio C Major Opus 87</h1>
						<div class="footnote">Live Recording - Tage der Kammermusik - HfMT M&uuml;nchen, April 2016</div>
						<h2>
						<ol>
							<li class="playaudio" title="brahms1.mp3">Allegro</li>
							<li class="playaudio" title="brahms2.mp3">Andante con moto</li>
							<li class="playaudio" title="brahms3.mp3">Scherzo, Presto</li>
							<li class="playaudio" title="brahms4.mp3">Finale, Allegro giocoso</li>
						</ol>
						</h2>
					</div>
					
				</li>
				<li>
					<div class="bullauge" style="background-image:url('img/ravel.jpg')"></div>
					<div>
						<h1>Maurice Ravel Trio a Minor</h1>
						<div class="footnote">Live Recording - &raquo;The House Concert&laquo; - Seoul, December 2016</div>
						<h2>
						<ol>
							<li class="playaudio" title="ravel_seoul1.mp3">Mod&eacute;r&eacute;</li>
							<li class="playaudio" title="ravel_seoul2.mp3">Pantoum (Assez vif)</li>
							<li class="playaudio" title="ravel_seoul3.mp3">Passacaille (Tr&egrave;s large)</li>
							<li class="playaudio" title="ravel_seoul4.mp3">Final (Anim&eacute;)</li>
						</ol>
						</h2>
					</div>
					
				</li>
				<li class="playaudio" title="poulenc.mp3">
					<div class="bullauge" style="background-image:url('img/poulenc.jpg')"></div>
					<div>
						<h1>Francis Poulenc</h1><h2>&raquo;Les Chemins de l'Amour&laquo;</h2>
					</div>							
				</li>
				<li class="playaudio" title="francaix.mp3">
					<div class="bullauge" style="background-image:url('img/francaix.jpg')"></div>
					<div>
						<h1>Jean Franc&#807;aix Piano Trio (1986)</h1>
						<div class="footnote">Live Recording - Brussels, October 2016 - Korean Culture Center Brussels</div>
					</div>							
				</li>
			</ul>	

			<div id="photos" class="content-block option">
<p>
<?php
for($i=2; $i < sizeof($diashow); ++$i) {
echo "<div class=\"bullauge\" style=\"background-image:url('$thumbs_dir$diashow[$i]');\" onclick=\"diashow(this,'$dias_dir$diashow[$i]');\"></div>\n";
}
?>
</p>

			</div>

<p class="footnote" style="display:inline-block;">
<br /><br />
<b>Photo-Credits:</b> Photos on this Website by Wulf Schaeffer, Shin-Joong Kim - Copyright &#169; 2016
</p>

			
		</div>

	</div>


	<div id="contact" class="page gold">
		<div class="content-bg-container"><div class="content-bg" style="background-image:url('img/kontakt.jpg');"><h1>Contact</h1></div></div>

		<div class="content-block">
			<div class="content-small">
<!--				<p style="font-weight:bold;">TRIO GAON</p>
				<p>
				Phone:<br />
				+49 176 63366042<br />
				+49 176 83272281<br />
				</p>

				<br />
				<hr /> -->
				<br />

				<a href="javascript:void(0);" onClick="show(this,'contactform');" class="active">Contact</a>
				<a href="javascript:void(0);" onClick="show(this,'impressum');">Disclaimer</a>

			</div>
			<div class="content-big option active" id="contactform">
				<form id="send_mail" action="korean.php#contact" method="post">
					<h2>Write us...</h2>
					<br />
					
					<div class="g-recaptcha" data-sitekey="6LdTyCEUAAAAADyHW2kOHyE1-fczakfDPSJN5Jyj" data-callback="onSubmit" data-size="invisible" data-badge="inline"></div>
					<input type="text" id="CName" name="ContactName" placeholder="Full Name" />
					<input type="text" id="CEmail" name="ContactEmail" placeholder="E-mail"/>
					<input type="checkbox" class="styled-checkbox" name="ContactNewsletter" id="newsletter" value="signup" />
					<label for="newsletter">
					I wish to be informed about upcoming concerts and other Trio news
					</label>
					<textarea rows="10" cols="40" id="CMessage" name="ContactMessage" placeholder="Your message..."></textarea>
					<br />
					<input id="submitform" name="ContactSubmit" type="submit" value="Send" />
				</form>
			</div>

			<div class="content-big option" id="impressum">
				<h1>Disclaimer</h1>Angaben gem&auml;&szlig; &sect; 5 TMG<br/><br/>TRIO GAON<br /><h2>Vertreten durch</h2>Tae-Hyung Kim, Jehye Lee, Samuel Lutzker<br/><br/>Pers&ouml;nlich haftende Gesellschafter<br/><br/>Tae-Hyung Kim, Jehye Lee, Samuel Lutzker<br/><h2>Kontakt</h2>E-Mail: triogaon@gmail.com<br/>Internetadresse: www.triogaon.de<br/><h2>Haftungsausschluss</h2>Haftung f&uuml;r Inhalte<br/>Die Inhalte unserer Seiten wurden mit gr&ouml;&szlig;ter Sorgfalt erstellt. F&uuml;r die Richtigkeit, Vollst&auml;ndigkeit und Aktualit&auml;t der Inhalte k&ouml;nnen wir jedoch keine Gew&auml;hr &uuml;bernehmen. Als Diensteanbieter sind wir gem&auml;&szlig; &sect; 7 Abs.1 TMG f&uuml;r eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach &sect;&sect; 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, &uuml;bermittelte oder gespeicherte fremde Informationen zu &uuml;berwachen oder nach Umst&auml;nden zu forschen, die auf eine rechtswidrige T&auml;tigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unber&uuml;hrt. Eine diesbez&uuml;gliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung m&ouml;glich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.<br/><br/>Haftung f&uuml;r Links<br/>Unser Angebot enth&auml;lt Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb k&ouml;nnen wir f&uuml;r diese fremden Inhalte auch keine Gew&auml;hr &uuml;bernehmen. F&uuml;r die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf m&ouml;gliche Rechtsverst&ouml;&szlig;e &uuml;berpr&uuml;ft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.<br/><br/>Urheberrecht<br/>Die durch die Seitenbetreiber erstellten bzw. verwendeten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielf&auml;ltigung, Bearbeitung, Verbreitung und jede Art der Verwertung au&szlig;erhalb der Grenzen des Urheberrechtes bed&uuml;rfen der Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur f&uuml;r den privaten, nicht kommerziellen Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.<br/><br/><br/><br/>

			</div>
		</div>	<!--content-block -->
	</div>
		


	
	<div id="videoplayer">
		<iframe title="YouTube video player" width="780" height="428" id="videoframe" frameborder="0" allowfullscreen></iframe>
		<img class="icon_close" src="img/close.png" onClick="$('#videoframe').attr('src','about:blank');$('#videoplayer').css('display','none');" />
	</div>
	
	<div id="diashow">
		<img class="icon_close" src="img/close.png" onClick="$('#diashow').css('display','none');" />
		<img class="icon_next" src="img/next.png" onClick="dia_next();" />
		<img class="icon_prev" src="img/prev.png" onClick="dia_prev();" />
		<img id="dia" />
	</div>
	

	
	<div id="audioplayer">
		<img class="closewin" src="img/close.png" onClick="playaudio();"/>

		<div id="cp_container_1" class="cp-container">
		<div class="cp-buffer-holder"> <!-- .cp-gt50 only needed when buffer is > than 50% -->
			<div class="cp-buffer-1"></div>
			<div class="cp-buffer-2"></div>
		</div>
		<div class="cp-progress-holder"> <!-- .cp-gt50 only needed when progress is > than 50% -->
			<div class="cp-progress-1"></div>
			<div class="cp-progress-2"></div>
		</div>
		<div class="cp-circle-control"></div>
		<ul class="cp-controls">
			<li><a class="cp-play" tabindex="1">play</a></li>
			<li><a class="cp-pause" style="display:none;" tabindex="1">pause</a></li> <!-- Needs the inline style here, or jQuery.show() uses display:inline instead of display:block -->
		</ul>
	</div>
		


</body>
</html>