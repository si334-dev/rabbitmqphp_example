<?php
function sendRequest($request) {
	if ($request['session_key'] === 'FAKEKEY123') {
		return ['returnCode' => '1'];
	}
	return ['returnCode' => '0'];
}

$sessionKey = $_COOKIE['session_key'] ?? '';
$valid ='0';

if ($sessionKey !== '') {
	$response = sendRequest([
	'type' => 'validate_session',
	'session_key' => $sessionKey,
	]);
	$valid = $response['returnCode'] ?? '0';
}

if ($valid !== '1') {
	header('Location: login.php');
	exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Home</title></head>
<body>
	<h2>Welcome!</h2>
	<p>You're logged in. Session key: <?php echo htmlspecialchars($sessionKey); ?></p>
	<a href="logout.php">Log out</a>
</body>
</html>
