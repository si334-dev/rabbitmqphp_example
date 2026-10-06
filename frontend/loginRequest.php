<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: login.php');
	exit();
}

$request = [
	'type' => 'login',
	'email' => $_POST['email'] ?? '',
	'password' => $_POST['password'] ?? '',
];

try {
	$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
	$response = $client->send_request($request);
} catch (Exception $e) {
	header('Location: login.php?error=server');
	exit();
}

if (($response['returnCode'] ?? '0') == '1') {
	setcookie('session_key', $response['session_token'] ?? '', time() + 3600, '/');
	header('Location: home.php');
} else {
	header('Location: login.php?error=1');
}
exit();
