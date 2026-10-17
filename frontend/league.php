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
	'type' => 'getLeagueInfo',
	'league_id' => $_GET['id'];
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
	
	//maybe put stuff here
} else {
	$_COOKIE['message'] = $response['message'];
	header('Location: home.php?error=1');
	exit();
}

