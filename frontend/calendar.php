<?php 
	$today = date('Y-m-d');

	function getJson($url){
		$text = @file_get_contents($url);
		
		if ($text === false){
			return[];
		}

		return json_decode($text, true);	
	}

	$data = getJson('https://api.jolpi.ca/ergast/f1/current.json');

	$races = $data['MRData']['RaceTable']['Races'] ?? [];

	$upcoming = [];
	$past = [];

	foreach($races as $race){
		if ($race['date'] >= $today){
			$upcoming[] = $race;
		} else {
			$past[] = $race;
		}
	}

	function showSession($name, $session){
		if(!isset($session)){
			return;
		}
		$time = substr($session['time'], 0, 5);

		echo "<li" . $name . ": " . $session["date"] . " at " . $time . " UTC </li>";

	}

?>


<!DOCTYPE html>

<html>

<head>
	<title> Calendar</title>
</head>

<body>

	<h3> Upcoming Races </h3>

	<?php foreach($upcoming as $race){
	echo "<p>" . $race['date'] . " - "  . $race["raceName"] . " - " .  $race["Circuit"]["circuitName"] . "</p";
	echo "\n";

	showSession("Free Practice 1", $race['FirstPractice'] ?? null);
	showSession("Free Practice 2", $race['SecondPractice'] ?? null);
	showSession("FreePractice 3", $race['ThirdPractice'] ?? null);

	showSession("SprintQualifying", $race['SprintQualifying'] ?? null);
	showSession("Sprint", $race['Sprint'] ?? null);
	showSession("Qualifying", $race['Qualifying'] ?? null);

	showSession("Race", ['date' => $race['date'], 'time' => $$race['time']]);

	}
	?>

<!-- here goes the next races with circuit and time for time at track -->
<!-- for the upcoming race will be divided in days with the time for the free practices, qualy, and race -->


	<h3> Past Races </h3>
<!-- past races with just date and circuit -->
	<?php foreach($past as $race){
		echo "<p>" . $race['date'] . " - " . $race['raceName'] . "</p>";
	}
	?>

</body>

</html>



