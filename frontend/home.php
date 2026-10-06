<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$sessionKey = $_COOKIE['session_key'] ?? '';
$valid = '0';

if ($sessionKey !== '') {
	try {
		$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
		$response = $client->send_request([
			'type' => 'validate_session',
			'session_key' => $sessionKey,
		]);
		$valid = $response['returnCode'] ?? '0';
	} catch (Exception $e) {
		$valid = '0';
	}
}

if ($valid != '1') {
	header('Location: login.php');
	exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Home</title></head>
<body>
	<h2>Welcome!</h2>
	<p>You are logged in.</p>
	<a href="logout.php">Log out</a>
</body>
</html>
