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
function lockVoter($id){
	global $memcache;
	$memcache = new Memcached;
	$memcache->addServer('localhost', 11211);
	try{
		$memcache->add("voter:" . $id, "1",30);//thread safe
		return true;
	}catch(Exception $e){
		return false;
	}
}
function unlockVoter($id){
	global $memcache;
	$memcache->delete("voter:" . $id);
}
function generateRandomString($length = 20) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
// Check connection
if ($mysqli->connect_error) return myDie("Error: Connection failed: " . $mysqli->connect_error);


if (empty ($_GET["Key"])) return myDie("Error: Key should note be empty ");
$voterKey=$mysqli->real_escape_string($_GET["Key"]);
	
$result = $mysqli->query("SELECT * FROM Voters where VoterKey='".$voterKey."'");
if (!$result || $result->num_rows == 0) return myDie("Error: Your vote token is invalid");


$result = $mysqli->query("SELECT * FROM Voters where Done=0 and VoterKey='". $voterKey ."'" );
if (!$result||$result->num_rows == 0) return myDie("Error: You have voted.");



if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$votes = array_map(function($v){ return (int) trim($v, "'"); }, explode(",", $_POST['votes']));
	$secret_code=generateRandomString();
	if(!lockVoter($voterKey)) return myDie("Another request is in processing! please wait for 30 seconds and retry!");
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	try{
		if($mysqli->query("UPDATE Voters set Done=1 where Done=0 and VoterKey='".$voterKey."'")!==TRUE || $mysqli->affected_rows!=1)
			throw new Exception('Voted.');
		if($mysqli->query("INSERT INTO Votes (date,secret) VALUES('" .date('Y-m-d H:i:s'). "','".$secret_code."')")!==TRUE)
			throw new Exception('Error.');

		$voteid=$mysqli->insert_id;
		$sql = "";
		foreach($votes as $k => $v) {
			$sql .= "INSERT INTO VoteDetails (VoteId, CandidateId, Preference) VALUES (". $voteid.", ".$v.", ".$k .");";
		}
		if ($mysqli->multi_query($sql) !== TRUE) 
			throw new Exception('Error in voting.');
		if(!$mysqli->query("UPDATE Voters set Done=2 where Done=1 and VoterKey='".$VoterKey."'")!==TRUE || $mysqli->affected_rows!=1)
			throw new Exception('Voting Error.');
		$mysqli->commit();
		unlockVoter($voterKey);
		return myDie("Your votes is recorded! To verify your choises, you can use your secret anynomous code:". $secret_code);
	} catch (exception $exception) {
		$mysqli->rollback();
		unlockVoter($voterKey);
		throw $exception;
	}

}

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

	
		<div class="row">
			<h2 class="col-12">Please drag and drop to select your preferences</h2>
		</div>
		<hr />
		<div id="list" class="row">
			<h4 class="col-12">Your preferences</h4>
			<div id="election-list" class="list-group col">
			<?php
			$result = $mysqli->query("SELECT * FROM Candidates");

			  // output data of each row
			  while($row = $result->fetch_assoc()) {
				echo '<div class="list-group-item" candidateId="'.$row["ID"].'" ><i class="fas fa-arrows-alt handle"></i> '.$row["Name"].'</div>';	
			  }

			?>
			</div>
		</div>
		<hr />
		<div class="row">
		
		<div class="btn btn-primary" onclick="submitVotes()">Submit Your Vote</div>
		<form id="elecform" method="post">
			  <input type="hidden" name="votes" id="votes"/>
		</form>
	</div>
	<!-- Latest Sortable -->
	<script src="assets/Sortable.js"></script>


	<script src="assets/app.js"></script>
</body>
</html>
