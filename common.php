<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
include 'PHPMailer/PHPMailer.php';
include 'PHPMailer/SMTP.php';
include 'PHPMailer/Exception.php';

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
function sendEmail($to,$subject,$body){
    
    $mail = new PHPMailer();

    // Settings
    $mail->IsSMTP();
    $mail->CharSet = 'UTF-8';
    
    $mail->Host       = $_ENV['MAIL_HOST'];    // SMTP server example
    $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
    $mail->SMTPAuth   = true;                  // enable SMTP authentication
    $mail->Port       = $_ENV['MAIL_PORT'];                    // set the SMTP port for the GMAIL server
    $mail->Username   = $_ENV['MAIL_USERNAME'];            // SMTP account username example
    $mail->Password   = $_ENV['MAIL_PASSWORD'];            // SMTP account password example
    
    // Content
    $mail->isHTML(true);                       // Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port       = 587;       
    $mail->setFrom($_ENV['MAIL_USERNAME'], 'RoboCup Election');
    $mail->addAddress($to);     //Add a recipient
    
    if(!$mail->send()){
        echo "Mailer Error: " . $mail->ErrorInfo;
    }

}
// Check connection
if ($mysqli->connect_error) return myDie("Error: Connection failed: " . $mysqli->connect_error,'danger');

?>