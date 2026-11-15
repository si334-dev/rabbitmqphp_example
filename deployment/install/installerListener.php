#!/usr/bin/php
<?php
require_once('rabbitStuff/path.inc');
require_once('rabbitStuff/get_host_info.inc');
require_once('rabbitStuff/rabbitMQLib.inc');

echo "Command format: command [cluster] [listeningMachine]";
if($argc < 3 || $argc > 3){
die("please review script command format");
}

$cluster =strtoupper($argv[1]); //intended target machine/queue for deployment
$target = $argv[2];
$queue = $cluster . $target;

function recieveInstall($bundleFile, $machine, $cluster, $version){
	
	global $queue;
	global $target;

	if(!($target === $machine)){
  	return array("returnCode" => '0', 'message'=>"this is intended for $machine and this is $target");
	}

	$rabbit_ip = parse_ini_file("./rabbitStuff/testRabbitMQ.ini", true)["$queue"]["BROKER_HOST"];

	$scp = ssh2_connect("$rabbit_ip", 22);
	if(!$scp){
  	return array("returnCode" => '0', 'message'=>"Failed to connect to remote");
	}
	if(!ssh2_auth_password($scp, 'vboxuser', 'admin')){	
 	 return array("returnCode" => '0', 'message'=>"auth failed from machine");
	}
	$remote = "bundles/" . $bundleFile;
	$localtmp = "/tmp/$bundleFile";
	if(!ssh2_scp_recv($scp, "$remote", "$localtmp")){
		return array("returnCode" => '0', 'message'=>"failed to copy from remote");
	}

	if(!file_exists("$localtmp")){
  		return array("returnCode" => '0', 'message'=>"file doesnt exsist here idk how");
	}
	$bundleName = pathinfo($bundleFile, PATHINFO_FILENAME);

	$zip = new ZipArchive;
	$zip->open("$localtmp");
	$zip->extractTo("/tmp/$bundleName");
	$zip->close();
	
	if(!file_exists("/tmp/$bundleName/$bundleName" . ".json")){
	 return array("returnCode" => '0', 'message'=>"json in directory does not match bundle name or it is missing");
	}	

	$jsonContents = file_get_contents("/tmp/$bundleName/$bundleName" . $version . ".json");
	$bundleInfo = json_decode($jsonContents, true);
	$commands = $bundleInfo["commands"];
	$filesToCopy = $bundleInfo['filesToCopy'];

	foreach($filesToCopy as $file){
		$src = "/tmp/$bundleName/" .  basename($file['source']);
		$dest = $file['destination'];
		copy($src, $dest);
	}

	foreach($commands as $command){
	shell_exec($command);
	}

	return array("returnCode" => '1', 'message'=>"$bundleFile $machine $cluster");
}


function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  //var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "install":
      return recieveInstall($request['bundle'], $request['machine'], $request['cluster'], $request['version']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini",$queue);

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

