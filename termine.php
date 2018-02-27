
<?php

function errorDie($msg) {
	die($msg);
}

 // ONLINE:
const DB_URL = "localhost";
const DB_USER = "28716m17779_1";
const DB_PASS = "laurenz0";
const DB_DBNAME = "28716m17779_1";
const DB_TABLE = "TrioConcerts";
/*
// OFFLINE
const DB_URL = "localhost";
const DB_USER = "root";
const DB_PASS = "laurenz0";
const DB_DBNAME = "quinteto";
const DB_TABLE = "Concerts";
*/

const DB_RESETPASSWORD = "kimleesam!reset";
const ADMIN_PASSWORD = "kimleesam";



	// Connect to DB
	$mysqli = mysqli_connect(DB_URL, DB_USER, DB_PASS);
	if (mysqli_connect_errno($mysqli)) {
	  errorDie("Failed to connect to MySQL: " . mysqli_connect_error());
	}


$sql=0;

function getNextConcert() {
	global $sql;
	
	if(!$sql)
		return;
	
	$res = $sql->fetch_object();

	return $res;
}


if(isset($_GET["reset"]) && $_GET["reset"] == DB_RESETPASSWORD) {


	$mysqli->select_db(DB_DBNAME);

	if(!$mysqli->query("DROP TABLE IF EXISTS " . DB_TABLE))
		errorDie("RESET-Error: # {$mysqli->errno} Failed to delete database. MSG: {$mysqli->error}");

	if (!$mysqli->query(
	"CREATE TABLE " . DB_TABLE 
		. "(concertdate DATE NOT NULL, "
		. "concerttime TIME, "
		. "location VARCHAR(200), "
		. "venue VARCHAR(200), "
		. "website VARCHAR(200), "
		. "program VARCHAR(200), UNIQUE(concertdate))"))
		errorDie("RESET-Error: # {$mysqli->errno} Failed to create " . DB_TABLE . ". MSG: {$mysqli->error}");

}
else  {
	$mysqli->select_db(DB_DBNAME);


	if(isset($_REQUEST["edit"])) {
		$a = explode(" ",$_REQUEST["edit"]);
		if($a[0] == "delete") {
			if (!$mysqli->query("DELETE FROM " . DB_TABLE . " WHERE concertdate='{$a[1]}'"))
				errorDie("DELETE-{$a[1]}-Error: # {$mysqli->errno} Failed to delete element from " . DB_TABLE . ". MSG: {$mysqli->error}");
		}
		if($a[0] == "change") {
			if (!$mysqli->query("UPDATE " . DB_TABLE 
				. " SET concertdate='{$_GET['concertdate']}', concerttime='{$_GET['concerttime']}', location='{$_GET['location']}', venue='{$_GET['venue']}', website='{$_GET['website']}', program='{$_GET['program']}' "
				. "WHERE concertdate='{$a[1]}'"))
				errorDie("CHANGE-{$a[1]}-Error: # {$mysqli->errno} Failed to change element from " . DB_TABLE . ". MSG: {$mysqli->error}");
		}		
	} else if(isset($_REQUEST["add"])) {
		if (!$mysqli->query(
		"INSERT INTO " . DB_TABLE
			. "(concertdate, concerttime, location, venue, website, program) "
			. "VALUES ('{$_GET['concertdate']}', '{$_GET['concerttime']}', '{$_GET['location']}', '{$_GET['venue']}', '{$_GET['website']}', '{$_GET['program']}')"))
			errorDie("INSERT-Error: # {$mysqli->errno} Failed to insert into " . DB_TABLE . ". MSG: {$mysqli->error}");

	}
	global $sql;
	
	if(!($sql=$mysqli->query("SELECT * FROM " . DB_TABLE . " ORDER BY concertdate")))
		errorDie("QUERY Error: # {$mysqli->errno} Failed to retrieve database. MSG: {$mysqli->error}");

	if(isset($_GET["admin"]) && $_GET["admin"] == ADMIN_PASSWORD) {
		?>
	<!doctype html>
	<html>
	<head>
	<script type="text/javascript">
	<!--
	// very dirtily move the form into the edited row
	var changeRow="";
	
	function changeThis(trTag) {
		if(trTag.id == "editfield")
			return;
			
		var plHolder,i,tdTags = trTag.getElementsByTagName("td");
		
		trHtml = trTag.innerHTML;
		
		for(i=0; i < tdTags.length-1; ++i) {
			plHolder = tdTags[i].innerHTML;
			tdTags[i].replaceChild(document.getElementById("editfield").getElementsByTagName("td")[i].firstElementChild,tdTags[i].firstChild);
			tdTags[i].firstElementChild.placeholder = tdTags[i].firstElementChild.value = plHolder;
		}

		document.getElementById("editfield").getElementsByTagName("td")[tdTags.length-1].innerHTML = "";
		trTag.getElementsByTagName("td")[tdTags.length-1].firstElementChild.value = trTag.getElementsByTagName("td")[tdTags.length-1].firstElementChild.value.replace("delete","change");		
		trTag.getElementsByTagName("td")[tdTags.length-1].firstElementChild.onclick = "";

		document.getElementById("editfield").innerHTML = changeRow;
		changeRow = trHtml;
		
		document.getElementById("editfield").id="";
		trTag.id = "editfield";
	}
	
	//-->
	</script>
	</head>
	<body>
	<form action="termine.php" method="GET">
	<table>
		<?php
		echo "<input type='hidden' name='admin' value='".ADMIN_PASSWORD."' />"; // Get us back to the admin page

		while($concert = $sql->fetch_assoc()) {	
			echo "<tr onclick=\"changeThis(this)\"><td>{$concert['concertdate']}</td>";
			echo "<td>{$concert['concerttime']}</td>";
			echo "<td>{$concert['location']}</td>";
			echo "<td>{$concert['venue']}</td>";
			echo "<td>{$concert['website']}</td>";
			echo "<td>{$concert['program']}</td>";
			echo "<td><input type='submit' name='edit' onclick='parentNode.id=\"editfield\"; website.value=\"http://someurl.com/\"' value='delete {$concert['concertdate']}' /></td></tr>";
		}
	
		?>
	<tr id="editfield">
		<td><input type="date" name="concertdate" /></td>
		<td><input type="time" name="concerttime" /></td>
		<td><input type="text" name="location" placeholder="Stadt/Land" /></td>
		<td><input type="text" name="venue" placeholder="Veranstaltungsort" /></td>
		<td><input type="url" name="website" value="http://" /></td>
		<td><input type="text" name="program" placeholder="Konzertprogramm" /></td>
		<td><input type="submit" name="add" /></td>
	</tr>
	</table>
	</form>
	</body>
	</html>

		<?php

	} 
}





?>