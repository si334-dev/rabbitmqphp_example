<?php

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

session_start();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
	header('Location: myLeagues.php');
	exit();
}

$session_key = $_COOKIE['session_key'] ?? ' ';

if ($session_key === ' '){
	header('Location: login.php');
	exit();
}

$league_id = (int)($_GET['league_id'] ?? 0);

if($league_id <= 0){
	header('Location: myLeagues.php');
	exit();
}

function backToDraft($message, $league_id){
	setcookie('message', $message, time() +60, '/');
	header('Location: draft.php?league_id=' . $league_id);
	exit();
}

$checkD = 'picked_drivers_' . $league_id;
$checkC = 'picked_constructors_' . $league_id;

$drivers = $_SESSION[$checkD] ?? [];
$constructors = $_SESSION[$checkC] ?? [];

if(count($drivers != 3) || count($constructors) != 2){
	backToDraft('3 Drivers and 2 Teams needed, review your team', $league_id);
}

if(count(array_unique($drivers)) != count($drivers) || count(array_unique($constructors)) != count($constructors)){
	backToDraft('Duplicate driver or team, review your team', $league_id);
}

foreach(array_merge($drivers, $constructors) as $rightId){
	if(!is_string($rightId) || $rightId === ' '){
		backToDraft('Invalid pick, review your team', $league_id);
	}
}

$request = ['type'=> 'draftTeam', 'token' => $session_key, 'league_id' => $league_id, 'drivers' => array_values($drivers), 'constructors' => array_values($constructors)];

try {
	$client = new rabbitMQClient('testRabbitMQ.ini', 'testServer');

	$response = $client->send_request($request);
} catch (Exception $e){
	backToDraft('Server unreachable, try again later', $league_id);
}

if(($response['returnCode'] ?? '0') == '1'){
	$_SESSION[$checkD] = [];
	$_SESSION[$checkC] = [];
	header('Location: team_management.php?league_id=' . $league_id . '&status=' . urlencode('Team Succesfully Saved'));
	exit();
}

backToDraft($response['message'] ?? 'Team Unable to save', $league_id);


?>

