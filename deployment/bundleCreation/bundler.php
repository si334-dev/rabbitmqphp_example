#!/usr/bin/php
<?php
require_once('../rabbitStuff/path.inc');
require_once('../rabbitStuff/get_host_info.inc');
require_once('../rabbitStuff/rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$bundleFile = $argv[1]; //get json file name based on what the user enters



$request = array();
$request['type'] = "deploySend";
$request['tmpName'] = basename($tmpZip);
$request['bundleInfo'] = $bundleInfo;

$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

