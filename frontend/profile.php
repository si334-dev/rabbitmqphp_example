<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


$request = [
	'type' => 'getProfileInfo',
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
	$user_id = $response['userDetails'][0];
	$email = $response['userDetails'][2];
	$username = $response['userDetails'][1];
	$leagueList = $response['leagueInfo'];
} else {
	$message = $response['message'];
	setcookie('message', "$message", time() + 60, '/');
	header('Location: home.php');
	exit();
}

?>
<!DOCTYPE html>
<html>
<head><title>Profile</title></head>
<body>
	<?php include ('nav.php'); ?>
	<h1> Your Profile </h1>
	<?php 

	echo "<strong>Username</strong>" . " " . " <p>$username </p>";
	echo "<strong>Email:</strong>" . " " . " <p>$email </p>";
	?>
	<strong> Your Leagues: </strong>
	<?php foreach ($leagueList as $league){
		$leagueName = $league['name'];
		$leagueID = $league['id']; 
		$url = "league.php?id=" . "$leagueID";
		echo "<br>";
		echo "<a href=$url>$leagueName</a> " . " ";
	}
	?>
	<p> </p>
	<a href="logout.php">Log out</a>
</body>
</html>

