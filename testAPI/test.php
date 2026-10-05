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

function ask($question){
	echo $question;
	return trim(fgets(STDIN));
}


echo " Test F1 API \n"; 
$name = ask("Enter a driver's name: ");

if ($name !== ""){
	$data = getJson("https://api.jolpi.ca/ergast/f1/current/driverstandings.json");
	$table = $data['MRData']['StandingsTable']['StandingsLists'][0] ?? null;

	if ($table === null){
		echo "Error, API not connecting";
	} else {
		$found = null;
		foreach ($table['DriverStandings'] as $row){
			$fullName = $row['Driver']['givenName'] . " " . $row['Driver']['familyName'];
			if (stripos($fullName, $name) !== false){
				$found = $row;
				break;
			}
		}

		if ($found === null){
			echo "No Driver Found. Not in " .$table['season'] . " F1 Season\n";
		} else {
			$driver = $found['Driver'];
			echo $driver['givenName'] .  " " . $driver['familyName'] . " #" . ($driver['permanentNumber'] ?? '?') . " - " . ($found['Constructors'][0]['name'] ?? '?') . "\n";
			echo "Drivers' Title Position: " . $found['position'] . " - " . $found['points'] . " points. Wins: " . $found['wins'] . " after " .$table['round'] . " race weekends. \n";

			$last = getJson("https://api.jolpi.ca/ergast/f1/current/last/results.json");
			$race = $last['MRData']['RaceTable']['Races'][0] ?? null;

			if ($race === null){
				echo "Last race not available \n";
				$gotResult = false;
				foreach($race['Results']as $r){
					if ($r['Driver']['driverId'] === $driver['driverId']){
						echo "P" . $r['position'];
						$gotResult = true;
					}
				}
				if(!$gotResult){
					echo "No driver found for this race/no resutl available";
				}
			}
		}
	}
}

/*

echo " Test Open Library API \n";
$title = ask("Enter a book title: ");

if ($title !== " "){
	$books = getJson("https://openlibrary.org/search.json?limit=1&q=" . urlencode($title));

	if($books === null){
		echo "fail open library api not available\n";
	}elseif (empty($books['docs'])){
		echo "$title not found in open library api\n";
	} else {
		$b = $books['docs'][0];
		echo ($b['title'] ?? '?') . "\n";
		echo "Author: " . ($b['author_name'][0]	?? '?') . "\n";
		echo "Date Published: " . ($b['first_publish_year'] ?? '?') . "\n";		
		echo "Editions: " . ($b['edition_count'] ?? '?') . "\n";
		echo "Total matches: " . ($books['numFound'] ?? '?' ) . "\n";
	}
}
	
*/

