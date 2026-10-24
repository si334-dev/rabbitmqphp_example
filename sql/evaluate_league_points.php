#!/usr/bin/php
<?php

//Change username(testUser) and password(admin) to other credential based
//on whoever is running the database. (or just make a new user)
$db = new mysqli('127.0.0.1', "testUser", "admin", "website_data");

//This code uses api results to condcut evaluations and update user points in the database. Most likly run every Monday
//to get team scores for weekend races.


function getJson($url){
	$options = ['http' => ['header' => "User-Agent: test-script\r\n"]];
	$context = stream_context_create($options);

	$text = @file_get_contents($url, false, $context);

	if($text === false){
	return null;
	}

	return json_decode($text, true);
}




function updateDraftPowerStats(){
	global $db;
	$data = getJson("http://api.jolpi.ca/ergast/f1/current/driverstandings/");
	$fullStandings = $data['MRData']['StandingsTable']['StandingsLists'][0]['DriverStandings'];
	$totalPoints = 0;

	foreach ($fullStandings as $standing){ 
		$totalPoints += $standing['points'];
	}

	foreach ($fullStandings as $standing){ 
		
		$id = $standing['Driver']['driverId'];
		$currentPlacement = $standing['positionText'];
		$currentPoints = $standing['points'];
		$percentage = $standing['points'] / $totalPoints;
		$power = round($percentage * 600);


		$st = $db->prepare("
		INSERT INTO DriverStats (api_driver_id, currentPlacement, currentPoints,  currentDraftPower) 
		VALUES (?, ?, ?, ?) AS new 
		ON DUPLICATE KEY UPDATE 
		currentPlacement = new.currentPlacement, currentPoints = new.currentPoints, currentDraftPower = new.currentDraftPower 
		");
		$st->bind_param("ssii", $id, $currentPlacement, $currentPoints, $power);
		$st->execute();
	}

	
	$data = getJson("http://api.jolpi.ca/ergast/f1/current/constructorstandings/");
	$fullStandings = $data['MRData']['StandingsTable']['StandingsLists'][0]['ConstructorStandings'];
	$totalPoints = 0;
	
	foreach ($fullStandings as $standing){ 
		$totalPoints += $standing['points'];
	}

	foreach ($fullStandings as $standing){ 
		
		$id = $standing['Constructor']['constructorId'];
		$percentage = $standing['points'] / $totalPoints;
		$currentPlacement = $standing['positionText'];
		$currentPoints = $standing['points'];
		$power = round($percentage * 400);


		$st = $db->prepare("	
		INSERT INTO constructorStats (api_constructor_id, currentPlacement, currentPoints, currentDraftPower) 
		VALUES (?, ?, ?, ?) AS new 
		ON DUPLICATE KEY UPDATE 
		currentPlacement = new.currentPlacement, currentPoints = new.currentPoints, currentDraftPower = new.currentDraftPower 
		");
		$st->bind_param("ssii", $id, $currentPlacement, $currentPoints, $power);
		$st->execute();
	}




}

function populateResultStats(){

	global $db;
	$data = getJson("http://api.jolpi.ca/ergast/f1/2026/last/results/");
	$lastRace = $data['MRData']['RaceTable']['Races'][0];
	$info = ['name' => $lastRace['raceName'], 'date' => $lastRace['date'], 'podium' => [], 'fastest' => null];
	$combinedPoints = [];

	foreach($lastRace['Results'] as $result){
		$pos = $result['positionText'];

		$id = $result['Driver']['driverId'];
		$points = $result['points'];
		$fastestLapRank = $result['FastestLap']['rank'] ?? null;
		
		$st = $db->prepare("	
		INSERT INTO DriverStats (api_driver_id, lastRaceResult, fastestLapRank) 
		VALUES (?, ?, ?) AS new 
		ON DUPLICATE KEY UPDATE 
		lastRaceResult = new.lastRaceResult, fastestLapRank = new.fastestLapRank
		");
		$st->bind_param("sss", $id, $pos, $fastestLapRank);
		$st->execute();

		$constructor = $result['Constructor']['constructorId'];

		if(array_key_exists($constructor, $combinedPoints)){
			$combinedPoints[$constructor] += $points;
		}else{
			$combinedPoints[$constructor] = $points;
		}
	}

	foreach($combinedPoints as $con => $points){
	
		
		$st = $db->prepare("	
		INSERT INTO constructorStats (api_constructor_id, combinedDriverPoints) 
		VALUES (?, ?) AS new 
		ON DUPLICATE KEY UPDATE 
		combinedDriverPoints = new.combinedDriverPoints
		");
		$st->bind_param("ss", $con, $points);
		$st->execute();
	}



	

}


function getEval(){

$data = getJson("http://api.jolpi.ca/ergast/f1/2026/drivers/");

}


$Evals = getEval($data);

//INSERT POINTS PER TEAM
