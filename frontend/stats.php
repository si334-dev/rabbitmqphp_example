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

	function getJson($url){
		$text = @file_get_contents($url);
		
		if ($text === false){
			return[];
		}

		return json_decode($text, true);	
	}

	$start = getJson('https://api.jolpi.ca/ergast/f1/current');

	$results = getJson("$start/last/results.json");
	$pitstops = getJson("$start/last/pitstops.json?limit=100");
	$season = getJson("$start/fastest/1/results.json?limit=100");

	$race = $results['MRData']['RaceTable']['Races'][0] ?? null;
	$circuitName = $race['raceName'] ?? 'Unavailable';
	$drivers = $race['Results'] ?? [];
	$stops = $pitstops['MRDdata']['RaceTable']['Races'][0]['PitStops'] ?? [];
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
	
?>

<!DOCTYPE html>
<html>

	<head>
		<title> Statistics </title>
	</head>

	<body>
		<h1> Last Race: <?php echo $circuitName; ?> </h1>
		
		<h3>Fastest Pit Stops</h3>
		<?php foreach($fastestStops as $s) {?>
			<p> <?php echo $s['driverId']; ?> - <?php echo $s['duration']; ?> seconds (lap <?php $s['lap'];?>) </p> 
		<?php } ?>

		<h3>Fastest Lap </h3>
		<?php if ($fastestLap) { ?>
			<p> <?php echo $fastestLap['Driver']['familyName']; ?> - <?php echo $fastestLap['FastestLap']['Time']['time']; ?> </p>
		<?php } ?>		

		<h3> DNFs </h3>
		<?php foreach ($dnfs as $d){ ?>
			<p> <?php echo $d['Driver']['familyName']; ?> - <?php echo $d['status']; ?> </p>
		<?php } ?>

		<h1> Overall Season </h1>

		<h3> Fastest Pit Stops </h3>

		<h3> Fastest Laps </h3>
		<?php foreach($seasonLaps as $r){ ?> 
			<p> <?php echo $r['raceName']; ?>: <?php echo $r['Results'][0]['Driver']['familyName']; ?> - <?php echo $r['Results'][0]['FastestLap']['Time']['time']; ?> </p>
		<?php } ?> 
		<h3> DNFs </h3>

</html>

