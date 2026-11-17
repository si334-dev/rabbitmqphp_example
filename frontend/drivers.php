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
 
function getJson($url){
	$options = ['http' => ['header' => "User-Agent: test-script\r\n"]];
	$context = stream_context_create($options);

	$text = @file_get_contents($url, false, $context);

	if($text === false){
	return null;
	}

	return json_decode($text, true);
}


$data = getJson("http://api.jolpi.ca/ergast/f1/2026/drivers/");
//print_r($data);
$need = $data['MRData']['DriverTable']['Drivers'];


?>
<!DOCTYPE html>
<html>
<head><title>Home</title><?php include 'header.php';?></head>
<body>
<?php include ('nav.php'); ?>
<main class="container">
	<h1> Drivers 2026 </h1>
	<ul class="list-group mb-3">
	<?php foreach ($need as $driver){ ?>
		<li class="list-group-item">
	<?php
		$gname = $driver['givenName']; 
		$fname = $driver['familyName'];
		$DOB = $driver['dateOfBirth']; 
		$nat = $driver['nationality'];
		$id = urlencode($driver['driverId']);
		echo "<a href=\"driver.php?id=$id\">" . htmlspecialchars("$gname $fname") . "</a> ";
		if(!empty($DOB) && !empty($nat)){
		echo "<strong> Date Of Birth: </strong> $DOB" . "<strong> Nationality: </strong> $nat";
		}

	?>
		</li>
	<?php } ?>

	</ul>
	<a href="logout.php">Log out</a>
</main>
</body>
</html>
