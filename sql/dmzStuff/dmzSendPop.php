#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('evaluate_league_points.php');

$client = new rabbitMQClient("testRabbitMQ.ini","testDMZ");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

$request = array();
$request['type'] = "doPop";
$dataD = getJson("http://api.jolpi.ca/ergast/f1/2026/drivers/?limit=100");
$request['drivers'] = $dataD;
$dataC = getJson("https://api.jolpi.ca/ergast/f1/current/constructorstandings/");
$request['constructors'] = $dataC;
$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

