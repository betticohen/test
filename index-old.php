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
	
		$from = "TRIO GAON website (GERMAN)";
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
	<meta name="description" content="Homepage des in M&uuml;nchen beheimateten Deutsch-Koreanischen Klaviertrios TRIO GAON">
	<meta name="robots" content="index,follow,noarchive">
	<meta name="keywords" content="Trio Gaon, Gaon, Klaviertrio, Bayerischer Rundfunk, Samuel Lutzker, Tae-Hyung Kim, Jehye Lee" />


	<link rel="stylesheet" href="menu.css?v=<?php echo filemtime('menu.css'); ?>">
	<link rel="stylesheet" href="layout.css?v=<?php echo filemtime('layout.css'); ?>">
	<link rel="stylesheet" href="colors.css?v=<?php echo filemtime('colors.css'); ?>">
	<link rel="stylesheet" href="responsive.css?v=<?php echo filemtime('responsive.css'); ?>">
	
	<title>TRIO GAON</title>

	<link rel="stylesheet" href="cp/css/not.the.skin.css">
	<link rel="stylesheet" href="cp/circle.skin/circle.player.css">
	
	<script type="text/javascript" src='https://www.google.com/recaptcha/api.js?hl=de'  async defer></script>
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
		echo "alert('Nachricht wurde gesendet. Vielen Dank.');";
	else {
		if(isset($mailtimer))
			echo "alert('Bitte probieren Sie es in $mailtimer Sekunden nochmal. Vielen Dank.');";
		else
			echo "alert('Fehler beim senden der Nachricht... CAPTCHA nicht angeklickt?');";
	}
}
?>

  function onSubmit(token) {
    document.getElementById("send_mail").submit();
  }

  function validate(event) {
    event.preventDefault();
    if (!document.getElementById('CName').value || !document.getElementById('CEmail').value || !document.getElementById('CMessage').value) {
      alert("Bitte füllen Sie das Formular aus");
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



	<input type="checkbox" id="menuopen" /><label for="menuopen" onclick></label> 

	<nav role="off-canvas" id="menunav">
		<ul id="menu" class="menu-std">
			<li id="homelogo"><a id="logo" href="#top">Trio Gaon</a></li>
			

			<li><a href="#musicians" title="Die Musiker des Trios">Vita</a></li>

			<li><a href="#media" title="Aufnahmen">Medien</a></li>
		
			<li><a href="#contact" title="Schreiben Sie uns!">Kontakt</a></li>
			
			<li id="socialmedia">
				<a href="http://www.triogaon.com/english.php" style="background-image:url('img/eng.png');" id="language1"></a>
				<a href="http://www.triogaon.com/korean.php" style="background-image:url('img/kor.png');" id="language2"></a>

<!--				<a href="javascript:void(0);" onclick="playaudio('audio/poulenc.mp3'); document.getElementById('audio_button').click();" style="background-image:url('img/listen.png');"></a> -->
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
					<h2>&raquo;Die Drei waren nicht nur Vermittler sondern selbst Botschafter unb&auml;ndiger Freude an der Musik&laquo; (Allg&auml;uer Zeitung)</h2>

					<p>Vom Anfang an gr&uuml;ndete sich das Trio GAON mit der Idee, drei Musiker mit v&ouml;llig verschiedenen kulturellen Hintergr&uuml;nden und Pers&ouml;nlichkeiten zusammen zu bringen, um daraus eine harmonische Einheit mit einer gemeinsamen Vision zu formen.</p>
					<p>Der Name, des Trios, das koreanische Wort &bdquo;Gaon&ldquo; ( <span style="color:rgb(39,39,123);text-shadow:2px 2px 10px darkgoldenrod;font-weight:bold;">&#xac00; &#xc628;</span> ) veranschaulicht diese Vision und hat zwei Bedeutungen: &bdquo;Mittelpunkt der Welt&ldquo;; es kann aber auch bedeuten: &bdquo;W&auml;rme erschaffen&ldquo;. Die Musiker m&ouml;chten diese beiden Aspekte auf die Situation im Konzert beziehen, in der sie in der Musik den Mittelpunkt der Welt in diesem Augenblick finden und durch die Energie der Musik zwischenmenschliche W&auml;rme und Empathie vermitteln. </p>

				</div>
		</div>

<!--		<div id="concertbubble" onClick="document.getElementById('mediatypes').scrollIntoView(); playvideo('C929IPOPIUU');">
			<div style="letter-spacing:2px;">Francis Poulenc</div>
			<div class="bullauge" style="background-image:url('img/poulenc.jpg');"></div>
			<div style="font-size:0.6em">&raquo;Les Chemins de l'Amour&laquo;</div>
		</div> -->

		<div id="concertbubble" onClick="window.open('http://www.acmtrioditrieste.it/');">
			<div style="letter-spacing:2px;">1st Prize</div>
			<div class="bullauge" style="background-image:url('img/trieste.jpg');background-position:30%;"></div>
			<div style="font-size:0.6em">and two special prizes at &raquo;Premio Trio di Trieste&laquo;</div>
		</div>

	</div>

	<div id="musicians" class="page red">
		<div class="content-bg-container"><div class="content-bg" style="background-image:url('img/trio-sw.jpg');"><h1>Biographie</h1></div></div>

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
					<h2>&raquo;Das Publikum erlebte einen Abend, wie es ihn in den f&uuml;hrenden Konzerts&auml;len der Welt erleben kann&laquo; (Donaukurier)</h2>

					<p>Das Trio GAON ist seit 2013 in M&uuml;nchen beheimatet und studierte dort an der Hochschule f&uuml;r Musik und Theater bei den Professoren Ana Chumachenco, Friedemann Berger und Christoph Poppen.</p>
					<p>W&auml;hrend seiner relativ kurzen Bestehenszeit konnte das Trio bereits durch Erfolge bei renommierten Wettbewerben auf sich aufmerksam machen: Zuletzt wurde es im September 2017 mit dem ersten Preis sowie zwei Sonderpreisen in Trieste beim traditionsreichen internationalen Kammermusikwettbewerb <i>Premio Trio di Trieste</i> ausgezeichnet. Im M&auml;rz 2015 gewann es den zweiten Preis beim <i>Joseph-Haydn-Wettbewerb für Kammermusik</i> in Wien und im April 2014 gewann es den Wettbewerb um den <i>Musikpreis des Kulturkreis‘ Gasteig e.V.</i> in der Kategorie Kammermusik mit dem ersten Preis und der höchsten Punktzahl des gesamten Wettbewerbes.</p>
					<p>Das Trio GAON kann auf eine rege Konzertt&auml;tigkeit in Deutschland, Belgien, &Ouml;sterreich und Korea zur&uuml;ckblicken, in der es ein vielf&auml;ltiges Repertoire zu Geh&ouml;r brachte und trat in Hallen wie der Philharmonie im Gasteig, M&uuml;nchen und der Kumho Art Hall, Seoul, auf.</p>
					<p>Der bekannte Musikkritiker und Musikwissenschaftler Harald Eggebrecht &auml;u&szlig;erte sich k&uuml;rzlich in der S&uuml;ddeutschen Zeitung &uuml;ber das junge Ensemble: <i>&bdquo;Das Trio GAON steigerte mit Witz, Ironie und bestechender Virtuosit&auml;t in Franc&#807;aix' Trio von 1986 das Vergn&uuml;gen ungemein. Hier gab es, bei bemerkenswerten solistischen Vorz&uuml;gen der drei, kammermusikalischen Geist und einen ausgereiften Sinn f&uuml;r Transparenz und Klangbalance zu bestaunen.&ldquo;</i></p>
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
				<p><h2>Der Pianist</h2>
				<a name="taehyung"><h1>Tae-Hyung Kim</h1></a> 
ist heute einer der bekanntesten koreanischen Pianisten der neuen Generation. Er wurde international bekannt, als er 2004 als erster koreanischer Pianist den 1. Preis und den Beethoven-Spezial-Preis beim Internationalen Klavierwettbewerb in Porto gewann. Im selben Jahr erhielt er den 2. Preis beim Internationalen Klavierwettbewerb Jeunesses Musicales und wurde bei einer Reihe von renommierten Wettbewerben, u. a. dem Internationalen Klavierwettbewerb Hamamatsu (2006) als auch dem Internationalen Klavierwettbewerb Long-Thibaud (2007) ausgezeichnet. 
Im Jahre 2013 gewann er den 1. Preis und den Publikumspreis beim Internationalen Klavierwettbewerb Hastings, wodurch ihm der internationale Durchbruch in seiner Pianistenkarriere gelang. Zus&auml;tzlich zu seinen bisherigen Wettbewerbsgewinnen erreichte er im Jahr 2010 den 5. Platz beim K&ouml;nigin-Elisabeth-Musikwettbewerb, welcher zu einem der drei gr&ouml;&szlig;ten Klavierwettbewerbe der Welt z&auml;hlt.
				</p><p>
Seitdem weitet Tae-Hyung Kim seine musikalischen Aktivit&auml;ten aus, indem er mit bedeutenden internationalen Orchestern wie dem Royal Philharmonic Orchestra, dem Russia National Philharmonic, dem Tokyo Symphony Orchestra, der Kioi Sinfonietta Tokyo, dem National Orchestra of Belgium, dem Orchestre National de France und unter anderem mit den renommierten Dirigenten Vladimir Spivakov, Marin Alsop, Emil Tabakov und Vakhtang Matchavariani spielte. 
				</p><p>
Nach seinem Studiumsabschluss an der Korean National University of Arts kam er nach Deutschland, wo er an der Hochschule f&uuml;r Musik und Theater M&uuml;nchen die Meisterklasse f&uuml;r Klavier bei Prof. Elisso Wirssaladze und die Meisterklasse f&uuml;r Liedgestaltung bei Prof. Helmut Deutsch absolvierte. Er f&uuml;hrte danach seine Ausbildung am Staatlichen Tschaikowski-Konservatorium in Moskau bei Prof. Elisso Wirssaladze fort und vertiefte dort sein Verst&auml;ndnis und seine Sensibilit&auml;t f&uuml;r Musik weiter. Derzeit h&auml;lt er sich in M&uuml;nchen auf und absolviert sein Studium der Kammermusik mit Christoph Poppen und Friedemann Berger an der Hochschule f&uuml;r Musik und Theater M&uuml;nchen. 
				</p><p>
Tae-Hyung Kim wird dankend seit 2008 von der DAEWON Cultural Foundation gef&ouml;rdert. Er wird generell von Presto Artists and Entertainment vertreten, in Frankreich und den Benelux-Staaten dabei mit Weinstadt Artists Management und in Russland/GUS in Zusammenarbeit mit SMOLART Concert Agency.
				</p>

			</div>

			<div id="bio3_text" class="content-big option">
				<p>
				<h2>Die Geigerin</h2>
				<a name="jehye"><h1>Jehye Lee</h1></a>
ist seit 2015 Konzertmeisterin der zweiten Violinen im Symphonieorchester des Bayerischen Rundfunks unter Chefdirigent Mariss Jansons. Zuvor war sie seit 2013 als 1. Konzertmeisterin bei den Augsburger Philharmonikern fest angestellt.
Geboren 1986 in Seoul/S&uuml;dkorea, begann sie mit sieben Jahren ihr Geigenstudium bei Prof. Nam Yun Kim an der Korean National University of Arts.
Im Anschluss setzte sie ihr Studium bei Prof. Miriam Fried am New England Conservatory in Boston/USA fort, wo sie 2009 ihren Masterabschluss erhielt.
Auf Einladung der Kronberg Academy studierte sie seit Oktober 2010 als &raquo;Young Soloist&laquo; bei Prof. Ana Chumachenco in Deutschland. Das Studium wurde durch das Gingko-Stipendium erm&ouml;glicht.
				</p><p>
Jehye Lee ist Preistr&auml;gerin zahlreicher internationaler Wettbewerbe.
Sie gewann unter anderem jeweils den 3. Platz beim Internationalen Violinwettbewerb Sion Valais in der Schweiz (2002) und beim Yehudi-Menuhin-Wettbewerb in England (2004).
2005 gewann sie den 2. Preis beim Wettbewerb des Prager Fr&uuml;hlings.
Im gleichen Jahr wurde sie mit dem 1. Preis, dem Publikums- und dem Sarasate-Preis beim Internationalen Pablo Sarasate-Violinwettbewerb in Spanien ausgezeichnet.
2009 gewann Jehye Lee den 1. Preis beim internationalen Violinwettbewerb Leopold Mozart in Augsburg und wurde zus&auml;tzlich mit dem Publikums- und Kammermusik-Sonderpreis geehrt.
Dar&uuml;ber hinaus gewann sie 2011 den 3. Preis beim Internationalen Tchaikovsky-Wettbewerb in St. Petersburg und erhielt au&szlig;erdem den Sonderpreis f&uuml;r die beste Interpretation eines Mozart Konzertes.
Jehye Lee hat damit erstmals in der Geschichte Koreas, die h&ouml;chste Plazierung bei diesem prestigevollem Wettbewerb errungen.
				</p><p>
Als Solistin trat Jehye Lee mit der Nordb&ouml;hmischen Philharmonie Teplice, dem Wiener Mozartorchester, dem Bilbao Sinfonieorchester, dem Kammerorchester des Bayerischen Rundfunks und dem M&uuml;nchener Rundfunkorchester auf.
Sie konzertierte in S&auml;len wie dem Seoul Arts Center, der Jordan Hall in Boston, der Dvorak Hall in Prag, der Victoria Hall in Genf, dem Prinzregententheater in M&uuml;nchen oder dem Grand Theater in Bordeaux.
				</p><p>
Als Kammermusikerin wird Jehye Lee regelm&auml;&szlig;ig zu Festivals eingeladen.
Sie arbeitete mit K&uuml;nstlern wie András Schiff, Yuri Bashmet, Gary Hoffman, Menahem Pressler, Misha Maisky, Frans Helmerson, Gidon Kremer, Tatjana Grindenko und Miriam Fried zusammen.				

				</p>
			</div>

			<div id="bio4_text" class="content-big option">
				<h2 style="text-align:right">&raquo;Sein ausdrucksvolles Spiel, seine
seelenvolle Interpretation, sein warmer Strich und sein zart atmendes, geistvolles und
nuancenreiches Rubato zeugten von einer Verinnerlichung jedes Tons&laquo; (Reutlinger Generalanzeiger)</h2><br />
				<p>
				<h2>Der Cellist</h2>
				<a name="samuel"><h1>Samuel Lutzker</h1></a> ist seit Fr&uuml;hjahr 2014 Mitglied im Symphonieorchester des Bayerischen Rundfunks unter Chefdirigent Mariss Jansons. Nach seinem Jungstudium in D&uuml;sseldorf bei Claus Reichardt studierte er in Berlin und Weimar bei Jens Peter Maintz und Wolfgang Emanuel Schmidt. Au&szlig;erdem erhielt er wichtige Impulse auf Meisterkursen unter Anderen mit Heinrich Schiff, David Geringas und Frans Helmerson.</p>
<p>Er ist Stipendiat der Studienstiftung des Deutschen Volkes, der Villa Musica-Stiftung und der Werner Richard - Dr. Carl D&ouml;rken Stiftung, sowie Preistr&auml;ger verschiedener nationaler und internationaler Wettbewerbe wie dem Bodensee-Musikwettbewerb, dem Khachaturian-Wettbewerb und dem Wettbewerb der Sinfonima-Stiftung.</p>
<p>Neben vielf&auml;ltigen Rezitalen und solistischen Auftritten bildet die Kammermusik einen Schwerpunkt seiner T&auml;tigkeit. In verschiedenen Ensembles hat er in Europa und Asien an Konzerten, CD-Aufnahmen und Rundfunkproduktionen mitgewirkt. Zu seinen Kammermusikpartnern z&auml;hlten unter Anderen Pierre-Laurent Aimard, Atar Arad, Lynn Harrell und Nina Tichman. Regelm&auml;&szlig;ige Einladungen  zum Kammermusikfestival des International Musicians Seminar Prussia Cove in Cornwall, England waren pr&auml;gende Inspirationsquellen.</p>
				
			</div>

		</div>

		
	</div> <!-- musicians -->
	
	
	<div id="media" class="page silver">
		<div class="content-bg-container"><div class="content-bg" style="background-image:url('img/media.jpg');"><h1>Medien</h1></div></div>

		<div class="content-block" id="mediatypes">
			<a href="#mediatypes" onClick="show(this,'videos');" class="active">Videos</a>
			<a href="#mediatypes" onClick="show(this,'audios');" id="audio_button">Audios</a>
			<a href="#mediatypes" onClick="show(this,'photos');">Photos</a>
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
				<li class="playvideo" title="LO07SirEpV8">
					<div class="bullauge"></div>
					<div>
						<h1>J. Franc&#807;aix Piano Trio (1986)</h1>
						<h2>Trieste Competition - September 2017</h2>
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
				
				<li class="playvideo" title="1Gahx-VcFdE">
					<div class="bullauge"></div>
					<div>
						<h1>Trio GAON Democlip</h1>
						<h2>Ausschnitte aus Mendelssohn und Brahms</h2>
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
		<div class="content-bg-container"><div class="content-bg" style="background-image:url('img/kontakt.jpg');"><h1>Kontakt</h1></div></div>

		<div class="content-block">
			<div class="content-small">
<!--				<p style="font-weight:bold;">TRIO GAON</p>
				<p>
				Telefon:<br />
				+49 176 63366042<br />
				+49 176 83272281<br />
				</p>
				<p>Kontaktadresse auf Anfrage</p>

				<br />
				<hr /> -->
				<br />

				<a href="javascript:void(0);" onClick="show(this,'contactform');" class="active">Kontakt</a>
				<a href="javascript:void(0);" onClick="show(this,'impressum');">Impressum</a>


			</div>
			<div class="content-big option active" id="contactform">
				<form id="send_mail" action="index.php#contact" method="post">
					<h2>Schreiben Sie uns...</h2>
					<br />
					
					<div class="g-recaptcha" data-sitekey="6LdTyCEUAAAAADyHW2kOHyE1-fczakfDPSJN5Jyj" data-callback="onSubmit" data-size="invisible" data-badge="inline"></div>
					<input type="text" id="CName" name="ContactName" placeholder="Vor- und Zuname..." />
					<input type="text" id="CEmail" name="ContactEmail" placeholder="Emailadresse..."/>
					<input type="checkbox" class="styled-checkbox" name="ContactNewsletter" id="newsletter" value="signup" />
					<label for="newsletter">
					ich m&ouml;chte vom Trio &uuml;ber Konzerte und andere Neuigkeiten informiert werden
					</label>
					<textarea rows="10" cols="40" id="CMessage" name="ContactMessage" placeholder="Ihre Nachricht..."></textarea>
					<br />
					<input id="submitform" name="ContactSubmit" type="submit" value="Absenden" />
				</form>
			</div>

			<div class="content-big option" id="impressum">
				<h1>Impressum</h1>Angaben gem&auml;&szlig; &sect; 5 TMG<br/><br/>TRIO GAON<br /><h2>Vertreten durch</h2>Tae-Hyung Kim, Jehye Lee, Samuel Lutzker<br/><br/>Pers&ouml;nlich haftende Gesellschafter<br/><br/>Tae-Hyung Kim, Jehye Lee, Samuel Lutzker<br/><h2>Kontakt</h2>E-Mail: triogaon@gmail.com<br/>Internetadresse: www.triogaon.de<br/><h2>Haftungsausschluss</h2>Haftung f&uuml;r Inhalte<br/>Die Inhalte unserer Seiten wurden mit gr&ouml;&szlig;ter Sorgfalt erstellt. F&uuml;r die Richtigkeit, Vollst&auml;ndigkeit und Aktualit&auml;t der Inhalte k&ouml;nnen wir jedoch keine Gew&auml;hr &uuml;bernehmen. Als Diensteanbieter sind wir gem&auml;&szlig; &sect; 7 Abs.1 TMG f&uuml;r eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach &sect;&sect; 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, &uuml;bermittelte oder gespeicherte fremde Informationen zu &uuml;berwachen oder nach Umst&auml;nden zu forschen, die auf eine rechtswidrige T&auml;tigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unber&uuml;hrt. Eine diesbez&uuml;gliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung m&ouml;glich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.<br/><br/>Haftung f&uuml;r Links<br/>Unser Angebot enth&auml;lt Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb k&ouml;nnen wir f&uuml;r diese fremden Inhalte auch keine Gew&auml;hr &uuml;bernehmen. F&uuml;r die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf m&ouml;gliche Rechtsverst&ouml;&szlig;e &uuml;berpr&uuml;ft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.<br/><br/>Urheberrecht<br/>Die durch die Seitenbetreiber erstellten bzw. verwendeten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielf&auml;ltigung, Bearbeitung, Verbreitung und jede Art der Verwertung au&szlig;erhalb der Grenzen des Urheberrechtes bed&uuml;rfen der Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur f&uuml;r den privaten, nicht kommerziellen Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.<br/><br/><br/><br/>

			</div>
		</div>	<!--content-block -->
	</div>
		


	
	<div id="videoplayer">
		<iframe title="YouTube video player" width="780" height="428" id="videoframe" frameborder="0"></iframe>
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