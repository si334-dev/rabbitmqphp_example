<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

//this code sets the user entered stuff they put into the form into variables to bbe sent to backend database
$request = array();
$request['type'] = "register";
$request['email'] = $_POST['email'];
$request['username'] = $_POST['username'];
$request['password'] = $_POST['password'];
$request['message'] = $msg;
$response = $client->send_request($request);
//$response = $client->publish($request);

if($response['returnCode'] == "1"){
	header()//set this to login page 

}else{

//some error message and send back to register
}

$payload = json_encode($response);
echo $payload;
