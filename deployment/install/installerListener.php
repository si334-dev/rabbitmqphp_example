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

function recieveInstall($bundleFile, $machine, $cluster){


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
      return recieveInstall($request['bundle'], $request['machine'], $request['cluster']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini",$queue);

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

