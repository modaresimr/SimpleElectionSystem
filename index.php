<?php
include_once('common.php');

// $result = $mysqli->query("SELECT * FROM Configs where name='is_open'");
// if (!$result || $result->num_rows == 0) return myDie("Error: No config");
// if ($result['Value']!=='1') return myDie("Error: poll is not active");

if (empty ($_GET["Key"])) return myDie("Error: Key should note be empty ",'danger');
$voterKey=$mysqli->real_escape_string($_GET["Key"]);
	
$result = $mysqli->query("SELECT * FROM Voters where VoterKey='".$voterKey."'");
if (!$result || $result->num_rows == 0) return myDie("Error: Your vote token is invalid",'danger');


$result = $mysqli->query("SELECT * FROM Voters where Done=0 and VoterKey='". $voterKey ."'" );
if (!$result||$result->num_rows == 0) return myDie("Error: You have voted.",'warning');




if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$votes = array_map(function($v){ return (int) trim($v, "'"); }, explode(",", $_POST['votes']));
	$secret_code=generateRandomString();
	$voter_email=$result->fetch_assoc();

	if(!lockVoter($voterKey)) return myDie("Another request is in processing! please wait for 30 seconds and retry!",'danger');
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	try{
		if($mysqli->query("UPDATE Voters set Done=1 where Done=0 and VoterKey='".$voterKey."'")!==TRUE || $mysqli->affected_rows!=1)
			throw new Exception('Voted.');
		if($mysqli->query("INSERT INTO Votes (date,secret) VALUES('" .date('Y-m-d H:i:s'). "','".$secret_code."')")!==TRUE)
			throw new Exception('Error.');

		$voteid=$mysqli->insert_id;
		$sql = "INSERT INTO VoteDetails (VoteId, CandidateId, Preference) VALUES ";
        $sql .= implode(',',array_map(function ($k,$v) { global $voteid; return "(". $voteid.", ".$v.", ".($k+1) .")"; }, array_keys($votes), $votes));
        $sql .=";";
		$result=$mysqli->query($sql);
		
		if ($result !== TRUE) 
			throw new Exception('Error in voting.');
		
		if(!$mysqli->commit())
			throw new Exception($mysqli->error);
		unlockVoter($voterKey);
		$body="Your vote is recorded! Please keep safe your anonymous secret code. <a href='verify.php?secret=". $secret_code . "'>".$secret_code."</a></br> The secret code is usable only for you and you can use it to verify your vote and make sure that your vote is counted. ";
		sendEmail($voter_email,"Your vote is recorded",$body);
		return myDie("Your vote is recorded! Please keep safe your anonymous secret code. <a href='verify.php?secret=". $secret_code . "'>".$secret_code."</a></br> The secret code is usable only for you and you can use it to verify your vote and make sure that your vote is counted. ",'success');
	} catch (exception $exception) {
		$mysqli->rollback();
		unlockVoter($voterKey);
		myDie($exception,'danger');
	}

}

myheader();
?>

	
		<div class="row">
			<h2 class="col-12">Please drag and drop to select your preferences</h2>
		</div>
		<hr />
		<div id="list" class="row">
			<h4 class="col-12">Your preferences</h4>
			<div id="election-list" class="list-group col">
			<?php
			$result = $mysqli->query("SELECT * FROM Candidates ORDER BY RAND();");

			  // output data of each row
			  while($row = $result->fetch_assoc()) {
				echo '<div class="list-group-item" candidateId="'.$row["ID"].'" ><i class="fas fa-arrows-alt handle"></i> '.$row["Name"].'</div>';	
			  }

			?>
			</div>
		</div>
		<hr />
		<div class="row">
		
		<div class="btn btn-primary" id="submit-btn" onclick="submitVotes()">Submit Your Vote</div>
		<form id="elecform" method="post">
			  <input type="hidden" name="votes" id="votes"/>
		</form>
	</div>



<?php
myfooter();
?>
