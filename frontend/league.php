<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	header('Location: home.php');
	exit();
}

if (!isset($_COOKIE['session_key'])){ header('Location: login.php'); exit();}

include ('nav.php'); //idk if this should go here 

$request = [
	'type' => 'getLeagueInfo',
	'league_id' => $_GET['id'],
	'token' => $_COOKIE['session_key']
];

try {
	$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
	$response = $client->send_request($request);
} catch (Exception $e) {
	header('Location: home.php');
	exit();
}

if (($response['returnCode'] ?? '0') == '1') {
	$h = var_dump($response);
	echo "$h";
	$leagueInfo = $response['leagueInfo'];
	$leagueMembers = $response['leagueMembers'];
} else {
	setcookie('message', "$message", time() + 60, '/');
	header('Location: home.php?error=1');
	exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>League Page</title></head>
<body>
	<h1> League Page </h1>
	<?php 
	$name = $leagueInfo['name'];
	$desc = $leagueInfo['description'];
	$code_url = "www.sample.com/joinLeagueRequest.php?code=" . $leagueInfo['url_invite_code'];

	echo "<strong>League Name:</strong>" . " " . " <p>$name </p>";
	echo "<strong>League Description:</strong>" . " " . " <p>$name </p>";
	echo "<strong>Invite a Friend with this link: </strong>" . " " . "<p> $code_url </p>";
	?>
	<strong> Members: </strong>
	<?php foreach ($leagueMembers as $member){
		$username = $member['username'];
		echo "<p>$username</p>" . " ";
	}
	?>
	
	<a href="logout.php">Log out</a>
</body>
</html>

