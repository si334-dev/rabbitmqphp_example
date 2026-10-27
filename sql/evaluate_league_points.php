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
	$data = getJson("http://api.jolpi.ca/ergast/f1/current/driverstandings/?limit=100");
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
		$power = round($percentage * 3000);


		$st = $db->prepare("
		INSERT INTO DriverStats (api_driver_id, currentPlacement, currentPoints,  currentDraftPower) 
		VALUES (?, ?, ?, ?) AS new 
		ON DUPLICATE KEY UPDATE 
		currentPlacement = new.currentPlacement, currentPoints = new.currentPoints, currentDraftPower = new.currentDraftPower 
		");
		$st->bind_param("ssii", $id, $currentPlacement, $currentPoints, $power);
		$st->execute();
	}

	
	$data = getJson("http://api.jolpi.ca/ergast/f1/current/constructorstandings/?limit=100");
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
		$power = round($percentage * 2000);


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
	$data = getJson("http://api.jolpi.ca/ergast/f1/2026/last/results/?limit=100");
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


function evalPoints(){

	global $db;
	$st = $db->query("SELECT * FROM UserTeams");
	$teamList = $st->fetch_all(MYSQLI_ASSOC);

	foreach($teamList as $team){
	
		$id = $team['id'];

		$st = $db->query("
			SELECT DriverStats.lastRaceResult, DriverStats.fastestLapRank FROM DriverStats
			JOIN Drivers ON DriverStats.api_driver_id = Drivers.api_driver_id
			JOIN TeamDrivers ON Drivers.id = TeamDrivers.driver_id
			WHERE TeamDrivers.team_id = $id
				  
				");
		$placementList = $st->fetch_all(MYSQLI_ASSOC);
		$pointsToAdd = 0;

		foreach($placementList as $result){
			
			switch ((int)$result['lastRaceResult']){
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
			if((int)$result['fastestLapRank'] === 1){$pointsToAdd += 5; }
		}

		$st = $db->query("
			SELECT constructorStats.combinedDriverPoints FROM constructorStats
			JOIN Constructors ON constructorStats.api_constructor_id = Constructors.api_constructor_id
			JOIN TeamConstructors ON Constructors.id = TeamConstructors.constructor_id
			WHERE TeamConstructors.team_id = $id
			ORDER BY constructorStats.combinedDriverPoints DESC
				  
				");
		$placementList = $st->fetch_all(MYSQLI_ASSOC);
		
		foreach($placementList as $result){

			//this makes it so the amount of points a person gets in the the game is tied to the combined
			//points that a teams drivers earned divided by 2, change later for better system
			$pointsToAdd += round ((int)$result['combinedDriverPoints'] * 2);
			
		}

		
		$st = $db->query("UPDATE UserTeams SET currentPoints = currentPoints + $pointsToAdd WHERE UserTeams.id = $id"); 
		//should probably check if record has been updated this week to not readd points mayber later
		
	

	}



}



//INSERT POINTS PER TEAM
