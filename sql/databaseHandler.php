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
	$st = $db->prepare("SELECT id, email, username, password from Users WHERE Users.email = :email;");
	$st->execute(['email' => $email);
	$fetch = $st->fetch(PDO::FETCH_ASSOC);
	if(password_verify($fetch["password"], $password)){
		$token = bin2hex(random_bytes(32));
		$expiration = date("Y-m-d H:i:s", strtotime("+1 hour"));
		$user_id = $fetch['id'];

		$st = $db->prepare("INSERT INTO Sessions (user_id, token, expires) VALUES (:user_id, :token, :expires) ON DUPLICATE KEY UPDATE token = :token2, expires = :expires2;";);
		$st->execute(['user_id' => $user_id, 'token' => $token, 'expires' => $expiration, 'token2' => $token, 'expires2' => $expiration );
		$fetch = $st->fetch(PDO::FETCH_ASSOC);
		return array("status" => "1", "message" => "login successful", "session_token" => $token);
        }
	return array("status" => "0", "message" => "login unsuccessful");

	}

}

function doRegister($email, $username, $password){
	
	global $db;
	$st = $db->prepare("INSERT id, email, username, password from Users WHERE Users.email = :email;");
	$st->execute(['email' => $email);
	$fetch = $st->fetch(PDO::FETCH_ASSOC);
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
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

