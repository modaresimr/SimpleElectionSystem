<?php
include_once('common.php');

$result1 = $mysqli->query("SELECT * FROM Votes order by rand()");
if(!$result1|| $result1->num_rows == 0) return myDie("Error: Your secret token is invalid",'danger');
myheader();
while ($vote=$result1->fetch_assoc()){
$voteid=$vote['ID'];
$result = $mysqli->query("SELECT * from VoteDetails as d where d.VoteID=".$voteid." order by d.Preference" );
//if (!$result||$result->num_rows == 0) return myDie("Error: No Vote registered.",'danger');


?>
                <div id="list" class="row">
                        <?php
                        $first=true;
                          // output data of each row
                          while($row = $result->fetch_assoc()) {
                                if($first){$first=false;}else{echo '>';}
                                echo mb_chr(64+ $row["CandidateId"]);
                          }

                        ?>
</br>
        </div>

<?php
}
myfooter();
?>