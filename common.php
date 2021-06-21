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
function myDie($txt,$class){
	global $mysqli;
	$mysqli->close();
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
		<div class="row">
			<div class="alert alert-<?echo $class;?>"><?php echo $txt;?></div>
		</div>
	</body>
	</html>

	<?php
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