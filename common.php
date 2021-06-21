<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = $_ENV["SQL_HOST"];
$username = $_ENV["SQL_USERNAME"];
$password = $_ENV["SQL_PASSWORD"];
$dbname = $_ENV["SQL_DB"];

function myheader(){
    ?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1" />

		<title>RoboCup Election</title>
		<link rel="stylesheet" type="text/css" href="assets/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="assets/font-awesome/all.css">
		<link rel="stylesheet" type="text/css" href="assets/theme.css?v1.1">

		<meta charset="utf-8"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
		
	</head>
	<body>	
    <?php
}
function myfooter(){
    ?>
	<script src="assets/Sortable.js"></script>
	<script src="assets/app.js?v1.1"></script>
    </body>
	</html>
    <?php
}
// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);
function myDie($txt,$class){
	global $mysqli;
	$mysqli->close();
	myheader();
	?>
		<div class="row">
			<div class="alert alert-<?echo $class;?> col-12"><?php echo $txt;?></div>
		</div>
    <?php
    myfooter();
	die();
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
if ($mysqli->connect_error) return myDie("Error: Connection failed: " . $mysqli->connect_error,'danger');

?>