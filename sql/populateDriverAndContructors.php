#!/usr/bin/php
<?php

$db = new mysqli('127.0.0.1', "testUser", "admin", "website_data");

function getJson($url){
	$options = ['http' => ['header' => "User-Agent: test-script\r\n"]];
	$context = stream_context_create($options);

	$text = @file_get_contents($url, false, $context);

	if($text === false){
	return null;
	}

	return json_decode($text, true);
}


$data = getJson("http://api.jolpi.ca/ergast/f1/2026/drivers/");
//print_r($data);
$need = $data['MRData']['DriverTable']['Drivers'];

foreach($need as $driver){
$id = $driver["driverId"];
$name = $driver['givenName'];
$permNum = $driver['permanentNumber'] ?? null;
$wiki = $driver['url'] ?? '';
$familyName = $driver['familyName'];
$birth = $driver['dateOfBirth'] ?? null; 
$nat = $driver['nationality'] ?? '';

$st = $db->prepare("INSERT INTO Drivers (api_driver_id, wiki_url, permNum, givenName, familyName, dateOfBirth, nationality)
	VALUES (?, ?, ?, ?, ?, ?, ?)

	");
$st->bind_param("ssissss", $id, $wiki, $permNum, $name, $familyName, $birth, $nat);
$st->execute();

}


$data = getJson("https://api.jolpi.ca/ergast/f1/current/constructorstandings/");
$need = $data['MRData']['StandingsTable']['StandingsLists'][0]['ConstructorStandings'] ?? [];
//print_r($data);
foreach($need as $standing){
	
$constructor = $standing['Constructor'];

$id = $constructor["constructorId"] ?? '';
$name = $constructor['name'];
$wiki = $constructor['url'] ?? '';
$nat = $constructor['nationality'] ?? '';

$st = $db->prepare("INSERT INTO Constructors (api_constructor_id, wiki_url, name, nationality)
	VALUES (?, ?, ?, ?)

	");
$st->bind_param("ssss", $id, $wiki, $name, $nat);
$st->execute();


}

echo "successfuly populated database";
