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

$team = null;

$standing = null;

$teamDrivers = [];

$lastRace = null;

if (preg_match('/^[a-z0-9_]+/i', $id)) {

	$data = getJson("$base/constructors/$id.json");

	$team = $data['MRData']['ConstructorTable']['Constructors'][0] ?? null;

	if ($team) {

		$data = getJson("$base/current/constructors/$id/constructorStandings.json");

		$standing = $data['MRData']['StandingsTable']['StandingsLists'][0]['ConstructorStanding'][0] ?? null;

		$data = getJson ("$base/current/constructors/$id/drivers.json");

		$teamDrivers = $data['MRData']['DriverTable']['Drivers'] ?? [];

		$data = getJson("$base/current/constructors/$id/results.json?limit=100");

		$races = $data['MRData']['RaceTable']['Races'] ?? [];

		$lastRace = end($races) ?: null;
	}
}
?>
<!DOCTYPE html>
<html>
<head><title>Constructor</title></head>
<body>
	<?php include('nav.php'); ?>

	<?php if (!team): ?>
		<h2>Constructor not found</h2>
		<p>Go back to <a href ="team_search.php">Teams</a>.</p>
	<?php else: ?>

		<h2><?php echo htmlspecialchars($team['name']); ?></h2>
		<p>Nationality: <?php echo htmlspecialchars($team['nationality']); ?></p>

		<h3>This Season</h3>
		<?php if ($standing): ?>
			<p>Championship position: P<?php echo htmlspecialchars($standing['position']); ?></p>
			<p>Points: <?php echo htmlspecialchars($standing['points']); ?></p>
			<p>Wins: <?php echo htmlspecialchars($standings['wins']); ?></p>
		<?php else: ?>
			<p>No standings this season.</p>
		<?php endif; ?>

		<h3>Drivers</h3>
		<?php if ($teamDrivers): ?>
			<ul>
			<?php foreach ($teamDrivers as $d): ?>
				<li><a href="driver.php?id=<?php echo urlencode($d['driverId']); ?>"><?php echo htmlspecialchars($d['givenName'] . ' ' . $d['familyName']); ?></a?</li>
			<?php endforeach; ?>
			</ul>
		<?php else: ?>
			<p>No drivers this season.</p>
		<?php endif; ?>

		<h3>Last Race</h3>
		<?php if ($lastRace): ?>
			<p><?php echo htmlspecialchars($lastRace['raceName']); ?> (Round <?php echo htmlspecialchars($lastRace['round']); ?>)</p>
			<ul>
			<?php foreach ($lastRace['Results'] ?? [] as $r): ?>
				<li><?php echo htmlspecialchars($r['Driver']['familyName']); ?>: P<?php echo htmlspcialchars($r['positionText']); ?> (<?php echo htmlspecialchars($r['points']); ?> pts)</li>
			<?php endforeach; ?>
			</ul>
		<?php else: ?>
			<p>No races this current season.</p>
		<?php endif; ?>

	<?php endif; ?>
</body>
</html>
