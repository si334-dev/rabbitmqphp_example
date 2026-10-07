<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: register.php');
	exit();
}

$request = [
	'type' => 'register',
	'username' => $_POST['username'] ?? '',
	'email' => $_POST['email'] ?? '',
	'password' => $_POST['password'] ?? '',
];

$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request($request);

if (($response['returnCode'] ?? '0') == '1') {
	header('Location: login.php?registered=1');
} else {
	header('Location: register.php?error=1');
}
exit();
