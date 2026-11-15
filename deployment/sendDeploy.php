#!/usr/bin/php
<?php
require_once('./rabbitStuff/path.inc');
require_once('./rabbitStuff/get_host_info.inc');
require_once('./rabbitStuff/rabbitMQLib.inc');

echo "Command format: command [cluster] [targetMachine] [bundleName] [version]";
if($argc < 5 || $argc > 5){
die("please review script command format");
}
$cluster =strtoupper($argv[1]); //intended target machine/queue for deployment
$target = $argv[2];
$bundle = $argv[3]; //bundle name
if(isset($argv[4])){
	$ver = $argv[4]; //version num
	$bundleFile = $bundle . $ver;
}else{die('bundle version is required');}

$queue = $cluster . $target;
$client = new rabbitMQClient("testRabbitMQ.ini","$queue");

if(!file_exists("/home/vboxuser/bundles/" . $bundleFile . ".zip")){
die('bundle file does not exist');
}

$db = new mysqli('127.0.0.1', "deployUser", "admin", "deploy");

$st = $db->prepare("SELECT machine, status FROM Bundles WHERE name = ? AND version = ?");
$st->bind_param("si", $bundle, $ver);
if(!$st->execute()){
die('db error');
}

$res = $st->get_result();
$fetchBundleInfo = $res->fetch_assoc();

if(empty($fetchBundleInfo)){
die('bundle does not exsist in database');
}

$st->close();
$status = $fetchBundleInfo['status'];
$machine = $fetchBundleInfo['machine'];
if($status === "failed"){
die("this bundle is marked as failed");
}

if(!($target === $machine)){
	die("this bundle is marked as $machine not $target. Please change your target spcification");
}

if($status === "new" && $cluster === "PROD"){
die("this package is marked as new so it cannot be pushed to prod");
}

$request = array();
$request['type'] = "install";
$request['bundle'] = $bundleFile . ".zip";
$request['machine'] = $machine;

$response = $client->send_request($request);
//$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

