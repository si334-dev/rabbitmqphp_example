<?php 

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('session.php');

$session_key = $_COOKIE['session_key'] ?? ' ';
if($session_key === ' '){
	header('Location: login.php');
	exit();
}


/* query database for draft power (driver stats and contructor stats)(combine with driver)
 * get the info from the database and display on the page
 * 
 * if more than 1,000 (divided in constrocors and drivers)
 *
 * allows to select
 *
 * check if it goes over 1000 if it does change/alert
 *
 *      draf.php to display info
 * draftrequest.php - >  new page
 *
 *
*
 database handler to send info to database (team_drivers/constructors linked through id number) 
 *
 * new 2 functions on databse handler (1 to get info to page and one to send the team built)
 *
 * frontend -> databaseHander -> backend -> handler -> frontend
 *
 * make function in database handler to get the info to request drivers, and then the part to send the information to rabbit. and display information mixed with draft power. 
 */


function callBackend($payload){

        try {
                $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
                return $client->send_request($payload);
        } catch (EXception $e){
                return null;
        }
}

$draftPower = 1000;

$data = callBackend(['type' => 'getDraftInfo', 'token' => $session_key]]);

$works = (($data['returnCode'] ?? '0') == '1');
$drivers =$data['drivers'] ?? [];
$constructors = $data['constructors'] ?? [];

?>

<!DOCTYPE html>
<html>

<head>
        <title> Draft </title>
</head>

<body>
<?php include('nav.php'); ?>

        <h2> Pick your team </h2>
        <p> You have 1000 power to create your team, with the 1000 you will choose 3 drivers and 2 teams. </p>

        <?php if(!$works){ ?>
        <p> <?php echo htmlspecialchars($data['message'] ?? "Data couldn't load"); ?> </p>
        <?php } else { ?>

        <h4>Drivers</h4>
        <table> 
                <tr><th>Driver</th><th>Draft Power Cost</th> </tr>
                <?php foreach($drivers as $d) { ?>
                        <tr>
                        <td> <?php echo htmlspecialchars($d['givenName'] . ' ' . $d['familyName']); ?> </td>
                        <td> <?php echo (int)$d['power']; ?> </td>
                        </tr>
                <?php } ?>
        </table>

        <h4>Constructors</h4>
        <table>
        <tr><th>Constructor</th><th>Draft Power Cost </th></tr>
        <?php foreach($constructors as $c) { ?>
                <tr>
                        <td> <?php echo htmlspecialchars($c['name']) ?></td>
                        <td> <?php echo (int)$c['power'] ?> </td>
                </tr>
        <?php } ?>
        </table>

        <?php } ?>
</body>
</html>

