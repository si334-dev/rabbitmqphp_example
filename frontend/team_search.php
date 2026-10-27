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

if ( $valid != '1') {
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

// what the user typed in the search box
$search = $_GET['search'] ?? '';

// teams: name, nationality, constructorId, position, points, wins
$data = getJson("https://api.jolpi.ca/ergast/f1/current/constructorstandings/");
$need = $data['MRData']['StandingsTable']['StandingsLists'][0]['ConstructorStandings'] ?? [];

//  grouped by team id
$driverData = getJson("https://api.jolpi.ca/ergast/f1/current/driverstandings/");
$driverList = $driverData['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'] ?? [];

$teamDrivers = [];
foreach ($driverList as $d) {
	foreach ($d['Constructors'] as $c) {
		$teamDrivers[$c['constructorId']][] = $d['Driver']['givenName'] . " " . $d['Driver']['familyName'];
	}
}

?>
<!DOCTYPE html>
<html>
<head><title>Team Search</title></head>
<body>
	<?php include ('nav.php'); ?>
	<h1> Team Search 2026 </h1>


	<form method="GET" action="team_search.php">
		<input type="text" name="search" placeholder="Search team" value="<?= htmlspecialchars($search) ?>">
		<button type="submit">Search</button>
	</form>

	<ul>
	<?php foreach ($need as $standing){
		$constructor = $standing['Constructor'];

		// skip teams that don't match the search
		if ($search !== '' && stripos($constructor['name'], $search) === false) {
			continue;
		}
		$id = $constructor['constructorId'];
	?>
		<li>
		<strong><?= $constructor['name'] ?></strong>
		<br>
		Nationality: <?= $constructor['nationality'] ?>
		<br>
		Championship position: <?= $standing['position'] ?>
		<br>
		Championship points: <?= $standing['points'] ?>
		<br>
		Wins: <?= $standing['wins'] ?>
		<br>
		Drivers: <?= implode(", ", $teamDrivers[$id] ?? []) ?>
		</li>
		<br>
	<?php } ?>

	</ul>
	<a href="logout.php">Log out</a>
</body>
</html>
