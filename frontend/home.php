<?php
require_once('session.php');

$user = requireLogin();

require_once('./app/helpers.php');
displaySessionMessage();


$nextRace = null;
$context = stream_context_create(['http' => ['timeout' => 5]]);
$json = @file_get_contents('https://api.jolpi.ca/ergast/f1/current/next.json', false, $context);
if ($json !== false) {
	$data = json_decode($json, true);
	$nextRace = $data['MRData']['RaceTable']['Races'][0] ?? null;
}

$raceTime = '';
if ($nextRace) {
	$when = new DateTime($nextRace['date'] . ' ' . ($nextRace['time'] ?? '00:00:00Z'));
	$when->setTimezone(new DateTimeZone('America/New_York'));
	$raceTime = $when->format('l, F j, Y \a\t g:i A T');
}
?>
<!DOCTYPE html>
<html>
<head><title>Home</title></head>
<body>
	<?php include ('nav.php'); ?>

	<h2>Welcome!</h2>

	<h3>Next Race!</h3>
	<?php if ($nextRace): ?>
		<p><?php echo htmlspecialchars($nextRace['raceName']); ?> (Round <?php echo htmlspecialchars($nextRace['round']); ?>)</p>
		<p><?php echo htmlspecialchars($nextRace['Circuit']['circuitName']); ?>, <?php echo htmlspecialchars($nextRace['Circuit']['Location']['locality']); ?>, <?php echo htmlspecialchars($nextRace['Circuit']['Location']['country']); ?></p>
		<p><?php echo htmlspecialchars($raceTime); ?></p>
	<?php else: ?>
		<p>Race info unavailable right now.</p>
	<?php endif; ?>
<!--	<h3>My Leagues</h3>
	<p>Coming soon.</p> -->
	<a href="createLeague.php">Create A League</a>

	<!-- <h3>Last Race Final Results</h3>
	<p>Coming soon. </p> -->
</body>
</html>
