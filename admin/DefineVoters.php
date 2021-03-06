<?php
include_once('../common.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if(isset($_POST['DoAll'])){
		$emails=preg_split('/(\s|;)+/',trim($_POST['emails']));
		foreach($emails as $k=>$email){
			$result=$mysqli->query("delete from Voters;");
			if ($result !== TRUE) return myDie('Can not remove old voters'.$mysqli->error,'danger');
			$result=$mysqli->query("delete from Votes;");
			if ($result !== TRUE) return myDie('Can not remove old votes. '.$mysqli->error,'danger');

			$sql = "INSERT INTO Voters (VoterKey, Email) VALUES ";
			$sql .= implode(',',array_map(function ($email) { global $voteid; return "('".(generateRandomString(40)) ."','". $email."')"; }, $emails));
			$sql .=";";
			$result=$mysqli->query($sql);
			
			if ($result !== TRUE) return myDie('Error in registering emails.'.$mysqli->error,'danger');
		}
	}
	if(isset($_POST['SendEmail'])){
		$result = $mysqli->query("SELECT * FROM Voters where EmailSent=0;");
		while($row = $result->fetch_assoc()) {
			try{
				sendEmail($row['Email'],"Your Vote Token","Please use the following link to vote: <a href='https://election.h2.robocup.org/?Key=".$row["VoterKey"]."'>https://election.h2.robocup.org/?Key=".$row["VoterKey"]."</a>");
				echo "Email sent successfully to ".$row['Email'] . "</br>";
				$mysqli->query("update Voters set EmailSent=1 where VoterKey='".$row["VoterKey"]."';");
			}catch(Exception $e){
				echo "Error email to ".$row['Email'] . " : ".$e."</br>";
			}
		}
	}
}




myheader();
// output data of each row

	?>
	<div id="list" class="row">
			<h4 class="col-12">Tokens and Emails</h4>
			<div id="election-list-fix" class="list-group col">
			<?php
			$result = $mysqli->query("SELECT * FROM Voters;");
			  // output data of each row
			  while($row = $result->fetch_assoc()) {
				echo '<div class="list-group-item" >'.$row["Email"].' : Sent='.$row["EmailSent"].' : <a href="https://election.h2.robocup.org/?Key='.$row["VoterKey"].'"> https://election.h2.robocup.org/?Key='.$row["VoterKey"].'</a></div>';	
			  }

			?>
			</div>
		</div>
		
		<hr />

		<form method="post">			
				<input type="submit" name="SendEmail" class="btn btn-primary" value="Send Emails - Will not send email if it is already sent!"/>
		</form>
<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
?>
		<div id="list" class="row">
			
			<div id="election-list-fix" class="list-group col">
			<form method="post">
			<div class="form-group">
			<label for="exampleFormControlTextarea1">Please Enter Emails</label>

				<textarea name="emails" rows=20 class="form-control col-12"></textarea>
			</div>
				<input type="submit" name="DoAll" class="btn btn-primary" value="Remove Old Voters, Create Vote Token"/>
			</form>
			</div>
		</div>
		<hr />
		
	</div>
	
<?php
}
myfooter();
?>
