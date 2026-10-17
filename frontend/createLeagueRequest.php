<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: login.php');
	exit();
}

$request = [
	'type' => 'createLeague',
	'name' => $_POST['League Name'] ?? '',
	'desc' => $_POST['Description'] ?? '',
	'token' => $_COOKIE['session_key']
];

try {
	$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
	$response = $client->send_request($request);
} catch (Exception $e) {
	header('Location: createLeague.php?error=server');
	exit();
}

if (($response['returnCode'] ?? '0') == '1') {
	$id = $response['league_id'];
	header("Location: league.php?id=$id");
} else {
	header('Location: createLeague.php?error=1');
}
exit();
