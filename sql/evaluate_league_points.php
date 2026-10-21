#!/usr/bin/php
<?php

//Change username(testUser) and password(admin) to other credential based
//on whoever is running the database. (or just make a new user)
$db = new mysqli('127.0.0.1', "testUser", "admin", "website_data");

//This code uses api results to condcut evaluations and update user points in the database.


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


function getEval($results){


}


$Evals = getEval($data);

//INSERT POINTS PER TEAM
