<?php 

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('session.php');

session_start();

$session_key = $_COOKIE['session_key'] ?? ' ';
if($session_key === ' '){
	header('Location: login.php');
	exit();
}

$minimessage = ' ';
if(isset($_COOKIE['message']) && $_COOKIE['message'] !== ' '){
	$minimessage = $_COOKIE['message'];
	setcookie('message', ' ', time() - 3600, '/');
}

function callBackend($payload){

        try {
                $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
                return $client->send_request($payload);
        } catch (EXception $e){
                return null;
        }
}


$draftPower = 1000;

$data = callBackend(['type' => 'getDraftInfo', 'token' => $session_key]);

$works = (($data['returnCode'] ?? '0') == '1');
$drivers =$data['drivers'] ?? [];
$constructors = $data['constructors'] ?? [];

$league_id = ((int)$_GET['league_id'] ?? 0);
if($league_id <= 0){
	header('Location: myLeagues.php');
	exit();
}

$checkD = 'picked_drivers_' . $league_id;
$checkC = 'picked_constructors_' . $league_id;

if(!isset($_SESSION[$checkD])){
	$_SESSION[$checkD] = [];
}

if(!isset($_SESSION[$checkC])){
	$_SESSION[$checkC] = [];
}

$driverPower = [];
$conPower = [];

foreach($drivers as $d){
	$driverPower[$d['api_driver_id']] = $d['power'];
}

foreach($constructors as $c){
	$conPower[$c['api_constructor_id']] = $c['power'];
}

function totalPowerUSe($picked, $powerOf){
	$total= 0;

	foreach($picked as $id){
		$total += $powerOf[$id] ?? 0;
	}
	 return $total;
}

$message = ' ';



if($works && $_SERVER['REQUEST_METHOD'] === 'POST'){
	
	if(isset($_POST['add_driver'])){
		$id = $_POST['add_driver'];
		
		if (!is_String($id) || !isset($driverPower[$id])){
			$message = "Driver doesn't exist";
		} elseif (in_array($id, $_SESSION[$checkD])){
			$message = 'Driver already selected';
		} elseif (count($_SESSION[$checkD]) >= 3){
			$message = 'Driver limit of 3 reached. Click clear to start over or move forward';
		} elseif ($spent + $driverPower[$id] > $draftPower){
			$message = "You can't afford that driver";
		} else {
			$_SESSION[$checkD][] = $id;
		}
	}

	if(isset($_POST['add_constructor'])){
		$id = $_POST['add_constructor'];

		if(!is_string($id) || !isset($conPower[$id])){
			$message = "Constructor doesn't exist";
		} elseif (in_array($id, $_SESSION[$checkC])){
			$message = 'Constructor already selected';
		} elseif (count($_SESSION[$checkC] >= 2)){
			$message = "Constructor limit reached. Clear to start over or move forward";
		} elseif ($spent + $conPower[$id] > $draftPower){
			$message = "You can't afford that team";
		} else {
			$_SESSION[$checkC][] = $id;
		}
	}
	
	if(isset($_POST['clear'])){
		$_SESSION[$ckeckD] = [];
		$_SESSION[$checkC] = [];
	}
}

$spent = totalPowerUse($_SESSION[$checkD], $driverPower) + totalPowerUse($_SESSION[$checkC], $conPower);
$left = $draftPower - $spent;


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

	<?php if($message !== ' '){ ?>
	<p> <?php echo htmlspecialchars($message); ?> </p>
	<?php } ?>

	<?php if($minimessage !== ' '){ ?>
		<p> <?php echo htmlspecialchars($minimessage); ?> </p>
	<?php } ?>

        <?php if(!$works){ ?>
        <p> <?php echo htmlspecialchars($data['message'] ?? "Data couldn't load"); ?> </p>
        <?php } else { ?>

	<p> Power Used: <?php echo $spent; ?> / <?php echo $left; ?> left </p>

	<form method="post" action"draft.php?league_id=<?php echo $league_id; ?> ">

	<button type="submit" formaction="draftRequest.php?league_id=<?php echo $league_id; ?> "> Save Team </button>

	<button type="submit" name="clear"> Clear </button>

        <h4>Drivers</h4>
        <table> 
                <tr><th>Driver</th><th>Draft Power Cost</th> </tr>
		<?php foreach($drivers as $d) { ?>
			<?php $id = $d['api_driver_id']; ?>
                        <tr>
                        <td> <?php echo htmlspecialchars($d['givenName'] . ' ' . $d['familyName']); ?> </td>
			<td> <?php echo $d['power']; ?> </td>
			<td> <?php if(in_array($id, $_SESSION[$checkD])){ ?>
				<b> Selected </b>
				<?php } elseif($d['power'] > $left){ ?>
					Too Expensive
				<?php } else{ ?> <button type="submit" name="add_driver" value="<?php echo htmlspecialchars($id); ?>"> Select </button>
			     <?php } ?>  </td>
                        </tr>
                <?php } ?>
        </table>

        <h4>Constructors</h4>
        <table>
        <tr><th>Constructor</th><th>Draft Power Cost </th></tr>
	<?php foreach($constructors as $c) { ?>
		<?php $id = $c['api_constructor_id']; ?>
                <tr>
                        <td> <?php echo htmlspecialchars($c['name']) ?></td>
			<td> <?php echo $c['power'];?> </td>
			<td> <?php if(in_array($id, $_SESSION[checkC])){ ?>
				<b> Selected </b>
				<?php } elseif ($c['power'] > $left) { ?>
			       Too Expensive
			       <?php }else { ?> <button type="submit" name="add_constructor" value="<?php echo htmlspecialchars($id); ?>" > Select </button>
			<?php } ?> </td>	
                </tr>
        <?php } ?>
        </table>

	</form>
        <?php } ?>
</body>
</html>

