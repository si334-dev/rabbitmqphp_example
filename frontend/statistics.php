
<?php 
/*start over statistics to get the right numbers 
 */

function getJson($url){
	$tempData = sys_get_temp_dir() . '/f1_' . md5($url) . '.json'; 

	if(!file_exists($tempData) || time() - filemtime($tempData) > 3600){
		$text = file_get_contents($url);
		file_put_contents($tempData, $text);
	}

	return json_decode(file_get_contents($tempData), true);
}

function podiumCount():array{
	$podiums = [];

	foreach([1, 2, 3] as $position){
		$url = "https://api.jolpi.ca/ergast/f1/2026/results/{$position}.json?limit=100";

		$data = getJson($url);

		foreach ($data['MRData']['RaceTable']['Races'] ?? [] as $race){
			$driver = $race['Results'][0]['Driver'];
			$name = $driver['givenName'] . ' ' . $driver['familyName'];
			$podiums[$name] = ($podiums[$name] ?? 0) +1; 
		}
	}

	arsort($podiums);
	return $podiums;
}

function top10Count():array{
	$count = [];

	$offset = 0;

	do{
		$data = getJson("https://api.jolpi.ca/ergast/f1/2026/results.json?limit=100&offset={&offset}")['MRData'];
		foreach($data['RaceTable']['Races'] as $race){
			foreach($race['Results'] as $result){
				if ($result['position'] > 10) continue;

				$name = $result['Driver']['givenName'] . ' '. $result['Driver']['familyName'];
				$count[$name] = ($count[$name] ?? 0) +1;
			}
		}

		$offset += 100;

	} while ($offset < $data['total']);

	asort($count);
	return $count;
}

function fastestLapCount(): array{
	$count = [];

	$data = getJson('https://api.jolpi.ca/ergast/f1/2026/fastest/1/results.json?limit=100');

	foreach($data ['MRData']['RaceTable']['Races'] ?? [] as $race){
		$driver = $race['Results'][0]['Driver'];
		$name = $driver['familyName'];
		$count[$name] = ($count[$name] ?? 0) +1;
	}

	arsort($count);
	return $count;
}

function lastRaceInfo(): array{
	$race = getJson('http://api.jolpi.ca/ergast/f1/2026/last/results.json?limit=100')['MRData']['RaceTable']['Races'][0] ?? null;

	$info = ['name' => $race['raceName'], 'date' => $race['date'], 'podium' => [], 'fastest' => null];

	foreach($race['Results'] as $result){
		$name =  $result['Driver']['familyName'];

		if($result['position'] <= 3){
			$info['podium'][] = $name . ' - ' . $result['Constructor']['name']; 
		}

		if (($result['FastestLap']['rank'] ?? ' ') === '1'){
			$info['fastest'] = ['driver' => $name, 'team' => $result['Constructor']['name'], 'time' => $result['FastestLap']['Time']['time']];
		}
	}

	return $info;
}
/* skipped for now because it makes the page unable to load due to time, might check later, if not delete
function dnfCount(){
	$dnfs = [];

	$notDNF = ['Finished', 'Lapped', 'Did not start', 'Disqualified'];

	$offset = 0;

	do{
		$data = getJson('https://api.jolpi.ca/ergast/f1/2026/results.json?limit=100&offset={$offset}')['MRData'];

		foreach($data['RaceTable']['Races'] as $race){
			foreach($race['Results'] as $result){
				$status = $result['status'];

				if(in_array($status, $notDNF) || str_starts_with($status, '+')) continue;

				$name = $result['Driver']['familyName'];
				$dnfs[$name] = ($dnfs[$name] ?? 0) +1;
			}
		}

		$offset = 100;
	} while($offset < $data['total']);

	arsort($dnfs);
	return $dnfs;
}
 */

$podiumNums = podiumCount();
$top10Nums = top10Count();
$fastestNums = fastestLapCount();
$lastRace = lastRaceInfo();
//$dnfsTotal = dnfCount();

?>

<!DOCTYPE html>

<html>

<head>
	<title> Statistics </title>
</head>

<body>
	<?php include ('nav.php'); ?>

	<h2> Last Race: <!-- Race Name here --><?php echo $lastRace['name']; ?> </h2>
	
	<h5> Podium </h5>
	<!-- last race podium 
		1 - Hamilton
		2 - Leclerc
		3 - Verstappen-->
	<ol>
	<?php foreach($lastRace['podium'] as $line){ ?>
		<li> <?php echo htmlspecialchars($line); ?> </li>
	<?php } ?>
	</ol>


	<h5> Fastest Lap </h5>
	<!-- Leclerc - Ferrari - 1:??? -->
	<p> <?php echo htmlspecialchars($lastRace['fastest']['driver'] . ' - ' . $lastRace['fastest']['team'] . ' - ' . $lastRace['fastest']['time']); ?> </p>

	<h2> Overall Season </h2>
	
	<h5> Podiums </h5>	
	<!-- how many podiums each driver has in the season
		Hamilton - 16
		LEclerc - 16
		Verstappen - 14
		Piastri - 10
		Albon - 7
		Perez - 5-->
	<ul><?php foreach($podiumNums as $driver => $count){ ?>
	  	<li> <?php echo htmlspecialchars($driver) . '- ' . $count; ?> </li>		
	<?php } ?>
	</ul>


	<h5> Top 10 Finishes </h5>
	<!-- how many times a driver has finished in the top 10, same format podiums -->
	<ul>
	<?php foreach($top10Nums as $driver => $count){ ?>
		<li> <?php echo htmlspecialchars($driver) . ' - ' . $count; ?>
	<?php } ?>
	</ul>

	<h5> DNFs </h5>
	<!-- how many times a driver has dnfd --->
	<ul>
	<?php foreach($dnfsTotal as $driver => $count){ ?>
		<li> <?php echo htmlspecialchars($driver) . ' - ' . $count; ?> </li>
	<?php } ?>
	</ul>

	<h5> Fastest Laps </h5>
	<!-- how many times a driver has achieved the fastest lap in a race -->
	<ul>
	<?php foreach($fastestNums as $driver => $count){ ?>
		<li> <?php echo htmlspecialchars($driver) . ' - ' . $count; ?> </li>
		<?php } ?>
		</ul>
	
	<h5> Sprint Races Podiums </h5>
	<!-- i don't think we need this, if i don't see them; they don't exists. ask about how it will work on evaluation, if needed add; if not remove->

</body>

</html>
