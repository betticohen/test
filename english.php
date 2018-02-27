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
	
		$from = "TRIO GAON website (ENGLISH)";
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
	
	<title>TRIO GAON</title>

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
			<a href="http://www.triogaon.com/korean.php" style="background-image:url('img/kor.png');"></a>
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
<h2>&raquo;The three didn't simply perform this work, but were irrepressible ambassadors conveying the joy of music itself&laquo; (Allg&auml;uer Zeitung)</h2>
<p>The TRIO GAON, formed in 2013, was created with the idea of bringing
together three musicians with very different cultural and professional
backgrounds who share a common vision.</p>
<p>This vision is metaphorically represented in the name of the TRIO. The
Korean word GAON ( <span style="color:rgb(39,39,123);text-shadow:2px 2px 10px darkgoldenrod;font-weight:bold;">&#xac00; &#xc628;</span> ) has two different meanings: &raquo;Center of the
World&laquo; and &raquo;Generating Warmth&laquo;. Both of these meanings can be
connected to a concert performance in which the musicians aspire to
make the musical work the 'center of the world' at that moment and
through the 'vessel' of music to convey and generate warmth and
energy.</p>

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
<h2>&raquo;Along with the extraordinary soloistic capabilities of the three
players, one was also astounded by their highly refined sense for
transparency and balance of sound, as well as by the presence of that
very spirit of what playing chamber music together can mean&laquo; (Sueddeutsche Zeitung)</h2>
						
<p>The Munich-based
TRIO GAON was
formed in 2013.
Together the three
musicians completed
their chamber music
degrees at the
Conservatory of
Munich, and were taught by Ana Chumachenco, Friedemann Berger and
Christoph Poppen. As participants of the European Chamber Music
Academy (ECMA) they are continuing to refine their chamber music
education.</p>


<p>The young ensemble has won prizes at influential
competitions. Most recently it was awarded the first prize with two
special prizes at the renowned international chamber music competition
in Trieste, Italy <i>Premio Trio di Trieste</i>. It has also won the second prize
at the international <i>Joseph Haydn Chamber Music Competition</i> in
Vienna and the Music Prize of <i>Kulturkreis Gasteig e.V.</i> where they
achieved the highest ranking of the entire competition.</p>

<p>The TRIO GAON has performed regularly in Germany, Belgium, Austria
and Korea, presenting a highly diverse repertoire in prestigious concert
halls such as the Munich Gasteig Philharmonic Hall and the Kumho Art
Hall in Seoul.</p>
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
is viewed as one of
the most promising pianists of his
generation. He received his early
musical education in Korea. At a
young age he moved to Munich to
study with the acclaimed pianist and
teacher Elisso Wirssaladze, with
whom he continued to refine and
complete his studies and musical
development at the Moscow
Conservatory of Music, after his
graduation in Munich.</p>
<p>He is a laureate of many international competitions, such as the
prestigious Queen Elizabeth Competiton, the Long-Thibaud competition
and the Concours Grand Prix Animato. He has been regularly invited to
appear as a soloist by many orchestras including the Royal Philharmonic
Orchestra and the Russian National Philharmonic Orchestra and
performs throughout Europe and Asia.</p>			
			</div>

			<div id="bio3_text" class="content-big option">
				<p><h2>Violinist</h2>
				<a name="jehye"><h1>Jehye Lee</h1></a> 
<p>has been the concertmaster of the second violins in the Bavarian Radio Symphony Orchestra since 2015. Previously she had been holding the position of 1. concertmaster of the Augsburg Philharmonic Orchestra.</p>

<p>At the age of seven, she started playing the violin as a student of Professor Nam Yun Kim at the Korean National University of Arts. Subsequently, she continued her studies with Professor Miriam Fried at the New England Conservatory in Boston, USA, where she graduated in 2009. In 2010 Jehye Lee took part in Chamber Music Connects the World. From 2010 to 2012 she studied as a Young Soloist at Kronberg Academy with Ana Chumachenco. These studies were funded by the Gingko Stipendium.</p>

<p>Jehye Lee has been honoured with numerous awards in various international competitions. She won third prize at the International Violin Competition Sion Valais in Switzerland (2002) and at the Yehudi Menuhin International Violin Competition in the United Kingdom (2004). In 2005 she was awarded the second prize at the Prague Spring International Competition. The same year she won first prize, along with the audience and Sarasate prizes at the Pablo Sarasate International Violin Competition in Spain. In May 2009 she won first prize as well as the audience and chamber music awards at the Leopold Mozart Competition in Augsburg, Germany. In 2011 she was awarded the third prize at the International Tchaikovsky Competition and also won a price for the best chamber concerto performance.</p>

<p>As a solo performer, Jehye Lee has appeared with the North Czech Philharmonic Teplice, the Vienna Mozart Orchestra, the Bilbao Symphony, the Bavarian Radio Chamber Orchestra and the Munich Radio Orchestra. She has also performed in major venues as the Seoul Arts Center, Jordan Hall in Boston, Dvorák Hall in Prague, Victoria Hall in Geneva, or the Grand Théatre de Bordeaux. As a chamber musician, Jehye Lee is a regular guest at numerous festivals. She has worked and performed with musicians such as Menahem Pressler, Laurence Lesser, Frans Helmerson, gidon Kremer, Tatjana Grindenko und Miriam Fried.</p>


<p>Jehye Lee plays a Nicolo Bergonzi violin (1760).</p>
				
			</div>

			<div id="bio4_text" class="content-big option">
				<p><h2>Cellist</h2>
				<a name="samuel"><h1>Samuel Lutzker</h1></a> 
<p>has been a
member of the Bavarian
Radio Symphony Orchestra
in Munich since 2014. After
early musical studies with
Claus Reichardt, he then
studied in Berlin with Jens Peter Maintz and Wolfgang Emanuel Schmidt.</p>
<p>He has received scholarships from the German National Scholarship
Program, the Villa Musica Foundation, the Werner Richard – Dr. Carl
D&ouml;rken Foundation, and been awarded prizes at national and
international competitions such as the Chachaturian Competition, the
Bodensee Competition, and the competition of the Sinfonima
Foundation.</p>
<p>He has performed as a soloist in different concert halls of Germany and other European cities.</p>
<p>As a dedicated chamber musician he has performed at various Festivals
throughout Europe and collaborated with distinguished artists, such as
Lynn Harrell, Pierre-Laurent Aimard, Atar Arad and Nina Tichman. 
As the cellist of the Tango Argentino ensemble &raquo;Quinteto Angel&laquo; he has been playing concerts in authentic Argentinean Tango style in many countries.</p>
<p>He plays a Cello by Ragnar Hayn in Berlin</p>

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
				<form id="send_mail" action="english.php#contact" method="post">
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