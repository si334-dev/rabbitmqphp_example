<?php 
/*
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
 */
?>	


<!DOCTYPE html>

<html>
<head>
	<title> How To Play </title>
</head>

<body>
<?php include ('nav.php'); ?>

	<h1> How To Play </h1>
			
		<p> To be able to play you will have to create a team for each league you join. You will have $1,000 to chose 2 teams and 3 drivers from the current F1 roster. <br> 
	 After completing your team you will either be invited to join a league or create a league, you can invite your friends by sharing the link so that everyone can play.  </p>

	<h3> Scoring </h3>
	<p> Similar to F1, the points for each driver will be distributed based on their final position after the race: <br>
	1st place will earn 50 points <br>
	2nd place will earn 45 points <br>
	3rd place will earn 38 points <br>
	4rd place will earn 32 points <br>
	5th place will earn 30 points <br>
	6th place will earn 28 points <br>
	7th place will earn 26 <br>
	8th - 24 <br>
	9th - 22  <br>
	10 - 20 <br>
	11 to 16 - 10 <br>
	17 to 22 - 5 points <br>
	
<br>
	DNFs get 0 points <br>
<br>

	There will be extra points:<br>
	If one of your drivers gets the fastest lap <br>
	
	Each of the extra instances will award 5 extra points each. <br>
	Each Constructor will earn fantasy points according to this formula (2 * (amount of F1 points the team earned last event))<br>
<!--
	<br>
	For guessing the number of drivers who retired during the race there will be 5 extra points and for guessing the drivers who retired there will be one extra point.

	<br>

	Example: You entered that for the Austing Grand Prix there are 5 DNFs and one of them is Antonelli. And during the race Perez, Stoll, Bottas, Alonso, and Antonelli DNfd then you will be awarded 6 extra points.
-->
	<h3> Draft </h3>
	<p> To put together your team you will have $1,000 to spend between teams and drivers. The cost of the teams and the drivers is determined by their position in the drivers' and constructors' current standings.

	<h3> Reminders </h3>

	<p> Every Monday after a race weekend we will send you a reminder to check your results to see how much points you scored. <br>
	Also, every Friday of a race week we will send a reminder to review your team and make changes before the races. </p>
<!--
	<h3> Sprint Weekends </h3>
	<p> For Sprint weekends we will have a different scoring which will be that the driver who finishes 1st will get 22 points, 2nd: 21, and so on until the 22nd driver will get 1. DNFs will get 0 points.</p>
-->
</body>

</html>



