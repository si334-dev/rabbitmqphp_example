<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	header('Location: home.php');
	exit();
}

if (!isset($_COOKIE['session_key'])){ header('Location: login.php'); exit();}


$request = [
	'type' => 'joinLeague',
	'joinCode' => $_GET['code'] ?? '',
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
	$id = $response['leagueID'];
	header("Location: league.php?id=$id");
} else {
	setcookie('message', "$message", time() + 60, '/');

	header('Location: home.php?error=1');
}
exit();
