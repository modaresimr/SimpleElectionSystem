<?php
include_once('common.php');

// $result = $mysqli->query("SELECT * FROM Configs where name='is_open'");
// if (!$result || $result->num_rows == 0) return myDie("Error: No config");
// if ($result['Value']!=='0') return myDie("Error: poll is still open");

$result = $mysqli->query("SELECT count(*) as count FROM Voters");
if (!$result || $result->num_rows == 0) return myDie("Error 1: No Voters",'danger');
$voter_counts=$result->fetch_assoc()['count'];

$result = $mysqli->query("SELECT count(*) as count FROM Voters where Done=1");
if (!$result || $result->num_rows == 0) return myDie("Error 2: No Voters",'danger');
$sent_vote_counts=$result->fetch_assoc()['count'];

$result = $mysqli->query("SELECT count(*) as count FROM Votes");
if (!$result || $result->num_rows == 0) return myDie("Error 3: No Votes",'danger');
$vote_counts=$result->fetch_assoc()['count'];


$result = $mysqli->query("SELECT d.Preference, c.Name,count(*) as count FROM Votes as v,VoteDetails as d,Candidates as c where v.ID=d.VoteID and c.ID=d.candidateID group by d.Preference, c.Name ORDER BY d.preference ASC,count(*) DESC" );
if (!$result||$result->num_rows == 0) return myDie("Error 4: in aggregating votes.",'danger');


header();
?>
	
		<hr />
		<div class="row">
			Total Voters= <?php echo $voter_counts;?><br/>
			Total Sent Votes= <?php echo $sent_vote_counts;?><br/>
			Total Received Votes= <?php echo $vote_counts;?><br/>
		</div>
		<div id="list" class="row">
		<div id="election-list-fix" class="list-group col">
		<?php
			$choice=0;
			while($row = $result->fetch_assoc()) {
				if($choice!==$row['Preference']){
					$choice=$row['Preference'];
					echo '<hr/><h4 class="col-12">Option '.$choice . '</h4>';
				}
			
							  
				echo '<div class="list-group-item d-flex justify-content-between align-items-center col-12" >'.$row["Name"]. '<span class="badge badge-primary rounded-pill">' .$row['count'].'</span></div>';	
			  }

			?>
			</div>
		</div>
		
		
	</div>
	
<?php
footer();
?>
