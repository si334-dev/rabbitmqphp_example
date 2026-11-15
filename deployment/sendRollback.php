#!/usr/bin/php
<?php
require_once('./rabbitStuff/path.inc');
require_once('./rabbitStuff/get_host_info.inc');
require_once('./rabbitStuff/rabbitMQLib.inc');

echo "Command format: command [cluster] [targetMachine] [bundleName]";
if($argc < 4 || $argc > 4){
die("please review script command format");
}
$cluster =strtoupper($argv[1]); //intended target machine/queue for deployment
$target = $argv[2];
$bundle = $argv[3]; //bundle name

$queue = $cluster . $target;
$client = new rabbitMQClient("testRabbitMQ.ini","$queue");


$db = new mysqli('127.0.0.1', "deployUser", "admin", "deploy");
$passed = "passed";
$st = $db->prepare("SELECT machine, version, status FROM Bundles WHERE name = ? AND status = ? ORDER BY version DESC LIMIT 1");
$st->bind_param("ss", $bundle, $passed);
if(!$st->execute()){
die('db error');
}

$res = $st->get_result();
$fetchBundleInfo = $res->fetch_assoc();

if(empty($fetchBundleInfo)){
die('bundle does not exsist in database');
}

$st->close();
$machine = $fetchBundleInfo['machine'];
$ver = $fetchBundleInfo['version'];

if(!file_exists("/home/vboxuser/bundles/" . $bundle . $ver . ".zip")){
die('bundle file does not exist');
}

if(!($target === $machine)){
	die("this bundle is marked as $machine not $target. Please change your target spcification");
}



$request = array();
$request['type'] = "install";
$request['bundle'] = $bundle . $ver . ".zip";
$request['machine'] = $machine;
$request['cluster'] = $cluster;
$request['version'] = $ver;
$request ['name'] = $bundle;

$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

