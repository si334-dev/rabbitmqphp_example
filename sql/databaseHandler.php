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

function createLeague($name, $desc, $token){
	global $db;
	//This preparation is to get user details from their session_token, REUSE THIS IF YOU NEED IT, prob should be a function
	$st = $db->prepare("
	SELECT Users.id, Users.email, Users.username FROM Users
	JOIN Sessions
	ON Users.id = Sessions.user_id
	WHERE Sessions.token = ?

	");	
		
	$st->bind_param("s", $token);
	$st->execute();
	$res = $st->get_result();
	$fetchUserDetails = $res->fetch_assoc();


	if(empty($fetchUserDetails)){
		return array("returnCode" => "0", "message" => "This session is not valid");
	}

	$userDetails = [$fetchUserDetails['id'], $fetchUserDetails['username'], $fetchUserDetails['email']];
	$user_id = $userDetails[0];
	$username = $userDetails[1];
	$email = $userDetails[2];

	$st->close();//i think this is needed when switching to prepare a different statement
	$st = $db->prepare("INSERT INTO Leagues (name, description, url_invite_code) VALUES (?, ?, ?)");
	$urlCode = uniqid();	
	$st->bind_param("sss", $name, $desc, $urlCode);
	if(!$st->execute()){ return array("returnCode" => "0", "message" => "Error creating league, please try again");}

	$leagueID = $db->insert_id;

	$isOwner = 1;
	$db->query("INSERT INTO LeagueUsers (league_id, user_id, isOwner) VALUES ($leagueID, $user_id, $isOwner)");
	
	return array("returnCode" => "1", "message" => "League successfully created", "leagueID" => $leagueID);
	
}

function joinLeague($code, $token){
	global $db;

	$st = $db->prepare("
	SELECT Users.id, Users.email, Users.username FROM Users
	JOIN Sessions
	ON Users.id = Sessions.user_id
	WHERE Sessions.token = ?

	");	
		
	$st->bind_param("s", $token);
	$st->execute();
	$res = $st->get_result();
	$fetchUserDetails = $res->fetch_assoc();


	if(empty($fetchUserDetails)){
		return array("returnCode" => "0", "message" => "This session is not valid");
	}
	
	
	$userDetails = [$fetchUserDetails['id'], $fetchUserDetails['username'], $fetchUserDetails['email']];
	$user_id = $userDetails[0];
		
	$st->close();	
	$st = $db->prepare("SELECT * FROM Leagues WHERE url_invite_code = ?");
	$st->bind_param("s", $code);
	$st->execute();
	$res = $st->get_result();
        $leagueInfo = $res->fetch_assoc();
	
	if(empty($leagueInfo)){
		return array("returnCode" => "0", "message" => "League invite code is incorrect");
	}
	
	$leagueID = $leagueInfo['id'];


	$st->close();	
	$st = $db->prepare("SELECT * FROM LeagueUsers WHERE league_id = ? AND user_id = ?");
	$st->bind_param("ii", $leagueID, $user_id);
	$st->execute();
	$res = $st->get_result();
        $leagueCheck = $res->fetch_assoc();
	
	if(!empty($leagueCheck)){
		return array("returnCode" => "0", "message" => "You are already part of this league");
	}

	$db->query("INSERT INTO LeagueUsers (league_id, user_id) VALUES ($leagueID, $user_id)");
	
	return array("returnCode" => "1", "message" => "League successfully joined", "leagueID" => $leagueID);
}

function getLeagueInfo($id, $token){
	
	global $db;

	$st = $db->prepare("
	SELECT Users.id, Users.email, Users.username FROM Users
	JOIN Sessions
	ON Users.id = Sessions.user_id
	WHERE Sessions.token = ?

	");	
		
	$st->bind_param("s", $token);
	$st->execute();
	$res = $st->get_result();
	$fetchUserDetails = $res->fetch_assoc();


	if(empty($fetchUserDetails)){
		return array("returnCode" => "0", "message" => "This session is not valid");
	}
	
	
	$userDetails = [$fetchUserDetails['id'], $fetchUserDetails['username'], $fetchUserDetails['email']];
	$user_id = $userDetails[0];


	$st->close();	
	$st = $db->prepare("
	SELECT Leagues.name, Leagues.description, Leagues.url_invite_code, LeagueUsers.isOwner FROM Leagues
	JOIN LeagueUsers 
	ON Leagues.id = LeagueUsers.league_id
	WHERE LeagueUsers.user_id = ? AND Leagues.id = ?
		");
	$st->bind_param("ii", $user_id, $id);
	$st->execute();
	$res = $st->get_result();
        $leagueInfo = $res->fetch_assoc();
	
	if(empty($leagueInfo)){
		return array("returnCode" => "0", "message" => "You are not a member of this league");
	}
	$st->close();	
	$st = $db->prepare("
	SELECT Users.username, Users.id FROM Users
	JOIN LeagueUsers 
	ON Users.id = LeagueUsers.user_id
	WHERE LeagueUsers.league_id = ?
		");
	$st->bind_param("i", $id);
	$st->execute();
	$res = $st->get_result();
        $leagueMembers = $res->fetch_all(MYSQLI_ASSOC);

	$st->close();	
	$st = $db->prepare("
	SELECT Users.username, Users.id, UserTeams.currentPoints FROM Users
	JOIN LeagueUsers 
	ON Users.id = LeagueUsers.user_id
	JOIN UserTeams
	ON LeagueUsers.user_id = UserTeams.user_id
	WHERE LeagueUsers.league_id = ? AND UserTeams.league_id = ?
	ORDER BY UserTeams.currentPoints DESC
		");
	$st->bind_param("ii", $id, $id);
	$st->execute();
	$res = $st->get_result();
        $leagueLeaderboard = $res->fetch_all(MYSQLI_ASSOC);
	
	return array("returnCode" => "1", "message" => "League info got successfully", "userDetails" => $userDetails, "leagueInfo" => $leagueInfo, "leagueMembers" => $leagueMembers, "leagueLeaderboard" => $leagueLeaderboard);

}



function getMyLeagues($token)
{
    global $db;

    // Find the user associated with this session token.
    $st = $db->prepare("
        SELECT user_id
        FROM Sessions
        WHERE token = ?
    ");
    $st->bind_param("s", $token);
    $st->execute();

    $result = $st->get_result();
    $user = $result->fetch_assoc();
    $st->close();

    if (empty($user)) {
        return [
            "returnCode" => "0",
            "message" => "Invalid session"
        ];
    }

    $userID = $user['user_id'];

    // Get all leagues this user belongs to.
   $st = $db->prepare("
    SELECT Leagues.id, Leagues.name, Leagues.description, LeagueUsers.isOwner,
           (SELECT COUNT(*) FROM LeagueUsers lu WHERE lu.league_id = Leagues.id) AS memberCount,
           UserTeams.name AS teamName
    FROM Leagues
    JOIN LeagueUsers ON Leagues.id = LeagueUsers.league_id
    LEFT JOIN UserTeams
           ON UserTeams.league_id = Leagues.id
          AND UserTeams.user_id = LeagueUsers.user_id
    WHERE LeagueUsers.user_id = ?
    ORDER BY Leagues.name
");

    $st->bind_param("i", $userID);
    $st->execute();

    $result = $st->get_result();
    $leagues = $result->fetch_all(MYSQLI_ASSOC);
    $st->close();

    return [
        "returnCode" => "1",
        "message" => "Leagues retrieved successfully",
        "leagues" => $leagues
    ];
}



function getProfileInfo($token){
	
	global $db;

	$st = $db->prepare("
	SELECT Users.id, Users.email, Users.username FROM Users
	JOIN Sessions
	ON Users.id = Sessions.user_id
	WHERE Sessions.token = ?

	");	
		
	$st->bind_param("s", $token);
	$st->execute();
	$res = $st->get_result();
	$fetchUserDetails = $res->fetch_assoc();


	if(empty($fetchUserDetails)){
		return array("returnCode" => "0", "message" => "This session is not valid");
	}
	
	
	$userDetails = [$fetchUserDetails['id'], $fetchUserDetails['username'], $fetchUserDetails['email']];
	$user_id = $userDetails[0];
	
	$st->close();	
	$st = $db->prepare("
	SELECT Leagues.name, Leagues.id FROM Leagues
	JOIN LeagueUsers
	ON Leagues.id = LeagueUsers.league_id
	WHERE LeagueUsers.user_id = ?
		");
	$st->bind_param("i", $user_id);
	$st->execute();
	$res = $st->get_result();
	$leagueList = $res->fetch_all(MYSQLI_ASSOC);

	
	return array("returnCode" => "1", "message" => "Profile info got successfully", "userDetails" => $userDetails, "leagueInfo" => $leagueList);
}

function getUserIdFromToken($token){
    global $db;
    $st = $db->prepare("SELECT user_id FROM Sessions WHERE token = ?");
    $st->bind_param("s", $token);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    return $row ? (int)$row['user_id'] : null;
}

function getOrCreateTeamId($user_id, $league_id){
    global $db;

    $st = $db->prepare("SELECT 1 FROM LeagueUsers WHERE league_id = ? AND user_id = ?");
    $st->bind_param("ii", $league_id, $user_id);
    $st->execute();
    $member = $st->get_result()->fetch_assoc();
    $st->close();
    if(empty($member)){
        return null;
    }

    $st = $db->prepare("SELECT id FROM UserTeams WHERE league_id = ? AND user_id = ?");
    $st->bind_param("ii", $league_id, $user_id);
    $st->execute();
    $team = $st->get_result()->fetch_assoc();
    $st->close();
    if(!empty($team)){
        return (int)$team['id'];
    }

    $st = $db->prepare("SELECT username FROM Users WHERE id = ?");
    $st->bind_param("i", $user_id);
    $st->execute();
    $u = $st->get_result()->fetch_assoc();
    $st->close();
    $name = ($u['username'] ?? 'My') . "'s team";

    $st = $db->prepare("INSERT INTO UserTeams (league_id, user_id, name) VALUES (?, ?, ?)");
    $st->bind_param("iis", $league_id, $user_id, $name);
    $st->execute();
    $id = $db->insert_id;
    $st->close();
    return (int)$id;
}

function getTeam($token, $league_id){
    global $db;

    $user_id = getUserIdFromToken($token);
    if($user_id === null){
        return array("returnCode" => "0", "message" => "This session is not valid");
    }

    $team_id = getOrCreateTeamId($user_id, (int)$league_id);
    if($team_id === null){
        return array("returnCode" => "0", "message" => "You are not a member of this league");
    }

    $st = $db->prepare("SELECT name, currentPoints FROM UserTeams WHERE id = ?");
    $st->bind_param("i", $team_id);
    $st->execute();
    $team = $st->get_result()->fetch_assoc();
    $st->close();

    $st = $db->prepare("
        SELECT Drivers.id, Drivers.givenName, Drivers.familyName
        FROM TeamDrivers JOIN Drivers ON Drivers.id = TeamDrivers.driver_id
        WHERE TeamDrivers.team_id = ?
        ORDER BY Drivers.familyName
    ");
    $st->bind_param("i", $team_id);
    $st->execute();
    $drivers = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();

    $st = $db->prepare("
        SELECT Constructors.id, Constructors.name
        FROM TeamConstructors JOIN Constructors ON Constructors.id = TeamConstructors.constructor_id
        WHERE TeamConstructors.team_id = ?
        ORDER BY Constructors.name
    ");
    $st->bind_param("i", $team_id);
    $st->execute();
    $constructors = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();

    $st = $db->prepare("
        SELECT id, givenName, familyName FROM Drivers
        WHERE id NOT IN (SELECT driver_id FROM TeamDrivers WHERE team_id = ?)
        ORDER BY familyName
    ");
    $st->bind_param("i", $team_id);
    $st->execute();
    $availableDrivers = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();

    $st = $db->prepare("
        SELECT id, name FROM Constructors
        WHERE id NOT IN (SELECT constructor_id FROM TeamConstructors WHERE team_id = ?)
        ORDER BY name
    ");
    $st->bind_param("i", $team_id);
    $st->execute();
    $availableConstructors = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();

    return array(
        "returnCode" => "1",
        "message" => "Team retrieved",
        "team" => $team,
        "drivers" => $drivers,
        "constructors" => $constructors,
        "availableDrivers" => $availableDrivers,
        "availableConstructors" => $availableConstructors
    );
}

function renameTeam($token, $league_id, $name){
    global $db;

    $user_id = getUserIdFromToken($token);
    if($user_id === null){
        return array("returnCode" => "0", "message" => "This session is not valid");
    }

    $name = trim($name);
    if($name === '' || strlen($name) > 100){
        return array("returnCode" => "0", "message" => "Team name must be 1-100 characters");
    }

    $team_id = getOrCreateTeamId($user_id, (int)$league_id);
    if($team_id === null){
        return array("returnCode" => "0", "message" => "You are not a member of this league");
    }

    $st = $db->prepare("UPDATE UserTeams SET name = ? WHERE id = ?");
    $st->bind_param("si", $name, $team_id);
    $st->execute();
    $st->close();

    return array("returnCode" => "1", "message" => "Team name updated");
}

function addTeamMember($token, $league_id, $kind, $item_id){
    global $db;

    if($kind === 'driver'){
        $table = 'TeamDrivers'; $col = 'driver_id'; $limit = 3;
    } elseif($kind === 'constructor'){
        $table = 'TeamConstructors'; $col = 'constructor_id'; $limit = 2;
    } else {
        return array("returnCode" => "0", "message" => "Invalid type");
    }

    $user_id = getUserIdFromToken($token);
    if($user_id === null){
        return array("returnCode" => "0", "message" => "This session is not valid");
    }

    $team_id = getOrCreateTeamId($user_id, (int)$league_id);
    if($team_id === null){
        return array("returnCode" => "0", "message" => "You are not a member of this league");
    }

    $st = $db->prepare("SELECT COUNT(*) AS c FROM $table WHERE team_id = ?");
    $st->bind_param("i", $team_id);
    $st->execute();
    $count = (int)$st->get_result()->fetch_assoc()['c'];
    $st->close();

    if($count >= $limit){
        return array("returnCode" => "0", "message" => "Your team is full ($limit {$kind}s max). Remove one first.");
    }

    try {
        $st = $db->prepare("INSERT INTO $table (team_id, $col) VALUES (?, ?)");
        $item = (int)$item_id;
        $st->bind_param("ii", $team_id, $item);
        $st->execute();
        $st->close();
    } catch (mysqli_sql_exception $e) {
        return array("returnCode" => "0", "message" => "Could not add that $kind (already on your team, or doesn't exist)");
    }

    return array("returnCode" => "1", "message" => ucfirst($kind) . " added");
}

function removeTeamMember($token, $league_id, $kind, $item_id){
    global $db;

    if($kind === 'driver'){
        $table = 'TeamDrivers'; $col = 'driver_id';
    } elseif($kind === 'constructor'){
        $table = 'TeamConstructors'; $col = 'constructor_id';
    } else {
        return array("returnCode" => "0", "message" => "Invalid type");
    }

    $user_id = getUserIdFromToken($token);
    if($user_id === null){
        return array("returnCode" => "0", "message" => "This session is not valid");
    }

    $team_id = getOrCreateTeamId($user_id, (int)$league_id);
    if($team_id === null){
        return array("returnCode" => "0", "message" => "You are not a member of this league");
    }

    $st = $db->prepare("DELETE FROM $table WHERE team_id = ? AND $col = ?");
    $item = (int)$item_id;
    $st->bind_param("ii", $team_id, $item);
    $st->execute();
    $removed = $st->affected_rows;
    $st->close();

    if($removed < 1){
        return array("returnCode" => "0", "message" => ucfirst($kind) . " wasn't on your team");
    }
    return array("returnCode" => "1", "message" => ucfirst($kind) . " removed");
}


function getEval($token, $league_id){

	global $db;

	$st = $db->prepare("
	SELECT Users.id, Users.email, Users.username FROM Users
	JOIN Sessions
	ON Users.id = Sessions.user_id
	WHERE Sessions.token = ?

	");	
		
	$st->bind_param("s", $token);
	$st->execute();
	$res = $st->get_result();
	$fetchUserDetails = $res->fetch_assoc();


	if(empty($fetchUserDetails)){
		return array("returnCode" => "0", "message" => "This session is not valid");
	}
	
	
	$userDetails = [$fetchUserDetails['id'], $fetchUserDetails['username'], $fetchUserDetails['email']];
	$user_id = $userDetails[0];
	
	$st->close();

	$st = $db->prepare("
	SELECT Drivers.familyName, Drivers.givenName, DriverStats.lastRaceResult, DriverStats.fastestLapRank FROM DriverStats
	JOIN Drivers ON Drivers.api_driver_id = DriverStats.api_driver_id
	JOIN TeamDrivers ON Drivers.id = TeamDrivers.driver_id 
	JOIN UserTeams ON UserTeams.id = TeamDrivers.team_id
	WHERE UserTeams.league_id = ? AND UserTeams.user_id = ? 

	");	
		
	$st->bind_param("ii", $league_id, $user_id);
	$st->execute();
	$res = $st->get_result();
	$drivers = $res->fetch_all(MYSQLI_ASSOC);;

	if(empty($drivers)){
		return array("returnCode" => "0", "message" => "You have no drivers");
	}
	$st->close();
	
	$st = $db->prepare("
	SELECT Constructors.name, constructorStats.combinedDriverPoints FROM constructorStats
	JOIN Constructors ON Constructors.api_constructor_id = constructorStats.api_constructor_id
	JOIN TeamConstructors ON Constructors.id = TeamConstructors.constructor_id 
	JOIN UserTeams ON UserTeams.id = TeamConstructors.team_id
	WHERE UserTeams.league_id = ? AND UserTeams.user_id = ? 

	");	
		
	$st->bind_param("ii", $league_id, $user_id);
	$st->execute();
	$res = $st->get_result();
	$constructors = $res->fetch_all(MYSQLI_ASSOC);
		
	if(empty($constructors)){
		return array("returnCode" => "0", "message" => "You have no constructors");
	}

	$st->close();
	$st = $db->prepare("SELECT name, currentPoints FROM UserTeams WHERE league_id = ? AND user_id = ?");
	$st->bind_param("ii", $league_id, $user_id);
	$st->execute();
	$res = $st->get_result();
	$teamInfo = $res->fetch_assoc();

	return array("returnCode" => "1", "message" => "Evaluation retrieved", 
       "teamInfo" => $teamInfo, "drivers" => $drivers, "constructors" => $constructors);


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
      return doValidate($request['session_key']);
    case "register":
      return doRegister($request['email'], $request['username'], $request['password']);
    case "createLeague":
      return createLeague($request['name'], $request['desc'], $request['token']);
    case "joinLeague":
      return joinLeague($request['joinCode'], $request['token']);
    case "getLeagueInfo":
      return getLeagueInfo($request['league_id'], $request['token']);

    case "getMyLeagues":
    	return getMyLeagues($request['token']);
    case "getProfileInfo":
      return getProfileInfo($request['token']);
    case "getTeam":
      return getTeam($request['token'], $request['league_id']);
    case "renameTeam":
      return renameTeam($request['token'], $request['league_id'], $request['name']);
    case "addTeamMember":
      return addTeamMember($request['token'], $request['league_id'], $request['kind'], $request['item_id']);
    case "removeTeamMember":
      return removeTeamMember($request['token'], $request['league_id'], $request['kind'], $request['item_id']);
    case "getEval":
      return getEval($request['token'], $request['league_id']);
  }

  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

