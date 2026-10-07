#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

//Change username(testUser) and password(admin) to other credential based
//on whoever is running the database. (or just make a new user)
$db = new mysqli('127.0.0.1', "testUser", "admin", "website_data");

function doLogin($email, $password)
{
	global $db;
	$st = $db->prepare("SELECT id, email, username, password from Users WHERE Users.email = ?");
	$st->bind_param("s", $email);
	$st->execute();
	$res = $st->get_result();
	$fetch = $res->fetch_assoc();

	if(empty($fetch)){
		return array("status" => "0", "message" => "login unsuccessful");
	}
	if(password_verify($password, $fetch["password"])){
		$token = bin2hex(random_bytes(32));
		$expiration = date("Y-m-d H:i:s", strtotime("+1 hour"));
		$user_id = $fetch['id'];

		$st = $db->prepare("INSERT INTO Sessions (user_id, token, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = ?, expires = ?");
		$st->bind_param("issss", $user_id, $token, $expiration, $token, $expiration);
		$st->execute();
		return array("returnCode" => "1", "message" => "login successful", "session_token" => $token);
        }
	return array("returnCode" => "0", "message" => "login unsuccessful");

	}



function doRegister($email, $username, $password){
	
	global $db;
	
	$st = $db->prepare("SELECT email, username FROM Users WHERE Users.email = ? OR Users.username = ?");
	$st->bind_param("ss", $email, $username);
	if($st->execute()){
	$res = $st->get_result();
	$fetch = $res->fetch_assoc();

	if(!empty($fetch)){
	 
		return array("returnCode" => "0", "message" => "registration unsuccessful: duplicate username or email");
	}


	$hash = password_hash($password, PASSWORD_BCRYPT);
	$st = $db->prepare("INSERT INTO Users (email, username, password) VALUES (?, ?, ?)");
	$st->bind_param("sss", $email, $username, $hash);
	$st->execute();
	//$res = $st->get_result();
	//$fetch = $res->fetch_assoc();
	 
	return array("returnCode" => "1", "message" => "registration successful");
	}
	
	return array("returnCode" => "0", "message" => "registration unsuccessful: duplicate username or email");
	
}

//need to update later to hand expiring sessions
function doValidate($token){
	global $db;

	$st = $db->prepare("SELECT * FROM Sessions WHERE Sessions.token = ?");
	$st->bind_param("s", $token);
	$st->execute();
	$res = $st->get_result();
        $fetch = $res->fetch_assoc();
	
	if(!empty($fetch)){
		return array("returnCode" => "1", "message" => "valid session");
	}
	
	return array("returnCode" => "0", "message" => "invalid session");
	
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['email'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
    case "register":
      return doRegister($request['email'], $request['username'], $request['password']);
  }

  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

