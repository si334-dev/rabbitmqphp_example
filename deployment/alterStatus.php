#!/usr/bin/php
<?php
require_once('./rabbitStuff/path.inc');
require_once('./rabbitStuff/get_host_info.inc');
require_once('./rabbitStuff/rabbitMQLib.inc');

echo "Command format: command [bundleName] [version] [status]";
if($argc < 4 || $argc > 4){
die("please review script command format");
}
$bundle = $argv[1]; //bundle name
$ver = $argv[2];
$newStatus = strtolower($argv[3]);
$bundleFile = $bundle . $ver;

if (!($newStatus === "new" || $newStatus === "passed" || $newStatus === "failed")){
die("status must be new, passed, or failed");
}

if(!file_exists("/home/vboxuser/bundles/" . $bundleFile . ".zip")){
die('bundle file does not exist');
}

$db = new mysqli('127.0.0.1', "deployUser", "admin", "deploy");

$st = $db->prepare("UPDATE Bundles SET status = ? WHERE name = ? AND version = ?");
$st->bind_param("ssi", $newStatus, $bundle, $ver);
if(!$st->execute()){
die('db error');
}

echo "Successfully updated";
$st->close();

