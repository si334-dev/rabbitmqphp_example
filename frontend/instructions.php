<?php 

	require_once('path.inc');
	require_once('get_host_info.inc');
	require_once('rabbitMQLib.inc');

	$sessionkey = $_COOKIE['session_key'] ?? ' ';
	$valid = 0;


	if ($sessionKey !== '') {
		try {
			$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
			$response = $client->send_request([
				'type' => 'validate_session',
				'session_key' => $sessionkey,
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
<head>
	<title> How To Play </title>
</head>

<body>

	<h1> How To Play </h1>

		<p> here will be the instuctions of how we will score and how the draft works </p>
</body>

</html>



