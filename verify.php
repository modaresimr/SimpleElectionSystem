<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = $_ENV["SQL_HOST"];
$username = $_ENV["SQL_USERNAME"];
$password = $_ENV["SQL_PASSWORD"];
$dbname = $_ENV["SQL_DB"];

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);
function myDie($txt){
	global $mysqli;
	$mysqli->close();
	die($txt);
	return true;
}
// Check connection
if ($mysqli->connect_error) return myDie("Error: Connection failed: " . $mysqli->connect_error);


if (empty ($_GET["Secret"])) return myDie("Error: Secret should note be empty ");
$secret=$mysqli->real_escape_string($_GET["Secret"]);
	
$result = $mysqli->query("SELECT * FROM Voters where Secret='".$secret."'");
if (!$result || $result->num_rows == 0) return myDie("Error: Your secret token is invalid");
$voteid=$result->fetch_assoc()['ID'];

$result = $mysqli->query("SELECT * FROM Voters as v,VoteDetails as d,Candidates as c where v.ID=d.VoteID and c.ID=d.candidateID and v.Secret='".$secret."' order by d.Preference" );
if (!$result||$result->num_rows == 0) return myDie("Error: No Vote registered.");



?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<link rel="icon" type="image/png" href="st/og-image.png">
	<title>RoboCup Election</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="assets/theme.css">

	<meta charset="utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	
</head>
<body>

	
		<hr />
		<div id="list" class="row">
			<h4 class="col-12">Your preferences</h4>
			<div id="election-list-fix" class="list-group col">
			<?php

			  // output data of each row
			  while($row = $result->fetch_assoc()) {
				  var_dump($row);
				echo '<div class="list-group-item" candidateId="'.$row["ID"].'" ><i class="fas fa-arrows-alt handle"></i> '.$row["Name"].'</div>';	
			  }

			?>
			</div>
		</div>
		<hr />
		
	</div>
	

	<script src="assets/app.js?v1"></script>
</body>
</html>
