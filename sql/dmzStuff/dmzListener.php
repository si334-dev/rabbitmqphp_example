#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('evaluate_league_points.php');
require_once('populateDriversAndConstructors.php');

function doEval($drivers, $constructors, $results)
{
	updateDraftPowerStats($drivers, $constructors);
	populateResultStats($results);
	evalPoints();
    return true;
}

function doPop($drivers, $constructors){
	doPopulate($drivers, $constructors);
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
    case "doEval":
      return doEval($request['drivers'],$request['constructors'], $request['results']);
    case "doPop":
      return doPop($request['drivers'],$request['constructors']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testDMZ");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

