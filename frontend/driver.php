<?php

require_once('session.php');

$user = requireLogin();


if (!function_exists('getJson')) {

	function getJson($url) {

		$context = stream_context_create(['http' => ['timeout' => 5]]);

		$json = @file_get_contents($url, false, $context);

		return $json === false ? null : json_decode($json, true);

	}
}

$base = 'https://api.jolpi.ca/ergast/f1';

$id = $_GET['id'] ?? '';

$driver = null;

$standing = null;

$team = null;

$lastRace = null;

$result = null;

if (preg_match('/^[a-z0-9_]+$/i', $id)) {
	$data = getJson("$base/drivers/$id.json");

	$driver = $data['MRData']['DriverTable']['Drivers'][0] ?? null;

	if ($driver) {

		$data = getJson("$base/current/drivers/$id/driverStandings.json");

		$standing = $data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'][0] ?? null;

		$team = $standing['Constructors'][0] ?? null;

		$data = getJson("$base/current/drivers/$id/results.json?limit=100");

		$races = $data['MRData']['RaceTable']['Races'] ?? [];

		$lastRace = end($races) ?: null;

		$result = $lastRace['Results'][0] ?? null;
	}
}
?>
<!DOCTYPE html>
<html>
<head><title>Driver</title></head>
<body>
	<?php include('nav.php'); ?>

	<?php if (!$driver): ?>
		<h2>Driver not found</h2>
		<p>Go back to <a href="drivers.php">Drivers</a>.</p>
	<?php else: ?>

		<h2><?php echo htmlspecialchars($driver['givenName'] . ' ' . $driver['familyName']); ?></h2>
		<p>Number: <?php echo htmlspecialchars($driver['permanentNumber'] ?? 'N/A'); ?></p>
		<p>Code: <?php echo htmlspecialchars($driver['code'] ?? 'N/A'); ?></p>
		<p>Nationality: <?php echo htmlspecialchars($driver['nationality']); ?></p>
		<p>Born: <?php echo htmlspecialchars($driver['dateOfBirth']); ?></p>

		<h3>This Season</h3>
		<?php if ($standing): ?>
			<p>Championship position: P<?php echo htmlspecialchars($standing['position']); ?></p>
			<p>Points: <?php echo htmlspecialchars($standing['points']); ?></p>
			<p>Wins: <?php echo htmlspecialchars($standing['wins']); ?></p>
			<?php if ($team): ?>
				<p>Team: <a href="constructor.php?id=<?php echo urlencode($team['constructorId']); ?>"><?php echo htmlspecialchars($team['name']); ?></a></p>
			<?php endif; ?>
		<?php else: ?>
			<p>No standings this season.</p>
		<?php endif; ?>

		<h3>Last Race</h3>
		<?php if ($lastRace && $result): ?>
			<p><?php echo htmlspecialchars($lastRace['raceName']); ?> (Round <?php echo htmlspecialchars($lastRace['round']); ?>)</p>
			<p>Started: P<?php echo htmlspecialchars($result['grid']); ?></p>
			<p>Finished: P<?php echo htmlspecialchars($result['positionText']); ?> (<?php echo htmlspecialchars($result['status']); ?>)</p>
			<p>Points: <?php echo htmlspecialchars($result['points']); ?></p>
		<?php else: ?>
			<p>No races this season.</p>
		<?php endif; ?>

	<?php endif; ?>
</body>
</html>
