<?php 

//	require_once('path.inc');
//	require_once('get_host_info.inc');
//	require_once('rabbitMQLib.inc');

/*
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

 */

	function getJson($url){
		$text = @file_get_contents($url);
		
		if ($text === false){
			return[];
		}

		return json_decode($text, true);	
	}

	$results = getJson("https://api.jolpi.ca/ergast/f1/current/last/results.json");
	$pitstops = getJson("https://api.jolpi.ca/ergast/f1/current/last/pitstops.json?limit=100");
	$season = getJson("https://api.jolpi.ca/ergast/f1/current/fastest/1/results.json?limit=100");

	$race = $results['MRData']['RaceTable']['Races'][0] ?? null;
	$circuitName = $race['raceName'] ?? 'Unavailable';
	$drivers = $race['Results'] ?? [];
	$stops = $pitstops['MRData']['RaceTable']['Races'][0]['PitStops'] ?? [];
	$seasonLaps = $season['MRData']['RaceTable']['Races'] ?? [];

	$fastestLap = null;
	foreach ($drivers as $d){
		if (($d['FastestLap']['rank'] ?? ' ') === '1') {
			$fastestLap =$d;
		}
	}

	$dnfs = [];
	foreach($drivers as $d){
		$status = $d['status'];
		if ($status !== 'Finished' && $status[0] !== '+'){
			$dnfs[] = $d;
		}
	}

	usort($stops, function($a, $b){
		return (float)$a['duration'] <=> (float)$b['duration'];
	});

	$fastestStops = array_slice($stops, 0, 10);

	$teamBest = [];
	$dnfCount = [];

	$lastRace = (int)($race['round'] ?? 0);

	for ($i =1; $i <= $lastRace; $i++){

		$r = getJson("https://api.jolpi.ca/ergast/f1/current/$i/results.json");
		$p = getJson("https://api.jolpi.ca/ergast/f1/current/$i/pitstops.json?limit=50");

		$rs = $r['MRData']['RaceTable']['Races'][0]['Results'] ?? [];
		$ps = $p['MRData']['RaceTable']['Races'][0]['PitStops'] ?? [];

		$teamOf = [];

		foreach ($r as $j){
			$teamOf[$j['Driver']['driverId']] = $j['Constructor']['name'];

			$st = $j['status'];
			if ($st !== 'Finished' && $st[0] !== '+'){
				$name = $j['Driver']['familyName'];
				$dnfCount[$name] = ($dnfCount[$name] ?? 0) +1;
			}
		}

		foreach ($ps as $s){
			$team = $teamOf[$s ['driverId']] ?? null;
			$time = (float)$s['duration'];

			if ($team && (!isset($teamBest[$team]) || $time < $teamBest[$team])){
				$teamBest[$team] = $time;
			}
		}
	}

	asort($teamBest);
	$topTeams = array_slice($teamBest, 0, 5, true);
	arsort($dnfCount);
	
?>

<!DOCTYPE html>
<html>

	<head>
		<title> Statistics </title>
	</head>

	<body>
		<h1> Last Race: <?php echo $circuitName; ?> </h1>
		
		<h3>Fastest Pit Stops</h3>   <!--displays top 5 fastests pit stops of the race-->
		<?php foreach($fastestStops as $s) {?>
			<p> test  <?php echo $s['driverId']; ?> - <?php echo $s['duration']; ?> seconds (lap <?php echo $s['lap'];?>) </p> 
		<?php } ?>

		<h3>Fastest Lap </h3> 
		<?php if ($fastestLap) { ?>
			<p> <?php echo $fastestLap['Driver']['familyName']; ?> - <?php echo $fastestLap['FastestLap']['Time']['time']; ?> </p>
		<?php } ?>		

		<h3> DNFs </h3>
		<?php foreach ($dnfs as $d){ ?> <!-- lists drivers if retired, ex: Stroll - DNF*/-->
			<p> <?php echo $d['Driver']['familyName']; ?> - <?php echo $d['status']; ?> </p>
		<?php } ?>

		<h1> Overall Season </h1>

		<h3> Fastest Pit Stops </h3> <!-- top 3?5? team with fastest pitstops overall -->
		<?php foreach($topTeams as $team => $time){ ?>
			<p> <?php echo $team; ?> - <?php echo $time; ?> seconds </p>
		<?php } ?>

		<h3> Fastest Laps </h3> <!--fastest lap of every race -->
		<?php foreach($seasonLaps as $r){ ?> 
			<p> <?php echo $r['raceName']; ?>: <?php echo $r['Results'][0]['Driver']['familyName']; ?> - <?php echo $r['Results'][0]['FastestLap']['Time']['time']; ?> </p>
		<?php } ?> 
		<h3> DNFs </h3> <!--number of dnfs per driver over the whole season. if d = retired; add to +1 individual driver dnf-->
		<?php foreach($dnfCount as $name => $count){ ?>
			<p> <?php echo $name; ?> - <?php echo $count; ?> </p>
		<?php } ?>

</body>

</html>

