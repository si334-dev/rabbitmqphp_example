<?php

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function requireLogin() {

$sessionKey = $_COOKIE['session_key'] ?? '';

if ($sessionKey === '') {

	header('Location: login.php');

	exit();
}

try {

	$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

	$response = $client->send_request([

		'type' => 'validate_session',

		'session_key' => $sessionKey, 
	]);
} catch (Exception $e) {

	$response = null;

}

if (($response['returnCode'] ?? '0') != '1') {

	header('Location: login.php');

	exit();

}

return [

		'session_key' => $sessionKey,

		'user_id' => $response['user_id'] ?? null,

		'username' => $response['username'] ?? null,
	];

}

