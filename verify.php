<?php
include_once('common.php');

if (empty ($_GET["secret"])) return myDie("Error: Secret should note be empty ",'danger');
$secret=$mysqli->real_escape_string($_GET["secret"]);
	
$result = $mysqli->query("SELECT * FROM Votes where Secret='".$secret."'");
if (!$result || $result->num_rows == 0) return myDie("Error: Your secret token is invalid",'danger');
$voteid=$result->fetch_assoc()['ID'];

$result = $mysqli->query("SELECT * FROM Votes as v,VoteDetails as d,Candidates as c where v.ID=d.VoteID and c.ID=d.candidateID and v.Secret='".$secret."' order by d.Preference" );
if (!$result||$result->num_rows == 0) return myDie("Error: No Vote registered.",'danger');


header();
?>
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
	
<?php
footer();
?>
