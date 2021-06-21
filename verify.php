<?php
include_once('common.php');

if (empty ($_GET["secret"])) return myDie("Error: Secret should note be empty ",'danger');
$secret=$mysqli->real_escape_string($_GET["secret"]);
	
$result = $mysqli->query("SELECT * FROM Votes where Secret='".$secret."'");
if (!$result || $result->num_rows == 0) return myDie("Error: Your secret token is invalid",'danger');
$voteid=$result->fetch_assoc()['ID'];

$result = $mysqli->query("SELECT * FROM Votes as v,VoteDetails as d,Candidates as c where v.ID=d.VoteID and c.ID=d.candidateID and v.Secret='".$secret."' order by d.Preference" );
if (!$result||$result->num_rows == 0) return myDie("Error: No Vote registered.",'danger');



?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<link rel="icon" type="image/png" href="st/og-image.png">
	<title>RoboCup Election</title>
	<link rel="stylesheet" href="assets/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
	<link rel="stylesheet" href="assets/font-awesome-all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
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
				  
				echo '<div class="list-group-item" >'.$row["Name"].'</div>';	
			  }

			?>
			</div>
		</div>
		<hr />
		
	</div>
	

	<script src="assets/app.js?v1.1"></script>
</body>
</html>
