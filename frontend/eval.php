<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


$request = [
	'type' => 'getEval',
	'token' => $_COOKIE['session_key'],
	'league_id' => $_GET['id']
];

try {
	$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
	$response = $client->send_request($request);
} catch (Exception $e) {
	header('Location: home.php');
	exit();
}

if (($response['returnCode'] ?? '0') == '1') {
	$user_id = $response['userDetails'][0];
	$email = $response['userDetails'][2];
	$username = $response['userDetails'][1];
	$teamInfo = $response['teamInfo'];
	$drivers = $response['drivers'];
	$constructors = $response['constructors'];
	$teamName = $teamInfo['name'];
	$teamPoints = $teamInfo['currentPoints'];
} else {
	$message = $response['message'];
	$id = $_GET['id'];
	setcookie('message', "$message", time() + 60, '/');
	header("Location: league.php?id=$id");
	exit();
}


function getPlacementPoints($placement){
$pointsToAdd = 0;
switch ((int)$placement){
				case 1:
				 $pointsToAdd += 50;
				 break;
				case 2:
				 $pointsToAdd += 45;
				 break;
				case 3:
				 $pointsToAdd += 38;
				 break;
				case 4:
				 $pointsToAdd += 32;
				 break;
				case 5:
				 $pointsToAdd += 30;
				 break;
				case 6:
				 $pointsToAdd += 28;
				 break;
				case 7:
				 $pointsToAdd += 26;
				 break;
				case 8:
				 $pointsToAdd += 24;
				 break;
				case 9:
				 $pointsToAdd += 22;
				 break;
				case 10:
				 $pointsToAdd += 20;
				 break;
				case 11:
        			case 12:
        			case 13:
        			case 14:
        			case 15:
        			case 16:
				 $pointsToAdd += 10;	
				 break;
				case 17:
        			case 18:
        			case 19:
        			case 20:
        			case 21:
        			case 22:
				 $pointsToAdd += 5;
				 break;
				default: 
				 $pointsToAdd += 0;
				 break;
		}
	return $pointsToAdd;
}

$totalDriverPoints = 0;
$totalConstructorPoints = 0;

?>
<!DOCTYPE html>
<html>
<head><title>Evaluation</title></head>
<body>
	<?php include ('nav.php'); ?>
	<h1> Your Team Evaluation </h1>
	<?php 

	echo "<strong>Team Name</strong>" . " " . " <p>$teamName </p>";
	echo "<strong>Current Points:</strong>" . " " . " <p>$teamPoints </p>";
	?>
	<strong> Your Evaluation: </strong>
	<?php foreach ($drivers as $driver){
		$driverFamily = $driver['familyName'];
		$driverGiven = $driver['givenName'];
		$lastResult = $driver['lastRaceResult'];
		$lapRank = $driver['fastestLapRank'];
		$driverPoints = getPlacementPoints($lastResult);
		$totalDriverPoints += $driverPoints;
		echo "<p>$driverGiven $driverFamily: $lastResult Fastest Lap Rank: $lapRank</p>";
		if((int)$lapRank === 1){
			echo "<p> This racer got the fastest Lap, +5 points</p>"; 
			$driverPoints += 5;
		}
		echo "<p>+$driverPoints Points</p>";
	}
	?>
	<?php foreach ($constructors as $con){
		$name = $con['name'];
		$comPoints = $con['combinedDriverPoints'];
		$fantasyPoints = $comPoints * 2;
		$totalConstructorPoints += $fantasyPoints;

		echo "<p>$name: $comPoints</p>";
		echo "<p>+$fantasyPoints Points!</p>";
	}
		$weekPoints = $totalDriverPoints + $totalConstructorPoints;
		echo "<p>Your Drivers earned: $totalDriverPoints Your Constructors earned: $totalConstructorPoints Total: $weekPoints</p>";
	?>
	
	<a href="logout.php">Log out</a>
</body>
</html>

