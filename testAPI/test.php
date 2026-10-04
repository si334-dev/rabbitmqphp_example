<?php

function getJson($url){
	$options = ['http' => ['header' => "User-Agent: test-script\r\n"]];
	$context = stream_context_create($options);

	$text = @file_get_contents($url, false, $context);

	if($text === false){
	return null;
	}

	return json_decode($text, true);
}

echo " Test F1 API \n"; 

$drivers = getJson("https://api.openf1.org/v1/drivers?driver_number=1");

if ($drivers && count($drivers) > 0){
	$d = $drivers[0];
	echo "Driver: " . ($d['full_name'] ?? '?') . "\n";
	echo "Team: " . ($d['team_name'] ?? '?') . "\n";
} else {
	echo "Fail: no answer from F1 API";
}


echo " Test Open Library API \n";

$books = getJson("https://openlibrary.org/search.json?q=the+hobbit&limit=1");

if ($books && !empty($books['docs'])){
	$b = $books['docs'][0];
	echo "Title: " . ($b['title'] ?? '?') . "\n";
	echo "Author: " . ($b['author_name'][0] ?? '?') . "\n";
	echo "Published in: " . ($b['first_publish_year'] ?? '?') . "\n";
} else {
	echo "Fail: no answer from Open Library"
		;
}


