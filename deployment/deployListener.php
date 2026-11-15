#!/usr/bin/php
<?php
require_once('rabbitStuff/path.inc');
require_once('rabbitStuff/get_host_info.inc');
require_once('rabbitStuff/rabbitMQLib.inc');

$db = new mysqli('127.0.0.1', "deployUser", "admin", "deploy");

function recieveDeployment($fileName, $bundleInfo)
{
	global $db;
	echo "$fileName";
	if(!file_exists("/home/vboxuser/bundles/" . $fileName)){
	return array("returnCode" => "0", "message" => "file not found on deployment VM");
	}
	$bundleName = $bundleInfo['bundleName'];
	$targetMachine = $bundleInfo['targetMachine'];
	
	//SELECT TO SEE IF BUNDLE EXSISTS, if so update version number to +1 and insert into database + update file name

	
	return array("returnCode" => "1", "message" => "retrieved");
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
    case "deploySend":
      return recieveDeployment($request['tmpName'], $request['bundleInfo']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

