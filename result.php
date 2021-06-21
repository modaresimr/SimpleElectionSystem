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


// $result = $mysqli->query("SELECT * FROM Configs where name='is_open'");
// if (!$result || $result->num_rows == 0) return myDie("Error: No config");
// if ($result['Value']!=='0') return myDie("Error: poll is still open");

$result = $mysqli->query("SELECT count(*) as count FROM Voters");
if (!$result || $result->num_rows == 0) return myDie("Error: No Voters");
$voter_counts=$result->fetch_assoc()['count'];

$result = $mysqli->query("SELECT count(*) as count FROM Voters where Done=1");
if (!$result || $result->num_rows == 0) return myDie("Error: No Voters");
$real_voter_counts=$result->fetch_assoc()['count'];

$result = $mysqli->query("SELECT count(*) as count FROM Votes");
if (!$result || $result->num_rows == 0) return myDie("Error: No Votes");
$vote_counts=$result->fetch_assoc()['count'];


$result = $mysqli->query("SELECT d.Preference, c.Name,count(*) as count FROM Votes as v,VoteDetails as d,Candidates as c where v.ID=d.VoteID and c.ID=d.candidateID group by d.Preference, c.Name ORDER BY d.preference ASC,count(*) DESC" );
if (!$result||$result->num_rows == 0) return myDie("Error: in aggregating votes.");



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
		<div id="election-list-fix" class="list-group col">
		<?php
			$choice=0;
			while($row = $result->fetch_assoc()) {
				if($choice!==$row['Preference']){
					$choice=$row['Preference'];
					echo '<h4 class="col-12">Choice '.$choice . '</h4><hr/>';
				}
			
							  
				echo '<div class="row"><div class="list-group-item col-9" >'.$row["Name"]. '</div><div class="list-group-item col-3">'.$row['count'].'</div></div>';	
			  }

			?>
			</div>
		</div>
		
		
	</div>
	

	<script src="assets/app.js?v1"></script>
</body>
</html>
